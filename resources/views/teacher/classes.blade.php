@extends('layouts.teacher')
@section('title','Lớp học — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Lớp học</h1><p>6 lớp đang hoạt động</p></div><a class="btn primary" href="#">+ Tạo lớp mới</a></div>
<div class="panel"><div class="pb">
  <table>
    <thead><tr><th>Tên lớp</th><th>Loại</th><th>Khối</th><th>Lịch học</th><th>Sĩ số</th><th>Trạng thái</th></tr></thead>
    <tbody>
      <tr onclick="location.href='{{ route('teacher.class',1) }}'" style="cursor:pointer"><td><b>Toán 9 — Nhóm A</b></td><td><span class="chip n">Học thêm</span></td><td>Lớp 9</td><td>T2, T4, T6 · 17:30</td><td>8</td><td><span class="chip g">Hoạt động</span></td></tr>
      <tr onclick="location.href='{{ route('teacher.class',2) }}'" style="cursor:pointer"><td><b>Lý 12 — Nhóm B</b></td><td><span class="chip n">Học thêm</span></td><td>Lớp 12</td><td>T2, T5 · 19:15</td><td>6</td><td><span class="chip g">Hoạt động</span></td></tr>
      <tr onclick="location.href='{{ route('teacher.class',3) }}'" style="cursor:pointer"><td><b>Gia sư 1-1 — Bé An</b></td><td><span class="chip b">Gia sư</span></td><td>Lớp 9</td><td>T2, T7 · 14:00</td><td>1</td><td><span class="chip g">Hoạt động</span></td></tr>
      <tr onclick="location.href='{{ route('teacher.class',4) }}'" style="cursor:pointer"><td><b>Văn 8 — Nhóm C</b></td><td><span class="chip n">Học thêm</span></td><td>Lớp 8</td><td>T3, T6 · 18:00</td><td>10</td><td><span class="chip g">Hoạt động</span></td></tr>
      <tr onclick="location.href='{{ route('teacher.class',5) }}'" style="cursor:pointer"><td><b>Anh 6 — Nhóm D</b></td><td><span class="chip n">Học thêm</span></td><td>Lớp 6</td><td>T4, T7 · 16:00</td><td>9</td><td><span class="chip g">Hoạt động</span></td></tr>
      <tr onclick="location.href='{{ route('teacher.class',6) }}'" style="cursor:pointer"><td><b>Gia sư 1-1 — Bé Minh</b></td><td><span class="chip b">Gia sư</span></td><td>Lớp 11</td><td>CN · 09:00</td><td>1</td><td><span class="chip a">Tạm dừng</span></td></tr>
    </tbody>
  </table>
</div></div>
@endsection
