@extends('layouts.admin')
@section('content')
<div class="card wf">
  <h2>Harvest Yield Scale</h2>
  <form class="wb" method="POST" action="{{ route('worker.harvest.store') }}" data-confirm="Record this harvest? The weight is added to the batch yield, the cleared bags are removed, and the weight is added to the selected store product." data-title="Record harvest" data-yes="RECORD">@csrf
    <label>Select batch</label>
    <select name="batch_id" required>
      @foreach ($batches as $b)<option value="{{ $b->id }}" @selected(old('batch_id') == $b->id)>{{ $b->code }} — {{ $b->name }}</option>@endforeach
    </select>
    <label>Add to store product</label>
    <select name="product_id">
      <option value="">Don't add to store (spoiled / kept)</option>
      @foreach ($products as $p)<option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }} — {{ rtrim(rtrim(number_format($p->stock_kg, 2), '0'), '.') }}kg in stock</option>@endforeach
    </select>
    <div class="two">
      <div><label>Weight (kilos)</label><input type="number" step="0.1" min="0.1" name="weight_kg" placeholder="0.0" required value="{{ old('weight_kg') }}"></div>
      <div><label>Bags cleared</label><input type="number" min="0" name="bags_cleared" placeholder="0" required value="{{ old('bags_cleared') }}"></div>
    </div>
    <button class="btn gold">RECORD HARVEST</button>
  </form>
</div>

<div class="sec">Recently Scaled Yields</div>
@foreach ($recent as $h)
  <div class="card lr" style="max-width:720px;margin-bottom:12px">
    <div><b>{{ $h->batch->code }}</b><small>{{ $h->created_at->format('Y-m-d H:i') }} · {{ $h->user->name }} · {{ $h->product ? '→ '.$h->product->name : 'not added to store' }}</small></div>
    <span class="hl">{{ rtrim(rtrim(number_format($h->weight_kg, 2), '0'), '.') }}kg · {{ $h->bags_cleared }} bags</span>
  </div>
@endforeach
@endsection
