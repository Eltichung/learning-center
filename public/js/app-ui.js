/* ===== UI dùng chung: modal, confirm, searchable-select ===== */

function openModal(id){ const m = document.getElementById(id); if (m) m.classList.add('show'); }
function closeModal(idOrEl){
  let m = null;
  if (typeof idOrEl === 'string') m = document.getElementById(idOrEl);
  else if (idOrEl && idOrEl.closest) m = idOrEl.closest('.modal-backdrop');
  if (m) m.classList.remove('show');
}

document.addEventListener('click', function(e){
  if (e.target.classList && e.target.classList.contains('modal-backdrop')) e.target.classList.remove('show');
});
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') document.querySelectorAll('.modal-backdrop.show').forEach(m => m.classList.remove('show'));
});

/* ----- Sidebar drawer (màn nhỏ) ----- */
function toggleSidebar(force){
  const open = typeof force === 'boolean' ? force : !document.body.classList.contains('nav-open');
  document.body.classList.toggle('nav-open', open);
}
window.toggleSidebar = toggleSidebar;
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') document.body.classList.remove('nav-open');
});

/* ----- Toast thông báo ----- */
function toast(message, type){
  let wrap = document.getElementById('lt-toast-wrap');
  if (!wrap){
    wrap = document.createElement('div');
    wrap.id = 'lt-toast-wrap';
    wrap.className = 'lt-toast-wrap';
    document.body.appendChild(wrap);
  }
  const t = document.createElement('div');
  t.className = 'lt-toast' + (type ? ' ' + type : '');
  t.textContent = message;
  wrap.appendChild(t);
  requestAnimationFrame(function(){ t.classList.add('show'); });
  setTimeout(function(){
    t.classList.remove('show');
    setTimeout(function(){ t.remove(); }, 300);
  }, 2200);
}
window.toast = toast;

/* ----- Confirm popup ----- */
function confirmAction(message, onYes){
  let bd = document.getElementById('lt-confirm');
  if (!bd){
    bd = document.createElement('div');
    bd.id = 'lt-confirm';
    bd.className = 'modal-backdrop';
    bd.innerHTML = '<div class="modal" style="width:420px">'
      + '<div class="mh"><h3>Xác nhận</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>'
      + '<div class="mb" id="lt-confirm-msg"></div>'
      + '<div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button>'
      + '<button type="button" class="btn primary" id="lt-confirm-ok">Đồng ý</button></div></div>';
    document.body.appendChild(bd);
  }
  document.getElementById('lt-confirm-msg').textContent = message;
  const ok = document.getElementById('lt-confirm-ok');
  const fresh = ok.cloneNode(true);
  ok.parentNode.replaceChild(fresh, ok);
  fresh.addEventListener('click', function(){ bd.classList.remove('show'); onYes(); });
  bd.classList.add('show');
}

/* Form có data-confirm: chặn submit, hỏi xác nhận trước */
document.addEventListener('submit', function(e){
  const f = e.target;
  if (f.dataset && f.dataset.confirm && !f.dataset.confirmed){
    e.preventDefault();
    confirmAction(f.dataset.confirm, function(){
      f.dataset.confirmed = '1';
      // Với form AJAX: gọi thẳng helper (vì f.submit() không trigger 'submit' event)
      if (window.shouldAjaxify && window.shouldAjaxify(f) && window.ajaxSubmit) {
        window.ajaxSubmit(f);
      } else {
        f.submit();
      }
    });
  }
});

/* ----- Popup nâng cấp gói (khi chạm giới hạn) -----
   data: {kind, unit, plan, used, max, upgrade_url, next:{name,classes,students}|null} */
function upRoom(v, unit){ return (v === null || v === undefined) ? ('không giới hạn ' + unit) : (v + ' ' + unit); }
function showUpgradeModal(data){
  data = data || {};
  var unit = data.unit || (data.kind === 'classes' ? 'lớp' : 'học sinh');
  var plan = data.plan || 'hiện tại';
  var used = (data.used != null ? data.used : '?');
  var max  = (data.max  != null ? data.max  : '?');
  var url  = data.upgrade_url || '/billing';
  var next = data.next;

  var msg, hint;
  if (next && next.name) {
    msg = 'Bạn đang sử dụng <b>' + used + '/' + max + ' ' + unit + '</b>. Nâng cấp lên <b class="up">'
        + next.name + '</b> để mở rộng giới hạn và tiếp tục sử dụng.';
    hint = 'Với <b class="up">' + next.name + '</b>, bạn có thể quản lý tối đa <b class="up">'
        + upRoom(next.classes, 'lớp') + '</b> và <b class="up">' + upRoom(next.students, 'học sinh') + '</b>.';
  } else {
    msg = 'Bạn đang sử dụng <b>' + used + '/' + max + ' ' + unit + '</b>. Liên hệ với chúng tôi để mở rộng giới hạn.';
    hint = '';
  }

  var bd = document.getElementById('lt-upgrade');
  if (!bd){
    bd = document.createElement('div');
    bd.id = 'lt-upgrade';
    bd.className = 'modal-backdrop up-backdrop';
    document.body.appendChild(bd);
  }
  bd.innerHTML =
      '<div class="modal upgrade">'
    +   '<button type="button" class="up-x" aria-label="Đóng" onclick="closeModal(this)">&times;</button>'
    +   '<div class="up-body">'
    +     '<div class="up-icon">👑</div>'
    +     '<h3 class="up-title">Bạn đã đạt giới hạn gói ' + plan + '</h3>'
    +     '<p class="up-msg">' + msg + '</p>'
    +     '<div class="up-usage">'
    +       '<div class="up-usage-row"><span>' + used + ' / ' + max + ' ' + unit + ' đang sử dụng</span></div>'
    +       '<div class="up-bar"><div class="up-bar-fill"></div></div>'
    +       (hint ? '<div class="up-hint">' + hint + '</div>' : '')
    +     '</div>'
    +     '<div class="up-actions">'
    +       '<button type="button" class="btn ghost" onclick="closeModal(this)">Để sau</button>'
    +       '<a class="btn primary" href="' + url + '">Nâng cấp gói ngay →</a>'
    +     '</div>'
    +   '</div>'
    + '</div>';
  bd.classList.add('show');
}
window.showUpgradeModal = showUpgradeModal;

/* ----- Searchable select (AJAX) -----
   <div class="ssel" data-url="/api/...">
     <input type="hidden" name="student_id">
     <input class="ssel-input" placeholder="...">
     <div class="ssel-list"></div>
   </div> */
function initSearchSelect(root){
  const input  = root.querySelector('.ssel-input');
  const list   = root.querySelector('.ssel-list');
  const hidden = root.querySelector('input[type=hidden]');
  const url    = root.dataset.url;
  let timer;

  function render(items){
    if (!items || !items.length){ list.innerHTML = '<div class="ssel-empty">Không có kết quả</div>'; }
    else list.innerHTML = items.map(it =>
      '<div class="ssel-opt" data-id="'+it.id+'" data-label="'+String(it.label).replace(/"/g,'&quot;')+'">'+it.label+'</div>'
    ).join('');
    list.classList.add('show');
  }
  function search(){
    const q = encodeURIComponent(input.value.trim());
    fetch(url + (url.includes('?') ? '&' : '?') + 'q=' + q, {headers:{'X-Requested-With':'XMLHttpRequest'}})
      .then(r => r.json()).then(render).catch(function(){ list.innerHTML = '<div class="ssel-empty">Lỗi tải dữ liệu</div>'; list.classList.add('show'); });
  }
  input.addEventListener('input', function(){ if (hidden) hidden.value = ''; clearTimeout(timer); timer = setTimeout(search, 250); });
  input.addEventListener('focus', search);
  list.addEventListener('click', function(e){
    const opt = e.target.closest('.ssel-opt'); if (!opt) return;
    input.value = opt.dataset.label;
    if (hidden) hidden.value = opt.dataset.id;
    list.classList.remove('show');
    root.dispatchEvent(new CustomEvent('ssel:select', {detail:{id:opt.dataset.id, label:opt.dataset.label}}));
  });
  document.addEventListener('click', function(e){ if (!root.contains(e.target)) list.classList.remove('show'); });
}
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.ssel[data-url]').forEach(initSearchSelect);
});

/* ----- Money input: hiển thị "120.000", gửi số nguyên qua hidden input -----
   <input class="money-input" data-target="price_per_session" inputmode="numeric">
   <input type="hidden" name="price_per_session" value="120000"> */
function fmtMoney(v){ v = ('' + v).replace(/\D/g, ''); return v ? Number(v).toLocaleString('vi-VN') : ''; }
function initMoneyInput(el){
  const scope = el.closest('form') || document;
  const hidden = scope.querySelector('input[type=hidden][name="' + el.dataset.target + '"]');
  if (hidden && hidden.value) el.value = fmtMoney(hidden.value);
  el.addEventListener('input', function(){
    const digits = el.value.replace(/\D/g, '');
    if (hidden) hidden.value = digits;
    el.value = digits ? Number(digits).toLocaleString('vi-VN') : '';
  });
}
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.money-input[data-target]').forEach(initMoneyInput);
});
window.fmtMoney = fmtMoney;
window.initMoneyInput = initMoneyInput;

/* ===== AJAX form submit =====
   Đánh dấu form bằng `data-ajax`. Helper sẽ:
     - chặn submit mặc định, POST qua fetch (Accept: application/json + X-Requested-With)
     - khóa nút submit khi đang gửi
     - 422: render lỗi validate dưới ô input (thẻ .field-err) và toast lỗi đầu tiên
     - 200/JSON {ok, redirect?, reload?}: toast ok, điều hướng/reload theo response
     - lỗi khác: toast lỗi mạng/server

   Tuỳ chọn trên form:
     data-no-toast      → không hiện toast khi success
     data-reload        → reload trang sau khi success (ghi đè response.reload)
*/
function getCsrfToken(){
  var el = document.querySelector('meta[name="csrf-token"]');
  if (el) return el.getAttribute('content');
  var inp = document.querySelector('input[name="_token"]');
  return inp ? inp.value : '';
}

function clearFormErrors(form){
  form.querySelectorAll('.field-err').forEach(function(n){ n.remove(); });
  form.querySelectorAll('.has-err').forEach(function(n){ n.classList.remove('has-err'); });
}

function showFieldErrors(form, errors){
  Object.keys(errors || {}).forEach(function(name){
    var msg = Array.isArray(errors[name]) ? errors[name][0] : errors[name];
    var field = form.querySelector('[name="' + name + '"]') || form.querySelector('[name="' + name + '[]"]');
    if (!field) return;
    field.classList.add('has-err');
    var wrap = field.closest('.field') || field.parentNode;
    var err = document.createElement('div');
    err.className = 'field-err';
    err.textContent = msg;
    wrap.appendChild(err);
  });
}

/* ===== Top loader bar (progress mảnh trên đầu trang) =====
   Show trước khi fetch, hide sau khi xong. Đồng thời đếm số request để tránh
   nhiều fetch chồng nhau bị tắt loader sớm. */
var LT_LOAD_COUNT = 0;
function showLoader(){
  LT_LOAD_COUNT++;
  var el = document.getElementById('lt-loader');
  if (el) el.classList.add('show');
}
function hideLoader(){
  LT_LOAD_COUNT = Math.max(0, LT_LOAD_COUNT - 1);
  if (LT_LOAD_COUNT === 0) {
    var el = document.getElementById('lt-loader');
    if (el) el.classList.remove('show');
  }
}
window.showLoader = showLoader;
window.hideLoader = hideLoader;

/* ===== refetchInto: refetch HTML fragment vào container =====
   Container có data-partial-url (hoặc truyền url thẳng). Server trả HTML thuần,
   JS thay innerHTML. */
async function refetchInto(target){
  var el = typeof target === 'string' ? document.querySelector(target) : target;
  if (!el) return false;
  var url = el.dataset.partialUrl || location.href;
  el.classList.add('is-loading');
  showLoader();
  try {
    var res = await fetch(url, {
      credentials: 'same-origin',
      headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) return false;
    el.innerHTML = await res.text();
    // Cho trang tự re-init JS phụ thuộc DOM mới (bảng tính tiền, money-input, ...)
    document.dispatchEvent(new CustomEvent('lt:refetched', { detail: { container: el } }));
    el.querySelectorAll('.money-input[data-target]').forEach(function(n){
      if (window.initMoneyInput) window.initMoneyInput(n);
    });
    return true;
  } catch (e) {
    return false;
  } finally {
    el.classList.remove('is-loading');
    hideLoader();
  }
}
window.refetchInto = refetchInto;

/* Đóng modal của 1 form (form nằm trong .modal-backdrop) */
function closeFormModal(form){
  var mod = form.closest('.modal-backdrop');
  if (mod) mod.classList.remove('show');
  document.body.style.overflow = '';
}

async function ajaxSubmit(form){
  clearFormErrors(form);
  var btn = form.querySelector('button[type=submit], [data-submit]');
  var oldBtnText = btn ? btn.innerHTML : '';
  // Toggle switch: lật trạng thái ngay (optimistic), rollback nếu lỗi.
  // Không hiện spinner "is-loading" trên switch cho đỡ giật.
  var toggle = form.hasAttribute('data-optimistic-toggle') ? form.querySelector('.switch') : null;
  var toggleWas = toggle ? toggle.classList.contains('on') : null;
  if (toggle) {
    toggle.classList.toggle('on', !toggleWas);
    toggle.setAttribute('aria-checked', (!toggleWas).toString());
  }
  if (btn) { btn.disabled = true; if (!toggle) btn.classList.add('is-loading'); }
  showLoader();

  var rollbackToggle = function () {
    if (!toggle) return;
    toggle.classList.toggle('on', toggleWas);
    toggle.setAttribute('aria-checked', toggleWas.toString());
  };

  var data = new FormData(form);
  var method = (data.get('_method') || form.getAttribute('method') || 'POST').toUpperCase();
  if (method !== 'POST' && method !== 'GET') {
    // method spoofing: thực tế gửi POST + _method
    data.set('_method', method);
  }
  var url = form.getAttribute('action') || window.location.href;

  try {
    var res = await fetch(url, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': getCsrfToken(),
      },
    });

    var ctype = res.headers.get('content-type') || '';
    var body = ctype.indexOf('application/json') !== -1 ? await res.json() : null;

    if (res.status === 422) {
      rollbackToggle();
      // Chạm giới hạn gói → popup nâng cấp (không dùng toast/field-err)
      if (body && body.code === 'plan_limit') {
        if (window.showUpgradeModal) showUpgradeModal(body);
        return false;
      }
      showFieldErrors(form, body && body.errors ? body.errors : {});
      var firstMsg = body && body.message ? body.message : 'Dữ liệu chưa hợp lệ';
      if (window.toast) toast(firstMsg, 'error');
      return false;
    }

    if (!res.ok) {
      rollbackToggle();
      var emsg = (body && (body.message || body.error)) || ('Lỗi ' + res.status);
      if (window.toast) toast(emsg, 'error');
      return false;
    }

    var ok = body && body.ok ? body.ok : '';
    if (ok && !form.hasAttribute('data-no-toast') && window.toast) toast(ok, 'success');

    // data-no-reload: giữ nguyên trang (dùng cho toggle/switch — state đã lật optimistic)
    if (form.hasAttribute('data-no-reload')) {
      return true;
    }

    // Form đánh dấu inline behavior (refetch/hide modal/reset) → làm inline, BỎ QUA body.redirect từ server
    var hasInlineBehavior = form.dataset.refetch
      || form.hasAttribute('data-hide-modal-on-success')
      || form.hasAttribute('data-reset-on-success');

    if (form.hasAttribute('data-reload') || (body && body.reload)) {
      window.location.reload();
      return true;
    }

    if (!hasInlineBehavior && body && body.redirect) {
      window.location.assign(body.redirect);
      return true;
    }

    // Refetch fragment nếu form đánh dấu
    var refetchSel = form.dataset.refetch || (body && body.refetch);
    if (refetchSel) {
      var sels = String(refetchSel).split(',').map(function(s){ return s.trim(); }).filter(Boolean);
      await Promise.all(sels.map(function(s){ return refetchInto(s); }));
    }

    // Đóng modal chứa form
    if (form.hasAttribute('data-hide-modal-on-success') || (body && body.hideModal)) {
      closeFormModal(form);
    }

    // Reset form sau success
    if (form.hasAttribute('data-reset-on-success')) {
      form.reset();
    }

    return true;
  } catch (e) {
    rollbackToggle();
    if (window.toast) toast('Lỗi mạng — vui lòng thử lại', 'error');
    return false;
  } finally {
    if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); btn.innerHTML = oldBtnText; }
    hideLoader();
  }
}

function shouldAjaxify(form){
  if (!(form instanceof HTMLFormElement)) return false;
  if (form.hasAttribute('data-no-ajax')) return false;
  var method = (form.getAttribute('method') || 'GET').toUpperCase();
  // GET form được ajaxify nếu có data-refetch (filter live) — ngược lại submit native
  if (method === 'GET') return form.hasAttribute('data-refetch');
  return true;
}

/* AJAX filter cho form GET: build query từ FormData, pushState + refetchInto */
async function ajaxFilterGet(form){
  var action = form.getAttribute('action') || location.pathname;
  var actionUrl = new URL(action, location.origin);
  var fd = new FormData(form);
  fd.forEach(function(v, k){
    if (v === '' || v === null || v === undefined) actionUrl.searchParams.delete(k);
    else actionUrl.searchParams.set(k, v);
  });
  // Cập nhật URL trên address bar (giữ path của form action, không phải partial)
  var newQuery = actionUrl.searchParams.toString();
  history.pushState({}, '', actionUrl.pathname + (newQuery ? '?' + newQuery : ''));

  // Refetch container: giữ partial base URL, thay query
  var el = document.querySelector(form.dataset.refetch);
  if (!el) return;
  var base = (el.dataset.partialUrl || '').split('?')[0];
  el.dataset.partialUrl = base + (newQuery ? '?' + newQuery : '');
  await refetchInto(el);
}
window.ajaxFilterGet = ajaxFilterGet;

document.addEventListener('submit', function(e){
  var form = e.target;
  if (!shouldAjaxify(form)) return;
  // Có data-confirm và chưa xác nhận → nhường cho confirm handler
  if (form.dataset.confirm && !form.dataset.confirmed) return;
  e.preventDefault();
  var method = (form.getAttribute('method') || 'GET').toUpperCase();
  if (method === 'GET') ajaxFilterGet(form);
  else ajaxSubmit(form);
});
window.ajaxSubmit = ajaxSubmit;
window.shouldAjaxify = shouldAjaxify;

/* ===== SPA navigate: <a data-refetch="#sel"> =====
   Điều hướng nội bộ không reload: pushState + refetch fragment.
   Container đích phải có data-partial-url (base URL của endpoint fragment). */
async function ajaxNavigate(url, selector, push){
  var el = document.querySelector(selector);
  if (!el) { window.location.assign(url); return; }
  var u = new URL(url, location.origin);
  if (push !== false) history.pushState({ refetch: selector }, '', u.pathname + u.search);

  var base = (el.dataset.partialUrl || '').split('?')[0];
  if (!base) { window.location.assign(url); return; }
  el.dataset.partialUrl = base + (u.search || '');
  await refetchInto(el);
}
window.ajaxNavigate = ajaxNavigate;

document.addEventListener('click', function(e){
  var a = e.target.closest('a[data-refetch]');
  if (!a) return;
  if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0) return;
  if (a.target && a.target !== '_self') return;
  var href = a.getAttribute('href');
  if (!href || href.charAt(0) === '#' || /^(https?:)?\/\//.test(href) && new URL(href, location.origin).origin !== location.origin) return;
  e.preventDefault();
  ajaxNavigate(a.href, a.dataset.refetch);
});

/* Select có data-refetch + data-nav-param: đổi option → điều hướng AJAX */
document.addEventListener('change', function(e){
  var sel = e.target;
  if (!(sel instanceof HTMLSelectElement)) return;
  if (!sel.dataset.refetch || !sel.dataset.navParam) return;
  var u = new URL(sel.dataset.navBase || location.pathname, location.origin);
  // giữ các param hiện có trên URL, chỉ thay param của select
  new URL(location.href).searchParams.forEach(function(v, k){ u.searchParams.set(k, v); });
  if (sel.value === '') u.searchParams.delete(sel.dataset.navParam);
  else u.searchParams.set(sel.dataset.navParam, sel.value);
  // reset các param phụ thuộc (vd đổi lớp thì bỏ session_id)
  (sel.dataset.navReset || '').split(',').map(function(s){ return s.trim(); }).filter(Boolean)
    .forEach(function(k){ u.searchParams.delete(k); });
  ajaxNavigate(u.toString(), sel.dataset.refetch);
});

/* Back/forward của browser: refetch lại fragment tương ứng */
window.addEventListener('popstate', function(e){
  var sel = (e.state && e.state.refetch) || null;
  if (!sel) { window.location.reload(); return; }
  var el = document.querySelector(sel);
  if (!el) { window.location.reload(); return; }
  var base = (el.dataset.partialUrl || '').split('?')[0];
  el.dataset.partialUrl = base + location.search;
  refetchInto(el);
});

/* ===== Bộ lọc Báo cáo: SELECT BOX đa chọn (dropdown checkbox) nhiều lớp + nhiều tháng =====
   Tick checkbox → cập nhật hidden input (CSV) + nhãn tóm tắt → refetch #reports-body (debounce).
   Luôn giữ ≥1 lựa chọn mỗi nhóm. "Tất cả lớp" = chọn hết. Delegation ở document (sống qua refetch). */
var _rptTimer;
function rptRefetch(form){
  clearTimeout(_rptTimer);
  _rptTimer = setTimeout(function(){
    if (window.ajaxFilterGet) ajaxFilterGet(form); else form.requestSubmit();
  }, 250);
}
function rptMsGroup(el){ return el ? el.closest('[data-rpt-ms]') : null; }
function rptMsSync(ms){
  var form  = ms.closest('form.rpt-filter');
  var kind  = ms.dataset.rptMs; // 'class' | 'month'
  var items = ms.querySelectorAll('.rpt-ms-item');
  var checked = Array.prototype.filter.call(items, function(i){ return i.checked; });
  var vals = checked.map(function(i){ return i.value; });

  // Hidden input CSV
  var hidden = form.querySelector(kind === 'class' ? 'input[name="class_ids"]' : 'input[name="months"]');
  if (hidden) hidden.value = vals.join(',');

  // Trạng thái "Tất cả lớp"
  var all = ms.querySelector('[data-rpt-all]');
  if (all) {
    all.checked = checked.length === items.length && items.length > 0;
    all.indeterminate = checked.length > 0 && checked.length < items.length;
  }

  // Nhãn tóm tắt
  var summary = ms.querySelector('.rpt-ms-summary');
  if (summary) {
    if (kind === 'class') {
      summary.textContent = (checked.length === items.length && items.length)
        ? 'Tất cả lớp'
        : (checked.length === 1 ? checked[0].parentNode.querySelector('span').textContent : checked.length + ' lớp');
    } else {
      summary.textContent = checked.length === 1
        ? 'Tháng ' + checked[0].value.split('-').reverse().join('/')   // YYYY-MM -> MM/YYYY
        : checked.length + ' tháng';
    }
  }
  return form;
}

// "Xem thêm tháng cũ" — nối thêm 12 tháng cũ hơn (VÔ HẠN; không đóng dropdown, không refetch)
function rptPrevMonth(ym){ // "2025-01" -> "2024-12"
  var p = ym.split('-'), y = +p[0], m = +p[1] - 1;
  if (m < 1) { m = 12; y--; }
  return y + '-' + (m < 10 ? '0' + m : '' + m);
}
function rptMonthLabelEl(ym){
  var p = ym.split('-');
  var lbl = document.createElement('label');
  lbl.className = 'rpt-ms-opt';
  lbl.innerHTML = '<input type="checkbox" class="rpt-ms-item" value="' + ym + '"> <span>T' + p[1] + '/' + p[0] + '</span>';
  return lbl;
}
function rptOldestMonth(ms){
  var o = null;
  ms.querySelectorAll('.rpt-ms-item').forEach(function(i){ if (o === null || i.value < o) o = i.value; });
  return o;
}
document.addEventListener('click', function(e){
  var mb = e.target.closest('.rpt-ms-morebtn');
  if (!mb) return;
  e.preventDefault();
  var ms  = mb.closest('[data-rpt-ms]');
  var list = ms.querySelector('.rpt-ms-months');
  var cur = rptOldestMonth(ms);
  if (!cur) return;
  var frag = document.createDocumentFragment();
  for (var k = 0; k < 12; k++) { cur = rptPrevMonth(cur); frag.appendChild(rptMonthLabelEl(cur)); }
  list.appendChild(frag);
});

// Preset chọn nhanh (Tháng này / 3-6 tháng gần nhất / cả năm nay / năm ngoái)
function rptLastN(current, n){ var out = [current], c = current; for (var i = 1; i < n; i++) { c = rptPrevMonth(c); out.push(c); } return out; }
function rptYearMonths(year, from, to){ var out = []; for (var m = from; m <= to; m++) out.push(year + '-' + (m < 10 ? '0' + m : '' + m)); return out; }
// Sinh đủ checkbox cho các tháng trong `list` (kể cả tháng tương lai của năm nay), sắp xếp mới→cũ, rồi tick đúng bộ.
function rptEnsureAndSelect(ms, list){
  var container = ms.querySelector('.rpt-ms-months');
  var have = {};
  ms.querySelectorAll('.rpt-ms-item').forEach(function(i){ have[i.value] = true; });
  list.forEach(function(m){ if (!have[m]) { container.appendChild(rptMonthLabelEl(m)); have[m] = true; } });
  var labels = Array.prototype.slice.call(container.querySelectorAll('.rpt-ms-opt'));
  labels.sort(function(a, b){ var av = a.querySelector('input').value, bv = b.querySelector('input').value; return av < bv ? 1 : (av > bv ? -1 : 0); });
  labels.forEach(function(l){ container.appendChild(l); });
  var set = {}; list.forEach(function(m){ set[m] = true; });
  ms.querySelectorAll('.rpt-ms-item').forEach(function(i){ i.checked = !!set[i.value]; });
}
document.addEventListener('click', function(e){
  var pb = e.target.closest('.rpt-ms-preset');
  if (!pb) return;
  e.preventDefault();
  var ms = pb.closest('[data-rpt-ms]');
  var current = ms.dataset.now || rptOldestMonth(ms); // tháng hiện tại theo server
  if (!current) return;
  var cy = +current.split('-')[0], list;
  switch (pb.dataset.preset) {
    case 'this-month': list = [current]; break;
    case 'last-3':     list = rptLastN(current, 3); break;
    case 'last-6':     list = rptLastN(current, 6); break;
    case 'this-year':  list = rptYearMonths(cy, 1, 12); break;      // cả năm nay (Jan–Dec)
    case 'last-year':  list = rptYearMonths(cy - 1, 1, 12); break;  // cả năm ngoái (Jan–Dec)
    default: return;
  }
  rptEnsureAndSelect(ms, list);
  var form = rptMsSync(ms);
  rptRefetch(form);
});

// Mở/đóng dropdown
document.addEventListener('click', function(e){
  var toggle = e.target.closest('.rpt-filter [data-rpt-toggle]');
  // Đóng mọi panel khác
  document.querySelectorAll('.rpt-ms-panel:not([hidden])').forEach(function(p){
    if (!toggle || p !== toggle.parentNode.querySelector('.rpt-ms-panel')) {
      if (!e.target.closest('.rpt-ms-panel')) p.hidden = true;
    }
  });
  if (!toggle) return;
  var panel = toggle.parentNode.querySelector('.rpt-ms-panel');
  if (panel) panel.hidden = !panel.hidden;
});

// Tick checkbox trong dropdown
document.addEventListener('change', function(e){
  var cb = e.target;
  if (!(cb instanceof HTMLInputElement) || cb.type !== 'checkbox') return;
  var ms = rptMsGroup(cb);
  if (!ms || !ms.closest('form.rpt-filter')) return;

  if (cb.hasAttribute('data-rpt-all')) {
    // "Tất cả lớp": bật = chọn hết; tắt = giữ lại lớp đầu (≥1)
    var items = ms.querySelectorAll('.rpt-ms-item');
    if (cb.checked) {
      items.forEach(function(i){ i.checked = true; });
    } else {
      items.forEach(function(i, idx){ i.checked = idx === 0; });
    }
  } else if (cb.classList.contains('rpt-ms-item')) {
    // Giữ ≥1: không cho bỏ tick cái cuối cùng
    var stillOn = ms.querySelectorAll('.rpt-ms-item:checked').length;
    if (!cb.checked && stillOn === 0) {
      cb.checked = true;
      if (window.toast) toast('Cần chọn ít nhất 1 ' + (ms.dataset.rptMs === 'class' ? 'lớp' : 'tháng'), 'error');
      return;
    }
  } else {
    return;
  }
  var form = rptMsSync(ms);
  rptRefetch(form);
});

/* ===== Composer nhận xét ĐA LOẠI — mô hình "ô" (dùng chung: modal điểm danh + hồ sơ HS) =====
   Mỗi nhận xét = 1 ô; trong ô chọn loại (dropdown) + viết. Bấm "＋ Thêm nhận xét" để thêm ô.
   Lưu = đồng bộ theo (HS, ngày, loại). Delegation ở document nên sống sót qua refetch. */
function cmtBox(el){ return el ? el.closest('.cmt-multi') : null; }
function cmtEsc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function cmtData(box){
  if (!box._cmtTypes) { try { box._cmtTypes = JSON.parse(box.dataset.types || '[]'); } catch (e) { box._cmtTypes = []; } }
  if (!box._cmtTpls) { try { box._cmtTpls = JSON.parse(box.dataset.templates || '{}'); } catch (e) { box._cmtTpls = {}; } }
  return { types: box._cmtTypes, tpls: box._cmtTpls };
}
function cmtTypeById(box, id){ return cmtData(box).types.find(function(t){ return String(t.id) === String(id); }); }
function cmtUsedTypeIds(box, exceptEl){
  var used = [];
  box.querySelectorAll('.cmt-box').forEach(function(bx){
    if (bx === exceptEl) return;
    if (bx.dataset.type) used.push(String(bx.dataset.type));
  });
  return used;
}
function cmtFirstUnusedType(box){
  var used = cmtUsedTypeIds(box, null);
  var t = cmtData(box).types.find(function(x){ return used.indexOf(String(x.id)) < 0; });
  return t ? String(t.id) : null;
}
/* Dựng lại hàng chip loại cho mọi ô: mỗi ô chỉ thấy loại của chính nó + loại chưa dùng ở ô khác */
function cmtRefreshChips(box){
  box.querySelectorAll('.cmt-box').forEach(function(bx){
    var wrap = bx.querySelector('.cmt-box-types'); if (!wrap) return;
    var cur = bx.dataset.type || '';
    var used = cmtUsedTypeIds(box, bx);
    wrap.innerHTML = '';
    cmtData(box).types.forEach(function(t){
      if (used.indexOf(String(t.id)) >= 0 && String(t.id) !== String(cur)) return;
      var chip = document.createElement('span');
      chip.className = 'cmt-type-chip' + (String(t.id) === String(cur) ? ' on' : '');
      chip.dataset.id = t.id; chip.setAttribute('role', 'button'); chip.tabIndex = 0;
      chip.setAttribute('style', t.style || '');
      chip.innerHTML = '<span class="ic">' + (t.icon || '') + '</span>' + cmtEsc(t.name);
      wrap.appendChild(chip);
    });
    var ct = cmtTypeById(box, cur);
    bx.setAttribute('style', ct ? ct.style : '');
  });
}
function cmtRenderBoxTpls(box, bx){
  var id = bx.dataset.type || '';
  var wrap = bx.querySelector('.cmt-tpls'); if (!wrap) return;
  wrap.innerHTML = '';
  ((cmtData(box).tpls || {})[id] || []).forEach(function(b){
    var chip = document.createElement('span');
    chip.className = 'cmt-tpl-chip'; chip.dataset.body = b;
    chip.innerHTML = '<span class="pl">+</span>' + cmtEsc(b);
    wrap.appendChild(chip);
  });
}
function cmtUpdateEmpty(box){
  var has = box.querySelector('.cmt-box');
  var empty = box.querySelector('.cmt-empty'); if (empty) empty.hidden = !!has;
  var addBtn = box.querySelector('.cmt-add-box');
  if (addBtn) { var full = cmtFirstUnusedType(box) == null; addBtn.disabled = full; addBtn.style.opacity = full ? '.5' : ''; }
}
function cmtAddBox(box, typeId, body){
  if (typeId == null) typeId = cmtFirstUnusedType(box);
  if (typeId == null) { if (window.toast) toast('Đã dùng hết các loại nhận xét', 'error'); return null; }
  var bx = document.createElement('div');
  bx.className = 'cmt-box';
  bx.dataset.type = String(typeId);
  bx.innerHTML =
    '<div class="cmt-box-top"><div class="cmt-box-types"></div>' +
    '<button type="button" class="cmt-box-remove" title="Bỏ ô này" aria-label="Bỏ ô này">✕</button></div>' +
    '<div class="cmt-tpls"></div>' +
    '<br>' +
    '<textarea class="cmt-block-body" rows="4" placeholder="Nội dung nhận xét…"></textarea>' +
    '<div class="cmt-box-foot">' +
      '<button type="button" class="btn ghost sm cmt-block-savetpl">🔖 Lưu lại mẫu câu</button>' +
      '<span class="cmt-savetpl-note">Lưu lại mẫu câu để tái sử dụng lần sau</span>' +
    '</div>';
  box.querySelector('.cmt-box-list').appendChild(bx);
  if (body != null) bx.querySelector('.cmt-block-body').value = body;
  cmtRefreshChips(box);
  cmtRenderBoxTpls(box, bx);
  cmtUpdateEmpty(box);
  return bx;
}
function cmtClearBoxes(box){ var list = box.querySelector('.cmt-box-list'); if (list) list.innerHTML = ''; }

/* Thu thập {type_id, body} từ các ô */
function cmtCollect(box){
  var items = [];
  box.querySelectorAll('.cmt-box').forEach(function(bx){
    var ta = bx.querySelector('.cmt-block-body');
    if (bx.dataset.type) items.push({ type_id: bx.dataset.type, body: ta ? ta.value : '' });
  });
  return items;
}

/* Nạp nhận xét đã có (theo ngày) → dựng các ô */
async function cmtLoadForDate(box){
  var sid = box.dataset.sid; if (!sid) return;
  var dInput = box.querySelector('.cmt-date');
  var date = dInput ? dInput.value : '';
  if (!date) return;
  cmtClearBoxes(box);
  var url = (box.dataset.urlFordate || '').replace('__SID__', sid) + '?date=' + encodeURIComponent(date);
  try {
    var res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
    if (res.ok) { var data = await res.json(); (data.items || []).forEach(function(it){ cmtAddBox(box, it.type_id, it.body); }); }
  } catch (e) {}
  if (!box.querySelector('.cmt-box')) cmtAddBox(box, null, '');
  cmtUpdateEmpty(box);
}

/* Đồng bộ (lưu) — trả về response body hoặc null */
async function cmtSync(box, btn){
  var sid = box.dataset.sid; if (!sid) return null;
  var dInput = box.querySelector('.cmt-date');
  var date = dInput ? dInput.value : '';
  var items = cmtCollect(box);
  var fd = new FormData();
  fd.append('comment_date', date || '');
  items.forEach(function(it, i){ fd.append('items[' + i + '][type_id]', it.type_id); fd.append('items[' + i + '][body]', it.body); });
  var old = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }
  showLoader();
  try {
    var res = await fetch((box.dataset.urlSync || '').replace('__SID__', sid), {
      method: 'POST', body: fd, credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
    });
    var ctype = res.headers.get('content-type') || '';
    var body = ctype.indexOf('application/json') !== -1 ? await res.json() : null;
    if (res.status === 422) { if (window.toast) toast((body && body.message) || 'Dữ liệu chưa hợp lệ', 'error'); return null; }
    if (!res.ok) { if (window.toast) toast((body && (body.message || body.error)) || ('Lỗi ' + res.status), 'error'); return null; }
    if (window.toast && body && body.ok) toast(body.ok, 'success');
    return body;
  } catch (e) { if (window.toast) toast('Lỗi mạng — vui lòng thử lại', 'error'); return null; }
  finally { if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); btn.innerHTML = old; } hideLoader(); }
}

/* POST đơn giản (thêm loại / lưu mẫu) → trả body */
async function cmtPostJson(url, payload){
  var fd = new FormData();
  Object.keys(payload).forEach(function(k){ fd.append(k, payload[k]); });
  var res = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() } });
  var body = (res.headers.get('content-type') || '').indexOf('json') !== -1 ? await res.json() : null;
  if (!res.ok) { if (window.toast) toast((body && (body.message || body.error)) || ('Lỗi ' + res.status), 'error'); return null; }
  if (window.toast && body && body.ok) toast(body.ok, 'success');
  return body;
}

/* Thêm loại mới → nạp vào data + tạo ngay 1 ô cho loại đó */
async function cmtAddTypeAction(box){
  var input = box.querySelector('.cmt-newtype-name');
  var name = input ? input.value.trim() : '';
  if (!name) { if (window.toast) toast('Nhập tên loại', 'error'); return; }
  var resp = await cmtPostJson(box.dataset.urlTypes, { name: name });
  if (resp && resp.type) {
    var d = cmtData(box);
    d.types.push({ id: resp.type.id, name: resp.type.name, icon: resp.type.icon, color: resp.type.color, style: resp.type.style });
    box.dataset.types = JSON.stringify(d.types);
    d.tpls[resp.type.id] = [];
    cmtRefreshChips(box);
    cmtAddBox(box, resp.type.id, '');
  }
  if (input) input.value = '';
  var row = box.querySelector('.cmt-newtype'); if (row) row.hidden = true;
}

/* Lưu nội dung ô hiện tại thành mẫu của loại đó */
async function cmtSaveTplAction(box, bx){
  if (!bx) return;
  var ta = bx.querySelector('.cmt-block-body');
  var val = ta ? ta.value.trim() : '';
  if (!val) { if (window.toast) toast('Nhập nội dung trước khi lưu mẫu', 'error'); return; }
  var typeId = bx.dataset.type || '';
  var resp = await cmtPostJson(box.dataset.urlTpls, { comment_type_id: typeId, body: val });
  if (resp && resp.template) {
    var d = cmtData(box); if (!d.tpls[typeId]) d.tpls[typeId] = []; d.tpls[typeId].push(resp.template.body);
    var chip = document.createElement('span');
    chip.className = 'cmt-tpl-chip'; chip.dataset.body = resp.template.body;
    chip.innerHTML = '<span class="pl">+</span>' + cmtEsc(resp.template.body);
    bx.querySelector('.cmt-tpls').appendChild(chip);
  }
}

/* Mở modal nhận xét (trang điểm danh) — nạp nhận xét hiện có của HS cho ngày buổi */
function openCommentModal(sid, name, date){
  var m = document.getElementById('m-comment'); if (!m) return;
  var box = m.querySelector('.cmt-multi'); if (!box) return;
  box.dataset.sid = sid;
  var nm = m.querySelector('#cmt-stud-name'); if (nm) nm.textContent = name || '';
  var d = box.querySelector('.cmt-date'); if (d && date) d.value = date;
  cmtLoadForDate(box);
  openModal('m-comment');
}
window.openCommentModal = openCommentModal;

/* Vẽ lại ô "Nhận xét" trên bảng điểm danh (nhiều badge, không refetch) */
function cmtRenderCell(resp){
  var table = document.getElementById('att-table'); if (!table || !resp) return;
  var btn = table.querySelector('.cmt-open-btn[data-sid="' + resp.student_id + '"]');
  if (!btn) return;
  var td = btn.closest('td'); var name = btn.dataset.name || ''; var date = btn.dataset.date || '';
  var inner = (resp.comments && resp.comments.length)
    ? '<span class="cmt-has">Xem nhận xét</span>'
    : '<span class="cmt-add-plain">💬 Nhận xét</span>';
  td.innerHTML = '<button type="button" class="cmt-open-btn" data-sid="' + resp.student_id + '" data-name="' + cmtEsc(name) + '" data-date="' + cmtEsc(date) + '" title="Nhận xét học sinh">' + inner + '</button>';
}

/* Tự nạp các composer inline (trang hồ sơ HS) khi trang/mảnh render */
function cmtInitAutoload(root){
  (root || document).querySelectorAll('.cmt-multi[data-autoload]').forEach(function(box){
    if (box.dataset.sid) cmtLoadForDate(box);
  });
}
document.addEventListener('DOMContentLoaded', function(){ cmtInitAutoload(document); });
document.addEventListener('lt:refetched', function(e){ cmtInitAutoload(e && e.detail ? e.detail.container : document); });

/* Click delegation: nút mở modal / lưu / các thao tác trong composer */
document.addEventListener('click', function(e){
  var open = e.target.closest('.cmt-open-btn');
  if (open) { openCommentModal(open.dataset.sid, open.dataset.name, open.dataset.date); return; }

  var msave = e.target.closest('.cmt-modal-save');
  if (msave) {
    (async function(){
      var back = msave.closest('.modal-backdrop'); if (!back) return;
      var box = back.querySelector('.cmt-multi'); if (!box) return;
      var resp = await cmtSync(box, msave);
      if (resp) { cmtRenderCell(resp); back.classList.remove('show'); document.body.style.overflow = ''; }
    })();
    return;
  }

  var isave = e.target.closest('.cmt-save-inline');
  if (isave) {
    (async function(){
      var box = (isave.parentElement && isave.parentElement.querySelector('.cmt-multi')) || document.querySelector('.cmt-multi');
      if (!box) return;
      var resp = await cmtSync(box, isave);
      if (resp) await refetchInto('#student-body');
    })();
    return;
  }

  var box = e.target.closest('.cmt-multi');
  if (!box) return;

  if (e.target.closest('.cmt-add-box')) { var nb = cmtAddBox(box, null, ''); if (nb) { var t = nb.querySelector('.cmt-block-body'); if (t) t.focus(); } return; }

  if (e.target.closest('.cmt-add-newtype')) {
    var row = box.querySelector('.cmt-newtype');
    if (row) { row.hidden = false; var inp = row.querySelector('.cmt-newtype-name'); if (inp) inp.focus(); }
    return;
  }
  if (e.target.closest('.cmt-newtype-cancel')) {
    var r = box.querySelector('.cmt-newtype');
    if (r) { r.hidden = true; var i = r.querySelector('.cmt-newtype-name'); if (i) i.value = ''; }
    return;
  }
  if (e.target.closest('.cmt-newtype-save')) { cmtAddTypeAction(box); return; }

  var rm = e.target.closest('.cmt-box-remove');
  if (rm) { var bx = rm.closest('.cmt-box'); if (bx) bx.remove(); cmtRefreshChips(box); cmtUpdateEmpty(box); return; }

  var savetpl = e.target.closest('.cmt-block-savetpl');
  if (savetpl) { cmtSaveTplAction(box, savetpl.closest('.cmt-box')); return; }

  var tplChip = e.target.closest('.cmt-tpl-chip');
  if (tplChip) {
    var bx2 = tplChip.closest('.cmt-box');
    var ta = bx2 ? bx2.querySelector('.cmt-block-body') : null;
    if (ta) { var cur = ta.value.trim(); ta.value = (cur ? cur + ' ' : '') + (tplChip.dataset.body || ''); ta.focus(); }
    return;
  }

  // Chọn loại cho 1 ô bằng chip (tab)
  var typeChip = e.target.closest('.cmt-type-chip');
  if (typeChip) {
    var bx3 = typeChip.closest('.cmt-box');
    if (bx3) { bx3.dataset.type = typeChip.dataset.id; cmtRefreshChips(box); cmtRenderBoxTpls(box, bx3); }
    return;
  }
});

/* Đổi ngày → nạp lại nhận xét của ngày đó */
document.addEventListener('change', function(e){
  var d = e.target.closest && e.target.closest('.cmt-date');
  if (!d) return;
  var box = cmtBox(d);
  if (box && box.dataset.sid) cmtLoadForDate(box);
});

/* Enter trong ô "Loại mới" = thêm luôn */
document.addEventListener('keydown', function(e){
  if (e.key !== 'Enter') return;
  var inp = e.target.closest && e.target.closest('.cmt-newtype-name');
  if (!inp) return;
  e.preventDefault();
  var box = cmtBox(inp);
  if (box) cmtAddTypeAction(box);
});
