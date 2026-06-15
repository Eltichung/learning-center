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
    confirmAction(f.dataset.confirm, function(){ f.dataset.confirmed = '1'; f.submit(); });
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
