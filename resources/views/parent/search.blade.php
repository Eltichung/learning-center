@extends('layouts.parent')
@section('title','Tra cứu — LớpThêm')

@section('content')
<form class="search-screen" method="GET" onsubmit="event.preventDefault(); var v=this.code.value.trim(); if(v) location.href='{{ url('p') }}/'+v;">
  <div class="mk">L</div>
  <h2>Tra cứu học tập</h2>
  <p>Nhập mã học sinh cô giáo gửi cho bạn để xem lịch học và học phí.</p>
  <input name="code" placeholder="VD: an-toan9" value="">
  <button class="btn primary" type="submit">Tra cứu</button>
  <div class="or">— hoặc —</div>
  <div style="font-size:12.5px;color:var(--muted)">Mở trực tiếp link cô giáo gửi:<br><b style="color:var(--brand)">lopthem.vn/co-lan/an-toan9</b></div>
</form>
@endsection
