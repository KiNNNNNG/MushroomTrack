@extends('layouts.admin')
@section('content')

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <div>
    <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0 0 4px;">Fruiting Batches</h1>
    <p style="font-size: 14px; color: #7c726a; margin: 0;">Active batches under daily monitoring.</p>
  </div>
  <a href="{{ route('worker.ai-detection') }}"
    onclick="if(!navigator.onLine){event.preventDefault();alert('AI Detection requires an active internet connection. Offline mode is enabled for manual fruiting checks.');}"
    style="background: #205e46; color: #ffffff; font-size: 13px; font-weight: 700; border-radius: 8px; padding: 10px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 7px; letter-spacing: 0.3px; transition: background 0.15s ease;"
    onmouseover="this.style.background='#184a36'" onmouseout="this.style.background='#205e46'">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    AI DETECTION
  </a>
</div>

{{-- Offline Banner --}}
<div id="offlineNoticeBanner" style="display: none; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600; align-items: center; justify-content: space-between;">
  <div style="display: flex; align-items: center; gap: 10px;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.58 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
    <div>
      <span style="font-weight: 800; color: #78350f;">OFFLINE MODE:</span>
      <span>You can record daily fruiting checks offline. Submissions will be safely saved on your device and automatically synced once an internet connection is re-established. (AI vision requires internet).</span>
    </div>
  </div>
</div>

{{-- Pending Sync Banner --}}
<div id="pendingSyncBanner" style="display: none; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600; align-items: center; justify-content: space-between;">
  <div style="display: flex; align-items: center; gap: 10px;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    <span><strong id="pendingSyncCount">0</strong> offline check(s) pending sync.</span>
  </div>
  <button type="button" onclick="syncOfflineDailyChecks()" style="background: #2563eb; color: #ffffff; border: none; border-radius: 6px; padding: 7px 16px; font-size: 12px; font-weight: 700; cursor: pointer;">
    SYNC NOW
  </button>
</div>

{{-- Fruiting Batches Cards Grid --}}
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
  @forelse ($batches as $b)
    <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 16px; padding: 22px 24px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03); display: flex; flex-direction: column;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
        <span style="font-size: 18px; font-weight: 800; color: #241c16;">{{ $b->code }}</span>
        <span style="background: #dcf3eb; color: #2d7862; font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 12px; letter-spacing: 0.4px;">FRUITING</span>
      </div>

      <div style="font-size: 14px; color: #7c726a; margin-bottom: 16px;">{{ $b->name }}</div>

      <div style="font-size: 13px; color: #756a60; margin-bottom: 6px;">
        Active Bags: <strong style="color: #241c16;">{{ $b->bags }} units</strong>
      </div>
      <div style="font-size: 13px; color: #756a60; margin-bottom: 22px;">
        Est. Harvest: <strong style="color: #241c16;">{{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</strong>
      </div>

      <button type="button"
        onclick="openDailyCheck({{ $b->id }}, '{{ addslashes($b->code) }}', '{{ addslashes($b->name) }}', {{ $b->bags }})"
        style="margin-top: auto; width: 100%; height: 42px; background: #205e46; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; letter-spacing: 0.4px; transition: background 0.15s ease;"
        onmouseover="this.style.background='#184a36'" onmouseout="this.style.background='#205e46'">
        START DAILY CHECK
      </button>
    </div>
  @empty
    <div style="grid-column: 1 / -1; padding: 60px 0; text-align: center; color: #8a7f74; background: #ffffff; border-radius: 16px; border: 1px solid #ece6dd;">
      No fruiting batches currently in cultivation.
    </div>
  @endforelse
</div>

{{-- Modal 1: Daily Check Initial Form --}}
<dialog id="dailyCheckModal" style="width: min(480px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Daily Check</h3>
    <p id="checkModalBatchInfo" style="font-size: 13px; color: #7c726a; margin: 0 0 20px;">BATCH-001 · White Oyster Premium Lot</p>

    {{-- Watered Toggle --}}
    <div style="margin-bottom: 20px;">
      <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px;">WATERED BATCH?</label>
      <div style="display: flex; gap: 10px;">
        <button type="button" id="btnWateredYes" onclick="setWatered(true)"
          style="flex: 1; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;">
          YES
        </button>
        <button type="button" id="btnWateredNo" onclick="setWatered(false)"
          style="flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
          NO
        </button>
      </div>
    </div>

    {{-- Upload Photos (Multiple) --}}
    <div style="margin-bottom: 24px;">
      <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px;">UPLOAD PHOTOS OF BAGS TO DISPOSE (MULTIPLE)</label>
      
      <input type="file" id="batchPhotosInput" multiple accept="image/*" style="display: none;" onchange="handlePhotosSelected(this)">
      
      <div onclick="document.getElementById('batchPhotosInput').click()"
        style="height: 76px; border: 1.5px dashed #d5cebf; border-radius: 10px; background: #fcfbfa; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; color: #756a60; font-size: 13px; font-weight: 600; transition: border-color 0.15s ease;"
        onmouseover="this.style.borderColor='#205e46'" onmouseout="this.style.borderColor='#d5cebf'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <span>&uarr; Tap to upload photos</span>
      </div>

      {{-- Preview Thumbnails --}}
      <div id="photosGallery" style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px;"></div>
    </div>

    {{-- Modal Footer --}}
    <div style="display: flex; gap: 10px; justify-content: flex-end;">
      <button type="button" onclick="document.getElementById('dailyCheckModal').close()"
        style="padding: 10px 22px; height: 44px; border-radius: 8px; border: 1px solid #dcd5c9; background: #ffffff; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
        CANCEL
      </button>
      <button type="button" id="detectBtn" onclick="runAiDetection()"
        style="padding: 10px 24px; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
        PROCEED TO CHECK
      </button>
    </div>
  </div>
</dialog>

{{-- Modal 2: AI Detection Result (Matching mockup) --}}
<dialog id="aiResultModal" style="width: min(520px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 4px;">AI Detection Result</h3>
    <p id="aiResultSubtitle" style="font-size: 13px; color: #7c726a; margin: 0 0 20px;">AI analysis of uploaded photos</p>

    {{-- Cards List --}}
    <div id="aiResultsList" style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; padding-right: 4px;"></div>

    <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #ece6dd; padding-top: 18px;">
      <div id="aiSummaryFooter" style="font-size: 13px; font-weight: 700; color: #241c16;"></div>
      <button type="button" onclick="finishAiDetection()"
        style="padding: 10px 28px; height: 42px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
        DONE
      </button>
    </div>
  </div>
</dialog>

{{-- Modal 3: Final Check Submission --}}
<dialog id="finalCheckModal" style="width: min(480px, 95vw); border-radius: 20px; border: none; padding: 0; box-shadow: 0 16px 48px rgba(0,0,0, 0.22); background: #ffffff;">
  <div style="padding: 28px 30px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #241c16; margin: 0 0 4px;">Daily Check</h3>
    <p id="finalBatchSubtitle" style="font-size: 13px; color: #7c726a; margin: 0 0 20px;">BATCH-001 · White Oyster Premium Lot</p>

    <form id="finalCheckForm" method="POST">
      @csrf
      <input type="hidden" name="watered" id="finalWateredInput" value="1">

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px;">WATERED BATCH?</label>
        <div style="display: flex; gap: 10px;">
          <button type="button" id="finalBtnWateredYes" onclick="setFinalWatered(true)"
            style="flex: 1; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;">
            YES
          </button>
          <button type="button" id="finalBtnWateredNo" onclick="setFinalWatered(false)"
            style="flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
            NO
          </button>
        </div>
      </div>

      <div style="margin-bottom: 18px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">LOST BAGS</label>
        <input type="text" id="finalLostDisplay" readonly
          style="width: 100%; height: 44px; border: 1px solid #e5ded4; border-radius: 8px; padding: 0 14px; font-size: 14px; font-weight: 600; color: #cf3b3b; background: #fcfbfa; outline: none; box-sizing: border-box;">
        <input type="hidden" name="lost_bags" id="finalLostBagsInput" value="0">
      </div>

      <div style="margin-bottom: 24px;">
        <label style="display: block; font-size: 11px; font-weight: 700; color: #8a7f74; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;">FARMER NOTES (OPTIONAL)</label>
        <textarea name="notes" id="finalNotesInput" rows="3" placeholder="Green mold spotted on left rack. Cleaned the area."
          style="width: 100%; border: 1px solid #e5ded4; border-radius: 8px; padding: 10px 14px; font-size: 14px; color: #241c16; background: #fcfbfa; outline: none; box-sizing: border-box; resize: vertical;"></textarea>
      </div>

      <div style="display: flex; gap: 10px; justify-content: flex-end;">
        <button type="button" onclick="document.getElementById('finalCheckModal').close()"
          style="padding: 10px 22px; height: 44px; border-radius: 8px; border: 1px solid #dcd5c9; background: #ffffff; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;">
          CANCEL
        </button>
        <button type="submit"
          style="padding: 10px 24px; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer; letter-spacing: 0.3px;">
          SUBMIT CHECK
        </button>
      </div>
    </form>
  </div>
</dialog>

<script>
var _curBatchId = null;
var _curBatchCode = '';
var _curBatchName = '';
var _isWatered = true;
var _selectedFiles = [];
var _aiDisposeCount = 0;
var _aiSavedCount = 0;

function openDailyCheck(batchId, code, name, bags) {
  _curBatchId = batchId;
  _curBatchCode = code;
  _curBatchName = name;
  _selectedFiles = [];
  _aiDisposeCount = 0;
  _aiSavedCount = 0;

  document.getElementById('checkModalBatchInfo').textContent = code + ' · ' + name;
  document.getElementById('photosGallery').innerHTML = '';
  document.getElementById('detectBtn').textContent = 'PROCEED TO CHECK';
  document.getElementById('batchPhotosInput').value = '';

  setWatered(true);
  document.getElementById('dailyCheckModal').showModal();
}

function setWatered(isYes) {
  _isWatered = isYes;
  var yesBtn = document.getElementById('btnWateredYes');
  var noBtn  = document.getElementById('btnWateredNo');

  if (isYes) {
    yesBtn.style.cssText = 'flex: 1; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;';
    noBtn.style.cssText  = 'flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;';
  } else {
    noBtn.style.cssText  = 'flex: 1; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;';
    yesBtn.style.cssText = 'flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;';
  }
}

function handlePhotosSelected(input) {
  var gallery = document.getElementById('photosGallery');
  gallery.innerHTML = '';
  _selectedFiles = Array.from(input.files);

  if (_selectedFiles.length > 0) {
    document.getElementById('detectBtn').textContent = 'DETECT (' + _selectedFiles.length + ')';
    
    _selectedFiles.forEach(function(file) {
      var thumb = document.createElement('div');
      thumb.style.cssText = 'width: 54px; height: 54px; border-radius: 8px; background: #ebdfcc; background-size: cover; background-position: center; border: 1px solid #e5ded4;';
      var reader = new FileReader();
      reader.onload = function(e) {
        thumb.style.backgroundImage = 'url(' + e.target.result + ')';
      };
      reader.readAsDataURL(file);
      gallery.appendChild(thumb);
    });
  } else {
    document.getElementById('detectBtn').textContent = 'PROCEED TO CHECK';
  }
}

function runAiDetection() {
  if (!navigator.onLine) {
    alert('AI Detection requires an active internet connection. Proceeding with manual check in offline mode.');
    document.getElementById('dailyCheckModal').close();
    showFinalCheck(0, 0, []);
    return;
  }

  if (!_selectedFiles.length) {
    // Directly go to final check without photos
    document.getElementById('dailyCheckModal').close();
    showFinalCheck(0, 0, []);
    return;
  }

  var formData = new FormData();
  _selectedFiles.forEach(function(file) {
    formData.append('photos[]', file);
  });
  formData.append('_token', '{{ csrf_token() }}');

  document.getElementById('detectBtn').textContent = 'ANALYZING...';
  document.getElementById('detectBtn').disabled = true;

  fetch('{{ route("worker.monitoring.analyzeMultiple") }}', {
    method: 'POST',
    body: formData,
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    document.getElementById('detectBtn').textContent = 'DETECT (' + _selectedFiles.length + ')';
    document.getElementById('detectBtn').disabled = false;
    document.getElementById('dailyCheckModal').close();

    _aiDisposeCount = data.dispose_count || 0;
    _aiSavedCount = data.saved_count || 0;

    renderAiResults(data.results || []);
    document.getElementById('aiResultModal').showModal();
  })
  .catch(function(err) {
    document.getElementById('detectBtn').textContent = 'DETECT (' + _selectedFiles.length + ')';
    document.getElementById('detectBtn').disabled = false;
    alert('AI Vision service is temporarily busy. Proceeding with manual check.');
    document.getElementById('dailyCheckModal').close();
    showFinalCheck(0, 0, []);
  });
}

function renderAiResults(results) {
  var list = document.getElementById('aiResultsList');
  list.innerHTML = '';
  document.getElementById('aiResultSubtitle').textContent = 'AI analysis of ' + results.length + ' uploaded photos';

  results.forEach(function(item) {
    var isDispose = item.status === 'dispose';
    var card = document.createElement('div');
    card.style.cssText = 'border: 1px solid ' + (isDispose ? '#fca5a5' : '#a7f3d0') + '; border-radius: 12px; padding: 14px; background: #ffffff; display: flex; gap: 14px; align-items: flex-start;';

    var iconBox = document.createElement('div');
    iconBox.style.cssText = 'width: 48px; height: 48px; border-radius: 8px; background: #ebdfcc; display: flex; align-items: center; justify-content: center; flex-shrink: 0;';
    iconBox.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a87834" stroke-width="2"><path d="M12 3c-4.97 0-9 3.58-9 8 0 2.2 1.01 4.2 2.68 5.61L5 21l4.49-1.35C10.28 19.85 11.13 20 12 20c4.97 0 9-3.58 9-8s-4.03-8-9-8z"/></svg>';

    var info = document.createElement('div');
    info.style.cssText = 'flex: 1;';

    var top = document.createElement('div');
    top.style.cssText = 'display: flex; align-items: center; gap: 8px; margin-bottom: 4px;';
    
    var badge = document.createElement('span');
    badge.textContent = item.badge;
    badge.style.cssText = isDispose
      ? 'background: #fde8e8; color: #cf3b3b; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 8px;'
      : 'background: #dcf3eb; color: #2d7862; font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 8px;';

    var title = document.createElement('span');
    title.style.cssText = 'font-size: 13px; font-weight: 700; color: #241c16;';
    title.textContent = 'Photo ' + item.index;

    top.appendChild(badge);
    top.appendChild(title);

    var dis = document.createElement('div');
    dis.style.cssText = 'font-size: 12px; color: #241c16; margin-bottom: 2px;';
    dis.innerHTML = '<strong>Disease:</strong> ' + item.disease;

    var sol = document.createElement('div');
    sol.style.cssText = 'font-size: 12px; color: #756a60;';
    sol.innerHTML = '<strong>Solution:</strong> ' + item.solution;

    info.appendChild(top);
    info.appendChild(dis);
    info.appendChild(sol);

    card.appendChild(iconBox);
    card.appendChild(info);
    list.appendChild(card);
  });

  var summaryHtml = '<span style="color:#cf3b3b">' + _aiDisposeCount + ' bag' + (_aiDisposeCount === 1 ? '' : 's') + ' to dispose</span> · <span style="color:#2d7862">' + _aiSavedCount + ' bag' + (_aiSavedCount === 1 ? '' : 's') + ' can be saved</span>';
  document.getElementById('aiSummaryFooter').innerHTML = summaryHtml;
}

function finishAiDetection() {
  document.getElementById('aiResultModal').close();
  showFinalCheck(_aiDisposeCount, _aiSavedCount);
}

function showFinalCheck(disposeCount, savedCount) {
  document.getElementById('finalBatchSubtitle').textContent = _curBatchCode + ' · ' + _curBatchName;
  document.getElementById('finalCheckForm').action = '/worker/monitoring/' + _curBatchId;
  
  setFinalWatered(_isWatered);

  var displayInput = document.getElementById('finalLostDisplay');
  var hiddenInput  = document.getElementById('finalLostBagsInput');

  if (disposeCount > 0) {
    displayInput.value = disposeCount + ' (from AI detection)';
    hiddenInput.value  = disposeCount;
  } else {
    displayInput.value = '0';
    hiddenInput.value  = 0;
  }

  document.getElementById('finalNotesInput').value = disposeCount > 0
    ? 'Defective bags detected and disposed based on AI scan.'
    : '';

  document.getElementById('finalCheckModal').showModal();
}

function setFinalWatered(isYes) {
  _isWatered = isYes;
  document.getElementById('finalWateredInput').value = isYes ? '1' : '0';
  var yesBtn = document.getElementById('finalBtnWateredYes');
  var noBtn  = document.getElementById('finalBtnWateredNo');

  if (isYes) {
    yesBtn.style.cssText = 'flex: 1; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;';
    noBtn.style.cssText  = 'flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;';
  } else {
    noBtn.style.cssText  = 'flex: 1; height: 44px; border-radius: 8px; border: none; background: #205e46; color: #ffffff; font-weight: 700; font-size: 13px; cursor: pointer;';
    yesBtn.style.cssText = 'flex: 1; height: 44px; border-radius: 8px; border: 1px solid #e5ded4; background: #fcfbfa; color: #332921; font-weight: 700; font-size: 13px; cursor: pointer;';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var finalForm = document.getElementById('finalCheckForm');
  if (finalForm) {
    finalForm.addEventListener('submit', function(e) {
      if (!navigator.onLine) {
        e.preventDefault();
        var bId = _curBatchId;
        var bCode = _curBatchCode;
        var bName = _curBatchName;
        var act = finalForm.action;
        var watered = document.getElementById('finalWateredInput').value;
        var lost = parseInt(document.getElementById('finalLostBagsInput').value, 10) || 0;
        var notes = document.getElementById('finalNotesInput').value;

        window.saveOfflineDailyCheck({
          url: act,
          batch_id: bId,
          batch_code: bCode,
          batch_name: bName,
          data: {
            watered: watered,
            lost_bags: lost,
            notes: notes
          },
          saved_at: new Date().toISOString()
        });

        document.getElementById('finalCheckModal').close();

        // Visually update batch button
        var btns = document.querySelectorAll('button[onclick*="openDailyCheck(' + bId + ',"]');
        btns.forEach(function(b) {
          b.style.background = '#8a5c18';
          b.textContent = 'CHECK SAVED (OFFLINE) ✓';
          b.disabled = true;
        });
      }
    });
  }
});
</script>
@endsection

