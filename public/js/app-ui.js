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

async function ajaxSubmit(form){
  clearFormErrors(form);
  var btn = form.querySelector('button[type=submit], [data-submit]');
  var oldBtnText = btn ? btn.innerHTML : '';
  if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }

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
      showFieldErrors(form, body && body.errors ? body.errors : {});
      var firstMsg = body && body.message ? body.message : 'Dữ liệu chưa hợp lệ';
      if (window.toast) toast(firstMsg, 'error');
      return false;
    }

    if (!res.ok) {
      var emsg = (body && (body.message || body.error)) || ('Lỗi ' + res.status);
      if (window.toast) toast(emsg, 'error');
      return false;
    }

    var ok = body && body.ok ? body.ok : '';
    if (ok && !form.hasAttribute('data-no-toast') && window.toast) toast(ok, 'success');

    if (form.hasAttribute('data-reload') || (body && body.reload)) {
      window.location.reload();
      return true;
    }
    if (body && body.redirect) {
      window.location.assign(body.redirect);
      return true;
    }
    // mặc định: reload để hiển thị state mới
    window.location.reload();
    return true;
  } catch (e) {
    if (window.toast) toast('Lỗi mạng — vui lòng thử lại', 'error');
    return false;
  } finally {
    if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); btn.innerHTML = oldBtnText; }
  }
}

function shouldAjaxify(form){
  if (!(form instanceof HTMLFormElement)) return false;
  if (form.hasAttribute('data-no-ajax')) return false;
  var method = (form.getAttribute('method') || 'GET').toUpperCase();
  if (method === 'GET') return false; // filter forms vẫn submit native (đổi URL)
  return true;
}
document.addEventListener('submit', function(e){
  var form = e.target;
  if (!shouldAjaxify(form)) return;
  e.preventDefault();
  ajaxSubmit(form);
});
window.ajaxSubmit = ajaxSubmit;
window.shouldAjaxify = shouldAjaxify;
