@extends('layouts.teacher')
@section('title','Chi tiết lớp — LớpThêm')

@section('content')
<div class="pagehead">
  <div>
    <a class="backlink" href="{{ route('teacher.classes') }}">← Lớp học</a>
    <h1>Toán 9 — Nhóm A</h1>
    <p><span class="chip n">Học thêm</span> &nbsp;Lớp 9 · Môn Toán · 8 học sinh</p>
  </div>
  <div><a class="btn ghost" href="{{ route('teacher.attendance') }}">Điểm danh</a> <a class="btn primary" href="#">Sửa lớp</a></div>
</div>

<div class="twocol">
  <div class="panel">
    <div class="ph"><h3>Học sinh trong lớp</h3><a class="btn ghost sm" href="#">+ Thêm</a></div>
    <div class="pb">
      <table>
        <thead><tr><th>Học sinh</th><th>Đơn giá/buổi</th><th>Công nợ</th></tr></thead>
        <tbody>
          <tr onclick="location.href='{{ route('teacher.student',1) }}'" style="cursor:pointer"><td><div class="stud"><div class="savatar">NA</div>Nguyễn Bảo An</div></td><td class="money">120.000đ</td><td><span class="chip r">−480.000đ</span></td></tr>
          <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">TH</div>Trần Gia Hân</div></td><td class="money">120.000đ</td><td><span class="chip g">Đã đóng</span></td></tr>
          <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">LM</div>Lê Minh</div></td><td class="money">100.000đ</td><td><span class="chip r">−300.000đ</span></td></tr>
          <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">PD</div>Phạm Đức</div></td><td class="money">120.000đ</td><td><span class="chip g">Đã đóng</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div>
    <div class="panel"><div class="ph"><h3>Lịch học cố định</h3></div><div class="pb" style="padding:14px 16px">
      <div class="prow"><div>Thứ 2</div><div class="r">17:30 – 19:00</div></div>
      <div class="prow"><div>Thứ 4</div><div class="r">17:30 – 19:00</div></div>
      <div class="prow"><div>Thứ 6</div><div class="r">17:30 – 19:00</div></div>
    </div></div>
    <div class="panel"><div class="ph"><h3>Tháng 6 này</h3></div><div class="pb" style="padding:14px 16px">
      <div class="prow"><div>Đã dạy</div><b>9 buổi</b></div>
      <div class="prow"><div>🔴 Nghỉ</div><div class="r">02/06 (lễ)</div></div>
      <div class="prow"><div>🔵 Học bù</div><div class="r">07/06 (CN)</div></div>
    </div></div>
  </div>
</div>
@endsection
