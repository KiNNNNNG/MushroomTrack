<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>MushFungAI - Offline Mode</title>
  <meta name="theme-color" content="#241c16">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="icon" type="image/png" href="/images/favicon.png">
  <link rel="apple-touch-icon" href="/images/icon-192.png">
  <link rel="manifest" href="/manifest.json">
  <link rel="stylesheet" href="/css/mushroomtrack.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #f9f6ee;
      color: #241c16;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 32px 18px 80px;
    }
    .offline-container {
      width: 100%;
      max-width: 560px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .brand-header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 8px;
    }
    .brand-header img {
      width: 44px;
      height: 44px;
      object-fit: contain;
      filter: drop-shadow(0 2px 6px rgba(0,0,0,0.15));
    }
    .brand-header span {
      font-size: 26px;
      font-weight: 800;
      color: #241c16;
      letter-spacing: -0.5px;
    }
    .brand-header span b {
      color: #a87834;
    }
    .card {
      background: #ffffff;
      border: 1px solid #ece6dd;
      border-radius: 18px;
      padding: 24px;
      box-shadow: 0 4px 20px rgba(40,30,20, 0.04);
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #fef3c7;
      border: 1px solid #fde68a;
      color: #92400e;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.5px;
      margin-bottom: 14px;
    }
    .status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #d97706;
      animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
      0% { transform: scale(0.95); opacity: 0.7; }
      50% { transform: scale(1.25); opacity: 1; }
      100% { transform: scale(0.95); opacity: 0.7; }
    }
    .title {
      font-size: 22px;
      font-weight: 800;
      color: #241c16;
      margin-bottom: 8px;
      letter-spacing: -0.4px;
    }
    .desc {
      font-size: 13.5px;
      line-height: 1.55;
      color: #7c726a;
      margin-bottom: 22px;
    }
    .btn-primary {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      height: 46px;
      background: #205e46;
      color: #ffffff;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: 0.4px;
      text-decoration: none;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      transition: background 0.15s ease;
    }
    .btn-primary:hover {
      background: #184a36;
    }
    .btn-secondary {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      height: 44px;
      background: #ffffff;
      color: #332921;
      font-size: 13px;
      font-weight: 700;
      text-decoration: none;
      border-radius: 10px;
      border: 1px solid #dcd5c9;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.15s ease;
    }
    .btn-secondary:hover {
      background: #fbf9f6;
      border-color: #c4b9a8;
    }
    .queue-title {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
    }
    .queue-title h4 {
      font-size: 15px;
      font-weight: 800;
      color: #241c16;
    }
    .queue-count {
      background: #e8f5e9;
      color: #205e46;
      font-size: 11px;
      font-weight: 800;
      padding: 3px 9px;
      border-radius: 10px;
    }
    .queue-item {
      background: #fcfbfa;
      border: 1px solid #ece6dd;
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .queue-item-name {
      font-size: 13px;
      font-weight: 800;
      color: #241c16;
      margin-bottom: 2px;
    }
    .queue-item-meta {
      font-size: 11.5px;
      color: #7c726a;
    }
    .reconnect-toast {
      display: none;
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      background: #205e46;
      color: #ffffff;
      padding: 12px 24px;
      border-radius: 24px;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 10px 30px rgba(0,0,0,0.25);
      z-index: 999999;
      align-items: center;
      gap: 10px;
    }
  </style>
</head>
<body>

<div id="reconnectToast" class="reconnect-toast">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
  <span>Internet connection restored! Syncing data...</span>
</div>

<div class="offline-container">
  <div class="brand-header">
    <img src="/images/logo-emblem.png" alt="MushFungAI">
    <span>MushFung<b>AI</b></span>
  </div>

  <div class="card">
    <div class="status-badge">
      <div class="status-dot"></div>
      <span>OFFLINE MODE ACTIVE</span>
    </div>

    <h2 class="title">No Internet Connection</h2>
    <p class="desc">
      You are running MushFungAI offline from your local device cache. You can view your fruiting batches and record daily monitoring checks. Submissions are saved securely on your phone and will automatically upload once you're back online.
    </p>

    <a href="/worker/monitoring" class="btn-primary">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
      OPEN FRUITING MONITORING
    </a>

    <a href="/worker/overview" class="btn-secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      VIEW OPERATIONS OVERVIEW
    </a>

    <button type="button" onclick="window.location.reload()" class="btn-secondary">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
      CHECK CONNECTION & RELOAD
    </button>
  </div>

  {{-- Local Pending Queue Card --}}
  <div class="card" id="offlineQueueCard" style="display: none;">
    <div class="queue-title">
      <h4>Pending Offline Submissions</h4>
      <span class="queue-count" id="offlineQueueCount">0</span>
    </div>
    <div id="offlineQueueList"></div>

    <button type="button" onclick="triggerSync()" class="btn-primary" style="margin-top: 14px;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      SYNC PENDING CHECKS NOW
    </button>
  </div>
</div>

<script src="/js/mushroomtrack.js"></script>
<script>
function renderOfflineQueue() {
  var checks = [];
  try {
    checks = JSON.parse(localStorage.getItem('mushfung_offline_checks') || '[]');
  } catch(e) {}

  var card = document.getElementById('offlineQueueCard');
  var list = document.getElementById('offlineQueueList');
  var countEl = document.getElementById('offlineQueueCount');

  if (checks.length > 0) {
    card.style.display = 'block';
    countEl.textContent = checks.length + ' queued';
    list.innerHTML = '';

    checks.forEach(function(item, idx) {
      var d = document.createElement('div');
      d.className = 'queue-item';
      var timeStr = item.saved_at ? new Date(item.saved_at).toLocaleTimeString() : 'Recently';
      d.innerHTML = '<div>' +
        '<div class="queue-item-name">' + (item.batch_code || 'Batch #' + item.batch_id) + ' · ' + (item.batch_name || 'Daily Check') + '</div>' +
        '<div class="queue-item-meta">Watered: ' + (item.data.watered == '1' ? 'Yes' : 'No') + ' · Lost bags: ' + (item.data.lost_bags || 0) + ' · ' + timeStr + '</div>' +
        '</div>' +
        '<button type="button" onclick="removeQueueItem(' + idx + ')" style="background:none;border:none;color:#cf3b3b;font-size:18px;font-weight:bold;cursor:pointer;padding:4px 8px;" title="Remove">&times;</button>';
      list.appendChild(d);
    });
  } else {
    card.style.display = 'none';
  }
}

function removeQueueItem(idx) {
  if (confirm('Discard this offline check?')) {
    var checks = JSON.parse(localStorage.getItem('mushfung_offline_checks') || '[]');
    checks.splice(idx, 1);
    localStorage.setItem('mushfung_offline_checks', JSON.stringify(checks));
    renderOfflineQueue();
  }
}

function triggerSync() {
  if (!navigator.onLine) {
    alert('Still offline. Please connect to Wi-Fi or mobile data first.');
    return;
  }
  if (window.syncOfflineDailyChecks) {
    window.syncOfflineDailyChecks();
    setTimeout(renderOfflineQueue, 1500);
  }
}

document.addEventListener('DOMContentLoaded', renderOfflineQueue);

window.addEventListener('online', function() {
  var toast = document.getElementById('reconnectToast');
  if (toast) toast.style.display = 'flex';
  if (window.syncOfflineDailyChecks) {
    window.syncOfflineDailyChecks();
  }
  setTimeout(function() {
    window.location.href = '/worker/monitoring';
  }, 1200);
});
</script>
</body>
</html>
