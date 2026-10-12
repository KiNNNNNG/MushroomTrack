@extends('layouts.admin')
@section('content')
<style>
  .lbl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
  .lbl{background:#fff;border:1px dashed #94a3b8;border-radius:12px;padding:14px;text-align:center;break-inside:avoid;page-break-inside:avoid}
  .lbl svg{width:160px;height:160px;display:block;margin:0 auto 8px}
  .lbl b{display:block;font:700 15px 'Courier New',monospace;letter-spacing:1px}
  .lbl .bag-num{display:block;font:900 22px 'Courier New',monospace;color:var(--primary,#22c55e);margin:2px 0 4px}
  .lbl span{display:block;font-size:12px;color:#475569;margin-top:2px}
  .lbl small{display:block;font-size:10px;color:#94a3b8;margin-top:6px;letter-spacing:.5px}
  @media print{
    header, aside, .flash, .no-print{display:none !important}
    .app{display:block !important}
    main{padding:0 !important;margin:0 !important;max-width:none !important}
    .lbl-grid{grid-template-columns:repeat(4,1fr);gap:8px}
    .lbl{border:1px dashed #000}
  }
</style>
<div class="toolbar no-print" style="gap:10px;align-items:center">
  @if (request('batch'))<a class="btn ghost" href="{{ route('worker.batches.labels') }}">SHOW ALL ACTIVE BATCHES</a>@endif
  <a class="btn ghost" href="javascript:history.back()">BACK</a>
  <button class="btn" onclick="window.print()">PRINT LABELS</button>
</div>
<p class="no-print" style="color:#64748b;font-size:13px;margin-bottom:18px">
  Each QR code is unique to an individual bag. When a worker scans it, the monitoring tab opens for that bag. Labels use <b>{{ config('app.url') }}</b> — make sure <code>APP_URL</code> is your real public address before printing.
</p>
<div class="lbl-grid">
  @forelse ($labels as $l)
    <div class="lbl">
      {!! $l['svg'] !!}
      <b>{{ $l['batch']->code }}</b>
      <span class="bag-num">BAG #{{ $l['bagNum'] }}</span>
      <span>{{ $l['batch']->name }}</span>
      <small>SCAN TO MONITOR · {{ $l['bagNum'] }} of {{ $l['total'] }}</small>
    </div>
  @empty
    <div class="empty">No batches to label.</div>
  @endforelse
</div>
@endsection
