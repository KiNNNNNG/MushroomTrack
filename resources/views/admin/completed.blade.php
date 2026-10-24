@extends('layouts.admin')
@section('content')

@if ($selectedBatch)
  <div style="margin-bottom: 24px;">
    <a href="{{ route('admin.completed') }}" style="font-size: 13px; font-weight: 700; color: #6b5a4e; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
      &larr; Back to completed batches
    </a>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
      <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #241c16; margin: 0 0 4px;">{{ $selectedBatch->code }} &mdash; {{ $selectedBatch->name }}</h1>
        <p style="font-size: 13px; color: #6b5a4e; margin: 0;">Completed batch &middot; Harvested yield: {{ $selectedBatch->yield_kg ? $selectedBatch->yield_kg.' kg' : '—' }}</p>
      </div>
      <button onclick="window.print()" style="background: #205e46; color: #fff; border: none; border-radius: 8px; padding: 10px 18px; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
        DOWNLOAD PDF
      </button>
    </div>
  </div>

  <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; min-width: 580px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">DATE</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">DAY</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">BAGS REMAINING</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">LOST TODAY</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">REMARKS</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($selectedBatch->monitoringLogs as $index => $l)
          @php $lostToday = $l->loss ? $l->loss->quantity : 0; @endphp
          <tr style="border-bottom: 1px solid #f2ede5;">
            <td style="padding: 14px 20px; color: #4a4037;">{{ $l->created_at->format('M d, Y') }}</td>
            <td style="padding: 14px 20px; color: #6b5a4e;">Day {{ $index + 1 }}</td>
            <td style="padding: 14px 20px; text-align: center; font-weight: 700; color: #241c16;">{{ $selectedBatch->bags }}</td>
            <td style="padding: 14px 20px; text-align: center;">
              @if ($lostToday > 0)
                <span style="color: #b91c1c; font-weight: 700;">{{ $lostToday }}</span>
              @else
                <span style="color: #6b5a4e;">&mdash;</span>
              @endif
            </td>
            <td style="padding: 14px 20px; color: #5a5047;">{{ $l->notes ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No logs recorded.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

@else
  <div style="margin-bottom: 20px;">
    <h1 style="font-size: 24px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Completed Batches</h1>
    <p style="font-size: 13px; color: #6b5a4e; margin: 0;">Harvested batches. Click a row to view its daily monitoring.</p>
  </div>

  <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; min-width: 580px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">BATCH ID</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">NAME</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">BAGS</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">YIELD</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">DATE COMPLETED</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($batches as $b)
          <tr onclick="window.location='{{ route('admin.completed', ['batch' => $b->id]) }}'"
              style="border-bottom: 1px solid #f2ede5; cursor: pointer; transition: background 0.15s;"
              onmouseover="this.style.background='#faf8f5'" onmouseout="this.style.background=''">
            <td style="padding: 14px 20px; font-weight: 700; color: #241c16; font-family: monospace; white-space: nowrap;">{{ $b->code }}</td>
            <td style="padding: 14px 20px; color: #4a4037;">{{ $b->name }}</td>
            <td style="padding: 14px 20px; text-align: center; font-weight: 700; color: #241c16; white-space: nowrap;">{{ $b->bags }}</td>
            <td style="padding: 14px 20px; color: #0e6b47; font-weight: 700; white-space: nowrap;">{{ $b->yield_kg ? $b->yield_kg.' kg' : '—' }}</td>
            <td style="padding: 14px 20px; color: #4a4037; white-space: nowrap;">{{ optional($b->updated_at)->format('Y-m-d') ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No completed batches yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif

@endsection

