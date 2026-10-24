@extends('layouts.admin')
@section('content')

<div style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Harvest Entry</h1>
  <p style="font-size: 14px; color: #7c726a; margin: 0;">All fruiting batches. Green border means ready to harvest.</p>
</div>

{{-- Fruiting Batches Cards Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
  @forelse ($batches as $b)
    @php
      $isReady = $b->harvest_date && $b->harvest_date->lte(today());
    @endphp

    <div style="background: #ffffff; border: {{ $isReady ? '2px solid #205e46' : '1px solid #e8e4dc' }}; border-radius: 16px; padding: 22px 24px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); display: flex; flex-direction: column;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
        <span style="font-size: 18px; font-weight: 800; color: #241c16;">{{ $b->code }}</span>
        @if ($isReady)
          <span style="background: #dcf3eb; color: #205e46; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">READY TO HARVEST</span>
        @endif
      </div>

      <div style="font-size: 14px; color: #7c726a; margin-bottom: 16px;">{{ $b->name }}</div>

      <div style="font-size: 13px; color: #756a60; margin-bottom: 6px;">
        Bags: <strong style="color: #241c16;">{{ $b->bags }}</strong>
      </div>
      <div style="font-size: 13px; color: #756a60; margin-bottom: 22px;">
        Est. Harvest: <strong style="color: #241c16;">{{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</strong>
      </div>

      <div style="margin-top: auto;">
        @if ($isReady)
          <button type="button"
            onclick="openHarvestModal({{ $b->id }}, '{{ addslashes($b->code) }}', {{ $b->bags }})"
            style="width: 100%; height: 42px; background: #205e46; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; letter-spacing: 0.4px; transition: background 0.15s ease;"
            onmouseover="this.style.background='#184a36'" onmouseout="this.style.background='#205e46'">
            HARVEST
          </button>
        @else
          <button type="button" disabled
            style="width: 100%; height: 42px; background: #ffffff; color: #b5aca2; border: 1px solid #e5ded4; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: not-allowed; letter-spacing: 0.4px;">
            HARVEST
          </button>
        @endif
      </div>
    </div>
  @empty
    <div style="grid-column: 1 / -1; padding: 60px 0; text-align: center; color: #8a7f74; background: #ffffff; border-radius: 16px; border: 1px solid #ece6dd;">
      No fruiting batches to harvest at this time.
    </div>
  @endforelse
</div>

{{-- Record Harvest Modal matching mockup 847629225_28925312750441790_7416823251096849814_n.png --}}
<dialog id="recordHarvestModal" style="width: min(440px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 20px;">Record Harvest</h3>

    <form method="POST" action="{{ route('worker.harvest.store') }}">
      @csrf
      <input type="hidden" name="batch_id" id="harvestBatchId">

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">BATCH NAME (AUTO)</label>
        <input type="text" id="harvestBatchCode" readonly
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 16px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">BAGS</label>
        <input type="text" id="harvestBags" readonly
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">KILOS HARVESTED</label>
        <input type="number" name="weight_kg" step="0.1" min="0.1" value="50.0" required
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #ffffff; outline: none; box-sizing: border-box;">
      </div>

      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('recordHarvestModal').close()"
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

<script>
function openHarvestModal(batchId, code, bags) {
  document.getElementById('harvestBatchId').value = batchId;
  document.getElementById('harvestBatchCode').value = code;
  document.getElementById('harvestBags').value = bags;
  document.getElementById('recordHarvestModal').showModal();
}
</script>
@endsection

