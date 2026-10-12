@extends('layouts.admin')
@section('content')

@if(session('new_batch_id'))
<dialog id="newBatchQrDlg" class="cd" style="text-align:center; padding: 30px;">
  <button type="button" class="cdx" onclick="this.closest('dialog').close()" aria-label="Close">✕</button>
  <h3 style="margin-bottom: 20px;">Batch Registered Successfully!</h3>
  <p style="margin-bottom: 25px;">Please print the QR labels for this new batch.</p>
  <a href="{{ route('worker.batches.labels', ['batch' => session('new_batch_id')]) }}" target="_blank" class="btn" style="width:100%; justify-content:center;">
    <x-icon n="print" s="18" /> DOWNLOAD QR LABELS
  </a>
</dialog>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    document.getElementById('newBatchQrDlg').showModal();
  });
</script>
@endif

<div class="card wf">
  <h2>New Substrate Registry</h2>
  <form class="wb" method="POST" action="{{ route('worker.register.store') }}" data-confirm="Register a new batch to incubating?" data-title="Register batch" data-yes="REGISTER">@csrf
    <label>Starting bags</label>
    <div style="display:flex;gap:10px;">
      <input type="number" name="bags" value="{{ old('bags', 100) }}" min="1" required style="max-width:150px">
      <button class="btn">REGISTER TO INCUBATING</button>
    </div>
  </form>
</div>

<div class="mon-hd" style="margin-top: 30px;">
  <span class="mon-title sec">Current Incubating List</span>
  <button type="button" class="btn" id="qrScanBtn">
    <x-icon n="camera" s="17" /> SCAN BAG QR
  </button>
</div>

<dialog id="qrDlg" class="qr-dlg">
  <button type="button" class="cdx" onclick="this.closest('dialog').close()" aria-label="Close">✕</button>
  <h3 style="margin-bottom:14px">Point at an incubating bag QR</h3>
  <div class="qr-viewport">
    <video id="qrVideo" autoplay playsinline muted></video>
    <div class="qr-frame"></div>
  </div>
  <p id="qrStatus" class="qr-status">Initialising camera…</p>
  <button type="button" class="btn ghost" style="width:100%;margin-top:18px" onclick="stopQr();this.closest('dialog').close()">CANCEL</button>
</dialog>


<div class="mgrid">
  @forelse ($batches as $b)
    @php
      $scanned = $b->scanned_bags ?? [];
      $good = 0;
      $reject = 0;
      foreach ($scanned as $status) {
          if ($status === 'good') $good++;
          elseif ($status === 'reject') $reject++;
      }
      $total = $b->bags + $b->lost_bags;
      $unscanned = max(0, $total - count($scanned));
    @endphp
    <div class="card mc" style="width:432px">
      <div class="mcb">
        <div class="mt1"><span class="mid">ID: {{ $b->code }}</span><span class="tag">INCUBATING</span></div>
        <h3 style="margin:14px 0 8px">{{ $b->name }}</h3>
        
        <div class="mrow"><span>Total Bags</span><span>{{ $total }} units</span></div>
        <div class="mrow"><span>Scanned Good</span><span style="color:var(--green); font-weight:bold;">{{ $good }}</span></div>
        <div class="mrow"><span>Scanned Reject</span><span style="color:var(--red); font-weight:bold;">{{ $reject }}</span></div>
        <div class="mrow" style="margin-bottom:18px"><span>Unscanned</span><span style="color:var(--gray)">{{ $unscanned }}</span></div>
        
        <form method="POST" action="{{ route('worker.register.finalize', $b) }}" data-confirm="Move this batch to fruiting? Unscanned bags will be considered lost." data-title="Move to fruiting" data-yes="MOVE">
            @csrf @method('PUT')
            <button class="btn gold sm2" style="width:100%; justify-content:center;">MOVE TO FRUITING</button>
        </form>
      </div>
    </div>
  @empty
    <div class="empty">No incubating batches.</div>
  @endforelse
</div>

<script>
/* ── QR scanner ─────────────────────────────────────────────────── */
let qrStream = null, qrAnim = null;

const qrScanBtn = document.getElementById('qrScanBtn');
if(qrScanBtn) {
    qrScanBtn.onclick = () => {
      document.getElementById('qrDlg').showModal();
      startQr();
    };
}

function startQr() {
  const video = document.getElementById('qrVideo');
  const status = document.getElementById('qrStatus');
  status.textContent = 'Initialising camera…';

  navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    .then(stream => {
      qrStream = stream;
      video.srcObject = stream;
      status.textContent = 'Point at an incubating bag QR code…';
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
  if (qrAnim) cancelAnimationFrame(qrAnim);
  if (qrStream) qrStream.getTracks().forEach(t => t.stop());
  qrStream = null;
}

function handleQrResult(raw) {
  // If it's a URL, redirect to it. The URL should be something like /worker/bag/BATCH-001/1
  if (raw.includes('/worker/bag/')) {
    window.location.href = raw;
  } else {
    // Attempt fallback
    alert("Scanned QR code doesn't look like a valid bag URL: " + raw);
  }
}

document.getElementById('qrDlg').addEventListener('close', stopQr);
</script>
@endsection
