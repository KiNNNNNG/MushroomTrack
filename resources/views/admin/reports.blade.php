@extends('layouts.admin')
@section('content')
<form method="GET" action="{{ route('admin.reports.export') }}" class="toolbar" style="gap:10px;align-items:center">
  <select name="range" title="Period for losses and harvests">
    @foreach (\App\Support\DateRange::OPTIONS as $k => $l)<option value="{{ $k }}" @selected($k === 'all')>{{ $l }}</option>@endforeach
  </select>
  <button class="btn ghost" name="format" value="xlsx">DOWNLOAD EXCEL</button>
  <button class="btn" name="format" value="pdf">DOWNLOAD PDF</button>
</form>
<div class="rhead">BATCH HEALTH &amp; SCHEDULE</div>
<div class="rlist">
  @forelse ($batches as $b)
    @php $pct = $b->health; @endphp
    <div class="card rcard">
      <div class="rtop">
        <div>
          <h3>{{ $b->code }} — {{ $b->name }}</h3>
          <div class="rsub">
            <span>Status: {{ ucfirst($b->status) }}</span>
            <span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Est. Harvest: {{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</span>
          </div>
        </div>
        <div class="hi"><b>{{ $pct }}%</b><small>HEALTH INDEX</small></div>
      </div>
      <div class="bar"><span @style(['flex: '.$pct])></span>@if ($b->lost_bags)<i @style(['flex: '.(100 - $pct)])></i>@endif</div>
      <div class="leg">
        <span><em></em>Healthy: {{ $b->bags }} Bags</span>
        <span><em></em>Lost: {{ $b->lost_bags }} Bags</span>
      </div>
    </div>
  @empty
    <div class="empty">No batches yet.</div>
  @endforelse
</div>
@if ($lossByReason->isNotEmpty())
  <div class="rhead" style="margin-top:28px">BAGS LOST BY REASON</div>
  <div class="card tbl plain" style="max-width:720px">
    <table>
      <tr><th>Reason</th><th class="r">Bags lost</th></tr>
      @foreach ($lossByReason as $row)
        <tr><td>{{ \App\Models\BagLoss::labelFor($row->reason) }}</td><td class="r"><b>{{ $row->qty }}</b></td></tr>
      @endforeach
    </table>
  </div>
@endif
@endsection
