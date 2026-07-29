@extends('layouts.admin')
@section('title','Giáo viên — Quản trị')

@section('content')
<div class="pagehead">
  <div><h1>Giáo viên</h1><p>{{ $teachers->total() }} giáo viên</p></div>
  <a class="btn primary" href="{{ route('admin.teachers.create') }}">+ Tạo tài khoản mới</a>
</div>

<form class="filterbar" method="GET" action="{{ route('admin.teachers') }}">
  <select name="status" onchange="this.form.submit()">
    <option value="">Tất cả trạng thái</option>
    <option value="active" @selected($status === 'active')>Hoạt động</option>
    <option value="locked" @selected($status === 'locked')>Đã khoá</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên / email...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($q !== '' || $status)<a class="btn ghost sm" href="{{ route('admin.teachers') }}">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Giáo viên</th><th>Email</th><th>SĐT</th><th>Số lớp</th><th>Số HS</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
      @forelse ($teachers as $t)
        <tr>
          <td><b>{{ $t->name }}</b></td>
          <td>{{ $t->email }}</td>
          <td>{{ $t->phone ?: '—' }}</td>
          <td>{{ $t->classes_count }}</td>
          <td>{{ $t->students_count }}</td>
          <td>@if ($t->status === 'locked')<span class="chip r">Đã khoá</span>@else<span class="chip g">Hoạt động</span>@endif</td>
          <td style="text-align:right"><a class="btn ghost sm" href="{{ route('admin.teacher', $t->id) }}">Quản lý</a></td>
        </tr>
      @empty
        <tr><td colspan="7" class="r" style="padding:16px">Chưa có giáo viên nào.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
  @include('partials.pagination', ['paginator' => $teachers])
</div></div>
@endsection
