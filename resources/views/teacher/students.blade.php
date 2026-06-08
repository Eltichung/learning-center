@extends('layouts.teacher')
@section('title','Học sinh — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Học sinh</h1><p>42 học sinh</p></div><a class="btn primary" href="#">+ Thêm học sinh</a></div>
<div class="panel"><div class="pb">
  <table>
    <thead><tr><th>Học sinh</th><th>Lớp</th><th>SĐT phụ huynh</th><th>Mã tra cứu</th><th>Công nợ</th></tr></thead>
    <tbody>
      <tr onclick="location.href='{{ route('teacher.student',1) }}'" style="cursor:pointer"><td><div class="stud"><div class="savatar">NA</div><div><b>Nguyễn Bảo An</b><div class="r">Lớp 9 · THCS Lê Quý Đôn</div></div></div></td><td>Toán 9-A, Gia sư</td><td>09xx xxx 821</td><td><span class="chip n">an-toan9</span></td><td><span class="chip r">−480.000đ</span></td></tr>
      <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">TH</div><div><b>Trần Gia Hân</b><div class="r">Lớp 9</div></div></div></td><td>Toán 9-A</td><td>09xx xxx 312</td><td><span class="chip n">han-9a</span></td><td><span class="chip g">Đã đóng</span></td></tr>
      <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">LM</div><div><b>Lê Minh</b><div class="r">Lớp 9</div></div></div></td><td>Toán 9-A</td><td>09xx xxx 905</td><td><span class="chip n">le-minh</span></td><td><span class="chip r">−300.000đ</span></td></tr>
      <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">PD</div><div><b>Phạm Đức</b><div class="r">Lớp 9</div></div></div></td><td>Toán 9-A</td><td>09xx xxx 447</td><td><span class="chip n">duc-toan</span></td><td><span class="chip g">Đã đóng</span></td></tr>
      <tr style="cursor:pointer"><td><div class="stud"><div class="savatar">VK</div><div><b>Vũ Khánh</b><div class="r">Lớp 12</div></div></div></td><td>Lý 12-B</td><td>09xx xxx 158</td><td><span class="chip n">khanh-ly12</span></td><td><span class="chip r">−600.000đ</span></td></tr>
    </tbody>
  </table>
</div></div>
@endsection
