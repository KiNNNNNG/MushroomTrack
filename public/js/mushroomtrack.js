document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('menuBtn'), dd = document.getElementById('menuDd');
  if (btn) {
    btn.onclick = e => { e.stopPropagation(); dd.classList.toggle('open'); };
    document.addEventListener('click', () => dd.classList.remove('open'));
  }
  const el = document.getElementById('salesChart');
  if (!el) return;
  const sales = JSON.parse(el.dataset.sales || '[]');
  const W = 1470, H = 330, L = 112, R = 1434, T = 22, B = 312;
  const max = Math.max(1000, Math.ceil(Math.max(0, ...sales.map(s => s.amount)) / 250) * 250);
  const y = v => B - (v / max) * (B - T);
  let g = '';
  [0, .25, .5, .75, 1].map(f => f * max).forEach(v => {
    g += `<text x="${L - 10}" y="${y(v) + 4}" text-anchor="end" font-size="12" fill="#8a96b0">${v}</text>`;
    if (v > 0) g += `<line x1="${L}" x2="${R}" y1="${y(v)}" y2="${y(v)}" stroke="#cbd2de" stroke-dasharray="4 3"/>`;
    g += `<line x1="${L - 6}" x2="${L}" y1="${y(v)}" y2="${y(v)}" stroke="#8a96b0"/>`;
  });
  const n = sales.length;
  const pts = sales.map((s, i) => ({ ...s, x: L + (R - L) * (i + .5) / n }));
  const line = n > 1 ? `<polyline fill="none" stroke="#c48a3a" stroke-width="2" points="${pts.map(p => p.x + ',' + y(p.amount)).join(' ')}"/>` : '';
  const dots = pts.map(p => `<circle cx="${p.x}" cy="${y(p.amount)}" r="5" fill="#fff" stroke="#c48a3a" stroke-width="3"/>
    <line x1="${p.x}" x2="${p.x}" y1="${B}" y2="${B + 5}" stroke="#8a96b0"/>
    <text x="${p.x}" y="${B + 22}" text-anchor="middle" font-size="12" fill="#8a96b0">${p.date}</text>`).join('');
  el.innerHTML = `<svg viewBox="0 0 ${W} ${H + 25}" width="100%" style="overflow:visible"><line x1="${L}" x2="${L}" y1="${T - 8}" y2="${B}" stroke="#8a96b0"/><line x1="${L}" x2="${R}" y1="${B}" y2="${B}" stroke="#8a96b0"/>${g}${line}${dots}</svg>`;
});

/* ---- dropdowns, daily check modal, monitoring viewer ---- */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-dd]').forEach(b => b.onclick = e => {
    e.stopPropagation();
    const d = b.nextElementSibling, wasOpen = d.classList.contains('open');
    document.querySelectorAll('.dd.open').forEach(x => x.classList.remove('open'));
    if (!wasOpen) d.classList.add('open');
  });
  document.addEventListener('click', () => document.querySelectorAll('.dd.open').forEach(d => d.classList.remove('open')));

  const clock = document.getElementById('sysTime');
  if (clock) { const tick = () => clock.textContent = new Date().toLocaleString(); tick(); setInterval(tick, 1000); }

  const dlg = document.getElementById('checkDlg');
  if (dlg) {
    const form = dlg.querySelector('form'), file = form.querySelector('input[type=file]'),
          grid = document.getElementById('photoGrid'), sub = form.querySelector('.sub');
    const lostIn = form.querySelector('#lostBags'), reasonRow = form.querySelector('#lossReasonRow'), reasonSel = form.querySelector('#lossReason');
    if (lostIn) lostIn.addEventListener('input', () => {
      const need = (parseInt(lostIn.value, 10) || 0) > 0;
      reasonRow.hidden = !need; reasonSel.required = need; if (!need) reasonSel.value = '';
    });
    document.querySelectorAll('[data-check]').forEach(b => b.onclick = () => {
      form.reset(); grid.innerHTML = ''; sub.disabled = true;
      form.action = b.dataset.action;
      if (lostIn) { lostIn.max = b.dataset.bags || ''; lostIn.dispatchEvent(new Event('input')); }
      document.getElementById('checkTitle').textContent = 'Daily Fruiting Check: ' + b.dataset.name;
      // Reset AI + condition state
      const aiScanRow = document.getElementById('aiScanRow');
      const aiResult = document.getElementById('aiResult');
      const condInput = document.getElementById('conditionInput');
      const condLabel = document.getElementById('conditionLabel');
      const aiPrescan = document.getElementById('aiPrescan');
      if (aiScanRow) aiScanRow.hidden = true;
      if (aiResult) { aiResult.hidden = true; aiResult.innerHTML = ''; }
      if (condInput) condInput.value = '';
      if (condLabel) condLabel.hidden = true;
      if (aiPrescan) aiPrescan.value = '0';
      document.querySelectorAll('.cond-btn').forEach(cb => cb.classList.remove('active'));
      dlg.showModal();
    });
    // Opened from a scanned QR code: /worker/monitoring?open=BATCH-001
    const wanted = new URLSearchParams(location.search).get('open');
    if (wanted) {
      const btn = [...document.querySelectorAll('[data-check]')].find(x => x.dataset.code === wanted);
      if (btn) btn.click();
      history.replaceState(null, '', location.pathname);
    }
    dlg.querySelectorAll('[data-pick]').forEach(b => b.onclick = () => {
      b.dataset.pick === 'capture' ? file.setAttribute('capture', 'environment') : file.removeAttribute('capture');
      file.click();
    });
    file.onchange = () => {
      grid.innerHTML = '';
      const f = file.files[0];
      if (!f) { sub.disabled = true; return; }
      const img = document.createElement('img'); img.src = URL.createObjectURL(f);
      grid.appendChild(img); sub.disabled = false;
      // Show AI scan button
      const aiScanRow = document.getElementById('aiScanRow');
      const aiResult  = document.getElementById('aiResult');
      const aiScanBtn = document.getElementById('aiScanBtn');
      if (aiScanRow) aiScanRow.hidden = false;
      if (aiResult) { aiResult.hidden = true; }
      if (aiScanBtn) { aiScanBtn.disabled = false; aiScanBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> AI SCAN PHOTO'; }
    };
  }

  const vd = document.getElementById('viewDlg');
  if (vd) document.querySelectorAll('[data-view]').forEach(b => b.onclick = () => {
    const d = b.dataset, img = vd.querySelector('#vImg');
    vd.querySelector('#vTitle').textContent = d.batch + ' — ' + d.date;
    img.src = d.photo || ''; img.style.display = d.photo ? 'block' : 'none';
    vd.querySelector('#vMeta').textContent = 'Watered: ' + d.watered + ' · Result: ' + d.result + ' · Lost bags: ' + (d.lost || 'None') + ' · By ' + d.worker;
    vd.querySelector('#vNotes').textContent = d.notes || 'No notes.';
    const ai = vd.querySelector('#vAi');
    if (ai) { ai.hidden = !d.ai; ai.textContent = d.ai ? 'AI review' + (d.aiConf ? ' (' + d.aiConf + ' confidence)' : '') + ': ' + d.ai : ''; }
    vd.showModal();
  });
});

/* ---- centred confirmation dialog ----
   Add data-confirm="message" to a <form> or a submit <button>.
   Optional: data-title, data-yes, data-no, data-tone="danger" */
document.addEventListener('DOMContentLoaded', () => {
  const dlg = document.createElement('dialog');
  dlg.className = 'cf';
  dlg.innerHTML = '<div class="cf-ic"></div><h3></h3><p></p><div class="fa"><button type="button" class="btn ghost" data-no>CANCEL</button><button type="button" class="btn" data-yes>CONFIRM</button></div>';
  document.body.appendChild(dlg);
  const $ = s => dlg.querySelector(s);
  let pending = null;
  let promisePending = null;

  document.addEventListener('submit', e => {
    const form = e.target, sub = e.submitter;
    if (form.dataset.confirmed) return;
    const src = sub && sub.dataset.confirm ? sub : (form.dataset.confirm ? form : null);
    if (!src) return;
    e.preventDefault();
    pending = { form, sub };
    const d = src.dataset, danger = d.tone === 'danger';
    dlg.classList.toggle('danger', danger);
    $('.cf-ic').textContent = danger ? '!' : '?';
    $('h3').textContent = d.title || 'Please confirm';
    $('p').textContent = d.confirm;
    $('[data-yes]').textContent = d.yes || 'CONFIRM';
    $('[data-no]').textContent = d.no || 'CANCEL';
    dlg.showModal();
  });

  $('[data-no]').onclick = () => { dlg.close(); if (promisePending) { promisePending(false); promisePending = null; } };
  dlg.addEventListener('click', e => { if (e.target === dlg) { dlg.close(); if (promisePending) { promisePending(false); promisePending = null; } } });
  dlg.addEventListener('close', () => { if (!dlg.dataset.go) pending = null; delete dlg.dataset.go; });
  $('[data-yes]').onclick = () => {
    if (promisePending) {
      promisePending(true); promisePending = null;
      dlg.close(); return;
    }
    if (!pending) return;
    const { form, sub } = pending; pending = null;
    dlg.dataset.go = '1'; dlg.close();
    form.dataset.confirmed = '1';
    sub ? form.requestSubmit(sub) : form.requestSubmit();
  };

  // Promise-based API: await window.appConfirm('message', { title, yes, tone })
  window.appConfirm = (message, opts = {}) => new Promise(resolve => {
    promisePending = resolve;
    pending = null;
    dlg.classList.toggle('danger', opts.tone === 'danger');
    $('.cf-ic').textContent = opts.tone === 'danger' ? '!' : '?';
    $('h3').textContent = opts.title || 'Please confirm';
    $('p').textContent = message;
    $('[data-yes]').textContent = opts.yes || 'CONFIRM';
    $('[data-no]').textContent = opts.no || 'CANCEL';
    dlg.showModal();
  });
});

/* ---- cart dialog: fetch-based remove + quantity controls ---- */
document.addEventListener('DOMContentLoaded', () => {
  const cartDlg = document.getElementById('cartDlg');
  if (!cartDlg) return;

  const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

  function reloadPage() { location.reload(); }

  function updateCheckoutTotal() {
    let total = 0;
    cartDlg.querySelectorAll('.ci[data-price]').forEach(row => {
      const qty = parseFloat(row.querySelector('.qty-in')?.value || 0);
      const price = parseFloat(row.dataset.price || 0);
      const priceEl = row.querySelector('.ci-price');
      const itemTotal = qty * price;
      if (priceEl) priceEl.textContent = '₱' + itemTotal.toLocaleString('en-PH', { maximumFractionDigits: 2 });
      const chk = row.querySelector('.cart-chk');
      if (!chk || chk.checked) {
        total += itemTotal;
      }
    });
    const totalEl = document.getElementById('cartTotal');
    if (totalEl) totalEl.textContent = '₱' + total.toLocaleString('en-PH', { maximumFractionDigits: 2 });
    const btn = document.getElementById('cartCheckoutBtn');
    if (btn) btn.textContent = 'CHECKOUT (₱' + total.toLocaleString('en-PH', { maximumFractionDigits: 2 }) + ')';
  }

  window.updateCartCheckboxTotal = function() {
    updateCheckoutTotal();
  };

  // Delete selected cart items
  window.cartDeleteSelected = async function() {
    const checked = cartDlg.querySelectorAll('.cart-chk:checked');
    if (!checked.length) {
      alert('Please select at least one item to delete.');
      return;
    }
    const confirmed = await window.appConfirm('Remove the selected items from your cart?', {
      title: 'Remove items',
      yes: 'REMOVE',
      tone: 'danger',
    });
    if (!confirmed) return;
    for (const cb of checked) {
      const row = cb.closest('.ci');
      if (!row) continue;
      await fetch(row.dataset.removeUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf(), 'X-HTTP-Method-Override': 'DELETE', 'Accept': 'application/json' },
      });
    }
    reloadPage();
  };

  cartDlg.addEventListener('click', async e => {
    // REMOVE button
    if (e.target.classList.contains('ci-rm')) {
      const row = e.target.closest('.ci');
      if (!row) return;
      const confirmed = await window.appConfirm('Remove this item from your cart?', {
        title: 'Remove item',
        yes: 'REMOVE',
        tone: 'danger',
      });
      if (!confirmed) return;
      const res = await fetch(row.dataset.removeUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf(), 'X-HTTP-Method-Override': 'DELETE', 'Accept': 'application/json' },
      });
      if (res.ok || res.redirected) { reloadPage(); }
      return;
    }

    // +/− buttons
    if (e.target.classList.contains('qty-btn')) {
      const row = e.target.closest('.ci');
      const input = row?.querySelector('.qty-in');
      if (!input) return;
      const dir = parseFloat(e.target.dataset.dir);
      const step = parseFloat(input.step) || 0.5;
      const min = parseFloat(input.min) || 0.5;
      const max = parseFloat(input.max) || 9999;
      let val = Math.round((parseFloat(input.value) + dir * step) * 100) / 100;
      val = Math.max(min, Math.min(max, val));
      input.value = val;
      updateCheckoutTotal();
      // debounce save
      clearTimeout(row._saveTimer);
      row._saveTimer = setTimeout(async () => {
        const body = new URLSearchParams({ _method: 'PUT', _token: csrf(), quantity_kg: val });
        await fetch(row.dataset.updateUrl, { method: 'POST', body });
      }, 600);
    }
  });

  cartDlg.addEventListener('change', e => {
    if (e.target.classList.contains('qty-in')) {
      const row = e.target.closest('.ci');
      updateCheckoutTotal();
      clearTimeout(row._saveTimer);
      row._saveTimer = setTimeout(async () => {
        const body = new URLSearchParams({ _method: 'PUT', _token: csrf(), quantity_kg: e.target.value });
        await fetch(row.dataset.updateUrl, { method: 'POST', body });
      }, 600);
    }
  });
});


/* ==========================================================================
   DISPLAY SETTINGS (Dark Mode, Font Size, Popover)
   ========================================================================== */
function initDisplaySettings() {
  const theme = localStorage.getItem('mushfung_theme') || 'light';
  const size = localStorage.getItem('mushfung_fontsize') || 'default';

  applyTheme(theme);
  applyFontSize(size);

  document.querySelectorAll('input.themeToggleSwitchGlobal, #themeToggleSwitch').forEach(sw => {
    sw.checked = (theme === 'dark');
  });

  document.querySelectorAll('.dsp-font-pill').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.size === size);
  });
}

function applyTheme(theme) {
  if (theme === 'dark') {
    document.documentElement.classList.add('dark-theme');
  } else {
    document.documentElement.classList.remove('dark-theme');
  }
}

function applyFontSize(size) {
  document.documentElement.classList.remove('font-small', 'font-default', 'font-large');
  document.documentElement.classList.add('font-' + size);

  if (size === 'small') {
    document.documentElement.style.zoom = '88%';
  } else if (size === 'large') {
    document.documentElement.style.zoom = '114%';
  } else {
    document.documentElement.style.zoom = '100%';
  }
}

window.toggleDarkTheme = function(isDark) {
  const theme = isDark ? 'dark' : 'light';
  localStorage.setItem('mushfung_theme', theme);
  applyTheme(theme);

  document.querySelectorAll('input.themeToggleSwitchGlobal, #themeToggleSwitch').forEach(sw => {
    sw.checked = isDark;
  });
};

window.setFontSize = function(size) {
  localStorage.setItem('mushfung_fontsize', size);
  applyFontSize(size);
  document.querySelectorAll('.dsp-font-pill').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.size === size);
  });
};

window.resetDisplaySettings = function() {
  localStorage.removeItem('mushfung_theme');
  localStorage.removeItem('mushfung_fontsize');
  applyTheme('light');
  applyFontSize('default');

  document.querySelectorAll('input.themeToggleSwitchGlobal, #themeToggleSwitch').forEach(sw => {
    sw.checked = false;
  });

  document.querySelectorAll('.dsp-font-pill').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.size === 'default');
  });
};

window.toggleDisplaySettings = function(event) {
  if (event) {
    event.stopPropagation();
    event.preventDefault();
  }
  const pop = document.getElementById('displaySettingsPopover');
  if (pop) {
    pop.classList.toggle('show');
  }
};

window.togglePasswordVisibility = function(inputId, btn) {
  const inp = document.getElementById(inputId);
  if (!inp) return;
  const isPw = inp.type === 'password';
  inp.type = isPw ? 'text' : 'password';
  btn.innerHTML = isPw
    ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.9 17.9A10.4 10.4 0 0 1 12 19c-6 0-10-7-10-7a18.7 18.7 0 0 1 5-5.7M9.9 5.2A9.7 9.7 0 0 1 12 5c6 0 10 7 10 7a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M2 2l20 20"/></svg>'
    : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="7" r="3"/></svg>';
};

document.addEventListener('click', (e) => {
  const pop = document.getElementById('displaySettingsPopover');
  const trig = document.getElementById('profileSettingsTrigger');
  if (pop && pop.classList.contains('show')) {
    if (!pop.contains(e.target) && (!trig || !trig.contains(e.target))) {
      pop.classList.remove('show');
    }
  }
});

document.addEventListener('DOMContentLoaded', () => {
  initDisplaySettings();
  const profTrigger = document.getElementById('profileSettingsTrigger');
  if (profTrigger) {
    profTrigger.onclick = function(e) {
      e.stopPropagation();
      e.preventDefault();
      const pop = document.getElementById('displaySettingsPopover');
      if (pop) pop.classList.toggle('show');
    };
  }
});
initDisplaySettings();

/* ==========================================================================
   PWA & SERVICE WORKER REGISTRATION
   ========================================================================== */
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then(reg => {
        // SW registered successfully
      })
      .catch(err => {
        console.warn('SW registration error:', err);
      });
  });
}

let deferredInstallPrompt = null;
function checkPwaDisplay() {
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  document.querySelectorAll('.pwa-install-btn, [data-pwa-install]').forEach(el => {
    el.style.display = isStandalone ? 'none' : 'inline-flex';
  });
}

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredInstallPrompt = e;
  checkPwaDisplay();
});

document.addEventListener('DOMContentLoaded', checkPwaDisplay);
window.addEventListener('appinstalled', () => {
  deferredInstallPrompt = null;
  document.querySelectorAll('.pwa-install-btn, [data-pwa-install]').forEach(el => el.style.display = 'none');
});

window.installMushFungApp = function() {
  if (deferredInstallPrompt) {
    deferredInstallPrompt.prompt();
    deferredInstallPrompt.userChoice.then((choiceResult) => {
      if (choiceResult.outcome === 'accepted') {
        console.log('App install accepted');
      }
      deferredInstallPrompt = null;
      document.querySelectorAll('.pwa-install-btn, [data-pwa-install]').forEach(el => {
        el.style.display = 'none';
      });
    });
  } else {
    const isIos = /iphone|ipad|ipod/.test(window.navigator.userAgent.toLowerCase());
    if (isIos) {
      alert('To install MushFungAI on iOS:\n1. Tap the Share button in Safari (square with arrow up)\n2. Tap "Add to Home Screen"');
    } else {
      alert('To install MushFungAI on mobile:\n1. Tap the 3-dots browser menu (⋮) at the top right\n2. Select "Install app" or "Add to Home Screen"');
    }
  }
};

/* Instant Navigation / Tab Prefetcher */
(function() {
  const prefetched = new Set();
  function prefetchUrl(url) {
    if (!url || prefetched.has(url)) return;
    try {
      const parsed = new URL(url, window.location.href);
      if (parsed.origin !== window.location.origin) return;
      if (parsed.pathname === window.location.pathname) return;
      prefetched.add(url);
      const link = document.createElement('link');
      link.rel = 'prefetch';
      link.href = url;
      link.as = 'document';
      document.head.appendChild(link);
    } catch(e) {}
  }

  document.addEventListener('touchstart', (e) => {
    const a = e.target.closest('a[href]');
    if (a) prefetchUrl(a.href);
  }, { passive: true });

  document.addEventListener('mouseover', (e) => {
    const a = e.target.closest('a[href]');
    if (a) prefetchUrl(a.href);
  }, { passive: true });
})();

/* ==========================================================================
   OFFLINE MONITORING SYNC ENGINE
   ========================================================================== */
const OFFLINE_CHECKS_KEY = 'mushfung_offline_checks';

window.getOfflineChecks = function() {
  try {
    return JSON.parse(localStorage.getItem(OFFLINE_CHECKS_KEY) || '[]');
  } catch(e) {
    return [];
  }
};

window.saveOfflineDailyCheck = function(check) {
  const list = window.getOfflineChecks();
  list.push(check);
  localStorage.setItem(OFFLINE_CHECKS_KEY, JSON.stringify(list));
  window.updateOnlineStatusUI();
  window.showToastNotification('✓ Check saved offline! It will sync automatically when online.', 'warning');
};

window.removeOfflineCheck = function(index) {
  const list = window.getOfflineChecks();
  list.splice(index, 1);
  localStorage.setItem(OFFLINE_CHECKS_KEY, JSON.stringify(list));
  window.updateOnlineStatusUI();
};

window.clearOfflineChecks = function() {
  localStorage.removeItem(OFFLINE_CHECKS_KEY);
  window.updateOnlineStatusUI();
};

let _isSyncing = false;
window.syncOfflineDailyChecks = async function() {
  if (!navigator.onLine || _isSyncing) return;
  const list = window.getOfflineChecks();
  if (!list.length) return;

  _isSyncing = true;
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const token = csrfMeta ? csrfMeta.content : '';

  let syncedCount = 0;
  const remaining = [];

  for (let i = 0; i < list.length; i++) {
    const item = list[i];
    try {
      const response = await fetch(item.url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify(item.data),
      });

      if (response.ok || response.redirected) {
        syncedCount++;
      } else {
        remaining.push(item);
      }
    } catch(err) {
      console.warn('Sync failed for item', item, err);
      remaining.push(item);
    }
  }

  localStorage.setItem(OFFLINE_CHECKS_KEY, JSON.stringify(remaining));
  _isSyncing = false;
  window.updateOnlineStatusUI();

  if (syncedCount > 0) {
    window.showToastNotification(`✓ Successfully synced ${syncedCount} offline daily check(s)!`, 'success');
    if (window.location.pathname.includes('/worker/monitoring') || window.location.pathname.includes('/worker/history')) {
      setTimeout(() => { window.location.reload(); }, 1200);
    }
  }
};

window.updateOnlineStatusUI = function() {
  const isOnline = navigator.onLine;
  const checks = window.getOfflineChecks();

  const offlineBanner = document.getElementById('offlineNoticeBanner');
  if (offlineBanner) {
    offlineBanner.style.display = isOnline ? 'none' : 'flex';
  }

  const pendingBanner = document.getElementById('pendingSyncBanner');
  const pendingCountEl = document.getElementById('pendingSyncCount');
  if (pendingBanner) {
    if (checks.length > 0) {
      pendingBanner.style.display = 'flex';
      if (pendingCountEl) pendingCountEl.textContent = checks.length;
    } else {
      pendingBanner.style.display = 'none';
    }
  }

  document.querySelectorAll('.net-status-indicator').forEach(el => {
    el.classList.toggle('is-offline', !isOnline);
    el.setAttribute('title', isOnline ? 'Online' : 'Offline Mode');
  });
};

window.showToastNotification = function(message, type = 'info') {
  let toast = document.getElementById('mushfungAppToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'mushfungAppToast';
    toast.style.cssText = 'position: fixed; bottom: 24px; right: 24px; z-index: 999999; max-width: 420px; padding: 14px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; box-shadow: 0 10px 28px rgba(0,0,0,0.2); transition: all 0.3s ease; display: flex; align-items: center; gap: 10px; transform: translateY(100px); opacity: 0; pointer-events: none;';
    document.body.appendChild(toast);
  }

  if (type === 'success') {
    toast.style.background = '#205e46';
    toast.style.color = '#ffffff';
  } else if (type === 'warning') {
    toast.style.background = '#8a5c18';
    toast.style.color = '#ffffff';
  } else {
    toast.style.background = '#241c16';
    toast.style.color = '#ffffff';
  }

  toast.textContent = message;
  toast.style.transform = 'translateY(0)';
  toast.style.opacity = '1';

  clearTimeout(window._toastTimeout);
  window._toastTimeout = setTimeout(() => {
    toast.style.transform = 'translateY(100px)';
    toast.style.opacity = '0';
  }, 4000);
};

window.addEventListener('online', () => {
  window.updateOnlineStatusUI();
  window.showToastNotification('Internet connection restored. Syncing offline data...', 'info');
  window.syncOfflineDailyChecks();
});

window.addEventListener('offline', () => {
  window.updateOnlineStatusUI();
  window.showToastNotification('You are now offline. Manual checks will be saved locally.', 'warning');
});

document.addEventListener('DOMContentLoaded', () => {
  window.updateOnlineStatusUI();
  if (navigator.onLine) {
    window.syncOfflineDailyChecks();
  }
});

