<!doctype html>
<html><head><meta charset="utf-8"><title>MushFungAI Sales</title>@include('exports._style')</head>
<body>
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp
<h1>MushFungAI — Sales Report</h1>
<div class="sub">Generated {{ now()->format('Y-m-d H:i') }} · Period: {{ $rangeLabel }}</div>
<div class="box"><b>Total sales: ₱{{ number_format($total, 2) }}</b> &nbsp;·&nbsp; {{ $fmt($totalKg) }} kg sold &nbsp;·&nbsp; {{ $sales->count() }} sale(s)</div>

<h2>By product</h2>
<table>
  <tr><th>Product</th><th class="r">Kg</th><th class="r">Amount</th></tr>
  @forelse ($byProduct as $name => $v)
    <tr><td>{{ $name }}</td><td class="r">{{ $fmt($v['kg']) }}</td><td class="r">₱{{ number_format($v['amount'], 2) }}</td></tr>
  @empty <tr><td colspan="3" class="none">No sales in this period.</td></tr> @endforelse
</table>

<h2>Revenue ledger</h2>
<table>
  <tr><th>Date</th><th>Order</th><th>Customer</th><th>Product</th><th class="r">Kg</th><th class="r">Amount</th></tr>
  @forelse ($sales as $s)
    <tr><td>{{ $s->sold_at->format('Y-m-d H:i') }}</td><td>{{ $s->order?->display_code ?? '—' }}</td><td>{{ $s->order?->user?->name ?? '—' }}</td><td>{{ $s->product->name ?? 'Deleted product' }}</td><td class="r">{{ $fmt($s->quantity_kg) }}</td><td class="r">₱{{ number_format($s->amount, 2) }}</td></tr>
  @empty <tr><td colspan="6" class="none">No sales in this period.</td></tr> @endforelse
  @if ($sales->isNotEmpty())
    <tr class="tot"><td colspan="4">TOTAL</td><td class="r">{{ $fmt($totalKg) }}</td><td class="r">₱{{ number_format($total, 2) }}</td></tr>
  @endif
</table>
</body></html>
