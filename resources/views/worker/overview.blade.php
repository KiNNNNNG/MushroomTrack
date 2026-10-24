@extends('layouts.admin')
@section('content')
@php $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2), '0'), '.'); @endphp

<div style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Operations Overview</h1>
  <p style="font-size: 14px; color: #7c726a; margin: 0;">Farm status at a glance.</p>
</div>

{{-- 4 Stat Cards matching mockup --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
  <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02);">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">ACTIVE FRUITING</div>
    <div style="font-size: 32px; font-weight: 800; color: #241c16; margin-top: 8px;">{{ $fruiting }}</div>
  </div>

  <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02);">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">INCUBATING</div>
    <div style="font-size: 32px; font-weight: 800; color: #241c16; margin-top: 8px;">{{ $incubating }}</div>
  </div>

  <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02);">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">TOTAL YIELD</div>
    <div style="font-size: 32px; font-weight: 800; color: #b28135; margin-top: 8px;">{{ $fmt($yield) }} kg</div>
  </div>

  <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; padding: 20px 22px; box-shadow: 0 2px 8px rgba(40,30,20, 0.02);">
    <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">BAGS LOST</div>
    <div style="font-size: 32px; font-weight: 800; color: #cf3b3b; margin-top: 8px;">{{ $lost }}</div>
  </div>
</div>

{{-- Recent Monitoring Logs Header --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
  <div style="font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.8px;">RECENT MONITORING LOGS</div>
  <a href="{{ route('worker.history') }}" style="font-size: 11px; font-weight: 700; color: #8c6c3e; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px;">VIEW ALL HISTORY &rarr;</a>
</div>

{{-- Logs Table --}}
<div style="overflow-x: auto; background: #ffffff; border: 1px solid #ece6dd; border-radius: 14px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
  <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
    <thead>
      <tr style="background: #faf8f5; border-bottom: 1px solid #ece6dd;">
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">BATCH</th>
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">DATE</th>
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">WATERED</th>
        <th style="padding: 14px 20px; text-align: center; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">LOST BAGS</th>
        <th style="padding: 14px 20px; text-align: left; font-size: 11px; font-weight: 800; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px;">NOTES</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($logs as $l)
        @php $lostBags = $l->loss ? $l->loss->quantity : 0; @endphp
        <tr style="border-bottom: 1px solid #f2ede5; transition: background 0.15s;" onmouseover="this.style.background='#fcfbfa'" onmouseout="this.style.background=''">
          <td style="padding: 14px 20px; font-weight: 700; color: #241c16;">{{ $l->batch->code ?? '—' }}</td>
          <td style="padding: 14px 20px; color: #4a4037;">{{ $l->created_at->format('Y-m-d') }}</td>
          <td style="padding: 14px 20px; color: #4a4037;">{{ $l->watered ? 'Yes' : 'No' }}</td>
          <td style="padding: 14px 20px; text-align: center;">
            @if ($lostBags > 0)
              <span style="color: #cf3b3b; font-weight: 700;">{{ $lostBags }}</span>
            @else
              <span style="color: #8a7f74;">&mdash;</span>
            @endif
          </td>
          <td style="padding: 14px 20px; color: #5a5047; max-width: 320px;">
            {{ $l->notes ?: 'No notes' }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="5" style="padding: 40px 20px; text-align: center; color: #8a7f74;">No monitoring logs yet.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection

