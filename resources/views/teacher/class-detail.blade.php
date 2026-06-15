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
              <td><div class="stud"><div class="savatar">{{ $row->student->initials() }}</div>{{ $row->student->full_name }}</div></td>
              <td class="money">{{ Money::vnd($row->price) }}</td>
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

{{-- Popup thêm học sinh vào lớp (chọn nhiều cùng lúc) --}}
<div class="modal-backdrop" id="m-addstudent">
  <form class="modal" method="POST" action="{{ route('teacher.class.addStudent', $class->id) }}">
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
