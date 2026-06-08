@extends('layouts.teacher')
@section('title','Báo cáo — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Báo cáo</h1><p>Doanh thu &amp; buổi dạy 6 tháng gần nhất</p></div><a class="btn ghost" href="#">Xuất PDF</a></div>

<div class="panel"><div class="ph"><h3>Doanh thu theo tháng</h3></div><div class="pb" style="padding:20px 16px">
  <div style="display:flex;align-items:flex-end;gap:18px;height:200px;padding:0 8px">
    <div style="flex:1;text-align:center"><div style="background:var(--brand-soft);height:120px;border-radius:8px 8px 0 0"></div><div class="r" style="margin-top:6px">T1<br>13tr</div></div>
    <div style="flex:1;text-align:center"><div style="background:var(--brand-soft);height:135px;border-radius:8px 8px 0 0"></div><div class="r" style="margin-top:6px">T2<br>14tr</div></div>
    <div style="flex:1;text-align:center"><div style="background:var(--brand-soft);height:150px;border-radius:8px 8px 0 0"></div><div class="r" style="margin-top:6px">T3<br>15tr</div></div>
    <div style="flex:1;text-align:center"><div style="background:var(--brand-soft);height:145px;border-radius:8px 8px 0 0"></div><div class="r" style="margin-top:6px">T4<br>15tr</div></div>
    <div style="flex:1;text-align:center"><div style="background:var(--brand-soft);height:165px;border-radius:8px 8px 0 0"></div><div class="r" style="margin-top:6px">T5<br>16.4tr</div></div>
    <div style="flex:1;text-align:center"><div style="background:var(--brand);height:185px;border-radius:8px 8px 0 0"></div><div class="r" style="margin-top:6px;color:var(--brand-ink);font-weight:700">T6<br>18.4tr</div></div>
  </div>
</div></div>

<div class="twocol">
  <div class="panel"><div class="ph"><h3>Lớp doanh thu cao nhất</h3></div><div class="pb"><table><tbody>
    <tr><td>Văn 8 — Nhóm C</td><td class="money" style="text-align:right">4.8tr</td></tr>
    <tr><td>Anh 6 — Nhóm D</td><td class="money" style="text-align:right">4.2tr</td></tr>
    <tr><td>Toán 9 — Nhóm A</td><td class="money" style="text-align:right">3.4tr</td></tr>
  </tbody></table></div></div>
  <div class="panel"><div class="ph"><h3>Tổng quan tháng 6</h3></div><div class="pb" style="padding:14px 16px">
    <div class="prow"><div>Tổng buổi đã dạy</div><b>148 buổi</b></div>
    <div class="prow"><div>Buổi nghỉ</div><b>6</b></div>
    <div class="prow"><div>Buổi học bù</div><b>4</b></div>
    <div class="prow"><div>Tỷ lệ thu học phí</div><b style="color:var(--green)">61%</b></div>
  </div></div>
</div>
@endsection
