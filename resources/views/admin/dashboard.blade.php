@extends('layouts.admin')
@section('content')

<div class="dash-hero" style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Admin Dashboard</h1>
  <p style="color: #7c726a; font-size: 14px; margin: 0;">Farm overview, real-time KPIs, and priority operations.</p>
</div>

{{-- 4 Stat Cards matching Worker / Customer UI --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
  <a href="{{ route('admin.batches') }}" style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02); text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; transition: transform .15s, box-shadow .15s;">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">ACTIVE BATCHES</div>
    <div>
      <div style="font-size: 32px; font-weight: 800; color: #241c16; margin-top: 8px;">{{ $activeBatches }}</div>
      <div style="font-size: 11px; color: #7c726a; margin-top: 4px;">of {{ $totalBatches }} total &rarr;</div>
    </div>
  </a>

  <a href="{{ route('admin.reports') }}" style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02); text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; transition: transform .15s, box-shadow .15s;">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">TOTAL HARVESTED</div>
    <div>
      <div style="font-size: 32px; font-weight: 800; color: #b28135; margin-top: 8px;">{{ rtrim(rtrim(number_format($harvestedYield, 1), '0'), '.') }} kg</div>
      <div style="font-size: 11px; color: #7c726a; margin-top: 4px;">All-time yield &rarr;</div>
    </div>
  </a>

  <a href="{{ route('admin.store') }}" style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02); text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; transition: transform .15s, box-shadow .15s;">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">AVAILABLE STOCK</div>
    <div>
      <div style="font-size: 32px; font-weight: 800; color: #241c16; margin-top: 8px;">{{ rtrim(rtrim(number_format($availableStock, 1), '0'), '.') }} kg</div>
      <div style="font-size: 11px; color: #7c726a; margin-top: 4px;">In inventory &rarr;</div>
    </div>
  </a>

  <a href="{{ route('admin.store', ['tab' => 'sales']) }}" style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02); text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; transition: transform .15s, box-shadow .15s;">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">REVENUE</div>
    <div>
      <div style="font-size: 32px; font-weight: 800; color: #205e46; margin-top: 8px;">₱{{ number_format($revenue) }}</div>
      <div style="font-size: 11px; color: #7c726a; margin-top: 4px;">Total sales − expenses &rarr;</div>
    </div>
  </a>
</div>

{{-- Production Yield Trend Chart --}}
<div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); margin-bottom: 30px; overflow: hidden;">
  <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid #ece6dd; background: #faf8f5;">
    <div>
      <h2 style="font-size: 15px; font-weight: 800; color: #241c16; letter-spacing: 0.5px; text-transform: uppercase; margin: 0;">Production Yield Over Time</h2>
      <p style="font-size: 12px; color: #7c726a; margin: 2px 0 0;">Cumulative harvested kilograms logged from farm batches</p>
    </div>
    <form id="rangeForm">
      <select name="range" onchange="document.getElementById('rangeForm').submit()" style="height: 36px; padding: 0 12px; border-radius: 8px; border: 1px solid #ece6dd; font-weight: 600; color: #241c16; background: #ffffff;">
        <option value="week" {{ request('range') == 'week' ? 'selected' : '' }}>This Week</option>
        <option value="month" {{ request('range') == 'month' ? 'selected' : '' }}>This Month</option>
        <option value="year" {{ request('range') == 'year' ? 'selected' : '' }}>This Year</option>
      </select>
    </form>
  </div>
  <div class="chartwrap" style="padding: 24px;">
    @if(blank($sales) || (is_countable($sales) ? count($sales) === 0 : empty($sales)))
      <div style="padding: 60px 0; text-align: center; color: #8a7f74; font-size: 13px;">
        No harvest or production data recorded for the selected period.
      </div>
    @else
      <div id="salesChart" data-sales="{{ json_encode($sales) }}"></div>
    @endif
  </div>
</div>

{{-- Recent Activity --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
  <h2 style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px; margin: 0;">Recent System Activity</h2>
  <span style="font-size: 12px; color: #8a7f74; font-weight: 600;">Latest audit records</span>
</div>

<div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); overflow: hidden;">
  @forelse($recentActivity as $act)
    <div style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-bottom: 1px solid #f2ede5;">
      <div style="width: 36px; height: 36px; border-radius: 50%; background: #ebdfcc; display: grid; place-items: center; color: #a87834; flex-shrink: 0;">
        <x-icon n="history" s="16" />
      </div>
      <div style="flex: 1;">
        <b style="display: block; font-size: 14px; margin-bottom: 2px; color: #241c16;">{{ $act->action }}</b>
        @if($act->details)<span style="color: #7c726a; font-size: 12px; display: block;">{{ $act->details }}</span>@endif
      </div>
      <small style="color: #8a7f74; font-size: 11px; font-weight: 600; white-space: nowrap;">{{ $act->created_at->diffForHumans(null, true, true) }}</small>
    </div>
  @empty
    <div style="padding: 40px; text-align: center; color: #8a7f74; font-size: 13px;">No recent activity logged yet.</div>
  @endforelse
</div>

@endsection