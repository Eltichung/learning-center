@extends('layouts.teacher')
@section('title','Hồ sơ học sinh — LớpThêm')

@section('content')
<div class="pagehead">
  <div>
    <a class="backlink" href="{{ route('teacher.students') }}">← Học sinh</a>
    <h1>Nguyễn Bảo An</h1>
    <p>Lớp 9 · THCS Lê Quý Đôn</p>
  </div>
  <a class="btn primary" href="{{ route('teacher.fees') }}">+ Ghi nhận đóng tiền</a>
</div>

<div class="twocol">
  <div>
    <div class="panel"><div class="ph"><h3>Thông tin & link tra cứu</h3></div><div class="pb" style="padding:16px">
      <div class="field"><label>Họ tên</label><input value="Nguyễn Bảo An"></div>
      <div class="field"><label>SĐT phụ huynh</label><input value="09xx xxx 821"></div>
      <div class="field">
        <label>Link tra cứu (slug do bạn tự đặt)</label>
        <div class="slugrow"><div class="pre">lopthem.vn/co-lan/</div><input value="an-toan9" style="flex:1"></div>
        <div class="hint">✓ Còn trống trong tài khoản của bạn</div>
      </div>
      <a class="btn ghost sm" href="{{ route('parent.info','an-toan9') }}" target="_blank">📋 Xem trang phụ huynh</a>
    </div></div>
  </div>
  <div>
    <div class="panel"><div class="ph"><h3>Công nợ</h3></div><div class="pb" style="padding:16px">
      <div style="font-size:28px;font-weight:800;color:var(--red)">−480.000đ</div>
      <div class="r" style="margin-top:4px">4 buổi chưa đóng × 120.000đ</div>
    </div></div>
    <div class="panel"><div class="ph"><h3>Đơn giá / buổi</h3></div><div class="pb" style="padding:16px">
      <div class="prow"><div>Toán 9 — Nhóm A</div><b>120.000đ</b></div>
      <div class="prow"><div>Gia sư 1-1</div><b>200.000đ</b></div>
    </div></div>
  </div>
</div>

<div class="panel"><div class="ph"><h3>Lịch sử đóng tiền</h3></div><div class="pb">
  <table>
    <thead><tr><th>Ngày</th><th>Số tiền</th><th>Hình thức</th><th>Ghi chú</th></tr></thead>
    <tbody>
      <tr><td>05/05/2026</td><td class="money">1.200.000đ</td><td><span class="chip b">Chuyển khoản</span></td><td class="r">Học phí tháng 5</td></tr>
      <tr><td>06/04/2026</td><td class="money">1.080.000đ</td><td><span class="chip n">Tiền mặt</span></td><td class="r">Tháng 4</td></tr>
    </tbody>
  </table>
</div></div>
@endsection
