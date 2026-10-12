<?php

use App\Mail\NoticeMail;
use App\Models\Batch;
use App\Models\MonitoringLog;
use App\Models\Notice;
use App\Models\NoticeDelivery;
use App\Models\User;
use App\Services\Reminders;
use App\Services\Sms;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

function anWorker(array $extra = []): User
{
    return User::factory()->create(['role' => 'worker', 'status' => 'active'] + $extra);
}

function anAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'status' => 'active']);
}

function anBatch(): Batch
{
    return Batch::create(['code' => 'AI-'.random_int(100, 999), 'name' => 'Oyster A', 'bags' => 10, 'lost_bags' => 0, 'yield_kg' => 0, 'status' => 'fruiting', 'harvest_date' => today()->addDays(14)]);
}

/** What Claude's tool_use answer looks like, so no real API call is made. */
function anFakeAi(array $input, int $status = 200): void
{
    Http::fake(['api.anthropic.com/*' => $status === 200
        ? Http::response(['content' => [['type' => 'tool_use', 'name' => 'report_photo_analysis', 'input' => $input]]], 200)
        : Http::response(['error' => 'overloaded'], $status)]);
}

function anSubmitCheck(User $w, Batch $b, array $extra = [])
{
    return test()->actingAs($w)->post(route('worker.monitoring.store', $b), $extra + [
        'watered' => 1, 'lost_bags' => 0, 'notes' => 'looks ok', 'photo' => UploadedFile::fake()->image('bag.jpg', 600, 400),
    ]);
}

beforeEach(function () {
    config([
        'mushroomtrack.ai.enabled' => true, 'mushroomtrack.ai.api_key' => 'sk-ant-test',
        'mushroomtrack.ai.auto_harvest_date' => true, 'mushroomtrack.ai.min_date_shift' => 2,
        'mushroomtrack.sms.driver' => 'log', 'mushroomtrack.notify.queue' => false,
    ]);
});

/* ---------------- AI photo review ---------------- */

it('lets the AI flag contamination, fill the result and warn the admins', function () {
    $admin = anAdmin(); $b = anBatch();
    anFakeAi(['status' => 'contaminated', 'contamination_type' => 'green_mold', 'confidence' => 'high', 'growth_stage' => 'young_fruiting', 'days_to_harvest' => 3, 'summary' => 'Green powdery patches on the bag.']);

    anSubmitCheck(anWorker(), $b)->assertRedirect(route('worker.history'));

    $log = MonitoringLog::first();
    expect($log->result)->toBe('contaminated')->and($log->ai_status)->toBe('contaminated')->and($log->ai_contamination)->toBe('green_mold')
        ->and($log->ai_confidence)->toBe('high')->and($log->ai_error)->toBeNull()->and($log->ai_analyzed_at)->not->toBeNull();
    expect(Notice::where('user_id', $admin->id)->where('title', 'Possible contamination')->exists())->toBeTrue();
    // a contaminated batch is not a good source for a harvest date
    expect($b->fresh()->harvest_date->toDateString())->toBe(today()->addDays(14)->toDateString());
});

it('moves the harvest date when the AI is confident about the stage', function () {
    $b = anBatch();
    anFakeAi(['status' => 'healthy', 'contamination_type' => 'none', 'confidence' => 'high', 'growth_stage' => 'young_fruiting', 'days_to_harvest' => 3, 'summary' => 'Healthy young caps.']);

    anSubmitCheck(anWorker(), $b);

    expect($b->fresh()->harvest_date->toDateString())->toBe(today()->addDays(3)->toDateString())
        ->and(MonitoringLog::first()->result)->toBe('healthy')->and(MonitoringLog::first()->ai_days_to_harvest)->toBe(3);
});

it('ignores a low-confidence estimate and a tiny date change', function () {
    $b = anBatch();
    anFakeAi(['status' => 'healthy', 'contamination_type' => 'none', 'confidence' => 'low', 'growth_stage' => 'pinning', 'days_to_harvest' => 5, 'summary' => 'Maybe pinning.']);
    anSubmitCheck(anWorker(), $b);
    expect($b->fresh()->harvest_date->toDateString())->toBe(today()->addDays(14)->toDateString());

    $b->update(['harvest_date' => today()->addDays(4)]);
    MonitoringLog::query()->delete();
    anFakeAi(['status' => 'healthy', 'contamination_type' => 'none', 'confidence' => 'high', 'growth_stage' => 'young_fruiting', 'days_to_harvest' => 5, 'summary' => 'Fine.']);
    anSubmitCheck(anWorker(), $b);
    expect($b->fresh()->harvest_date->toDateString())->toBe(today()->addDays(4)->toDateString());
});

it('never lets the AI make a worker-reported problem look healthy', function () {
    $b = anBatch();
    anFakeAi(['status' => 'healthy', 'contamination_type' => 'none', 'confidence' => 'high', 'growth_stage' => 'mature_fruiting', 'days_to_harvest' => 1, 'summary' => 'Looks fine.']);

    anSubmitCheck(anWorker(), $b, ['watered' => 0]);

    expect(MonitoringLog::first()->result)->toBe('attention');
});

it('keeps the daily check working when the AI is down', function () {
    $b = anBatch();
    anFakeAi([], 529);

    anSubmitCheck(anWorker(), $b)->assertRedirect(route('worker.history'))->assertSessionHas('ok');

    $log = MonitoringLog::first();
    expect($log->result)->toBe('healthy')->and($log->ai_status)->toBeNull()->and($log->ai_error)->not->toBeNull();
});

it('skips the AI quietly when there is no API key', function () {
    config(['mushroomtrack.ai.api_key' => null]);
    Http::fake();

    anSubmitCheck(anWorker(), anBatch())->assertRedirect(route('worker.history'));

    Http::assertNothingSent();
    expect(MonitoringLog::first()->ai_analyzed_at)->toBeNull();
});

/* ---------------- SMS helpers ---------------- */

it('turns every common way of writing a Philippine mobile number into 09XXXXXXXXX', function () {
    expect(Sms::normalize('+63 917 123 4567'))->toBe('09171234567')
        ->and(Sms::normalize('0917-123-4567'))->toBe('09171234567')
        ->and(Sms::normalize('639171234567'))->toBe('09171234567')
        ->and(Sms::normalize('9171234567'))->toBe('09171234567')
        ->and(Sms::normalize('(049) 562 1234'))->toBeNull()
        ->and(Sms::normalize(''))->toBeNull()
        ->and(Sms::normalize(null))->toBeNull();
});

it('writes plain-ASCII text messages so one SMS stays 160 characters', function () {
    $text = Sms::compose('Payment received', 'We got your ₱450 — thank you');

    expect($text)->toBe('MushFungAI: Payment received. We got your PHP 450 - thank you')->and(mb_strlen($text))->toBeLessThanOrEqual(300);
});

/* ---------------- Email + SMS delivery ---------------- */

it('sends email and SMS only through the channels the person switched on', function () {
    Mail::fake();
    $u = anWorker(['notify_email' => true, 'notify_sms' => true, 'phone' => '0917 123 4567']);

    Notice::send($u->id, 'Order confirmed', 'ORD-1 is confirmed.', '/shop/orders', true);

    Mail::assertSent(NoticeMail::class, fn ($m) => $m->hasTo($u->email));
    expect(NoticeDelivery::where('user_id', $u->id)->where('channel', 'sms')->where('status', 'sent')->count())->toBe(1)
        ->and(NoticeDelivery::where('user_id', $u->id)->where('channel', 'email')->where('status', 'sent')->count())->toBe(1);

    $off = anWorker(['notify_email' => false, 'notify_sms' => false, 'phone' => '09171234567']);
    Notice::send($off->id, 'Order confirmed', 'x', null, true);
    expect(NoticeDelivery::where('user_id', $off->id)->count())->toBe(0)->and(Notice::where('user_id', $off->id)->count())->toBe(1);
});

it('does not send anything outside the app unless the caller asks for it', function () {
    Mail::fake();
    $u = anWorker(['notify_email' => true, 'notify_sms' => true, 'phone' => '09171234567']);

    Notice::send($u->id, 'New order', 'bell only');

    Mail::assertNothingSent();
    expect(NoticeDelivery::count())->toBe(0);
});

it('still emails someone who switched the bell off, and records a skipped SMS for a bad number', function () {
    Mail::fake();
    $u = anWorker(['notify_in_app' => false, 'notify_email' => true, 'notify_sms' => true, 'phone' => '12345']);

    Notice::send($u->id, 'Order ready', 'Come get it', null, true);

    Mail::assertSent(NoticeMail::class);
    expect(Notice::where('user_id', $u->id)->count())->toBe(0)
        ->and(NoticeDelivery::where('user_id', $u->id)->where('channel', 'sms')->value('status'))->toBe('skipped');
});

it('does not text inactive accounts', function () {
    Mail::fake();
    $u = anWorker(['status' => 'inactive', 'notify_sms' => true, 'phone' => '09171234567']);

    Notice::send($u->id, 'Hello', 'x', null, true);

    expect(NoticeDelivery::count())->toBe(0);
    Mail::assertNothingSent();
});

it('only lets people switch SMS on when their profile has a Philippine mobile number', function () {
    $u = anWorker(['phone' => '(049) 562 1234']);

    $this->actingAs($u)->put(route('profile.notifications'), ['notify_in_app' => 1, 'notify_sms' => 1])->assertSessionHasErrors();
    expect($u->fresh()->notify_sms)->toBeFalse();

    $u->forceFill(['phone' => '0917 123 4567'])->save();
    $this->actingAs($u)->put(route('profile.notifications'), ['notify_in_app' => 1, 'notify_email' => 1, 'notify_sms' => 1])->assertSessionHasNoErrors();
    expect($u->fresh()->notify_sms)->toBeTrue()->and($u->fresh()->notify_email)->toBeTrue();
});

it('turns SMS off again when the phone number is changed to something invalid', function () {
    $u = anWorker(['phone' => '09171234567', 'notify_sms' => true]);

    $this->actingAs($u)->put(route('profile.update'), ['name' => $u->name, 'username' => $u->username ?? 'worker1', 'email' => $u->email, 'phone' => '555'])->assertSessionHasNoErrors();

    expect($u->fresh()->notify_sms)->toBeFalse();
});

/* ---------------- Daily reminders ---------------- */

it('sends one daily-check digest per worker and never repeats it the same day', function () {
    Mail::fake();
    $w = anWorker(['notify_sms' => true, 'phone' => '09171234567']);
    anBatch(); anBatch();

    expect(Reminders::forWorker($w, true))->toBe(1);
    Reminders::forWorker($w, true);
    Reminders::forWorker($w);

    expect(Notice::where('user_id', $w->id)->where('title', 'Daily check due')->count())->toBe(1)
        ->and(NoticeDelivery::where('user_id', $w->id)->where('channel', 'sms')->count())->toBe(1);
});

it('does not remind about batches that were already checked today', function () {
    $w = anWorker(); $b = anBatch();
    MonitoringLog::create(['batch_id' => $b->id, 'user_id' => $w->id, 'watered' => true, 'result' => 'healthy']);

    expect(Reminders::forWorker($w, true))->toBe(0);
});

it('runs the reminder command for every active worker', function () {
    Mail::fake();
    $a = anWorker(['notify_email' => true]); $inactive = anWorker(['status' => 'inactive']);
    anBatch();

    $this->artisan('mushroomtrack:remind')->assertSuccessful();

    expect(Notice::where('user_id', $a->id)->count())->toBe(1)->and(Notice::where('user_id', $inactive->id)->count())->toBe(0);
    Mail::assertSent(NoticeMail::class, 1);
});
