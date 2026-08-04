@extends('layouts.admin')
@section('title','Giáo viên — Quản trị')

@section('content')
<div class="pagehead">
  <div><h1>Giáo viên</h1><p>{{ $teachers->total() }} giáo viên</p></div>
  <a class="btn primary" href="{{ route('admin.teachers.create') }}">+ Tạo tài khoản mới</a>
</div>

<form class="filterbar" method="GET" action="{{ route('admin.teachers') }}" data-refetch="#admin-teachers">
  <select name="status" onchange="this.form.requestSubmit()">
    <option value="">Tất cả trạng thái</option>
    <option value="active" @selected($status === 'active')>Hoạt động</option>
    <option value="locked" @selected($status === 'locked')>Đã khoá</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên / email...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($q !== '' || $status)<a class="btn ghost sm" href="{{ route('admin.teachers') }}" data-refetch="#admin-teachers">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
  <div id="admin-teachers" data-partial-url="{{ route('admin.teachers', array_merge(request()->query(), ['partial' => 1])) }}">
    @include('admin.partials.teachers-list', compact('teachers'))
  </div>
</div></div>

@endsection
