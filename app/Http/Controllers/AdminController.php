<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\DateRange;
use App\Support\Qr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
        public function dashboard(Request $request)
    {
        $range = $request->query('range', 'month');
        $from = DateRange::from($range);

        $salesQuery = Sale::query()->when($from, fn ($q) => $q->where('sold_at', '>=', $from));

        $sales = (clone $salesQuery)
            ->selectRaw('DATE(sold_at) as date, SUM(amount) as amount')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn ($s) => ['date' => $s->date, 'amount' => (float) $s->amount])->values();

        $activeBatches  = Batch::whereIn('status', ['incubating', 'fruiting'])->count();
        $totalBatches   = Batch::count();
        $harvestedYield = (float) Batch::sum('yield_kg');
        $availableStock = (float) Product::sum('stock_kg');

        return view('admin.dashboard', [
            'activeBatches'  => $activeBatches,
            'totalBatches'   => $totalBatches,
            'harvestedYield' => $harvestedYield,
            'availableStock' => $availableStock,
            'revenue'        => (Sale::sum('amount') ?? 0) - (Schema::hasTable('expenses') ? (Expense::sum('amount') ?? 0) : 0),
            'yield'          => $harvestedYield,
            'sales'          => $sales,
            'range'          => $range,
            'recentActivity' => ActivityLog::latest()->take(5)->get(),
        ]);
    }

    public function batches(Request $request)
    {
        $batchId = $request->query('batch');
        $selectedBatch = null;
        if ($batchId) {
            $selectedBatch = Batch::with(['monitoringLogs' => fn ($q) => $q->oldest(), 'losses'])->find($batchId);
        }

        $allBatches = Batch::with(['monitoringLogs' => fn ($q) => $q->latest()->limit(1)])->orderBy('code')->get();

        return view('admin.batches', [
            'batches'       => $allBatches,
            'activeBatches' => $allBatches->whereIn('status', ['incubating', 'fruiting']),
            'selectedBatch' => $selectedBatch,
            'tab'           => $request->query('tab', 'batches'),
        ]);
    }

    public function completedBatches(Request $request)
    {
        $batchId = $request->query('batch');
        $selectedBatch = null;
        if ($batchId) {
            $selectedBatch = Batch::with(['monitoringLogs' => fn ($q) => $q->oldest(), 'harvests'])->find($batchId);
        }
        return view('admin.completed', [
            'batches'       => Batch::where('status', 'harvested')->orderByDesc('updated_at')->get(),
            'selectedBatch' => $selectedBatch,
        ]);
    }

    public function store(Request $request)
    {
        $tab   = $request->query('tab', 'products');
        $range = $request->query('range', 'month');
        $month = $request->query('month', now()->month);
        $year  = $request->query('year',  now()->year);

        $from = DateRange::from($range);

        $hasExpenses = Schema::hasTable('expenses');
        $expensesQuery = $hasExpenses ? \App\Models\Expense::query()
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month) : null;

        $salesQuery = \App\Models\Sale::query()
            ->whereYear('sold_at', $year)
            ->whereMonth('sold_at', $month);

        return view('admin.store', [
            'tab'           => $tab,
            'range'         => $range,
            'month'         => (int) $month,
            'year'          => (int) $year,
            'products'      => Product::orderBy('id')->get(),
            'orders'        => $tab === 'orders'   ? Order::with('user', 'product', 'payment')->whereIn('status', ['pending', 'confirmed'])->latest()->get() : collect(),
            'history'       => $tab === 'history'  ? Order::with('user', 'product', 'payment')->whereIn('status', ['completed', 'cancelled'])->latest()->get() : collect(),
            'sales'         => $tab === 'sales'    ? $salesQuery->get() : collect(),
            'totalSales'    => $salesQuery->sum('amount'),
            'expenses'      => ($tab === 'expenses' && $expensesQuery) ? $expensesQuery->get() : collect(),
            'totalExpenses' => $expensesQuery ? $expensesQuery->sum('amount') : 0,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'expense_date' => 'required|date',
            'description'  => 'required|string|max:255',
            'quantity'     => 'required|integer|min:1',
            'amount'       => 'required|numeric|min:0',
        ]);
        \App\Models\Expense::create($data);
        return back()->with('ok', 'Expense added.');
    }

    public function destroyExpense(\App\Models\Expense $expense)
    {
        $expense->delete();
        return back()->with('ok', 'Expense deleted.');
    }

    public function deleteHistory(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!empty($ids)) {
            Order::whereIn('id', $ids)->whereIn('status', ['completed', 'cancelled'])->delete();
        }
        return back()->with('ok', 'Selected records deleted.');
    }

    public function reports()
    {
        return view('admin.reports', [
            'batches'      => Batch::orderBy('code')->get(),
            'lossByReason' => BagLoss::selectRaw('reason, SUM(quantity) as qty')->groupBy('reason')->orderByDesc('qty')->get(),
        ]);
    }



    public function users()
    {
        return view('admin.users', ['users' => User::orderBy('id')->get()]);
    }
}
