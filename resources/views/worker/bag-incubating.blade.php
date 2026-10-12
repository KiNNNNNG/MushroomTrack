@extends('layouts.admin')
@section('content')

<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; margin-top: -10px;">
  <a href="{{ route('worker.register') }}" style="color: var(--ink);">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
  </a>
  <h1 style="font-size: 16px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Incubating Check</h1>
</div>

<div class="card wf" style="max-width:500px; margin: 0 auto; text-align:center; padding: 30px 20px; border: none; box-shadow: none; background: transparent;">
    
    <div style="width: 180px; height: 180px; margin: 0 auto -20px auto; border-radius: 12px; background: #e9e4de url('https://images.unsplash.com/photo-1590059345229-3733215be132?auto=format&fit=crop&q=80&w=300') center/cover; position: relative; z-index: 1;"></div>
    
    <div class="card" style="padding: 40px 20px 20px 20px; border-radius: 16px; position: relative; z-index: 2; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 24px;">
      <h2 style="font-size: 20px; font-weight: 800; color: var(--ink); margin-bottom: 4px; letter-spacing: -0.5px; border: 0; padding: 0; background: none; text-transform: none;">{{ $batch->code }}</h2>
      <p style="color:var(--muted); font-size: 14px; margin-bottom: 20px; font-weight: 700;">Bag #{{ $bagNum }} of {{ $totalBags }}</p>
      
      <div>
          @if($status === 'good')
              <span style="display: inline-flex; align-items: center; gap: 8px; background:#ecfdf5; border: 1px solid #a7f3d0; color:#059669; font-size:14px; font-weight: 700; padding:8px 16px; border-radius:20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Marked Good
              </span>
          @elseif($status === 'reject')
              <span style="display: inline-flex; align-items: center; gap: 8px; background:#fff1f2; border: 1px solid #fecdd3; color:#e11d48; font-size:14px; font-weight: 700; padding:8px 16px; border-radius:20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Rejected
              </span>
          @else
              <span style="display: inline-flex; align-items: center; gap: 8px; background:#fef2f2; border: 1px solid #fecaca; color:#b91c1c; font-size:14px; font-weight: 700; padding:8px 16px; border-radius:20px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg> Not Scanned Yet
              </span>
          @endif
      </div>
    </div>

    <p style="color:var(--ink); margin-bottom: 24px; font-size: 16px; font-weight: 700;">Is this bag ready for fruiting?</p>

    <!-- Form / Buttons -->
    <form action="{{ route('worker.bag.scan', ['batch' => $batch->code, 'bag' => $bagNum]) }}" method="POST" style="display:flex; flex-direction:column; gap:16px; margin-bottom: 24px;">
        @csrf
        <button type="submit" name="status" value="good" class="btn" style="width: 100%; justify-content: center; background:#059669; font-size:20px; font-weight: 800; padding:20px; border-radius:12px; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><polyline points="16 10 12 14 8 14"></polyline></svg>
            GOOD
        </button>

        <button type="submit" name="status" value="reject" class="btn" style="width: 100%; justify-content: center; background:#e11d48; font-size:20px; font-weight: 800; padding:20px; border-radius:12px; box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            REJECT
        </button>
    </form>

    <a href="{{ route('worker.register') }}" class="btn ghost" style="width: 100%; justify-content: center; border-radius: 12px; padding: 16px; font-size: 14px; color: var(--brown); background: #f6f4f1;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
      Back to List
    </a>
</div>
@endsection
