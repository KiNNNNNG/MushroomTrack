@extends('layouts.admin')
@section('content')

{{-- -- Hero / Greeting ----------------------------------------------- --}}
<div class="dash-hero" style="position: relative; margin: 0 0 24px 0; border-radius: 12px; overflow: hidden; padding: 36px 30px; background: url('https://images.unsplash.com/photo-1606757519131-016ce3497e88?auto=format&fit=crop&q=80&w=1200') center/cover no-repeat;">
  <div style="position: absolute; inset: 0; background: linear-gradient(to right, rgba(40,30,24,0.88), rgba(40,30,24,0.4));"></div>
  <div style="position: relative; z-index: 1;">
    <span style="display: inline-block; background: var(--gold); color: #fff; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px; letter-spacing: 0.8px; text-transform: uppercase; margin-bottom: 8px;">Cultivation Operations</span>
    <h1 style="font-size: 28px; font-weight: 800; letter-spacing: -0.5px; color: #fff;">Hello, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
    <p style="color: rgba(255,255,255,0.85); font-size: 14px; margin-top: 4px;">Ready for today's cultivation inspections and harvests.</p>
  </div>
</div>

{{-- -- Section: Today's Tasks & Quick Workflow Actions ------------- --}}
<div style="margin-bottom: 30px;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); text-transform: uppercase; letter-spacing: 0.5px;">Today's Priority Tasks</h2>
      @if($needsCheckCount > 0)
        <span style="background: #fffbeb; color: var(--amber); border: 1px solid #fde68a; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">{{ $needsCheckCount }} need daily check</span>
      @else
        <span style="background: #ecfdf5; color: var(--green); border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">All daily checks complete</span>
      @endif
    </div>
  </div>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
    {{-- Task Action 1: Fruiting Check --}}
    <div class="card" style="padding: 18px; display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid var(--green);">
      <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
          <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; color: var(--green); display: grid; place-items: center;">
            <x-icon n="clipboard" s="16" />
          </div>
          <b style="font-size: 14px; color: var(--ink);">Fruiting Inspection</b>
        </div>
        <p style="font-size: 12px; color: var(--muted); margin: 0 0 14px 0;">Record growth condition, check contamination, and monitor bag health.</p>
      </div>
      <a href="{{ route('worker.monitoring') }}" class="btn" style="height: 36px; justify-content: center; font-size: 11px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        START DAILY CHECK
      </a>
    </div>

    {{-- Task Action 2: Batch Registration --}}
    <div class="card" style="padding: 18px; display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid var(--gold);">
      <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
          <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffaf2; color: var(--gold); display: grid; place-items: center;">
            <x-icon n="plus" s="16" />
          </div>
          <b style="font-size: 14px; color: var(--ink);">Batch Registration</b>
        </div>
        <p style="font-size: 12px; color: var(--muted); margin: 0 0 14px 0;">Register newly inoculated substrate bags into the incubation room.</p>
      </div>
      <a href="{{ route('worker.register') }}" class="btn secondary" style="height: 36px; justify-content: center; font-size: 11px;">
        <x-icon n="plus" s="14" />
        REGISTER BAGS
      </a>
    </div>

    {{-- Task Action 3: Harvest Entry --}}
    <div class="card" style="padding: 18px; display: flex; flex-direction: column; justify-content: space-between; border-left: 4px solid #3b82f6;">
      <div>
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
          <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #3b82f6; display: grid; place-items: center;">
            <x-icon n="scale" s="16" />
          </div>
          <b style="font-size: 14px; color: var(--ink);">Log Harvest Weights</b>
        </div>
        <p style="font-size: 12px; color: var(--muted); margin: 0 0 14px 0;">Weigh freshly picked flushes and send yields to store inventory.</p>
      </div>
      <a href="{{ route('worker.harvest') }}" class="btn secondary" style="height: 36px; justify-content: center; font-size: 11px;">
        <x-icon n="scale" s="14" />
        RECORD HARVEST
      </a>
    </div>
  </div>
</div>

{{-- -- Operational KPI Cards ----------------------------------------- --}}
<div class="stats" style="margin-bottom: 30px;">
  <div class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; display: grid; place-items: center; color: var(--green);">
        <x-icon n="batches" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700;">Active Fruiting</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ $fruiting }}</strong>
  </div>
  
  <div class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; display: grid; place-items: center; color: #3b82f6;">
        <x-icon n="history" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700;">Incubating</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ $incubating }}</strong>
  </div>

  <div class="card stat g" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffaf2; display: grid; place-items: center; color: var(--gold);">
        <x-icon n="scale" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700;">Total Yield</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ rtrim(rtrim(number_format($yield, 1), '0'), '.') }} kg</strong>
  </div>

  <div class="card stat r" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fff1f2; display: grid; place-items: center; color: var(--dred);">
        <x-icon n="trash" s="18" />
      </div>
      <small style="margin: 0; flex: 1; font-weight: 700;">Bags Lost</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ $lost }}</strong>
  </div>
</div>

{{-- -- Active Batches Requiring Check -------------------------------- --}}
@if($fruitingBatches->count())
  <div style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
      <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); text-transform: uppercase; letter-spacing: 0.5px;">Active Fruiting Batches</h2>
      <a href="{{ route('worker.monitoring') }}" style="font-size: 12px; color: var(--gold); font-weight: 700; text-transform: uppercase;">View All Monitoring &rarr;</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
      @foreach($fruitingBatches as $fb)
        @php
          $lastLog = $fb->monitoringLogs->first();
          $checkedToday = $lastLog && $lastLog->created_at->isToday();
        @endphp
        <div class="card" style="padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
          <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
              <div>
                <b style="font-size: 15px; color: var(--ink);">{{ $fb->code }}</b>
                <span style="font-size: 12px; color: var(--muted); display: block;">{{ $fb->name }}</span>
              </div>
              <span style="font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 12px; {{ $checkedToday ? 'background: #ecfdf5; color: var(--green);' : 'background: #fffbeb; color: var(--amber);' }}">
                {{ $checkedToday ? 'CHECKED TODAY' : 'NEEDS CHECK' }}
              </span>
            </div>

            <div style="display: flex; gap: 16px; font-size: 12px; color: #475569; margin: 12px 0 16px 0; padding: 10px 12px; background: #faf9f7; border-radius: 8px;">
              <div><span style="color: var(--muted);">Bags:</span> <strong>{{ $fb->bags }}</strong></div>
              <div><span style="color: var(--muted);">Flush:</span> <strong>{{ $fb->flush_count }}/{{ $fb->max_flushes ?? 5 }}</strong></div>
              <div><span style="color: var(--muted);">Est. Harvest:</span> <strong>{{ optional($fb->harvest_date)->format('M d, Y') ?? '—' }}</strong></div>
            </div>
          </div>

          <a href="{{ route('worker.monitoring', ['open' => $fb->code]) }}" class="btn" style="height: 36px; justify-content: center; font-size: 11px; {{ $checkedToday ? 'background: var(--brown);' : '' }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            {{ $checkedToday ? 'REVIEW INSPECTION' : 'START DAILY CHECK' }}
          </a>
        </div>
      @endforeach
    </div>
  </div>
@endif

{{-- -- Recent Logs --------------------------------------------------- --}}
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
  <h2 style="font-size: 16px; font-weight: 800; color: var(--brown); text-transform: uppercase; letter-spacing: 0.5px;">Recent Monitoring Logs</h2>
  <a href="{{ route('worker.history') }}" style="font-size: 12px; color: var(--gold); font-weight: 700; text-transform: uppercase;">View All History &rarr;</a>
</div>

<div class="card logs" style="margin-top:0; overflow: hidden;">
  @forelse ($logs as $l)
    <div class="lr" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--line);">
      <div class="l" style="display: flex; align-items: center; gap: 14px;">
        <div class="th" style="background-image:url('{{ $l->photo_url ?? '' }}'); width: 44px; height: 44px; border-radius: 8px;"></div>
        <div>
          <b style="font-size: 14px; color: var(--ink);">{{ $l->batch?->code ?? 'Batch' }}</b>
          <small style="color: var(--muted); font-size: 11px;">{{ optional($l->created_at)->diffForHumans() ?? 'Recently' }}</small>
        </div>
      </div>
      <div>
        <span class="hl {{ $l->condition === 'good' ? '' : 'warn' }}" style="font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px;">
          {{ strtoupper($l->condition ?? 'HEALTHY') }}
        </span>
      </div>
    </div>
  @empty
    <div style="padding: 30px; text-align: center; color: var(--muted); font-size: 13px;">No recent monitoring logs.</div>
  @endforelse
</div>

@endsection