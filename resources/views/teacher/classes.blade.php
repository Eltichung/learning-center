@extends('layouts.teacher')
@section('title','Lớp học — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Lớp học</h1><p>{{ $activeCount }} lớp đang hoạt động</p></div><button class="btn primary" type="button" onclick="newClass()">+ Tạo lớp mới</button></div>

<form class="filterbar" method="GET" action="{{ route('teacher.classes') }}" data-refetch="#classes-list">
  <select name="grade" onchange="this.form.requestSubmit()">
    <option value="">Tất cả khối</option>
    @for ($g = 1; $g <= 12; $g++)<option value="{{ $g }}" @selected($grade === $g)>Lớp {{ $g }}</option>@endfor
  </select>
  <select name="type" onchange="this.form.requestSubmit()">
    <option value="">Tất cả loại</option>
    <option value="group" @selected($type === 'group')>Học thêm (nhóm)</option>
    <option value="tutor_1on1" @selected($type === 'tutor_1on1')>Gia sư 1-1</option>
  </select>
  <select name="status" onchange="this.form.requestSubmit()">
    <option value="">Tất cả trạng thái</option>
    <option value="active" @selected($status === 'active')>Hoạt động</option>
    <option value="paused" @selected($status === 'paused')>Tạm dừng</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên lớp...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($grade || $q !== '' || $type || $status)<a class="btn ghost sm" href="{{ route('teacher.classes') }}" data-refetch="#classes-list">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
  <div id="classes-list" data-partial-url="{{ route('teacher.classes.partial', request()->query()) }}">
    @include('teacher.partials.classes-list', compact('classes'))
  </div>
</div></div>

@include('partials.class-modal', ['classRefetch' => '#classes-list'])

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
