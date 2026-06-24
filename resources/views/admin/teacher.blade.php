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

<div class="twocol">
  <div>
    <div class="panel"><div class="ph"><h3>Trạng thái tài khoản</h3></div><div class="pb" style="padding:16px">
      <p class="r" style="margin:0 0 12px;font-size:13px">
        {{ $teacher->status === 'locked'
            ? 'Tài khoản đang bị khoá — giáo viên không đăng nhập được.'
            : 'Tài khoản đang hoạt động bình thường.' }}
      </p>
      <form method="POST" action="{{ route('admin.teacher.toggleStatus', $teacher->id) }}"
            data-confirm="{{ $teacher->status === 'locked' ? 'Mở khoá tài khoản này?' : 'Khoá tài khoản này? Giáo viên sẽ không đăng nhập được.' }}">
        @csrf @method('PUT')
        @if ($teacher->status === 'locked')
          <button class="btn primary" type="submit">Mở khoá tài khoản</button>
        @else
          <button class="btn danger" type="submit">Khoá tài khoản</button>
        @endif
      </form>
    </div></div>

    <div class="panel"><div class="ph"><h3>Quyền (role)</h3></div><div class="pb" style="padding:16px">
      <form method="POST" action="{{ route('admin.teacher.role', $teacher->id) }}" data-confirm="Đổi quyền tài khoản này?">
        @csrf @method('PUT')
        <div class="field"><label>Role</label>
          <select name="role">
            <option value="owner" @selected($teacher->role === 'owner')>Giáo viên (owner)</option>
            <option value="super_admin" @selected($teacher->role === 'super_admin')>Quản trị (super_admin)</option>
          </select>
        </div>
        <button class="btn primary" type="submit">Lưu quyền</button>
      </form>
    </div></div>
  </div>

  <div>
    <div class="panel"><div class="ph"><h3>Đặt lại mật khẩu</h3></div><div class="pb" style="padding:16px">
      <form method="POST" action="{{ route('admin.teacher.password', $teacher->id) }}" data-confirm="Đặt lại mật khẩu cho giáo viên này?">
        @csrf @method('PUT')
        <div class="field"><label>Mật khẩu mới <span style="color:var(--red)">*</span></label>
          <input type="password" name="password" required minlength="6" placeholder="Tối thiểu 6 ký tự"></div>
        <div class="field"><label>Nhập lại mật khẩu <span style="color:var(--red)">*</span></label>
          <input type="password" name="password_confirmation" required minlength="6"></div>
        <button class="btn primary" type="submit">Đặt lại mật khẩu</button>
      </form>
    </div></div>
  </div>
</div>
@endsection
