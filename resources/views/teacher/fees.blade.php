@extends('layouts.teacher')
@section('title','Học phí & công nợ — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Học phí & công nợ</h1><p>Tháng 6/2026</p></div><a class="btn primary" href="#">+ Ghi nhận đóng tiền</a></div>

<div class="cards" style="grid-template-columns:repeat(3,1fr)">
  <div class="card"><div class="lbl">Đã thu tháng này</div><div class="val green">11.2tr</div></div>
  <div class="card"><div class="lbl">Còn phải thu</div><div class="val red">7.2tr</div></div>
  <div class="card"><div class="lbl">Học sinh còn nợ</div><div class="val">9</div></div>
</div>

<div class="panel"><div class="ph"><h3>Danh sách công nợ</h3><a class="btn ghost sm" href="#">Lọc: Còn nợ ▾</a></div><div class="pb">
  <table>
    <thead><tr><th>Học sinh</th><th>Số buổi chưa đóng</th><th>Công nợ</th><th>Lần đóng gần nhất</th><th></th></tr></thead>
    <tbody>
      <tr><td><div class="stud"><div class="savatar">VK</div>Vũ Khánh</div></td><td>4 buổi</td><td><span class="chip r">−600.000đ</span></td><td class="r">05/05</td><td style="text-align:right"><a class="btn primary sm" href="#">Thu tiền</a></td></tr>
      <tr><td><div class="stud"><div class="savatar">NA</div>Nguyễn Bảo An</div></td><td>4 buổi</td><td><span class="chip r">−480.000đ</span></td><td class="r">05/05</td><td style="text-align:right"><a class="btn primary sm" href="#">Thu tiền</a></td></tr>
      <tr><td><div class="stud"><div class="savatar">LM</div>Lê Minh</div></td><td>3 buổi</td><td><span class="chip r">−300.000đ</span></td><td class="r">02/05</td><td style="text-align:right"><a class="btn primary sm" href="#">Thu tiền</a></td></tr>
    </tbody>
  </table>
</div></div>
@endsection
