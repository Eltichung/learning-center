@extends('layouts.base')
@section('title','Đăng nhập — LớpThêm')

@section('body')
<div class="login-wrap">
  <form class="login-card" method="GET" action="{{ route('teacher.dashboard') }}">
    <div class="mark">L</div>
    <h1>Đăng nhập</h1>
    <div class="sub">Quản lý lớp học thêm của bạn</div>
    <div class="field"><label>Email</label><input type="email" name="email" value="colan@email.com"></div>
    <div class="field"><label>Mật khẩu</label><input type="password" name="password" value="password"></div>
    <button class="btn primary" style="width:100%;padding:12px" type="submit">Đăng nhập</button>
    <div style="text-align:center;margin-top:14px;font-size:12.5px;color:var(--muted)">
      Chưa có tài khoản? <b style="color:var(--brand)">Đăng ký miễn phí</b>
    </div>
  </form>
</div>
@endsection
