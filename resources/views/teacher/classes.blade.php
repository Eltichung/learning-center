@extends('layouts.teacher')
@section('title','Lớp học — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Lớp học</h1><p>{{ $activeCount }} lớp đang hoạt động</p></div><button class="btn primary" type="button" onclick="newClass()">+ Tạo lớp mới</button></div>

<form class="filterbar" method="GET" action="{{ route('teacher.classes') }}">
  <select name="grade" onchange="this.form.submit()">
    <option value="">Tất cả khối</option>
    @for ($g = 1; $g <= 12; $g++)<option value="{{ $g }}" @selected($grade === $g)>Lớp {{ $g }}</option>@endfor
  </select>
  <select name="type" onchange="this.form.submit()">
    <option value="">Tất cả loại</option>
    <option value="group" @selected($type === 'group')>Học thêm (nhóm)</option>
    <option value="tutor_1on1" @selected($type === 'tutor_1on1')>Gia sư 1-1</option>
  </select>
  <select name="status" onchange="this.form.submit()">
    <option value="">Tất cả trạng thái</option>
    <option value="active" @selected($status === 'active')>Hoạt động</option>
    <option value="paused" @selected($status === 'paused')>Tạm dừng</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên lớp...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($grade || $q !== '' || $type || $status)<a class="btn ghost sm" href="{{ route('teacher.classes') }}">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Tên lớp</th><th>Loại</th><th>Khối</th><th>Lịch học</th><th>Khai giảng</th><th>Sĩ số</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
      @forelse ($classes as $c)
        @php($cdata = [
          'id' => $c->id, 'name' => $c->name, 'type' => $c->type, 'grade' => $c->grade,
          'subject' => $c->subject, 'status' => $c->status,
          'start_date' => optional($c->start_date)->toDateString(),
          'schedules' => $c->schedules->sortBy('weekday')->map(fn ($s) => [
            'weekday' => (int) $s->weekday,
            'start' => $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') : '17:30',
            'end' => $s->end_time ? \Illuminate\Support\Carbon::parse($s->end_time)->format('H:i') : '19:00',
          ])->values(),
          'locked' => (int) ($c->submitted_count ?? 0) > 0,
        ])
        <tr>
          <td><b>{{ $c->name }}</b></td>
          <td><span class="chip {{ $c->typeChip() }}">{{ $c->typeLabel() }}</span></td>
          <td>{{ $c->gradeLabel() }}</td>
          <td>{{ $c->scheduleLabel() }}</td>
          <td>{{ $c->start_date ? $c->start_date->format('d/m/Y') : '—' }}</td>
          <td>{{ $c->class_students_count }}</td>
          <td>
            <span class="chip {{ $c->statusChip() }}">{{ $c->statusLabel() }}</span>
            @if ($c->status === 'paused' && $c->ended_at)<div class="r" style="font-size:11px">KT: {{ $c->ended_at->format('d/m/Y') }}</div>@endif
          </td>
          <td style="text-align:right;white-space:nowrap">
            <div class="row-menu-wrap" style="position:relative;display:inline-flex;gap:6px;align-items:center;justify-content:flex-end">
              <a class="btn ghost sm" href="{{ route('teacher.attendance', ['class_id' => $c->id]) }}">Điểm danh</a>
              <a class="btn ghost sm" href="{{ route('teacher.class', $c->id) }}">Chi tiết</a>
              <button class="btn ghost sm kebab" type="button" aria-label="Thêm thao tác" onclick="toggleRowMenu(this)" style="padding:6px 8px">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
              </button>
              <div class="row-menu">
                <button type="button" class="rmi" id="editbtn-{{ $c->id }}" onclick='closeRowMenus(); editClass(@json($cdata))'>Sửa lớp</button>
                <form method="POST" action="{{ route('teacher.classes.duplicate', ['id' => $c->id], false) }}" data-confirm="Nhân bản lớp “{{ $c->name }}” (kèm lịch học và toàn bộ học sinh)?">
                  @csrf
                  <button type="submit" class="rmi">Nhân bản</button>
                </form>
              </div>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="r" style="padding:18px 16px">Chưa có lớp nào.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
  @include('partials.pagination', ['paginator' => $classes])
</div></div>

@include('partials.class-modal')

<script>
// Menu "..." mỗi dòng — dùng position:fixed để không bị bảng (overflow) cắt
function toggleRowMenu(btn){
  var menu = btn.parentNode.querySelector('.row-menu');
  var isOpen = menu.style.display === 'block';
  closeRowMenus();
  if(isOpen) return;
  menu.style.display = 'block';
  var r = btn.getBoundingClientRect();
  var mw = menu.offsetWidth || 150;
  menu.style.top = (r.bottom + 4) + 'px';
  menu.style.left = Math.max(8, r.right - mw) + 'px';
}
function closeRowMenus(){ document.querySelectorAll('.row-menu').forEach(function(m){ m.style.display='none'; }); }
document.addEventListener('click', function(e){
  if(e.target.closest('.kebab') || e.target.closest('.row-menu')) return;
  closeRowMenus();
});
window.addEventListener('scroll', closeRowMenus, true);
window.addEventListener('resize', closeRowMenus);

// Sau khi nhân bản, tự mở form sửa lớp mới (?edit=<id>)
document.addEventListener('DOMContentLoaded', function(){
  var editId = new URLSearchParams(window.location.search).get('edit');
  if(editId){ var btn=document.getElementById('editbtn-'+editId); if(btn) btn.click(); }
});
</script>
@endsection
