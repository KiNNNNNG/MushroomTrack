@extends('layouts.admin')
@section('content')

<div style="margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Harvest Entry</h1>
  <p style="font-size: 14px; color: #7c726a; margin: 0;">Each batch allows four harvests, then its bags are disposed. Green border = ready to harvest. Expected yield: <strong>450g – 900g per bag</strong> (7–10 days after fruiting).</p>
</div>

{{-- Fruiting Batches Cards Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
  @forelse ($batches as $b)
    @php
      $isReady = $b->harvest_date && $b->harvest_date->lte(today());
      $minYield = round($b->bags * $yieldMinPerBag, 2);
      $maxYield = round($b->bags * $yieldMaxPerBag, 2);
      $daysFruiting = optional($b->harvest_date)->diffInDays(now(), false);
    @endphp

    <div style="background: #ffffff; border: {{ $isReady ? '2px solid #205e46' : '1px solid #e8e4dc' }}; border-radius: 16px; padding: 22px 24px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); display: flex; flex-direction: column;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
        <span style="font-size: 18px; font-weight: 800; color: #241c16;">{{ $b->code }}</span>
        @if ($isReady)
          <span style="background: #dcf3eb; color: #205e46; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">READY TO HARVEST</span>
        @else
          <span style="background: #fef3c7; color: #92400e; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">MATURING</span>
        @endif
      </div>

      <div style="font-size: 14px; color: #7c726a; margin-bottom: 14px;">{{ $b->name }} &middot; {{ $b->flush_count }}/4 harvests completed</div>

      {{-- Stats grid --}}
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
        <div style="background: #f9f7f4; border-radius: 10px; padding: 10px 12px;">
          <div style="font-size: 10px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">BAGS</div>
          <div style="font-size: 16px; font-weight: 800; color: #241c16;">{{ $b->bags }}</div>
        </div>
        <div style="background: #f9f7f4; border-radius: 10px; padding: 10px 12px;">
          <div style="font-size: 10px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;">EST. HARVEST</div>
          <div style="font-size: 13px; font-weight: 800; color: #241c16;">{{ optional($b->harvest_date)->format('M d, Y') ?? '—' }}</div>
        </div>
      </div>

      {{-- Expected Yield Section --}}
      <div style="background: linear-gradient(135deg, #e8f5e9 0%, #f1faf4 100%); border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
        <div style="font-size: 10px; font-weight: 800; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
          📊 EXPECTED YIELD (450g–900g/bag)
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <div style="font-size: 18px; font-weight: 800; color: #065f46;">{{ $minYield }} – {{ $maxYield }} kg</div>
            <div style="font-size: 11px; color: #059669; margin-top: 2px;">Based on {{ $b->bags }} bags</div>
          </div>
          <div style="text-align: right;">
            <div style="font-size: 11px; color: #065f46; font-weight: 700;">Per bag:</div>
            <div style="font-size: 13px; font-weight: 800; color: #065f46;">450g – 900g</div>
          </div>
        </div>
      </div>

      <div style="margin-top: auto;">
        @if ($isReady)
          <button type="button"
            onclick="openHarvestModal({{ $b->id }}, '{{ addslashes($b->code) }}', {{ $b->bags }}, {{ $minYield }}, {{ $maxYield }}, {{ $b->flush_count + 1 }}, '{{ \Illuminate\Support\Str::uuid() }}')"
            style="width: 100%; height: 42px; background: #205e46; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; letter-spacing: 0.4px; transition: background 0.15s ease;"
            onmouseover="this.style.background='#184a36'" onmouseout="this.style.background='#205e46'">
            RECORD HARVEST
          </button>
        @else
          <button type="button" disabled
            style="width: 100%; height: 42px; background: #ffffff; color: #b5aca2; border: 1px solid #e5ded4; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: not-allowed; letter-spacing: 0.4px;"
            title="Batch not yet ready for harvest">
            NOT READY YET
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

{{-- Record Harvest Modal --}}
<dialog id="recordHarvestModal" style="width: min(480px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Record Harvest</h3>
    <p id="harvestModalSubtitle" style="font-size: 13px; color: #7c726a; margin: 0 0 20px;">Enter the actual yield harvested from this batch.</p>

    <form method="POST" action="{{ route('worker.harvest.store') }}">
      @csrf
      <input type="hidden" name="batch_id" id="harvestBatchId">
      <input type="hidden" name="submission_id" id="harvestSubmissionId">
      <input type="hidden" name="flush_number" id="harvestFlushNumber">

      <div style="margin-bottom: 14px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">BATCH</label>
        <input type="text" id="harvestBatchCode" readonly
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      <div style="margin-bottom: 14px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">BAGS</label>
        <input type="text" id="harvestBags" readonly
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 700; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box;">
      </div>

      {{-- Expected Yield Info box --}}
      <div id="expectedYieldBox" style="background: #e8f5e9; border: 1px solid #a7f3d0; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
        <div style="font-size: 10px; font-weight: 800; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">📊 EXPECTED YIELD RANGE</div>
        <div id="expectedYieldText" style="font-size: 16px; font-weight: 800; color: #065f46;">— kg to — kg</div>
        <div style="font-size: 11px; color: #059669; margin-top: 2px;">Based on 450g–900g per bag. Enter actual yield below.</div>
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">ACTUAL YIELD (KILOGRAMS HARVESTED)</label>
        <input type="number" name="weight_kg" id="harvestWeightKg" step="0.01" min="0.1"
          placeholder="Enter actual kg harvested"
          style="width: 100%; height: 44px; border: 2px solid #205e46; border-radius: 8px; padding: 0 14px; font-size: 16px; font-weight: 700; color: #241c16; background: #ffffff; outline: none; box-sizing: border-box;"
          required>
        <div style="font-size: 11px; color: #7c726a; margin-top: 5px;">This will be automatically added to store stock.</div>
      </div>

      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('recordHarvestModal').close()"
          style="padding: 10px 22px; height: 44px; border-radius: 8px; border: 1px solid #dcd5c9; background: #ffffff; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
          CANCEL
        </button>
        <button type="submit"
          style="padding: 10px 24px; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
          SUBMIT HARVEST
        </button>
      </div>
    </form>
  </div>
</dialog>

<script>
function openHarvestModal(batchId, code, bags, minYield, maxYield, flushNumber, submissionId) {
  document.getElementById('harvestSubmissionId').value = submissionId;
  document.getElementById('harvestFlushNumber').value = flushNumber;
  document.getElementById('harvestBatchId').value = batchId;
  document.getElementById('harvestBatchCode').value = code;
  document.getElementById('harvestBags').value = bags + ' bags';
  document.getElementById('harvestModalSubtitle').textContent = code + ' ? Harvest ' + flushNumber + '/4. ' + (flushNumber === 4 ? 'Dispose of the bags after this harvest.' : 'Enter actual yield harvested.');
  document.getElementById('expectedYieldText').textContent = minYield + ' kg  –  ' + maxYield + ' kg';

  // Suggest the midpoint as a starting value
  var suggested = Math.round((minYield + maxYield) / 2 * 100) / 100;
  document.getElementById('harvestWeightKg').value = suggested;
  document.getElementById('harvestWeightKg').min = '0.1';
  document.getElementById('recordHarvestModal').showModal();
  document.getElementById('harvestWeightKg').focus();
  document.getElementById('harvestWeightKg').select();
}
</script>
@endsection
