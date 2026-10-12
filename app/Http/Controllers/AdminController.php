<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BagLoss;
use App\Models\Batch;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\DateRange;
use App\Support\Qr;
use Illuminate\Http\Request;

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

        return view('admin.dashboard', [
            'totalBatches'   => Batch::count(),
            'pendingOrders'  => Order::where('status', 'pending')->count(),
            'revenue'        => $salesQuery->sum('amount'),
            'yield'          => (float) Batch::sum('yield_kg'),
            'sales'          => $sales,
            'range'          => $range,
            'recentActivity' => ActivityLog::latest()->take(5)->get(),
        ]);
    }

    public function batches()
    {
        return view('admin.batches', ['batches' => Batch::orderBy('code')->get()]);
    }

    public function store(Request $request)
    {
        $tab = $request->query('tab', 'products');
        $range = $request->query('range', 'month');
        $from = DateRange::from($range);

        return view('admin.store', [
            'tab'        => $tab,
            'range'      => $range,
            'products'   => Product::orderBy('id')->get(),
            'orders'     => $tab === 'orders'  ? Order::with('user', 'product', 'payment')->whereIn('status', ['pending', 'confirmed'])->latest()->get() : collect(),
            'history'    => $tab === 'history' ? Order::with('user', 'product', 'payment')->whereIn('status', ['completed', 'cancelled'])->latest()->get() : collect(),
            'totalSales' => Sale::query()->when($from, fn ($q) => $q->where('sold_at', '>=', $from))->sum('amount'),
        ]);
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
