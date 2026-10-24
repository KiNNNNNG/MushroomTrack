@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

@if ($batch)
  {{-- Completed Batch Detail matching mockup 846528078_1754976745805957_3137682348547307232_n.png --}}
  <div style="margin-bottom: 24px;">
    <a href="{{ route('worker.completed') }}" style="font-size: 13px; font-weight: 700; color: #8a7f74; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
      &larr; Back
    </a>
    <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">{{ $batch->code }} &mdash; {{ $batch->name }}</h1>
    <p style="font-size: 13px; color: #7c726a; margin: 0;">Completed &middot; Harvested {{ optional($batch->updated_at)->format('Y-m-d') ?? '—' }}</p>
  </div>

  {{-- Green Total Harvested Banner --}}
  <div style="background: #205e46; border-radius: 14px; padding: 22px 28px; color: #ffffff; margin-bottom: 24px; box-shadow: 0 4px 18px rgba(32, 94, 70, 0.18);">
    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.8px; opacity: 0.85; text-transform: uppercase;">TOTAL HARVESTED</div>
    <div style="font-size: 32px; font-weight: 800; margin-top: 6px;">{{ $fmt($batch->yield_kg) }} kg</div>
  </div>

  {{-- Daily Monitoring Logs Table --}}
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
            <td colspan="6" style="padding: 40px 20px; text-align: center; color: #8a7f74;">No monitoring checks recorded for this batch.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

@else
  {{-- Completed Batches List matching mockup 850582612_29048311094804821_6232961512261782258_n.png --}}
  <div style="margin-bottom: 24px;">
    <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Completed Batches</h1>
    <p style="font-size: 14px; color: #7c726a; margin: 0;">Harvested batches. Click a row to view its daily monitoring.</p>
  </div>

  <div style="overflow-x: auto; background: #ffffff; border: 1px solid #ece6dd; border-radius: 16px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
      <thead>
        <tr style="background: #faf8f5; border-bottom: 1px solid #ece6dd;">
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">BATCH ID</th>
          <th style="padding: 16px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">NAME</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">BAGS</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">YIELD</th>
          <th style="padding: 16px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">DATE COMPLETED</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($batches as $b)
          <tr onclick="location.href='{{ route('worker.completed', ['batch' => $b->id]) }}'"
            style="border-bottom: 1px solid #f2ede5; cursor: pointer; transition: background 0.15s;"
            onmouseover="this.style.background='#fcfbfa'" onmouseout="this.style.background=''">
            <td style="padding: 16px 20px; font-weight: 700; color: #241c16; font-size: 14px;">{{ $b->code }}</td>
            <td style="padding: 16px 20px; color: #4a4037; font-size: 14px;">{{ $b->name }}</td>
            <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px;">{{ $b->bags }}</td>
            <td style="padding: 16px 20px; text-align: center; font-weight: 800; color: #241c16; font-size: 14px;">{{ $fmt($b->yield_kg) }} kg</td>
            <td style="padding: 16px 20px; text-align: center; color: #4a4037; font-size: 14px;">
              {{ optional($b->updated_at)->format('Y-m-d') ?? '—' }}
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding: 40px 20px; text-align: center; color: #8a7f74;">No completed batches yet.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif
@endsection

