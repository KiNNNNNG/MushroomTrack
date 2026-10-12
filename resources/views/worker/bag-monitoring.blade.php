@extends('layouts.admin')
@section('content')
@php
  $fmt = fn ($n) => rtrim(rtrim(number_format((float)$n, 2), '0'), '.');
  $now = now();
  $harvestDate = $batch->harvest_date;
  $estDispose  = $batch->estimated_dispose_date;
  $expectedYield = $batch->expected_next_yield_kg;
  $lastLog = $logs->first();
  $lastAi  = $lastAiLog;
@endphp

{{-- ── Top bar ─────────────────────────────────────────────────── --}}
<div class="mon-hd" style="margin-bottom:18px">
  <div>
    <span class="mon-title">Bag #{{ $bagNum }}</span>
    <span class="mid" style="margin-left:10px">{{ $batch->code }}</span>
  </div>
  <a class="btn ghost" href="{{ route('worker.monitoring') }}">← MONITORING</a>
</div>

{{-- ── Batch + Bag Summary Card ────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px">

  {{-- Bag Identity --}}
  <div class="card" style="padding:20px">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:6px">Bag Identity</div>
    <div style="font:900 32px 'Courier New',monospace;color:var(--primary,#22c55e)">BAG #{{ $bagNum }}</div>
    <div style="font-weight:700;font-size:18px;margin:4px 0">{{ $batch->name }}</div>
    <div style="font-size:13px;color:var(--muted)">Batch: <span style="font-family:'Courier New',monospace;font-weight:700">{{ $batch->code }}</span></div>
    <div style="margin-top:10px">
      <span class="tag">{{ strtoupper($batch->status) }}</span>
      @if($batch->disposed_at) <span class="tag" style="background:#fee2e2;color:#b91c1c">DISPOSED</span> @endif
    </div>
    <div style="font-size:12px;color:var(--muted);margin-top:10px">
      Bag {{ $bagNum }} of {{ $totalBags }} total bags in this batch
    </div>
  </div>

  {{-- AI Harvest Predictions ──────────────────────────────────────── --}}
  <div class="card" style="padding:20px">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px">🤖 AI Predictions</div>

    {{-- Possible Harvest Date --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
      <span style="font-size:13px;color:var(--ink)">📅 Possible Harvest</span>
      <span style="font-weight:700;font-size:14px">
        @if($lastAi && $lastAi->ai_days_to_harvest !== null)
          @php $aiHarvest = $now->copy()->addDays($lastAi->ai_days_to_harvest); @endphp
          @if($lastAi->ai_days_to_harvest === 0)
            <span style="color:#16a34a">🌾 Ready Today!</span>
          @else
            {{ $aiHarvest->format('M j, Y') }}
            <small style="color:var(--muted);font-weight:400">(~{{ $lastAi->ai_days_to_harvest }}d)</small>
          @endif
        @elseif($harvestDate)
          {{ $harvestDate->format('M j, Y') }}
          <small style="color:var(--muted);font-weight:400">(planned)</small>
        @else
          <span style="color:var(--muted)">—</span>
        @endif
      </span>
    </div>

    {{-- Expected Yield --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
      <span style="font-size:13px;color:var(--ink)">⚖️ Expected Next Yield</span>
      <span style="font-weight:700;font-size:14px">
        ~{{ $fmt($expectedYield) }} kg
        <small style="color:var(--muted);font-weight:400">(est.)</small>
      </span>
    </div>

    {{-- Flush Count --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding-bottom:10px;border-bottom:1px solid var(--line)">
      <span style="font-size:13px;color:var(--ink)">🔁 Harvest Flushes</span>
      <span style="font-weight:700;font-size:14px">
        {{ $batch->flush_count }} / {{ $batch->max_flushes ?? 5 }}
        @if($batch->flushes_exhausted)
          <span style="color:#dc2626"> ⚠ Exhausted</span>
        @else
          <small style="color:var(--muted);font-weight:400">({{ $batch->flushes_remaining }} left)</small>
        @endif
      </span>
    </div>

    {{-- Dispose Date --}}
    <div style="display:flex;justify-content:space-between;align-items:center">
      <span style="font-size:13px;color:var(--ink)">🗑️ Dispose By</span>
      <span style="font-weight:700;font-size:14px">
        @if($batch->disposed_at)
          <span style="color:#dc2626">{{ $batch->disposed_at->format('M j, Y') }}</span>
        @elseif($batch->flushes_exhausted)
          <span style="color:#dc2626">Now (max flushes reached)</span>
        @elseif($lastAi && $lastAi->ai_stage === 'past_peak')
          <span style="color:#d97706">⚠ AI: Past peak — consider disposing</span>
        @elseif($estDispose)
          {{ $estDispose->format('M j, Y') }}
          <small style="color:var(--muted);font-weight:400">(est.)</small>
        @else
          <span style="color:var(--muted)">—</span>
        @endif
      </span>
    </div>
  </div>

  {{-- Latest AI Analysis ──────────────────────────────────────────── --}}
  @if($lastAi && $lastAi->ai_summary)
  <div class="card ai-card" style="padding:20px;border-left:4px solid
    @if($lastAi->ai_status === 'healthy') #16a34a
    @elseif($lastAi->ai_status === 'contaminated') #dc2626
    @elseif($lastAi->ai_status === 'attention') #d97706
    @else #64748b @endif">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:8px">Latest AI Review</div>
    <div class="ai-card-hd" style="margin-bottom:8px">
      <span class="ai-badge" style="background:
        @if($lastAi->ai_status === 'healthy') #dcfce7;color:#15803d
        @elseif($lastAi->ai_status === 'contaminated') #fee2e2;color:#b91c1c
        @elseif($lastAi->ai_status === 'attention') #fef9c3;color:#92400e
        @else #f1f5f9;color:#64748b @endif">
        {{ strtoupper($lastAi->ai_status ?? 'UNKNOWN') }}
      </span>
      <span style="font-size:11px;color:var(--muted);margin-left:8px">{{ $lastAi->ai_confidence ?? '' }} confidence</span>
    </div>
    @if($lastAi->ai_line)
      <div style="font-size:12px;color:#52617d;margin-bottom:8px">{{ $lastAi->ai_line }}</div>
    @endif
    <p style="font-size:13px;line-height:1.6;margin:0">{{ $lastAi->ai_summary }}</p>
    <div style="font-size:11px;color:var(--muted);margin-top:8px">
      Reviewed: {{ $lastAi->ai_analyzed_at->format('M j, Y g:i a') }}
    </div>
  </div>
  @endif

  {{-- Batch Stats ─────────────────────────────────────────────────── --}}
  <div class="card" style="padding:20px">
    <div style="font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:12px">Batch Stats</div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Active Bags</span><span style="font-weight:700">{{ $batch->bags }}</span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Lost Bags</span><span style="font-weight:700;color:#dc2626">{{ $batch->lost_bags }}</span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Total Yield</span><span style="font-weight:700">{{ $fmt($batch->yield_kg) }} kg</span>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:8px">
      <span style="font-size:13px">Health</span>
      <span style="font-weight:700;color:{{ $batch->health >= 70 ? '#16a34a' : ($batch->health >= 40 ? '#d97706' : '#dc2626') }}">
        {{ $batch->health }}%
      </span>
    </div>
    @if($batch->status === 'fruiting')
      <div style="margin-top:14px">
        <a class="btn" style="width:100%;justify-content:center;text-align:center"
           href="{{ route('worker.monitoring', ['open' => $batch->code]) }}">
          📋 DO DAILY CHECK
        </a>
      </div>
    @endif
  </div>
</div>

{{-- ── Recent Monitoring Logs ──────────────────────────────────── --}}
<div class="sec" style="margin-bottom:14px">Recent Batch Monitoring Logs</div>
<div class="card tbl">
  <table>
    <tr>
      <th>Date</th>
      <th>Worker</th>
      <th>Watered</th>
      <th>Result</th>
      <th>AI Review</th>
      <th>Lost Bags</th>
      <th>Photo</th>
    </tr>
    @forelse ($logs as $log)
      <tr>
        <td class="date">{{ $log->created_at->format('M j, Y g:i a') }}</td>
        <td>{{ $log->user?->name ?? '—' }}</td>
        <td style="text-align:center">{{ $log->watered ? '✅' : '❌' }}</td>
        <td><span class="tag" style="background:{{ $log->result === 'healthy' ? '#dcfce7' : '#fef9c3' }};color:{{ $log->result === 'healthy' ? '#15803d' : '#92400e' }}">{{ $log->result_label }}</span></td>
        <td style="font-size:12px;color:var(--muted)">
          @if($log->ai_line)
            <span style="color:{{ $log->ai_status === 'healthy' ? '#16a34a' : ($log->ai_status === 'contaminated' ? '#dc2626' : '#d97706') }}">{{ $log->ai_line }}</span>
          @elseif($log->ai_error)
            <span style="color:#dc2626">AI error</span>
          @else
            <span style="color:var(--muted)">—</span>
          @endif
        </td>
        <td style="text-align:center">
          @if($log->loss)
            <span style="color:#dc2626;font-weight:700">-{{ $log->loss->quantity }}</span>
          @else
            —
          @endif
        </td>
        <td>
          @if($log->photo_url)
            <a href="{{ $log->photo_url }}" target="_blank">
              <img src="{{ $log->photo_url }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--line)">
            </a>
          @else
            —
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="empty">No monitoring logs for this batch yet.</td></tr>
    @endforelse
  </table>
</div>
@endsection

