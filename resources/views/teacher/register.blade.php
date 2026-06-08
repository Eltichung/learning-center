@extends('layouts.base')
@section('title','Đăng ký — LớpThêm')

@section('body')
<div class="login-wrap">
  <form class="login-card" method="POST" action="{{ route('teacher.register') }}">
    @csrf
    <div class="mark">L</div>
    <h1>Đăng ký miễn phí</h1>
    <div class="sub">Tạo tài khoản quản lý lớp học thêm</div>

    @if ($errors->any())
      <div class="auth-error">
        @foreach ($errors->all() as $err)
          <div>{{ $err }}</div>
        @endforeach
      </div>
    @endif

    <div class="field">
      <label>Họ tên</label>
      <input type="text" name="name" value="{{ old('name') }}" autofocus>
    </div>
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" value="{{ old('email') }}">
    </div>
    <div class="field">
      <label>Mật khẩu</label>
      <input type="password" name="password" value="">
    </div>
    <div class="field">
      <label>Nhập lại mật khẩu</label>
      <input type="password" name="password_confirmation" value="">
    </div>

    <button class="btn primary" style="width:100%;padding:12px" type="submit">Tạo tài khoản</button>
    <div style="text-align:center;margin-top:14px;font-size:12.5px;color:var(--muted)">
      Đã có tài khoản?
      <a href="{{ route('teacher.login') }}" style="color:var(--brand);font-weight:700;text-decoration:none">Đăng nhập</a>
    </div>
  </form>
</div>
@endsection
