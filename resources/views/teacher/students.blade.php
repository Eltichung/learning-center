@extends('layouts.teacher')
@section('title','Học sinh — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead"><div><h1>Học sinh</h1><p>{{ $students->total() }} học sinh</p></div><button class="btn primary" type="button" onclick="openModal('m-student')">+ Thêm học sinh</button></div>

<form class="filterbar" method="GET" action="{{ route('teacher.students') }}" data-refetch="#students-list">
  <select name="class_id" onchange="this.form.requestSubmit()">
    <option value="">Tất cả lớp</option>
    @foreach ($classList as $c)<option value="{{ $c->id }}" @selected($classId === $c->id)>{{ $c->name }}</option>@endforeach
  </select>
  <select name="status" onchange="this.form.requestSubmit()">
    <option value="active" @selected($status === 'active')>Hoạt động</option>
    <option value="inactive" @selected($status === 'inactive')>Ngừng HĐ</option>
    <option value="" @selected($status === '')>Tất cả trạng thái</option>
  </select>
  <select name="pay_status" onchange="this.form.requestSubmit()">
    <option value="">Tất cả công nợ</option>
    <option value="unpaid" @selected($payStatus === 'unpaid')>Còn nợ</option>
    <option value="paid" @selected($payStatus === 'paid')>Đã đóng</option>
  </select>
  <select name="fee_visibility" onchange="this.form.requestSubmit()">
    <option value="">Học phí PH: Tất cả</option>
    <option value="shown" @selected(($feeVisibility ?? '') === 'shown')>Đang hiện</option>
    <option value="hidden" @selected(($feeVisibility ?? '') === 'hidden')>Đang ẩn</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên / mã...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($classId || $status !== 'active' || $payStatus || ($feeVisibility ?? '') || $q !== '')<a class="btn ghost sm" href="{{ route('teacher.students') }}" data-refetch="#students-list">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
  <div id="students-list" data-partial-url="{{ route('teacher.students.partial', request()->query()) }}">
    @include('teacher.partials.students-list', compact('students'))
  </div>
</div></div>

{{-- Popup thêm học sinh --}}
<div class="modal-backdrop" id="m-student">
  <form class="modal" method="POST" action="{{ route('teacher.students.store', [], false) }}"
        data-refetch="#students-list" data-hide-modal-on-success data-reset-on-success>
    @csrf
    <div class="mh"><h3>Thêm học sinh</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="field"><label>Họ tên <span style="color:var(--red)">*</span></label><input name="full_name" required placeholder="VD: Nguyễn Bảo An"></div>
      <div class="grid2">
        <div class="field"><label>Mã tra cứu (slug) <span style="color:var(--red)">*</span></label><input name="student_code" required placeholder="VD: an-toan9"></div>
        <div class="field"><label>SĐT phụ huynh <span style="color:var(--red)">*</span></label><input name="parent_phone" required placeholder="09xxxxxxxx"></div>
      </div>
      <div class="field"><label>Kênh liên lạc PH (Zalo / link Facebook)</label><input name="parent_contact" placeholder="VD: 0900xxx (Zalo) hoặc fb.com/..."></div>
      <div class="grid2">
        <div class="field"><label>Thêm vào lớp <span style="color:var(--red)">*</span></label>
          <select name="class_id" required onchange="fillClassPrice(this)"><option value="">— Chọn lớp —</option>@foreach ($classList as $c)<option value="{{ $c->id }}" data-price="{{ $c->default_price ?: 120000 }}">{{ $c->name }}</option>@endforeach</select></div>
        <div class="field"><label>Đơn giá / buổi (VNĐ) <span style="color:var(--red)">*</span></label>
          <input class="money-input" data-target="price_per_session" inputmode="numeric" placeholder="120.000" required>
          <input type="hidden" name="price_per_session" value="120000">
        </div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary">Thêm học sinh</button></div>
  </form>
</div>

@push('scripts')
<script>
// Copy link trang tra cứu của phụ huynh
function copyLookup(url, el){
  var done = function(){ toast('✓ Đã copy link tra cứu', 'success'); };
  var fail = function(){ window.prompt('Copy link tra cứu:', url); };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(done).catch(fail);
  } else {
    fail();
  }
}
// Toggle show_fees qua AJAX — switch inline, không confirm, không reload
document.addEventListener('click', function(e){
  var btn = e.target.closest('.fee-toggle');
  if (!btn || btn.disabled) return;

  // Optimistic UI: toggle ngay, rollback nếu server báo lỗi
  var prev = btn.dataset.state === '1';
  var next = !prev;
  btn.dataset.state = next ? '1' : '0';
  btn.classList.toggle('on', next);
  btn.setAttribute('aria-checked', next ? 'true' : 'false');
  btn.disabled = true;

  var fd = new FormData();
  fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
  fd.append('_method', 'PUT');

  fetch(btn.dataset.url, {
    method: 'POST',
    body: fd,
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
  })
  .then(function(r){ return r.json().then(function(d){ return { ok: r.ok, data: d }; }); })
  .then(function(res){
    if (!res.ok) {
      // rollback
      btn.dataset.state = prev ? '1' : '0';
      btn.classList.toggle('on', prev);
      btn.setAttribute('aria-checked', prev ? 'true' : 'false');
      if (window.toast) toast((res.data && res.data.message) || 'Lỗi', 'error');
      return;
    }
    btn.title = next ? 'Đang hiện học phí trên PWA — bấm để ẩn' : 'Đang ẩn học phí trên PWA — bấm để hiện';
  })
  .catch(function(){
    // rollback nếu lỗi mạng
    btn.dataset.state = prev ? '1' : '0';
    btn.classList.toggle('on', prev);
    btn.setAttribute('aria-checked', prev ? 'true' : 'false');
    if (window.toast) toast('Lỗi mạng', 'error');
  })
  .finally(function(){ btn.disabled = false; });
});

// Chọn lớp -> tự fill đơn giá mặc định của lớp đó
function fillClassPrice(sel){
  var opt = sel.options[sel.selectedIndex];
  var p = opt ? opt.getAttribute('data-price') : null;
  if(!p) return;
  var form = sel.closest('form');
  var hidden = form.querySelector('input[type=hidden][name="price_per_session"]');
  var disp = form.querySelector('.money-input[data-target="price_per_session"]');
  if(hidden) hidden.value = p;
  if(disp) disp.value = window.fmtMoney ? window.fmtMoney(p) : p;
}
</script>
@endpush
@endsection
