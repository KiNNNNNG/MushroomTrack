@extends('layouts.admin')
@section('content')

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
  <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #241c16; margin: 0;">AI Detection</h1>
  <a href="{{ route('worker.monitoring') }}"
    style="background: #ffffff; color: #332921; font-size: 13px; font-weight: 700; border: 1px solid #dcd5c9; border-radius: 8px; padding: 8px 20px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
    &larr; BACK
  </a>
</div>

{{-- Offline Warning Banner --}}
<div id="aiOfflineBanner" style="display: none; background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; font-weight: 600; align-items: center; gap: 10px;">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="1" y1="1" x2="23" y2="23"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.58 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
  <span><strong>Internet Required:</strong> AI Detection requires an active internet connection to communicate with the vision model. Manual checks can still be performed offline on the Fruiting Monitoring page.</span>
</div>

<div class="ai-detection-layout">
  {{-- Viewfinder Section --}}
  <div>
    <div style="background: #1c1511; border-radius: 18px; padding: 18px; box-shadow: 0 8px 24px rgba(0,0,0, 0.12);">
      <div id="viewfinderFrame" style="height: 360px; border: 2px solid #b28135; border-radius: 12px; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; background: #1c1511;">
        
        {{-- Default Mushroom Icon or Uploaded Image --}}
        <img id="scanPreview" src="" alt="Scan Preview" style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
        
        <div id="defaultScanGraphic" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
          <svg width="84" height="84" viewBox="0 0 64 64" fill="none" stroke="#d49a3d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 36 C12 20, 20 12, 32 12 C44 12, 52 20, 52 36 Z" />
            <circle cx="24" cy="22" r="2" fill="#d49a3d" />
            <circle cx="38" cy="20" r="2" fill="#d49a3d" />
            <circle cx="32" cy="28" r="2" fill="#d49a3d" />
            <circle cx="44" cy="30" r="2" fill="#d49a3d" />
            <circle cx="20" cy="30" r="2" fill="#d49a3d" />
            <path d="M26 36 L26 48 C26 51, 29 53, 32 53 C35 53, 38 51, 38 48 L38 36" />
          </svg>
          <span style="font-size: 12px; color: #8a7f74; font-weight: 600; margin-top: 12px; letter-spacing: 0.5px;">POSITION MUSHROOM IN FRAME</span>
        </div>
      </div>
    </div>

    <input type="file" id="aiScanFileInput" accept="image/*" capture="environment" style="display: none;" onchange="onScanFilePicked(this)">

    <button type="button" onclick="document.getElementById('aiScanFileInput').click()" id="scanActionBtn"
      style="margin-top: 14px; width: 100%; height: 44px; background: #205e46; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; letter-spacing: 0.3px; transition: background 0.15s ease;"
      onmouseover="this.style.background='#184a36'" onmouseout="this.style.background='#205e46'">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
      SCAN / CAPTURE
    </button>
  </div>

  {{-- Result Card matching mockup --}}
  <div style="background: #ffffff; border: 1px solid #ece6dd; border-radius: 18px; padding: 28px 30px; box-shadow: 0 4px 18px rgba(40,30,20, 0.03);">
    <div style="margin-bottom: 12px;">
      <span style="background: #dcf3eb; color: #2d7862; font-size: 10px; font-weight: 800; padding: 4px 12px; border-radius: 12px; letter-spacing: 0.5px;">RESULT</span>
    </div>

    <h2 id="aiSpeciesTitle" style="font-size: 22px; font-weight: 800; color: #241c16; margin: 0 0 3px;">White Oyster Mushroom</h2>
    <div id="aiConfidenceSub" style="font-size: 13px; color: #7c726a; margin-bottom: 18px;">Pleurotus ostreatus &middot; 96% confidence</div>

    <div style="margin-bottom: 20px;">
      <span style="font-size: 14px; color: #241c16;">Condition: <strong id="aiConditionText" style="color: #241c16;">Fungus gnats detected on caps</strong></span>
    </div>

    <div style="margin-bottom: 20px;">
      <div style="font-size: 13px; font-weight: 800; color: #205e46; margin-bottom: 8px;">What to do:</div>
      <ul id="aiWhatToDoList" style="margin: 0; padding-left: 20px; font-size: 13px; color: #4a4037; line-height: 1.6;">
        <li>Install fine mesh (kulambo) on vents and doors</li>
        <li>Use yellow sticky traps near bags</li>
        <li>Avoid over-watering; keep floor dry</li>
        <li>Remove and discard decaying bags immediately</li>
      </ul>
    </div>

    <div>
      <div style="font-size: 13px; font-weight: 800; color: #b42318; margin-bottom: 8px;">Prevention:</div>
      <ul id="aiPreventionList" style="margin: 0; padding-left: 20px; font-size: 13px; color: #4a4037; line-height: 1.6;">
        <li>Sanitize room weekly</li>
        <li>Keep substrate pasteurized</li>
      </ul>
    </div>
  </div>
</div>

<script>
function updateAiDetectionOnlineStatus() {
  var b = document.getElementById('aiOfflineBanner');
  if (b) b.style.display = navigator.onLine ? 'none' : 'flex';
}
window.addEventListener('online', updateAiDetectionOnlineStatus);
window.addEventListener('offline', updateAiDetectionOnlineStatus);
document.addEventListener('DOMContentLoaded', updateAiDetectionOnlineStatus);

function onScanFilePicked(input) {
  var file = input.files[0];
  if (!file) return;

  if (!navigator.onLine) {
    alert('AI Vision Detection requires an active internet connection. Please connect to the internet to analyze photos with AI.');
    input.value = '';
    return;
  }

  var reader = new FileReader();
  reader.onload = function(e) {
    document.getElementById('scanPreview').src = e.target.result;
    document.getElementById('scanPreview').style.display = 'block';
    document.getElementById('defaultScanGraphic').style.display = 'none';
  };
  reader.readAsDataURL(file);

  var btn = document.getElementById('scanActionBtn');
  btn.textContent = 'ANALYZING PHOTO...';
  btn.disabled = true;

  var formData = new FormData();
  formData.append('photo', file);
  formData.append('_token', '{{ csrf_token() }}');

  fetch('{{ route("worker.monitoring.analyze") }}', {
    method: 'POST',
    body: formData,
  })
  .then(function(res) { return res.json(); })
  .then(function(data) {
    btn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> SCAN / CAPTURE';
    btn.disabled = false;

    if (data.error) {
      alert(data.error);
      return;
    }

    if (data.contamination && data.contamination !== 'none') {
      document.getElementById('aiConditionText').textContent = data.contamination_label || data.contamination;
      document.getElementById('aiWhatToDoList').innerHTML = '<li>Isolate the affected bags immediately.</li><li>Sanitize surrounding racks with alcohol.</li><li>Improve ventilation and regulate humidity.</li>';
      document.getElementById('aiPreventionList').innerHTML = '<li>Maintain strict room hygiene.</li><li>Inspect air vents and water filters.</li>';
    } else {
      document.getElementById('aiConditionText').textContent = 'Healthy growth stage (' + (data.stage_label || 'Normal') + ')';
      document.getElementById('aiWhatToDoList').innerHTML = '<li>Maintain current watering schedule.</li><li>Check humidity twice daily.</li><li>Estimated harvest in ' + (data.days_to_harvest || 3) + ' days.</li>';
      document.getElementById('aiPreventionList').innerHTML = '<li>Continue regular misting.</li><li>Ensure good ambient airflow.</li>';
    }
  })
  .catch(function(err) {
    btn.innerHTML = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg> SCAN / CAPTURE';
    btn.disabled = false;
  });
}
</script>
@endsection

