<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Sends a daily-check photo to Claude (vision) and returns a structured opinion:
 * health status, contamination type, growth stage and an estimate of the days left until harvest.
 *
 * It is a second pair of eyes, not a replacement for the worker: callers decide what to do with the answer.
 */
class PhotoAnalyzer
{
    public const STATUSES      = ['healthy', 'attention', 'contaminated', 'unclear'];
    public const CONTAMINATION = ['none', 'green_mold', 'black_mold', 'orange_pink_mold', 'cobweb_mold', 'bacterial', 'pests', 'other'];
    public const CONFIDENCE    = ['low', 'medium', 'high'];
    public const STAGES        = ['colonizing', 'pinning', 'young_fruiting', 'mature_fruiting', 'past_peak', 'unknown'];

    public const CONTAMINATION_LABELS = [
        'none' => 'None', 'green_mold' => 'Green mold', 'black_mold' => 'Black mold', 'orange_pink_mold' => 'Orange / pink mold',
        'cobweb_mold' => 'Cobweb mold', 'bacterial' => 'Bacterial rot', 'pests' => 'Pests', 'other' => 'Other contamination',
    ];

    public const STAGE_LABELS = [
        'colonizing' => 'Still colonizing', 'pinning' => 'Pinning', 'young_fruiting' => 'Young fruiting',
        'mature_fruiting' => 'Mature fruiting', 'past_peak' => 'Past peak', 'unknown' => 'Unknown',
    ];

    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';
    private const MAX_EDGE = 1568;   // longest side sent to the model, in pixels

    private const SYSTEM = <<<'TXT'
You are the crop-health assistant of MushFungAI, a small oyster mushroom (Pleurotus) farm in the Philippines.
Each day a worker photographs a batch of fruiting bags. Look at the photo and call report_photo_analysis exactly once.

What is NORMAL (never call these contamination): white, fluffy or cottony mycelium; slightly yellowish water droplets on mycelium; tiny pins (primordia); oyster-shaped caps in white, grey, beige, pink or yellow.

Signs of contamination: green powdery patches (Trichoderma); black or dark grey patches or powder; orange or pink powder; grey wispy cobweb mold that spreads fast over the substrate (do not confuse with dense white mycelium); yellow or brown slimy, wet patches (bacterial); flies, gnats, mites or larvae.

status: "contaminated" only if you can actually see signs of it; "attention" for ambiguous or non-contamination problems (discoloration, dry or cracked caps, too wet, leggy stems, dying pins); "healthy" otherwise; "unclear" if the photo is too dark, blurry, too far away, or does not show mushroom bags.
confidence: be honest. Use "low" when unsure.
growth_stage and days_to_harvest: oyster mushrooms are usually ready about 3 to 7 days after pins appear, and are best picked while the cap edges still curl slightly inward. 0 means ready to pick today. Only give days_to_harvest when you can see the stage clearly; leave it out when the bags are still colonizing or the photo is unclear. Do not invent precision.
summary: one or two short, plain sentences for the worker: what you see and what to do next. Maximum 250 characters.
TXT;

    public static function enabled(): bool
    {
        if (!config('mushroomtrack.ai.enabled')) return false;
        return filled(config('mushroomtrack.ai.api_key')) || filled(config('mushroomtrack.ai.gemini_api_key'));
    }

    /**
     * @param  array{batch?:string,today?:string,planned_harvest?:?string,previous?:?string}  $context
     * @return array{status:string,contamination:string,confidence:string,stage:string,days_to_harvest:?int,summary:string}
     *
     * @throws RuntimeException when the photo cannot be reviewed (callers should keep going without AI)
     */
    public function analyze(string $path, array $context = []): array
    {
        [$data, $mediaType] = $this->prepare($path);

        $note = implode("\n", [
            'Batch: '.($context['batch'] ?? 'unknown'),
            'Today: '.($context['today'] ?? now()->toDateString()),
            'Planned harvest date: '.($context['planned_harvest'] ?? 'not set'),
            'Previous AI review: '.($context['previous'] ?? 'none'),
            'Review the photo and return the requested structured analysis.',
        ]);

        if (filled(config('mushroomtrack.ai.gemini_api_key'))) {
            return $this->analyzeWithGemini($data, $mediaType, $note);
        }

        return $this->analyzeWithAnthropic($data, $mediaType, $note);
    }

    private function analyzeWithGemini(string $data, string $mediaType, string $note): array
    {
        $key = config('mushroomtrack.ai.gemini_api_key');
        $primaryModel = config('mushroomtrack.ai.model', 'gemini-3.5-flash');
        if (!str_contains($primaryModel, 'gemini')) $primaryModel = 'gemini-3.5-flash';

        $candidateModels = array_values(array_unique([$primaryModel, 'gemini-3.5-flash', 'gemini-3-flash-preview', 'gemini-3.5-flash-lite']));
        $lastError = null;

        foreach ($candidateModels as $model) {
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

            $response = Http::acceptJson()->asJson()
                ->connectTimeout(8)->timeout((int) config('mushroomtrack.ai.timeout', 40))
                ->post($endpoint, [
                    'system_instruction' => ['parts' => [['text' => self::SYSTEM]]],
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['inline_data' => ['mime_type' => $mediaType, 'data' => $data]],
                                ['text' => $note]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'response_mime_type' => 'application/json',
                        'response_schema' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'status' => ['type' => 'STRING', 'enum' => self::STATUSES],
                                'contamination_type' => ['type' => 'STRING', 'enum' => self::CONTAMINATION],
                                'confidence' => ['type' => 'STRING', 'enum' => self::CONFIDENCE],
                                'growth_stage' => ['type' => 'STRING', 'enum' => self::STAGES],
                                'days_to_harvest' => ['type' => 'INTEGER'],
                                'summary' => ['type' => 'STRING'],
                            ],
                            'required' => ['status', 'contamination_type', 'confidence', 'growth_stage', 'summary'],
                        ],
                    ]
                ]);

            if ($response->successful()) {
                $jsonStr = $response->json('candidates.0.content.parts.0.text');
                if ($jsonStr) {
                    $decoded = json_decode($jsonStr, true);
                    if (is_array($decoded)) {
                        return $this->clean($decoded);
                    }
                }
            }

            Log::warning("MushFungAI Gemini review on {$model} returned {$response->status()}", [
                'body' => Str::limit($response->body(), 300)
            ]);
            $lastError = "AI service error (HTTP {$response->status()})";
        }

        throw new RuntimeException($lastError ?? 'AI service temporarily unavailable. Please tap Re-Scan Photo to try again.');
    }

    private function analyzeWithAnthropic(string $data, string $mediaType, string $note): array
    {
        $response = Http::withHeaders([
                'x-api-key'         => (string) config('mushroomtrack.ai.api_key'),
                'anthropic-version' => '2023-06-01',
            ])
            ->acceptJson()->asJson()
            ->connectTimeout(8)->timeout((int) config('mushroomtrack.ai.timeout', 40))
            ->post(self::ENDPOINT, [
                'model'       => (string) config('mushroomtrack.ai.model'),
                'max_tokens'  => 700,
                'system'      => self::SYSTEM,
                'tools'       => [$this->tool()],
                'tool_choice' => ['type' => 'tool', 'name' => 'report_photo_analysis'],
                'messages'    => [[
                    'role'    => 'user',
                    'content' => [
                        ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mediaType, 'data' => $data]],
                        ['type' => 'text', 'text' => $note],
                    ],
                ]],
            ]);

        if ($response->failed()) {
            Log::warning('MushFungAI photo review failed', ['status' => $response->status(), 'body' => Str::limit($response->body(), 500)]);
            throw new RuntimeException('AI service error (HTTP '.$response->status().')');
        }

        foreach ((array) $response->json('content', []) as $block) {
            if (($block['type'] ?? null) === 'tool_use' && ($block['name'] ?? null) === 'report_photo_analysis' && is_array($block['input'] ?? null)) {
                return $this->clean($block['input']);
            }
        }

        throw new RuntimeException('AI returned no usable answer.');
    }

    /** Keep only values we understand, so a surprising answer can never break the page. */
    private function clean(array $in): array
    {
        $pick = fn ($v, array $allowed, string $default) => in_array($v, $allowed, true) ? $v : $default;

        $status        = $pick($in['status'] ?? null, self::STATUSES, 'unclear');
        $stage         = $pick($in['growth_stage'] ?? null, self::STAGES, 'unknown');
        $contamination = $status === 'healthy' ? 'none' : $pick($in['contamination_type'] ?? null, self::CONTAMINATION, 'none');
        if ($status === 'contaminated' && $contamination === 'none') $contamination = 'other';

        $days = $in['days_to_harvest'] ?? null;
        $days = is_numeric($days) ? max(0, min(30, (int) round((float) $days))) : null;
        if ($status === 'unclear' || in_array($stage, ['unknown', 'colonizing'], true)) $days = null;
        if ($stage === 'past_peak') $days = 0;

        return [
            'status'          => $status,
            'contamination'   => $contamination,
            'confidence'      => $pick($in['confidence'] ?? null, self::CONFIDENCE, 'low'),
            'stage'           => $stage,
            'days_to_harvest' => $days,
            'summary'         => Str::limit(trim((string) ($in['summary'] ?? '')), 280, '...'),
        ];
    }

    private function tool(): array
    {
        return [
            'name'        => 'report_photo_analysis',
            'description' => 'Report what the daily-check photo of the mushroom batch shows.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'status'             => ['type' => 'string', 'enum' => self::STATUSES],
                    'contamination_type' => ['type' => 'string', 'enum' => self::CONTAMINATION, 'description' => 'Use "none" when status is healthy.'],
                    'confidence'         => ['type' => 'string', 'enum' => self::CONFIDENCE],
                    'growth_stage'       => ['type' => 'string', 'enum' => self::STAGES],
                    'days_to_harvest'    => ['type' => 'integer', 'minimum' => 0, 'maximum' => 30, 'description' => 'Estimated days until ready to harvest. Omit when it cannot be judged from the photo.'],
                    'summary'            => ['type' => 'string', 'description' => 'One or two short sentences for the worker, max 250 characters.'],
                ],
                'required' => ['status', 'contamination_type', 'confidence', 'growth_stage', 'summary'],
            ],
        ];
    }

    /**
     * Returns [base64, media type]. Big phone photos are shrunk with GD when it is available,
     * which is faster and cheaper; otherwise the original is sent if it is small enough.
     */
    private function prepare(string $path): array
    {
        if (! is_file($path)) throw new RuntimeException('Photo file not found.');

        $bytes     = (string) file_get_contents($path);
        $mime      = (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        $supported = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (function_exists('imagecreatefromstring') && ($img = @imagecreatefromstring($bytes)) !== false) {
            $w = imagesx($img);
            $h = imagesy($img);
            $tooBig = max($w, $h) > self::MAX_EDGE;

            if ($tooBig || strlen($bytes) > 1_500_000 || ! in_array($mime, $supported, true)) {
                if ($tooBig) {
                    $scale  = self::MAX_EDGE / max($w, $h);
                    $nw     = max(1, (int) round($w * $scale));
                    $nh     = max(1, (int) round($h * $scale));
                    $canvas = imagecreatetruecolor($nw, $nh);
                    imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
                    imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    $img = $canvas;
                }
                ob_start();
                imagejpeg($img, null, 85);

                return [base64_encode((string) ob_get_clean()), 'image/jpeg'];
            }
        }

        if (! in_array($mime, $supported, true)) throw new RuntimeException('This photo format cannot be reviewed by AI.');
        if (strlen($bytes) > 3_500_000) throw new RuntimeException('Photo is too large for AI review.');

        return [base64_encode($bytes), $mime];
    }
}
