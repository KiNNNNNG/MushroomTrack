<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Harvest;
use App\Models\Sale;
use App\Support\DateRange;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/**
 * Admin downloads (PDF / Excel) for Reports and Sales.
 * Requires: composer require dompdf/dompdf openspout/openspout
 */
class ExportController extends Controller
{
    private function params(Request $request, string $defaultRange): array
    {
        $v = $request->validate([
            'format' => ['required', 'in:pdf,xlsx'],
            'range'  => ['nullable', 'in:week,month,year,all'],
        ]);
        $range = $v['range'] ?? $defaultRange;

        return [$v['format'], $range, DateRange::from($range)];
    }

    /** Batch health, bag losses and harvests. Defaults to all time. */
    public function reports(Request $request)
    {
        [$format, $range, $from] = $this->params($request, 'all');

        $batches  = Batch::orderBy('code')->get();
        $losses   = BagLoss::with('batch', 'user')->when($from, fn ($q) => $q->where('created_at', '>=', $from))->orderBy('created_at')->get();
        $harvests = Harvest::with('batch', 'product', 'user')->when($from, fn ($q) => $q->where('created_at', '>=', $from))->orderBy('created_at')->get();
        $byReason = $losses->groupBy('reason')->map(fn ($g) => (int) $g->sum('quantity'))->sortDesc();

        ActivityLog::record('Export', 'Reports '.strtoupper($format).' ('.$range.')');
        $file = 'mushfungai-reports-'.now()->format('Y-m-d');

        if ($format === 'pdf') {
            return $this->pdf('exports.reports', [
                'rangeLabel' => DateRange::label($range), 'allTime' => $range === 'all',
                'batches' => $batches, 'losses' => $losses, 'harvests' => $harvests, 'byReason' => $byReason,
            ], $file.'.pdf');
        }

        return $this->xlsx([
            'Batches' => [
                'headings' => ['Batch ID', 'Name', 'Status', 'Healthy bags', 'Lost bags', 'Health index %', 'Yield (kg)', 'Est. harvest'],
                'rows' => $batches->map(fn ($b) => [
                    $b->code, $b->name, ucfirst($b->status), (int) $b->bags, (int) $b->lost_bags, $b->health,
                    (float) $b->yield_kg, optional($b->harvest_date)->format('Y-m-d') ?? '',
                ])->all(),
            ],
            'Lost bags' => [
                'headings' => ['Date', 'Batch', 'Bags lost', 'Reason', 'Reported by', 'Notes'],
                'rows' => $losses->map(fn ($l) => [
                    $l->created_at->format('Y-m-d H:i'), $l->batch->code ?? '', (int) $l->quantity, $l->reason_label,
                    $l->user->name ?? 'System', (string) $l->notes,
                ])->all(),
            ],
            'Harvests' => [
                'headings' => ['Date', 'Batch', 'Store product', 'Weight (kg)', 'Bags cleared', 'Worker'],
                'rows' => $harvests->map(fn ($h) => [
                    $h->created_at->format('Y-m-d H:i'), $h->batch->code ?? '', $h->product->name ?? 'Not added to store',
                    (float) $h->weight_kg, (int) $h->bags_cleared, $h->user->name ?? '',
                ])->all(),
            ],
        ], $file.'.xlsx');
    }

    /** Completed sales (revenue ledger). Defaults to this month, like the Sales tab. */
    public function sales(Request $request)
    {
        [$format, $range, $from] = $this->params($request, 'month');

        $sales = Sale::with('product', 'order.user')->when($from, fn ($q) => $q->where('sold_at', '>=', $from))->orderBy('sold_at')->get();
        $totalKg = (float) $sales->sum('quantity_kg');
        $total   = (float) $sales->sum('amount');
        $byProduct = $sales->groupBy(fn ($s) => $s->product->name ?? 'Deleted product')
            ->map(fn ($g) => ['kg' => (float) $g->sum('quantity_kg'), 'amount' => (float) $g->sum('amount')])->sortByDesc('amount');

        ActivityLog::record('Export', 'Sales '.strtoupper($format).' ('.$range.')');
        $file = 'mushfungai-sales-'.now()->format('Y-m-d');

        if ($format === 'pdf') {
            return $this->pdf('exports.sales', [
                'rangeLabel' => DateRange::label($range), 'sales' => $sales,
                'totalKg' => $totalKg, 'total' => $total, 'byProduct' => $byProduct,
            ], $file.'.pdf');
        }

        return $this->xlsx([
            'Sales' => [
                'headings' => ['Date', 'Order', 'Customer', 'Product', 'Quantity (kg)', 'Amount (PHP)'],
                'rows' => $sales->map(fn ($s) => [
                    $s->sold_at->format('Y-m-d H:i'), $s->order?->display_code ?? '', $s->order?->user?->name ?? '',
                    $s->product->name ?? 'Deleted product', (float) $s->quantity_kg, (float) $s->amount,
                ])->all(),
                'footer' => ['', '', '', 'TOTAL', $totalKg, $total],
            ],
            'By product' => [
                'headings' => ['Product', 'Quantity (kg)', 'Amount (PHP)'],
                'rows' => $byProduct->map(fn ($v, $name) => [$name, $v['kg'], $v['amount']])->values()->all(),
            ],
        ], $file.'.xlsx');
    }

    private function pdf(string $view, array $data, string $filename)
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');   // has the peso sign (₱)
        $options->set('isRemoteEnabled', false);

        $pdf = new Dompdf($options);
        $pdf->loadHtml(view($view, $data)->render());
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** @param array<string, array{headings: array, rows: array, footer?: array}> $sheets */
    private function xlsx(array $sheets, string $filename)
    {
        $path = tempnam(sys_get_temp_dir(), 'mf');
        $bold = (new Style())->setFontBold();

        $writer = new XlsxWriter();
        $writer->openToFile($path);
        $first = true;
        foreach ($sheets as $title => $sheet) {
            if ($first) { $writer->getCurrentSheet()->setName($title); $first = false; }
            else { $writer->addNewSheetAndMakeItCurrent()->setName($title); }

            $writer->addRow(Row::fromValues($sheet['headings'], $bold));
            foreach ($sheet['rows'] as $row) $writer->addRow(Row::fromValues($row));
            if (isset($sheet['footer'])) $writer->addRow(Row::fromValues($sheet['footer'], $bold));
        }
        $writer->close();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
