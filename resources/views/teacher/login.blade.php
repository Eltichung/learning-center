@extends('layouts.base')
@section('title','Đăng nhập — LớpThêm')

@section('body')
<div class="login-wrap">
  <form class="login-card" method="POST" action="{{ route('teacher.login') }}">
    @csrf
    <div class="mark">L</div>
    <h1>Đăng nhập</h1>
    <div class="sub">Quản lý lớp học thêm của bạn</div>

    @if ($errors->any())
      <div class="auth-error">{{ $errors->first() }}</div>
    @endif

    <div class="field">
      <label>Email</label>
      <input type="email" name="email" value="{{ old('email', 'colan@email.com') }}" autofocus>
    </div>
    <div class="field">
      <label>Mật khẩu</label>
      <input type="password" name="password" value="">
    </div>
    <label class="checkrow"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>

    <button class="btn primary" style="width:100%;padding:12px" type="submit">Đăng nhập</button>
    <div style="text-align:center;margin-top:14px;font-size:12.5px;color:var(--muted)">
      Chưa có tài khoản?
      <a href="{{ route('teacher.register') }}" style="color:var(--brand);font-weight:700;text-decoration:none">Đăng ký miễn phí</a>
    </div>
  </form>
</div>
@endsection
