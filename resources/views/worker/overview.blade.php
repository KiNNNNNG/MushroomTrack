@extends('layouts.admin')
@section('content')

<div class="dash-hero" style="position: relative; margin: 0 0 24px 0; border-radius: 12px; overflow: hidden; padding: 40px 30px; background: url('https://images.unsplash.com/photo-1606757519131-016ce3497e88?auto=format&fit=crop&q=80&w=1200') center/cover no-repeat;">
  <div style="position: absolute; inset: 0; background: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.2));"></div>
  <div style="position: relative; z-index: 1;">
    <h1 style="font-size: 32px; font-weight: 800; letter-spacing: -0.5px; color: #fff;">Hello, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
    <p style="color: rgba(255,255,255,0.8); font-size: 16px; margin-top: 4px;">Worker</p>
  </div>
</div>

<div class="stats" style="margin-bottom: 30px;">
  <div class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; display: grid; place-items: center; color: var(--green);">
        <x-icon n="batches" s="18" />
      </div>
      <small style="margin: 0; flex: 1;">Active Fruiting</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ $fruiting }}</strong>
  </div>
  
  <div class="card stat" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; display: grid; place-items: center; color: #3b82f6;">
        <x-icon n="history" s="18" />
      </div>
      <small style="margin: 0; flex: 1;">Incubating</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ $incubating }}</strong>
  </div>

  <div class="card stat g" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffaf2; display: grid; place-items: center; color: var(--gold);">
        <x-icon n="scale" s="18" />
      </div>
      <small style="margin: 0; flex: 1;">Total Yield</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ rtrim(rtrim(number_format($yield, 1), '0'), '.') }} kg</strong>
  </div>

  <div class="card stat r" style="display: flex; flex-direction: column; gap: 12px; height: auto; padding: 16px;">
    <div style="display: flex; align-items: center; gap: 8px;">
      <div style="width: 32px; height: 32px; border-radius: 8px; background: #fff1f2; display: grid; place-items: center; color: var(--dred);">
        <x-icon n="trash" s="18" />
      </div>
      <small style="margin: 0; flex: 1;">Bags Lost</small>
    </div>
    <strong style="margin: 0; font-size: 24px;">{{ $lost }}</strong>
  </div>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
  <h2 style="font-size: 16px; font-weight: 700; color: var(--brown);">Recent Logs</h2>
  <a href="{{ route('worker.history') }}" style="font-size: 12px; color: var(--gold); font-weight: 700; text-transform: uppercase;">View All</a>
</div>

<div class="card logs" style="margin-top:0;">
  @forelse ($logs as $l)
    <div class="lr" style="padding: 16px 20px;">
      <div class="l">
        <div class="th" @style(["background-image: url('".$l->photo_url."')" => $l->photo_url])></div>
        <div><b style="font-size: 15px;">{{ $l->batch->code }}</b><small style="margin-top:2px;">Today</small></div>
      </div>
      <span class="hl {{ $l->result === 'healthy' ? '' : ($l->result === 'contaminated' ? 'bad' : 'warn') }}">{{ $l->result_label }}</span>
    </div>
  @empty
    <div class="empty" style="padding:30px; text-align: center;">No monitoring logs yet.</div>
  @endforelse
</div>

@endsection
