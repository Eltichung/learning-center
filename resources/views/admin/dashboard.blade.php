@extends('layouts.admin')
@section('title','Tổng quan — Quản trị')

@section('content')
<div class="pagehead"><div><h1>Tổng quan hệ thống</h1><p>Toàn bộ giáo viên &amp; dữ liệu</p></div></div>

<div class="cards">
  <div class="card"><div class="lbl">Giáo viên</div><div class="val">{{ $stats->teachers }}</div><div class="sub">{{ $stats->locked }} đang bị khoá</div></div>
  <div class="card"><div class="lbl">Tổng lớp học</div><div class="val">{{ $stats->classes }}</div></div>
  <div class="card"><div class="lbl">Tổng học sinh</div><div class="val">{{ $stats->students }}</div></div>
</div>

<div class="panel"><div class="ph"><h3>Bắt đầu</h3></div><div class="pb" style="padding:16px">
  <a class="btn primary" href="{{ route('admin.teachers') }}">Quản lý giáo viên →</a>
</div></div>
@endsection
