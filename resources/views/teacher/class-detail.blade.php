@extends('layouts.teacher')
@section('title','Chi tiết lớp — LớpThêm')
@use('App\Support\Money')
@use('App\Models\Classroom')

@section('content')
<div class="pagehead">
  <div>
    <a class="backlink" href="{{ route('teacher.classes') }}">← Lớp học</a>
    <h1>{{ $class->name }}</h1>
    <p><span class="chip {{ $class->typeChip() }}">{{ $class->typeLabel() }}</span> &nbsp;{{ $class->gradeLabel() }} · Môn {{ $class->subject }} · {{ $students->count() }} học sinh</p>
  </div>
  @php($cdata = [
    'id' => $class->id, 'name' => $class->name, 'type' => $class->type, 'grade' => $class->grade,
    'subject' => $class->subject, 'status' => $class->status,
    'start_date' => optional($class->start_date)->toDateString(),
    'schedules' => $class->schedules->sortBy([['weekday', 'asc'], ['start_time', 'asc']])->map(fn ($s) => [
      'weekday' => (int) $s->weekday,
      'start' => $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') : '17:30',
      'end' => $s->end_time ? \Illuminate\Support\Carbon::parse($s->end_time)->format('H:i') : '19:00',
    ])->values(),
    'locked' => (int) ($class->submitted_count ?? 0) > 0,
  ])
  <div><a class="btn ghost" href="{{ route('teacher.attendance', ['class_id' => $class->id]) }}">Điểm danh</a> <button class="btn primary" type="button" onclick='editClass(@json($cdata))'>Sửa lớp</button></div>
</div>

<div id="class-detail-body" data-partial-url="{{ route('teacher.class.partial', ['id' => $class->id] + request()->query()) }}">
  @include('teacher.partials.class-detail-body', compact('class','students','taught','offs','makeups','period','periodLabel','sessions'))
</div>

{{-- Popup sửa đơn giá học sinh trong lớp này --}}
<div class="modal-backdrop" id="m-editprice">
  <form class="modal" method="POST" id="f-editprice" data-refetch="#class-detail-body" data-hide-modal-on-success>
    @csrf
    @method('PUT')
    <div class="mh"><h3>Sửa đơn giá / buổi</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="field"><label>Học sinh</label><input id="editprice-name" disabled></div>
      <div class="field"><label>Đơn giá / buổi (VNĐ) <span style="color:var(--red)">*</span></label>
        <input id="editprice-disp" inputmode="numeric" placeholder="120.000" autocomplete="off">
        <input type="hidden" name="price_per_session" id="editprice-value" value="0" required>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary">Lưu</button></div>
  </form>
</div>

{{-- Popup lịch sử sửa đơn giá --}}
<div class="modal-backdrop" id="m-pricehistory">
  <div class="modal">
    <div class="mh"><h3 id="pricehistory-title">Lịch sử sửa đơn giá</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb" id="pricehistory-body"><div class="r" style="padding:8px 0">Đang tải...</div></div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Đóng</button></div>
  </div>
</div>

{{-- Popup thêm học sinh vào lớp (chọn nhiều cùng lúc) --}}
<div class="modal-backdrop" id="m-addstudent">
  <form class="modal" method="POST" action="{{ route('teacher.class.addStudent', ['id' => $class->id], false) }}"
        data-refetch="#class-detail-body" data-hide-modal-on-success data-reset-on-success>
    @csrf
    <div class="mh"><h3>Thêm học sinh vào lớp</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="field"><label>Chọn học sinh — gõ tìm, chọn nhiều <span style="color:var(--red)">*</span></label>
        <div class="ssel" data-url="{{ route('api.students.search', ['exclude_class' => $class->id]) }}" id="add-ssel">
          <input class="ssel-input" placeholder="Gõ tên / mã rồi chọn..." autocomplete="off">
          <div class="ssel-list"></div>
        </div>
        <div id="add-chips" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px"></div>
      </div>
      <div class="field"><label>Đơn giá / buổi (VNĐ) — mặc định theo lớp</label>
        <input class="money-input" data-target="price_per_session" inputmode="numeric" placeholder="120.000">
        <input type="hidden" name="price_per_session" id="add-price" value="{{ $classDefaultPrice }}">
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary">Thêm vào lớp</button></div>
  </form>
</div>

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
// Xem lịch sử sửa đơn giá của 1 học sinh trong lớp này
function viewPriceHistory(studentId, name){
  var body = document.getElementById('pricehistory-body');
  document.getElementById('pricehistory-title').textContent = 'Lịch sử sửa đơn giá — ' + name;
  body.innerHTML = '<div class="r" style="padding:8px 0">Đang tải...</div>';
  openModal('m-pricehistory');
  var url = '{{ route('teacher.class.student.priceHistory', ['id' => $class->id, 'studentId' => '__SID__']) }}'.replace('__SID__', studentId);
  fetch(url, {headers: {'Accept':'application/json'}, credentials: 'same-origin'})
    .then(function(r){ return r.json(); })
    .then(function(d){
      var logs = d.logs || [];
      if(!logs.length){ body.innerHTML = '<div class="r" style="padding:8px 0">Chưa có lần sửa nào.</div>'; return; }
      var fmt = window.fmtMoney || function(v){ return v; };
      var html = '<table style="width:100%"><thead><tr><th>Thời điểm</th><th>Giá cũ</th><th>Giá mới</th><th>Người sửa</th></tr></thead><tbody>';
      logs.forEach(function(l){
        html += '<tr><td>'+l.at+'</td><td class="money">'+fmt(l.old_price)+'đ</td><td class="money">'+fmt(l.new_price)+'đ</td><td>'+(l.user||'—')+'</td></tr>';
      });
      html += '</tbody></table>';
      body.innerHTML = html;
    })
    .catch(function(){ body.innerHTML = '<div class="r" style="padding:8px 0;color:var(--red)">Không tải được lịch sử.</div>'; });
}
// Mở modal sửa đơn giá cho 1 học sinh trong lớp này
function editPrice(studentId, name, currentPrice){
  var form = document.getElementById('f-editprice');
  form.action = '{{ route('teacher.class.student.price', ['id' => $class->id, 'studentId' => '__SID__']) }}'.replace('__SID__', studentId);
  document.getElementById('editprice-name').value = name;
  var hidden = document.getElementById('editprice-value');
  var disp = document.getElementById('editprice-disp');
  var price = String(currentPrice || 0).replace(/\D/g, '');
  hidden.value = price;
  disp.value = price ? Number(price).toLocaleString('vi-VN') : '';
  openModal('m-editprice');
  setTimeout(function(){ disp.focus(); disp.select(); }, 50);
}
// Format ô đơn giá: chỉ giữ số, hiển thị có dấu chấm ngăn nghìn
document.addEventListener('DOMContentLoaded', function(){
  var disp = document.getElementById('editprice-disp');
  var hidden = document.getElementById('editprice-value');
  if(!disp || !hidden) return;
  disp.addEventListener('input', function(){
    var digits = disp.value.replace(/\D/g, '');
    hidden.value = digits;
    var formatted = digits ? Number(digits).toLocaleString('vi-VN') : '';
    if(disp.value !== formatted) disp.value = formatted;
  });
});
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    var ssel=document.getElementById('add-ssel'), chips=document.getElementById('add-chips');
    if(!ssel) return;
    ssel.addEventListener('ssel:select', function(e){
      var id=e.detail.id, label=String(e.detail.label);
      if(chips.querySelector('[data-id="'+id+'"]')) return;
      var chip=document.createElement('span'); chip.className='chip n'; chip.dataset.id=id;
      chip.style.cssText='display:inline-flex;align-items:center;gap:6px';
      chip.innerHTML=label.replace(/</g,'&lt;')+'<input type="hidden" name="students[]" value="'+id+'"><a href="#" style="color:var(--red);text-decoration:none" onclick="this.parentNode.remove();return false">×</a>';
      chips.appendChild(chip);
      ssel.querySelector('.ssel-input').value='';
    });
  });
})();
</script>

@include('partials.class-modal', ['classRefetch' => '#class-detail-body'])
@endsection
