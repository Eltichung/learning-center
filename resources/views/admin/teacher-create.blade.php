@extends('layouts.admin')
@section('title','Tạo tài khoản — Quản trị')

@section('content')
<div class="pagehead"><div>
  <a class="backlink" href="{{ route('admin.teachers') }}">← Giáo viên</a>
  <h1>Tạo tài khoản mới</h1>
</div></div>

<div class="panel" style="max-width:640px">
  <div class="pb" style="padding:18px">
    <form method="POST" action="{{ route('admin.teachers.store') }}" data-confirm="Tạo tài khoản này?">
      @csrf
      <div class="field"><label>Tên hiển thị <span style="color:var(--red)">*</span></label>
        <input name="name" required maxlength="255" value="{{ old('name') }}">
      </div>
      <div class="field"><label>Email <span style="color:var(--red)">*</span></label>
        <input type="email" name="email" required maxlength="255" value="{{ old('email') }}">
      </div>
      <div class="grid2">
        <div class="field"><label>Mật khẩu <span style="color:var(--red)">*</span></label>
          <input type="password" name="password" required minlength="6" id="pw-input" value="{{ old('password') }}">
        </div>
        <div class="field"><label>Nhập lại <span style="color:var(--red)">*</span></label>
          <input type="password" name="password_confirmation" required minlength="6" id="pw-confirm">
        </div>
      </div>
      <div style="margin:-6px 0 8px">
        <button type="button" class="btn ghost sm" onclick="randomPw()">🎲 Sinh mật khẩu ngẫu nhiên</button>
      </div>
      <div class="field"><label>Số điện thoại</label>
        <input name="phone" maxlength="20" value="{{ old('phone') }}">
      </div>

      <div class="field"><label>Gói khởi tạo <span style="color:var(--red)">*</span></label>
        <select name="plan" id="plan-select">
          @foreach ($plans as $p)
            <option value="{{ $p->slug }}" @selected(old('plan','trial') === $p->slug)>
              {{ $p->name }} · {{ (int) ($p->limits['classes'] ?? 0) }} lớp / {{ (int) ($p->limits['students'] ?? 0) }} HS
              @if ($p->price > 0) · {{ number_format($p->price,0,',','.') }}đ/tháng @endif
            </option>
          @endforeach
        </select>
      </div>

      <div class="field" id="months-field" style="display:none">
        <label>Kích hoạt bao lâu</label>
        <select name="months">
          <option value="1" selected>1 tháng</option>
          <option value="3">3 tháng</option>
          <option value="6">6 tháng</option>
          <option value="12">12 tháng</option>
        </select>
        <div class="r" style="font-size:11px;margin-top:4px">Không thu tiền — admin cấp thủ công.</div>
      </div>

      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
        <a class="btn ghost" href="{{ route('admin.teachers') }}">Huỷ</a>
        <button class="btn primary" type="submit">Tạo tài khoản</button>
      </div>
    </form>
  </div>
</div>

<script>
  function randomPw(){
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    let s = '';
    for (let i = 0; i < 10; i++) s += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('pw-input').value = s;
    document.getElementById('pw-input').type = 'text';
    document.getElementById('pw-confirm').value = s;
    document.getElementById('pw-confirm').type = 'text';
  }
  const sel = document.getElementById('plan-select');
  const monthsField = document.getElementById('months-field');
  function syncMonths(){ monthsField.style.display = (sel.value === 'trial' || sel.value === 'vip') ? 'none' : 'block'; }
  sel.addEventListener('change', syncMonths);
  syncMonths();
</script>
@endsection
