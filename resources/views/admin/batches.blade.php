@extends('layouts.admin')
@section('content')
<div class="toolbar" style="gap:10px">
  {{-- Actions moved to worker side --}}
</div>
<div class="card tbl">
  <table>
    <tr><th>Batch ID</th><th style="width:40%">Name</th><th>Bags</th><th>Status</th><th>Harvest Date</th></tr>
    @forelse ($batches as $b)
      <tr class="click" onclick="document.getElementById('b{{ $b->id }}').showModal()">
        <td class="mono">{{ $b->code }}</td>
        <td>{{ $b->name }}</td>
        <td>{{ $b->bags }}</td>
        <td><span class="tag">{{ strtoupper($b->status) }}</span></td>
        <td class="date">{{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</td>
      </tr>
    @empty
      <tr><td colspan="5" class="empty">No batches yet.</td></tr>
    @endforelse
  </table>
</div>
@foreach ($batches as $b) @include('admin.partials.batch-form', ['b' => $b]) @endforeach
@endsection
