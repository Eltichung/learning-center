@extends('layouts.teacher')
@section('title','Điểm danh — LớpThêm')

@section('content')
<div class="pagehead">
  <div><h1>Điểm danh</h1><p>Toán 9 — Nhóm A · Thứ Hai 08/06/2026 · Buổi thường</p></div>
  <div style="display:flex;gap:8px">
    <select class="field" style="margin:0;padding:8px 11px"><option>Toán 9 — Nhóm A</option><option>Lý 12 — Nhóm B</option></select>
    <a class="btn ghost" href="#">Đánh dấu nghỉ</a>
  </div>
</div>

<div class="note">💡 Mặc định tất cả <b>Có mặt</b>. Mỗi buổi có mặt/học bù được tính <b>1 buổi</b> theo đơn giá của học sinh. Chỉ cần sửa người vắng rồi Lưu.</div>

<div class="panel"><div class="pb">
  <table class="attgrid">
    <thead><tr><th>Học sinh</th><th>Đơn giá/buổi</th><th style="width:340px">Trạng thái</th><th>Thành tiền</th></tr></thead>
    <tbody>
      <tr><td><div class="stud"><div class="savatar">NA</div>Nguyễn Bảo An</div></td><td class="money">120.000đ</td>
        <td><div class="seg"><span class="on p">Có mặt</span><span class="e">Vắng phép</span><span class="a">Vắng</span><span class="m">Học bù</span></div></td><td class="money">120.000đ</td></tr>
      <tr><td><div class="stud"><div class="savatar">TH</div>Trần Gia Hân</div></td><td class="money">120.000đ</td>
        <td><div class="seg"><span class="on p">Có mặt</span><span class="e">Vắng phép</span><span class="a">Vắng</span><span class="m">Học bù</span></div></td><td class="money">120.000đ</td></tr>
      <tr><td><div class="stud"><div class="savatar">LM</div>Lê Minh</div></td><td class="money">100.000đ</td>
        <td><div class="seg"><span class="p">Có mặt</span><span class="on e">Vắng phép</span><span class="a">Vắng</span><span class="m">Học bù</span></div></td><td class="money" style="color:var(--muted)">0đ</td></tr>
      <tr><td><div class="stud"><div class="savatar">PD</div>Phạm Đức</div></td><td class="money">120.000đ</td>
        <td><div class="seg"><span class="on p">Có mặt</span><span class="e">Vắng phép</span><span class="a">Vắng</span><span class="m">Học bù</span></div></td><td class="money">120.000đ</td></tr>
    </tbody>
  </table>
</div></div>

<div style="display:flex;justify-content:space-between;align-items:center">
  <div style="font-size:13px;color:var(--muted)">Tổng buổi này: <b style="color:var(--ink)">3 buổi · 360.000đ</b></div>
  <a class="btn primary" href="#">Lưu điểm danh</a>
</div>

@push('scripts')
<script src="{{ asset('js/attendance.js') }}"></script>
@endpush
@endsection
