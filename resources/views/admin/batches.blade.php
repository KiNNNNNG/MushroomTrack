@extends('layouts.admin')
@section('content')

@if ($selectedBatch)
  <div style="margin-bottom: 24px;">
    <a href="{{ route('admin.batches') }}" style="font-size: 13px; font-weight: 700; color: #6b5a4e; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 12px;">
      &larr; Back to batches
    </a>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
      <div>
        <h1 style="font-size: 24px; font-weight: 800; color: #241c16; margin: 0 0 4px;">{{ $selectedBatch->code }} &mdash; {{ $selectedBatch->name }}</h1>
        <p style="font-size: 13px; color: #6b5a4e; margin: 0;">Daily monitoring &middot; {{ ucfirst($selectedBatch->status) }} &middot; Est. harvest {{ optional($selectedBatch->harvest_date)->format('Y-m-d') ?? '—' }}</p>
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
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">DATE</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">DAY</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">BAGS REMAINING</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">LOST TODAY</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">REMARKS</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($selectedBatch->monitoringLogs as $index => $l)
          @php $lostToday = $l->loss ? $l->loss->quantity : 0; @endphp
          <tr style="border-bottom: 1px solid #f2ede5;">
            <td style="padding: 14px 20px; color: #4a4037; white-space: nowrap;">{{ $l->created_at->format('M d, Y') }}</td>
            <td style="padding: 14px 20px; color: #6b5a4e; white-space: nowrap;">Day {{ $index + 1 }}</td>
            <td style="padding: 14px 20px; text-align: center; font-weight: 700; color: #241c16; white-space: nowrap;">{{ $selectedBatch->bags }}</td>
            <td style="padding: 14px 20px; text-align: center; white-space: nowrap;">
              @if ($lostToday > 0)
                <span style="color: #b91c1c; font-weight: 700;">{{ $lostToday }}</span>
              @else
                <span style="color: #6b5a4e;">&mdash;</span>
              @endif
            </td>
            <td style="padding: 14px 20px; color: #5a5047;">{{ $l->notes ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No logs recorded yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

@else
  <div style="margin-bottom: 20px;">
    <h1 style="font-size: 24px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Cultivation & Batches</h1>
    <p style="font-size: 13px; color: #6b5a4e; margin: 0;">Click a row to view its monitoring history.</p>
  </div>

  <div class="table-responsive" style="background: #ffffff; border: 1px solid #e8e2d8; border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; box-sizing: border-box; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; min-width: 580px; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #e8e2d8;">
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">BATCH ID</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px;">NAME</th>
          <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">BAGS</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">STATUS</th>
          <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #6b5a4e; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">HARVEST DATE</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($activeBatches as $b)
          <tr onclick="window.location='{{ route('admin.batches', ['batch' => $b->id]) }}'"
              style="border-bottom: 1px solid #f2ede5; cursor: pointer; transition: background 0.15s;"
              onmouseover="this.style.background='#faf8f5'" onmouseout="this.style.background=''">
            <td style="padding: 14px 20px; font-weight: 700; color: #241c16; font-family: monospace; white-space: nowrap;">{{ $b->code }}</td>
            <td style="padding: 14px 20px; color: #4a4037;">{{ $b->name }}</td>
            <td style="padding: 14px 20px; text-align: center; font-weight: 700; color: #241c16; white-space: nowrap;">{{ $b->bags }}</td>
            <td style="padding: 14px 20px; white-space: nowrap;">
              @php
                $statusColors = [
                  'incubating' => ['bg' => '#fef9e7', 'color' => '#d97706', 'dot' => '#d97706'],
                  'fruiting'   => ['bg' => '#e6f7f0', 'color' => '#0e6b47', 'dot' => '#0e6b47'],
                  'harvested'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'dot' => '#1d4ed8'],
                ];
                $sc = $statusColors[$b->status] ?? ['bg' => '#f3f4f6', 'color' => '#374151', 'dot' => '#9ca3af'];
              @endphp
              <span style="display: inline-flex; align-items: center; gap: 6px; background: {{ $sc['bg'] }}; color: {{ $sc['color'] }}; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $sc['dot'] }};"></span>
                {{ $b->status }}
              </span>
            </td>
            <td style="padding: 14px 20px; color: #4a4037; white-space: nowrap;">{{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="padding: 40px 20px; text-align: center; color: #6b5a4e;">No active batches found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif

@endsection
