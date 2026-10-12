@extends('layouts.admin')
@section('content')

{{-- ── Top bar: QR Camera scan button ─────────────────────────── --}}
<div class="mon-hd">
  <span class="mon-title">Fruiting Batches</span>
  <button type="button" class="btn" id="qrScanBtn">
    <x-icon n="camera" s="17" /> SCAN QR CODE
  </button>
</div>

{{-- ── QR scanner overlay ───────────────────────────────────────── --}}
<dialog id="qrDlg" class="qr-dlg">
  <button type="button" class="cdx" onclick="this.closest('dialog').close()" aria-label="Close">✕</button>
  <h3 style="margin-bottom:14px">Point at a batch QR code</h3>
  <div class="qr-viewport">
    <video id="qrVideo" autoplay playsinline muted></video>
    <div class="qr-frame"></div>
  </div>
  <p id="qrStatus" class="qr-status">Initialising camera…</p>
  <button type="button" class="btn ghost" style="width:100%;margin-top:18px" onclick="stopQr();this.closest('dialog').close()">CANCEL</button>
</dialog>

{{-- ── Batch cards ─────────────────────────────────────────────── --}}
<div class="mgrid">
  @forelse ($batches as $b)
    @php
      $fmt = fn ($n) => rtrim(rtrim(number_format((float)$n, 2), '0'), '.');
    @endphp
    <div class="card mc" style="padding: 16px;">
      <div style="display: flex; gap: 16px; margin-bottom: 16px;">
        <div style="width: 80px; height: 80px; border-radius: 8px; background: #e9e4de url('https://images.unsplash.com/photo-1590059345229-3733215be132?auto=format&fit=crop&q=80&w=200') center/cover; flex-shrink: 0;"></div>
        <div style="flex: 1;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px;">
            <b style="font-size: 16px; color: var(--ink);">{{ $b->code }}</b>
            <span style="background: #ecfdf5; color: #059669; font-size: 10px; font-weight: 700; padding: 4px 8px; border-radius: 12px; letter-spacing: 0.5px;">FRUITING</span>
          </div>
          <div style="color: var(--muted); font-size: 13px; margin-bottom: 8px;">{{ $b->name }}</div>
          
          <div style="display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: #475569;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <x-icon n="batches" s="14" /> <span>Active Bags: <strong style="color:var(--ink)">{{ $b->bags }} units</strong></span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
              <span>Flush: <strong style="color:var(--ink)">{{ $b->flush_count }}/{{ $b->max_flushes ?? 5 }}</strong> @if(!$b->flushes_exhausted)({{ $b->flushes_remaining }} left)@endif</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
              <x-icon n="history" s="14" /> <span>Est. Harvest: <strong style="color:var(--ink)">{{ optional($b->harvest_date)->format('Y-m-d') ?? '—' }}</strong></span>
            </div>
          </div>
        </div>
      </div>

      <button class="btn" style="width: 100%; justify-content: center; background: var(--green); color: #fff;" data-check
              data-action="{{ route('worker.monitoring.store', $b) }}"
              data-code="{{ $b->code }}"
              data-name="{{ $b->name }}"
              data-bags="{{ $b->bags }}"
              data-harvest="{{ optional($b->harvest_date)->format('Y-m-d') ?? '' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
        START DAILY CHECK
      </button>
    </div>
  @empty
    <div class="empty" style="padding:30px; text-align:center;">No batches are fruiting right now.</div>
  @endforelse
</div>


{{-- ── START DAILY CHECK dialog ───────────────────────────────────────── --}}
<dialog id="checkDlg" class="cd">
  <button type="button" class="cdx" onclick="this.closest('dialog').close()" aria-label="Close">✕</button>
  <h3 id="checkTitle"></h3>

  <form method="POST" enctype="multipart/form-data"
        data-confirm="Submit today's check for this batch?"
        data-title="Submit check" data-yes="SUBMIT">@csrf

    <div class="bx"><span>System Time</span><span id="sysTime" style="font-family:'Courier New',monospace;font-size:12px"></span></div>

    {{-- Watered? --}}
    <div class="bx w"><span>Watered Batch?</span>
      <span>
        <label><input type="radio" name="watered" value="1" checked> Yes</label> &nbsp;&nbsp;
        <label><input type="radio" name="watered" value="0"> No</label>
      </span>
    </div>

    {{-- Condition buttons --}}
    <div class="bx w" style="flex-direction:column;align-items:flex-start;gap:12px">
      <span style="font-weight:700;font-size:13px">Mushroom Condition</span>
      <div style="display:flex;gap:12px;width:100%">
        <button type="button" class="cond-btn cond-good" data-cond="good">
          ✅ GOOD
        </button>
        <button type="button" class="cond-btn cond-defect" data-cond="defect">
          ⚠️ DEFECT
        </button>
      </div>
      <input type="hidden" name="condition" id="conditionInput" value="">
      <span id="conditionLabel" class="cond-label" hidden></span>
    </div>

    {{-- Lost bags --}}
    <div class="bx w"><span>Report lost bags <small style="font-weight:400;color:#64748b">(optional)</small></span>
      <span><input type="number" name="lost_bags" id="lostBags" min="0" value="0" style="width:80px;padding:8px;border:1px solid var(--line);border-radius:8px"> bags</span>
    </div>
    <div class="bx w" id="lossReasonRow" hidden><span>Reason</span>
      <select name="loss_reason" id="lossReason" style="padding:8px;border:1px solid var(--line);border-radius:8px;max-width:300px">
        <option value="">Select a reason…</option>
        @foreach (config('mushroomtrack.loss_reasons') as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>

    {{-- Photo capture --}}
    <div class="pl">
      <span class="label" style="font-size:11px">Capture or upload photo</span>
      <span>
        <button type="button" class="mb" data-pick="capture"><x-icon n="camera" s="16" />CAPTURE</button>
        <button type="button" class="mb" data-pick="upload"><x-icon n="upload" s="16" />UPLOAD</button>
      </span>
    </div>
    <input type="file" name="photo" accept="image/*" hidden>
    <div class="pg" id="photoGrid"></div>

    {{-- AI scan button (hidden until photo chosen) --}}
    <div id="aiScanRow" hidden style="margin-bottom:18px">
      <button type="button" id="aiScanBtn" class="btn" style="width:100%;justify-content:center">
        <x-icon n="camera" s="16" /> AI SCAN PHOTO
      </button>
    </div>
    {{-- AI result card --}}
    <div id="aiResult" hidden class="ai-card">
      <div class="ai-card-hd">
        <span class="ai-badge" id="aiBadge"></span>
        <span id="aiConfidence" style="font-size:11px;color:var(--muted)"></span>
        <span id="aiDays" style="font-size:12px;font-weight:700;color:var(--gold)"></span>
      </div>
      <div id="aiStageRow" style="font-size:12px;color:#52617d;margin-bottom:6px"></div>
      <p id="aiSummary" style="font-size:13px;line-height:1.6;margin:0"></p>
      <p style="font-size:11px;color:var(--muted);margin:8px 0 0">Notes auto-filled below. Edit freely before submitting.</p>
    </div>
    <input type="hidden" name="ai_prescan" id="aiPrescan" value="0">

    {{-- Notes --}}
    <div class="label" style="font-size:11px;margin-bottom:10px">Farmer notes</div>
    <textarea name="notes" id="checkNotes" placeholder="Describe anything unusual…"></textarea>

    <button class="sub" disabled>SUBMIT CHECK</button>
  </form>
</dialog>

<script>
const AI_ANALYZE_URL = @json(route('worker.monitoring.analyze'));
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

/* ── QR scanner ─────────────────────────────────────────────────── */
let qrStream = null, qrAnim = null;

document.getElementById('qrScanBtn').onclick = () => {
  document.getElementById('qrDlg').showModal();
  startQr();
};

function startQr() {
  const video = document.getElementById('qrVideo');
  const status = document.getElementById('qrStatus');
  status.textContent = 'Initialising camera…';

  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(stream => {
      qrStream = stream;
      video.srcObject = stream;
      status.textContent = 'Point at a batch QR code…';
      scanLoop(video);
    })
    .catch(() => { status.textContent = 'Camera access denied. Allow camera and try again.'; });
}

function scanLoop(video) {
  if (typeof BarcodeDetector === 'undefined') {
    document.getElementById('qrStatus').textContent = 'QR scanning not supported in this browser. Use Chrome on Android.';
    return;
  }
  const detector = new BarcodeDetector({ formats: ['qr_code'] });
  const tick = () => {
    if (!qrStream) return;
    detector.detect(video).then(codes => {
      if (codes.length) {
        const raw = codes[0].rawValue;
        stopQr();
        document.getElementById('qrDlg').close();
        handleQrResult(raw);
        return;
      }
      qrAnim = requestAnimationFrame(tick);
    }).catch(() => { qrAnim = requestAnimationFrame(tick); });
  };
  video.addEventListener('loadedmetadata', () => { qrAnim = requestAnimationFrame(tick); }, { once: true });
}

function stopQr() {
  cancelAnimationFrame(qrAnim);
  qrStream?.getTracks().forEach(t => t.stop());
  qrStream = null;
}

function handleQrResult(raw) {
  // Support full URL (/worker/scan/BATCH-001) or bare code (BATCH-001)
  const match = raw.match(/\/scan\/([^/?#]+)/);
  const code = match ? match[1] : raw.trim();
  // Find matching batch card and trigger its START DAILY CHECK button
  const btn = [...document.querySelectorAll('[data-check]')].find(b => b.dataset.code === code);
  if (btn) {
    btn.click();
  } else {
    // Redirect to the scan route which will handle it server-side
    window.location.href = '/worker/scan/' + encodeURIComponent(code);
  }
}

document.getElementById('qrDlg').addEventListener('close', stopQr);

/* ── AI Scan ─────────────────────────────────────────────────────── */
const aiScanRow = document.getElementById('aiScanRow');
const aiScanBtn = document.getElementById('aiScanBtn');
const aiResult  = document.getElementById('aiResult');
const aiPrescan = document.getElementById('aiPrescan');

if (aiScanBtn) {
  aiScanBtn.onclick = async () => {
    const fileInput = document.querySelector('#checkDlg input[type=file]');
    if (!fileInput?.files[0]) return;

    aiScanBtn.disabled = true;
    aiScanBtn.textContent = '⏳ Scanning…';

    const fd = new FormData();
    fd.append('photo', fileInput.files[0]);
    fd.append('_token', CSRF);

    try {
      const res = await fetch(AI_ANALYZE_URL, { method: 'POST', body: fd });

      if (res.status === 503) {
        // AI not configured — show setup instructions
        aiResult.hidden = false;
        aiResult.className = 'ai-card ai-card-warn';
        aiResult.innerHTML = `
          <div class="ai-card-hd"><span class="ai-badge" style="background:#fef9c3;color:#92400e">⚙️ AI NOT SET UP</span></div>
          <p style="font-size:13px;line-height:1.6;margin:0 0 8px">
            The AI photo scanner needs an API key to work. You can use either <strong>Gemini</strong> or <strong>Claude</strong>.
          </p>
          <p style="font-size:12px;color:var(--muted);margin:0">
            Add <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px">GEMINI_API_KEY=your_key_here</code>
            to your <strong>.env</strong> file, then restart the server.
          </p>`;
        return;
      }


      if (!res.ok) throw new Error('AI error ' + res.status);
      const r = await res.json();
      renderAiResult(r);
    } catch (e) {
      aiResult.hidden = false;
      aiResult.className = 'ai-card';
      aiResult.innerHTML = '<p style="color:#b91c1c">⚠ AI scan failed: ' + e.message + '</p>';
    } finally {
      aiScanBtn.disabled = false;
      aiScanBtn.innerHTML = '🔍 RE-SCAN PHOTO';
    }
  };
}

const STATUS_COLORS = { healthy: '#16a34a', attention: '#d97706', contaminated: '#dc2626', unclear: '#64748b' };
const STATUS_ICONS  = { healthy: '✅', attention: '⚠️', contaminated: '🚨', unclear: '❓' };

function renderAiResult(r) {
  const badge = document.getElementById('aiBadge');
  badge.textContent = (STATUS_ICONS[r.status] || '') + ' ' + r.status.toUpperCase();
  badge.style.background = (STATUS_COLORS[r.status] || '#64748b') + '22';
  badge.style.color = STATUS_COLORS[r.status] || '#64748b';

  document.getElementById('aiConfidence').textContent = r.confidence + ' confidence';
  document.getElementById('aiDays').textContent = r.days_to_harvest != null
    ? (r.days_to_harvest === 0 ? '🌾 Ready to harvest today!' : '🗓 ~' + r.days_to_harvest + ' days to harvest')
    : '';

  let stageText = r.stage_label || r.stage;
  if (r.contamination && r.contamination !== 'none') stageText += ' · ' + (r.contamination_label || r.contamination);
  document.getElementById('aiStageRow').textContent = stageText;
  document.getElementById('aiSummary').textContent = r.summary;

  // Auto-fill notes
  const notes = document.getElementById('checkNotes');
  let autoNote = '[AI] ' + r.summary;
  if (r.days_to_harvest != null) autoNote += (r.days_to_harvest === 0 ? ' Ready to harvest.' : ' Est. harvest in ' + r.days_to_harvest + ' day(s).');
  if (notes.value.trim() === '' || notes.value.startsWith('[AI]')) notes.value = autoNote;

  // Auto-set condition based on AI
  if (r.status === 'healthy') triggerCondition('good');
  else if (r.status === 'contaminated') triggerCondition('defect');

  if (aiPrescan) aiPrescan.value = '1';
  aiResult.hidden = false;
}

/* ── Condition buttons ───────────────────────────────────────────── */
function triggerCondition(cond) {
  document.querySelectorAll('.cond-btn').forEach(b => b.classList.remove('active'));
  const btn = document.querySelector('.cond-btn[data-cond="' + cond + '"]');
  if (btn) btn.classList.add('active');
  const input = document.getElementById('conditionInput');
  const label = document.getElementById('conditionLabel');
  if (input) input.value = cond;
  if (label) {
    label.hidden = false;
    label.textContent = cond === 'good' ? '✅ Marked as GOOD' : '⚠️ Marked as DEFECT';
    label.style.color = cond === 'good' ? '#16a34a' : '#dc2626';
  }
}

document.querySelectorAll('.cond-btn').forEach(b => {
  b.addEventListener('click', () => triggerCondition(b.dataset.cond));
});
</script>
@endsection
