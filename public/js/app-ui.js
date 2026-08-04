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
