@extends('layouts.admin')
@section('title', $teacher->name.' — Quản trị')

@section('content')
<div class="pagehead"><div>
  <a class="backlink" href="{{ route('admin.teachers') }}">← Giáo viên</a>
  <h1>{{ $teacher->name }}
    @if ($teacher->status === 'locked')<span class="chip r" style="font-size:12px;vertical-align:middle">Đã khoá</span>@endif
    @if ($teacher->role === 'super_admin')<span class="chip b" style="font-size:12px;vertical-align:middle">Admin</span>@endif
  </h1>
  <p>{{ $teacher->email }}{{ $teacher->phone ? ' · '.$teacher->phone : '' }} · {{ $teacher->classes_count }} lớp · {{ $teacher->students_count }} học sinh</p>
</div></div>

<div id="admin-teacher-body" data-partial-url="{{ route('admin.teacher.partial', $teacher->id) }}">
  @include('admin.partials.teacher-body', compact('teacher','plans','recentOrders'))
</div>

@endsection
