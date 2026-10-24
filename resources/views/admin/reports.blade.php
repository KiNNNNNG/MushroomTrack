@extends('layouts.admin')
@section('content')

@php
  $totalLostBags = $batches->sum('lost_bags');
@endphp

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
  <div>
    <span style="font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.8px;">BATCH HEALTH &amp; SCHEDULE</span>
  </div>
  <div style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 20px; padding: 6px 14px; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
    <span style="width: 7px; height: 7px; border-radius: 50%; background: #b91c1c; display: inline-block;"></span>
    <span style="font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.6px;">TOTAL LOST BAGS</span>
    <strong style="font-size: 14px; font-weight: 800; color: #b91c1c;">{{ $totalLostBags }}</strong>
    <span style="font-size: 11px; font-weight: 800; color: #0e6b47; margin-left: 2px;">&bull; LIVE</span>
  </div>
</div>

<div style="display: flex; flex-direction: column; gap: 14px;">
  @forelse ($batches as $b)
    @php
      $pct = $b->health;
      $statusText = strtoupper($b->status);
      $estHarvest = optional($b->harvest_date)->format('Y-m-d');
    @endphp
    <div style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 14px; padding: 22px 26px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
        <div>
          <h3 style="font-size: 15px; font-weight: 800; color: #241c16; margin: 0 0 4px; letter-spacing: 0.2px;">
            {{ $b->code }} &mdash; {{ strtoupper($b->name) }}
          </h3>
          <div style="font-size: 11px; font-weight: 700; color: #6b5a4e; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.4px;">
            <span>STATUS: {{ $statusText }}</span>
            @if ($estHarvest)
              <span style="color: #a87834;">&bull; EST. HARVEST: {{ $estHarvest }}</span>
            @endif
          </div>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: {{ $pct >= 90 ? '#1b623f' : ($pct >= 70 ? '#d97706' : '#b91c1c') }}; line-height: 1;">
          {{ $pct }}%
        </div>
      </div>

      {{-- Bar --}}
      <div style="height: 6px; background: #f2ede5; border-radius: 3px; overflow: hidden; margin-bottom: 12px; display: flex;">
        <div style="height: 100%; background: #1b623f; width: {{ $pct }}%;"></div>
        @if ($b->lost_bags > 0)
          <div style="height: 100%; background: #b91c1c; width: {{ 100 - $pct }}%;"></div>
        @endif
      </div>

      <div style="display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; letter-spacing: 0.4px;">
        <span style="color: #241c16; display: inline-flex; align-items: center; gap: 6px;">
          <span style="width: 7px; height: 7px; background: #1b623f; display: inline-block;"></span>
          HEALTHY: {{ $b->bags }} BAGS
        </span>
        <span style="color: #b91c1c; display: inline-flex; align-items: center; gap: 6px;">
          <span style="width: 7px; height: 7px; background: #b91c1c; display: inline-block;"></span>
          LOST: {{ $b->lost_bags }} BAGS
        </span>
      </div>
    </div>
  @empty
    <div style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 14px; padding: 40px; text-align: center; color: #6b5a4e; font-size: 13px;">
      No batches recorded yet.
    </div>
  @endforelse
</div>

@endsection
