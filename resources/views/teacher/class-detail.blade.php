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
    'weekdays' => $class->schedules->pluck('weekday')->map(fn ($w) => (int) $w)->values(),
    'start_time' => optional($class->schedules->first())->start_time ? \Illuminate\Support\Carbon::parse($class->schedules->first()->start_time)->format('H:i') : '17:30',
    'end_time' => optional($class->schedules->first())->end_time ? \Illuminate\Support\Carbon::parse($class->schedules->first()->end_time)->format('H:i') : '19:00',
    'locked' => (int) ($class->sessions_count ?? 0) > 0,
  ])
  <div><a class="btn ghost" href="{{ route('teacher.attendance', ['class_id' => $class->id]) }}">Điểm danh</a> <button class="btn primary" type="button" onclick='editClass(@json($cdata))'>Sửa lớp</button></div>
</div>

<div class="twocol">
  <div class="panel">
    <div class="ph"><h3>Học sinh trong lớp</h3><button class="btn ghost sm" type="button" onclick="openModal('m-addstudent')">+ Thêm</button></div>
    <div class="pb">
      <div class="tablewrap">
      <table>
        <thead><tr><th>Học sinh</th><th>Đơn giá/buổi</th><th>Công nợ lớp này</th></tr></thead>
        <tbody>
          @forelse ($students as $row)
            <tr onclick="location.href='{{ route('teacher.student', $row->student->id) }}'" style="cursor:pointer">
              <td><div class="stud" style="width:100%"><div class="savatar">{{ $row->student->initials() }}</div>
                <div>{{ $row->student->full_name }}</div>
                <span class="row-acts">
                  <a class="icon-act" href="{{ route('teacher.student', $row->student->id) }}" data-tip="Chi tiết" onclick="event.stopPropagation()">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                  <a class="icon-act" href="#" onclick='event.stopPropagation(); copyLookup(@json(route("parent.info", $row->student->student_code)), this); return false;' data-tip="Copy link tra cứu">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                  </a>
                  <a class="icon-act" href="#" data-tip="Lịch sử sửa giá"
                     onclick='event.stopPropagation(); viewPriceHistory(@json($row->student->id), @json($row->student->full_name)); return false;'>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v5l3 2"/></svg>
                  </a>
                </span>
              </div></td>
              <td class="money">
                {{ Money::vnd($row->price) }}
                <a class="icon-act" href="#" data-tip="Sửa đơn giá"
                   onclick='event.stopPropagation(); editPrice(@json($row->student->id), @json($row->student->full_name), @json((int) $row->price)); return false;'>
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </a>
              </td>
              <td>
                @if ($row->balanceClass > 0)<span class="chip r">−{{ Money::vnd($row->balanceClass) }}</span>
                @else<span class="chip g">Đã đóng</span>@endif
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="r" style="padding:16px">Lớp chưa có học sinh.</td></tr>
          @endforelse
        </tbody>
      </table>
      </div>
      <div class="r" style="padding:8px 16px 4px;line-height:1.5">💡 Công nợ tính <b>riêng cho lớp này</b>, không phải tổng các lớp. Tiền phụ huynh đóng được ưu tiên trừ vào <b>lớp ghi danh trước</b>, nên một học sinh có thể đã đủ ở lớp này nhưng còn nợ ở lớp khác (xem "Chi tiết" ở màn Học phí).</div>
    </div>
  </div>
  <div>
    <div class="panel"><div class="ph"><h3>Lịch học cố định</h3></div><div class="pb" style="padding:14px 16px">
      @forelse ($class->schedules->sortBy('weekday') as $sc)
        <div class="prow"><div>{{ Classroom::weekdayLabel((int) $sc->weekday) }}</div><div class="r">{{ \Illuminate\Support\Carbon::parse($sc->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($sc->end_time)->format('H:i') }}</div></div>
      @empty
        <div class="prow r">Chưa đặt lịch.</div>
      @endforelse
    </div></div>
    <div class="panel">
      <div class="ph"><h3>Buổi học · {{ $periodLabel }}</h3>
        <div style="display:flex;gap:6px">
          <a class="btn {{ $period==='week' ? 'primary' : 'ghost' }} sm" href="{{ route('teacher.class', ['id' => $class->id, 'period' => 'week']) }}">Tuần</a>
          <a class="btn {{ $period==='month' ? 'primary' : 'ghost' }} sm" href="{{ route('teacher.class', ['id' => $class->id, 'period' => 'month']) }}">Tháng</a>
        </div>
      </div>
      <div class="pb" style="padding:14px 16px">
        <div class="prow"><div>Đã dạy</div><b>{{ $taught }} buổi</b></div>
        <div class="prow"><div>🔴 Nghỉ</div><b>{{ $offs->count() }}</b></div>
        <div class="prow"><div>🔵 Học bù</div><b>{{ $makeups->count() }}</b></div>
      </div>
    </div>
  </div>
</div>

{{-- Danh sách buổi trong kỳ --}}
<div class="panel"><div class="ph"><h3>Các buổi trong {{ $periodLabel }}</h3></div><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Ngày</th><th>Giờ</th><th>Loại</th><th>Điểm danh</th></tr></thead>
    <tbody>
      @forelse ($sessions as $s)
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($s->date)->format('d/m/Y') }}</td>
          <td>{{ $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') : '—' }}</td>
          <td>
            @switch($s->type)
              @case('off')<span class="chip r">Nghỉ</span>@break
              @case('makeup')<span class="chip b">Học bù</span>@break
              @default<span class="chip n">Buổi thường</span>
            @endswitch
          </td>
          <td>
            @if ($s->type === 'off')<span class="r">—</span>
            @elseif ($s->attendance_submitted_at)<span class="chip g">✓ {{ \Illuminate\Support\Carbon::parse($s->attendance_submitted_at)->format('H:i d/m') }}</span>
            @else<span class="chip a">Chưa điểm danh</span>@endif
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="r" style="padding:16px">Không có buổi nào trong kỳ này.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div></div>

{{-- Popup sửa đơn giá học sinh trong lớp này --}}
<div class="modal-backdrop" id="m-editprice">
  <form class="modal" method="POST" id="f-editprice">
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
  <form class="modal" method="POST" action="{{ route('teacher.class.addStudent', ['id' => $class->id], false) }}">
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

@include('partials.class-modal')
@endsection
