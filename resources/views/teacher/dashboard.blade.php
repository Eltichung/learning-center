@extends('layouts.teacher')
@section('title','Tổng quan — LớpThêm')

@section('content')
<div class="pagehead">
  <div><h1>Xin chào, Cô Lan 👋</h1><p>Thứ Hai, 08/06/2026 — Hôm nay có 3 buổi học</p></div>
  <a class="btn primary" href="{{ route('teacher.attendance') }}">+ Điểm danh nhanh</a>
</div>

<div class="cards">
  <div class="card"><div class="lbl">Lớp đang dạy</div><div class="val">6</div><div class="sub">42 học sinh</div></div>
  <div class="card"><div class="lbl">Buổi hôm nay</div><div class="val">3</div><div class="sub">2 chưa điểm danh</div></div>
  <div class="card"><div class="lbl">Doanh thu tháng 6</div><div class="val green">18.4tr</div><div class="sub">↑ 12% so tháng trước</div></div>
  <div class="card"><div class="lbl">Đang nợ học phí</div><div class="val red">7.2tr</div><div class="sub">9 học sinh</div></div>
</div>

<div class="panel">
  <div class="ph"><h3>Buổi học hôm nay</h3><a class="btn ghost sm" href="{{ route('teacher.classes') }}">Xem tất cả lớp</a></div>
  <div class="pb">
    <table>
      <thead><tr><th>Giờ</th><th>Lớp</th><th>Sĩ số</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
        <tr><td><b>17:30</b><div class="r">– 19:00</div></td><td>Toán 9 — Nhóm A</td><td>8 học sinh</td><td><span class="chip a">Chưa điểm danh</span></td><td style="text-align:right"><a class="btn primary sm" href="{{ route('teacher.attendance') }}">Điểm danh</a></td></tr>
        <tr><td><b>19:15</b><div class="r">– 20:45</div></td><td>Lý 12 — Nhóm B</td><td>6 học sinh</td><td><span class="chip a">Chưa điểm danh</span></td><td style="text-align:right"><a class="btn primary sm" href="{{ route('teacher.attendance') }}">Điểm danh</a></td></tr>
        <tr><td><b>14:00</b><div class="r">– 15:30</div></td><td>Gia sư 1-1 — Bé An</td><td>1 học sinh</td><td><span class="chip g">Đã điểm danh</span></td><td style="text-align:right"><a class="btn ghost sm" href="#">Xem</a></td></tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
