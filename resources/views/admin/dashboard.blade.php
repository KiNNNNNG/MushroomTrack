@extends('layouts.admin')
@section('content')

<div class="dash-hero" style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: var(--brown);">Admin Dashboard</h1>
  <p style="color: var(--muted); font-size: 14px; margin-top: 4px;">Farm overview, real-time KPIs, and priority operations.</p>
</div>

{{-- -- 4 Key KPI Cards (Clickable) --------------------------------- --}}
<div class="stats" style="margin-bottom: 30px;">
  <a href="{{ route('admin.batches') }}" class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 18px; text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div style="width: 36px; height: 36px; border-radius: 10px; background: #f0fdf4; display: grid; place-items: center; color: var(--green);">
        <x-icon n="batches" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700; color: #475569;">Active Batches</small>
    </div>
    <div style="display: flex; align-items: baseline; justify-content: space-between;">
      <strong style="margin: 0; font-size: 26px; color: var(--ink);">{{ $activeBatches }}</strong>
      <span style="font-size: 11px; color: var(--muted);">of {{ $totalBatches }} total &rarr;</span>
    </div>
  </a>

  <a href="{{ route('admin.reports') }}" class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 18px; text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div style="width: 36px; height: 36px; border-radius: 10px; background: #fffaf2; display: grid; place-items: center; color: var(--gold);">
        <x-icon n="scale" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700; color: #475569;">Total Harvested</small>
    </div>
    <div style="display: flex; align-items: baseline; justify-content: space-between;">
      <strong style="margin: 0; font-size: 26px; color: var(--gold);">{{ rtrim(rtrim(number_format($harvestedYield, 1), '0'), '.') }} kg</strong>
      <span style="font-size: 11px; color: var(--muted);">All-time &rarr;</span>
    </div>
  </a>

  <a href="{{ route('admin.store') }}" class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 18px; text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div style="width: 36px; height: 36px; border-radius: 10px; background: #eff6ff; display: grid; place-items: center; color: #3b82f6;">
        <x-icon n="store" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700; color: #475569;">Available Stock</small>
    </div>
    <div style="display: flex; align-items: baseline; justify-content: space-between;">
      <strong style="margin: 0; font-size: 26px; color: var(--ink);">{{ rtrim(rtrim(number_format($availableStock, 1), '0'), '.') }} kg</strong>
      <span style="font-size: 11px; color: var(--muted);">In inventory &rarr;</span>
    </div>
  </a>

  <a href="{{ route('admin.store', ['tab' => 'orders']) }}" class="card stat {{ $attentionCount ? 'r' : '' }}" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 18px; text-decoration: none; color: inherit; transition: transform .15s, box-shadow .15s;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $attentionCount ? '#fef2f2' : '#f0fdf4' }}; display: grid; place-items: center; color: {{ $attentionCount ? 'var(--dred)' : 'var(--green)' }};">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700; color: #475569;">Needs Attention</small>
    </div>
    <div style="display: flex; align-items: baseline; justify-content: space-between;">
      <strong style="margin: 0; font-size: 26px; color: {{ $attentionCount ? 'var(--dred)' : 'var(--green)' }};">{{ $attentionCount }}</strong>
      <span style="font-size: 11px; color: var(--muted);">View alerts &rarr;</span>
    </div>
  </a>
</div>

{{-- -- Priority Tasks / What Needs Attention Today ------------------- --}}
<div style="margin-bottom: 30px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); text-transform: uppercase; letter-spacing: 0.5px;">What Needs Attention Today</h2>
      @if($attentionCount > 0)
        <span style="background: #fef2f2; color: var(--dred); border: 1px solid #fecdd3; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">{{ $attentionCount }} actionable</span>
      @endif
    </div>
  </div>

  @if($attentionCount === 0)
    <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 14px; background: #f0fdf4; border-color: #bbf7d0;">
      <div style="width: 32px; height: 32px; border-radius: 50%; background: #28684F; color: #fff; display: grid; place-items: center; flex-shrink: 0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div style="font-size: 13px; color: #166534; font-weight: 600;">
        All farm operations are up to date. No pending orders, low stock, or overdue tasks detected.
      </div>
    </div>
  @else
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
      {{-- Pending orders --}}
      @foreach($pendingOrdersList as $po)
        <div class="card" style="padding: 16px; border-left: 4px solid var(--amber); display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
              <span style="font-size: 10px; font-weight: 800; background: #fffbeb; color: var(--amber); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">Pending Order</span>
              <b style="font-size: 13px; color: var(--ink);">Order #{{ $po->id }}</b>
            </div>
            <div style="font-size: 12px; color: var(--muted);">{{ $po->user->name ?? 'Customer' }} &bull; {{ $po->quantity_kg }}kg {{ $po->product->name ?? 'Mushroom' }}</div>
          </div>
          <a href="{{ route('admin.store', ['tab' => 'orders']) }}" class="btn" style="height: 32px; padding: 0 12px; font-size: 11px;">Review</a>
        </div>
      @endforeach

      {{-- Low stock items --}}
      @foreach($lowStockProducts as $lp)
        <div class="card" style="padding: 16px; border-left: 4px solid var(--dred); display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
              <span style="font-size: 10px; font-weight: 800; background: #fef2f2; color: var(--dred); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">Low Stock</span>
              <b style="font-size: 13px; color: var(--ink);">{{ $lp->name }}</b>
            </div>
            <div style="font-size: 12px; color: var(--muted);">Only <strong>{{ $lp->stock_kg }} kg</strong> remaining in inventory</div>
          </div>
          <a href="{{ route('admin.store', ['tab' => 'products']) }}" class="btn secondary" style="height: 32px; padding: 0 12px; font-size: 11px;">Restock</a>
        </div>
      @endforeach

      {{-- Upcoming Harvests --}}
      @foreach($upcomingHarvests as $uh)
        <div class="card" style="padding: 16px; border-left: 4px solid var(--green); display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
              <span style="font-size: 10px; font-weight: 800; background: #ecfdf5; color: var(--green); padding: 2px 6px; border-radius: 4px; text-transform: uppercase;">Upcoming Harvest</span>
              <b style="font-size: 13px; color: var(--ink);">{{ $uh->code }}</b>
            </div>
            <div style="font-size: 12px; color: var(--muted);">Est. {{ optional($uh->harvest_date)->format('M d, Y') }} &bull; {{ $uh->bags }} bags</div>
          </div>
          <a href="{{ route('admin.batches') }}" class="btn secondary" style="height: 32px; padding: 0 12px; font-size: 11px;">View Batch</a>
        </div>
      @endforeach
    </div>
  @endif
</div>

{{-- -- Production Yield Trend Chart --------------------------------- --}}
<div class="card trend" style="margin-bottom: 30px; margin-top: 0;">
  <div class="thead" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--line);">
    <div>
      <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); letter-spacing: 0.5px; text-transform: uppercase; margin: 0;">Production Yield Over Time</h2>
      <p style="font-size: 12px; color: var(--muted); margin-top: 2px;">Cumulative harvested kilograms logged from farm batches</p>
    </div>
    <form id="rangeForm">
      <select name="range" onchange="document.getElementById('rangeForm').submit()" style="height: 36px; padding: 0 12px; border-radius: 8px; border: 1px solid var(--line); font-weight: 600;">
        <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
        <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
        <option value="year" {{ request('range') == 'year' ? 'selected' : '' }}>This Year</option>
      </select>
    </form>
  </div>
  <div class="chartwrap" style="padding: 24px;">
    @if(empty($sales) || count($sales) === 0)
      <div style="padding: 60px 0; text-align: center; color: var(--muted); font-size: 13px;">
        No harvest or production data recorded for the selected period.
      </div>
    @else
      <div id="salesChart" data-sales="{{ json_encode($sales) }}"></div>
    @endif
  </div>
</div>

{{-- -- Recent Activity ----------------------------------------------- --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
  <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); text-transform: uppercase; letter-spacing: 0.5px;">Recent System Activity</h2>
  <span style="font-size: 12px; color: var(--muted); font-weight: 600;">Latest audit records</span>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
  @forelse($recentActivity as $act)
    <div style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-bottom: 1px solid var(--line);">
      <div style="width: 36px; height: 36px; border-radius: 50%; background: #f6f4f1; display: grid; place-items: center; color: var(--brown); flex-shrink: 0;">
        <x-icon n="history" s="16" />
      </div>
      <div style="flex: 1;">
        <b style="display: block; font-size: 14px; margin-bottom: 2px; color: var(--ink);">{{ $act->action }}</b>
        @if($act->details)<span style="color: var(--muted); font-size: 12px; display: block;">{{ $act->details }}</span>@endif
      </div>
      <small style="color: var(--muted); font-size: 11px; white-space: nowrap;">{{ $act->created_at->diffForHumans(null, true, true) }}</small>
    </div>
  @empty
    <div style="padding: 40px; text-align: center; color: var(--muted); font-size: 13px;">No recent activity logged yet.</div>
  @endforelse
</div>

@endsection