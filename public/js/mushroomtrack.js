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
      total += itemTotal;
      if (priceEl) priceEl.textContent = '₱' + itemTotal.toLocaleString('en-PH', { maximumFractionDigits: 2 });
    });
    const btn = document.getElementById('cartCheckoutBtn');
    if (btn) btn.textContent = 'CHECKOUT (₱' + total.toLocaleString('en-PH', { maximumFractionDigits: 2 }) + ')';
  }

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
