<!doctype html>
<html><head><meta charset="utf-8"><title>MushFungAI Reports</title>@include('exports._style')</head>
<body>
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
<h1>MushFungAI — Batch Reports</h1>
<div class="sub">Generated {{ now()->format('Y-m-d H:i') }} · Losses &amp; harvests: {{ $rangeLabel }}</div>

<h2>Batch health &amp; schedule</h2>
<table>
  <tr><th>Batch</th><th>Name</th><th>Status</th><th class="r">Healthy</th><th class="r">Lost</th><th class="r">Health</th><th class="r">Yield kg</th><th>Est. harvest</th></tr>
  @forelse ($batches as $b)
    <tr><td>{{ $b->code }}</td><td>{{ $b->name }}</td><td>{{ ucfirst($b->status) }}</td><td class="r">{{ $b->bags }}</td><td class="r">{{ $b->lost_bags }}</td><td class="r">{{ $b->health }}%</td><td class="r">{{ $fmt($b->yield_kg) }}</td><td>{{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</td></tr>
  @empty <tr><td colspan="8" class="none">No batches.</td></tr> @endforelse
  @if ($batches->isNotEmpty())
    <tr class="tot"><td colspan="3">TOTAL</td><td class="r">{{ $batches->sum('bags') }}</td><td class="r">{{ $batches->sum('lost_bags') }}</td><td></td><td class="r">{{ $fmt($batches->sum('yield_kg')) }}</td><td></td></tr>
  @endif
</table>

<h2>Bags lost by reason</h2>
<table>
  <tr><th>Reason</th><th class="r">Bags lost</th></tr>
  @forelse ($byReason as $reason => $qty)
    <tr><td>{{ \App\Models\BagLoss::labelFor($reason) }}</td><td class="r">{{ $qty }}</td></tr>
  @empty <tr><td colspan="2" class="none">No losses recorded for this period.</td></tr> @endforelse
</table>

<h2>Loss reports</h2>
<table>
  <tr><th>Date</th><th>Batch</th><th class="r">Bags</th><th>Reason</th><th>Reported by</th></tr>
  @forelse ($losses as $l)
    <tr><td>{{ $l->created_at->format('Y-m-d H:i') }}</td><td>{{ $l->batch->code ?? '' }}</td><td class="r">{{ $l->quantity }}</td><td>{{ $l->reason_label }}</td><td>{{ $l->user->name ?? 'System' }}</td></tr>
  @empty <tr><td colspan="5" class="none">None.</td></tr> @endforelse
</table>

<h2>Harvests</h2>
<table>
  <tr><th>Date</th><th>Batch</th><th>Store product</th><th class="r">Weight kg</th><th class="r">Bags cleared</th><th>Worker</th></tr>
  @forelse ($harvests as $h)
    <tr><td>{{ $h->created_at->format('Y-m-d H:i') }}</td><td>{{ $h->batch->code ?? '' }}</td><td>{{ $h->product->name ?? 'Not added to store' }}</td><td class="r">{{ $fmt($h->weight_kg) }}</td><td class="r">{{ $h->bags_cleared }}</td><td>{{ $h->user->name ?? '' }}</td></tr>
  @empty <tr><td colspan="6" class="none">None.</td></tr> @endforelse
  @if ($harvests->isNotEmpty())
    <tr class="tot"><td colspan="3">TOTAL</td><td class="r">{{ $fmt($harvests->sum('weight_kg')) }}</td><td class="r">{{ $harvests->sum('bags_cleared') }}</td><td></td></tr>
  @endif
</table>
</body></html>
