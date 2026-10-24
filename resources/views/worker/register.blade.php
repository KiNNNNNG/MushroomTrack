@extends('layouts.admin')
@section('content')

@php
  $latest = \App\Models\Batch::orderBy('id', 'desc')->first();
  $nextId = $latest ? $latest->id + 1 : 1;
  $nextCode = 'BATCH-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
@endphp

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Current Incubating List</h1>
    <p style="font-size: 14px; color: #7c726a; margin: 0;">Batches waiting to move to fruiting.</p>
  </div>
  <button type="button" onclick="document.getElementById('registerBatchModal').showModal()"
    style="background: #205e46; color: #ffffff; font-size: 13px; font-weight: 700; border: none; border-radius: 8px; padding: 10px 18px; cursor: pointer; display: inline-flex; align-items: center; gap: 7px; letter-spacing: 0.3px; transition: background 0.15s ease;"
    onmouseover="this.style.background='#184a36'" onmouseout="this.style.background='#205e46'">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    REGISTER BATCH
  </button>
</div>

{{-- Incubating Batches Cards Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
  @forelse ($batches as $b)
    <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 16px; padding: 22px 24px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); display: flex; flex-direction: column;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <span style="font-size: 16px; font-weight: 800; color: #241c16;">{{ $b->code }} &middot; {{ $b->name }}</span>
        <span style="background: #f3e8d3; color: #9e6b20; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">INCUBATING</span>
      </div>

      <div style="font-size: 13px; color: #756a60; margin-bottom: 22px;">
        Bags: <strong style="color: #241c16;">{{ $b->bags }} units</strong>
      </div>

      <form method="POST" action="{{ route('worker.register.finalize', $b) }}" style="margin-top: auto;">
        @csrf @method('PUT')
        <button type="submit"
          style="width: 100%; height: 40px; background: #8c6c3e; color: #ffffff; border: none; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; letter-spacing: 0.4px; transition: background 0.15s ease;"
          onmouseover="this.style.background='#755830'" onmouseout="this.style.background='#8c6c3e'">
          MOVE TO FRUITING
        </button>
      </form>
    </div>
  @empty
    <div style="grid-column: 1 / -1; padding: 60px 0; text-align: center; color: #8a7f74; background: #ffffff; border-radius: 16px; border: 1px solid #ece6dd;">
      No incubating batches currently waiting. Click "+ Register Batch" to add one.
    </div>
  @endforelse
</div>

{{-- Register Batch Modal matching mockup 849769926_1039589502438127_7886146916531536219_n.png --}}
<dialog id="registerBatchModal" style="width: min(440px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 20px;">Register Batch</h3>

    <form method="POST" action="{{ route('worker.register.store') }}">
      @csrf

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">BATCH NAME (AUTO)</label>
        <input type="text" value="{{ $nextCode }}" readonly
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">NUMBER OF BAGS</label>
        <input type="number" name="bags" value="100" min="1" required
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #ffffff; outline: none; box-sizing: border-box;">
      </div>

      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('registerBatchModal').close()"
          style="padding: 10px 22px; height: 44px; border-radius: 8px; border: 1px solid #dcd5c9; background: #ffffff; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
          CANCEL
        </button>
        <button type="submit"
          style="padding: 10px 24px; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
          SUBMIT
        </button>
      </div>
    </form>
  </div>
</dialog>
@endsection

