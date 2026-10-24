@extends('layouts.admin')
@section('content')

@if ($batch)
  {{-- Detail View matching mockup 847549499_2199226841022783_3901140770592205303_n.png --}}
  <div style="margin-bottom: 24px;">
    <a href="{{ route('worker.history') }}" style="font-size: 13px; font-weight: 700; color: #8a7f74; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
      &larr; Back
    </a>
    <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">{{ $batch->code }} &mdash; {{ $batch->name }}</h1>
    <p style="font-size: 13px; color: #7c726a; margin: 0;">Daily monitoring &middot; {{ ucfirst($batch->status) }} &middot; Est. harvest {{ optional($batch->harvest_date)->format('Y-m-d') ?? '—' }}</p>
  </div>

  <div style="overflow-x: auto; background: #ffffff; border: 1px solid #ece6dd; border-radius: 16px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #ece6dd;">
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">DATE</th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">DAY</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">BAGS REMAINING</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">LOST TODAY</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">WATERED</th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">REMARKS</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($logs as $index => $l)
          @php
            $lostToday = $l->loss ? $l->loss->quantity : 0;
            $dayNum = $index + 1;
          @endphp
          <tr style="border-bottom: 1px solid #f2ede5; transition: background 0.15s;" onmouseover="this.style.background='#fcfbfa'" onmouseout="this.style.background=''">
            <td style="padding: 16px 20px; color: #4a4037; font-size: 14px; white-space: nowrap;">{{ $l->created_at->format('M d, Y') }}</td>
            <td style="padding: 16px 20px; color: #4a4037; font-size: 14px;">Day {{ $dayNum }}</td>
            <td style="padding: 16px 20px; text-align: center; font-weight: 700; color: #241c16; font-size: 14px;">{{ $batch->bags }}</td>
            <td style="padding: 16px 20px; text-align: center; font-size: 14px;">
              @if ($lostToday > 0)
                <span style="color: #cf3b3b; font-weight: 700;">{{ $lostToday }}</span>
              @else
                <span style="color: #8a7f74;">&mdash;</span>
              @endif
            </td>
            <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px;">{{ $l->watered ? 'Yes' : 'No' }}</td>
            <td style="padding: 16px 20px; color: #5a5047; font-size: 14px; max-width: 260px;">{{ $l->notes ?: '—' }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="padding: 40px 20px; text-align: center; color: #8a7f74;">No monitoring checks recorded for this batch yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

@else
  {{-- Summary Table (No redundant batches) matching mockup 848370232_940607825482089_7396078569561412454_n.png --}}
  <div style="margin-bottom: 20px;">
    <h2 style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px; margin: 0;">MONITORING HISTORY</h2>
  </div>

  <div style="overflow-x: auto; background: #ffffff; border: 1px solid #ece6dd; border-radius: 16px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #ece6dd;">
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">BATCH</th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">NAME</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">ACTUAL BAGS</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">LOST BAGS</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">LAST CHECK</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">ACTION</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($batches as $b)
          @php
            $lastLog = $b->monitoringLogs->first();
          @endphp
          <tr style="border-bottom: 1px solid #f2ede5; transition: background 0.15s;" onmouseover="this.style.background='#fcfbfa'" onmouseout="this.style.background=''">
            <td style="padding: 16px 20px; font-weight: 700; color: #241c16; font-size: 14px;">{{ $b->code }}</td>
            <td style="padding: 16px 20px; color: #4a4037; font-size: 14px;">{{ $b->name }}</td>
            <td style="padding: 16px 20px; text-align: center; font-weight: 700; color: #241c16; font-size: 14px;">{{ $b->bags }}</td>
            <td style="padding: 16px 20px; text-align: center; font-size: 14px;">
              @if ($b->lost_bags > 0)
                <span style="color: #cf3b3b; font-weight: 700;">{{ $b->lost_bags }}</span>
              @else
                <span style="color: #8a7f74;">&mdash;</span>
              @endif
            </td>
            <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px;">
              {{ $lastLog ? $lastLog->created_at->format('Y-m-d') : '—' }}
            </td>
            <td style="padding: 16px 20px; text-align: center;">
              <a href="{{ route('worker.history', ['batch' => $b->id]) }}"
                style="background: transparent; border: none; color: #241c16; font-size: 13px; font-weight: 800; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; letter-spacing: 0.4px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                VIEW
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="padding: 40px 20px; text-align: center; color: #8a7f74;">No batches found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif
@endsection

