@extends('layouts.parent')
@section('title','Tra cứu — LớpThêm')

@section('content')
<form class="search-screen" method="POST" action="{{ route('parent.search') }}">
  @csrf
  <div class="mk">L</div>
  <h2>Tra cứu học tập</h2>
  <p>Nhập mã học sinh cô giáo gửi cho bạn để xem lịch học và học phí.</p>
  @if ($errors->any())
    <div class="auth-error" style="text-align:center">{{ $errors->first() }}</div>
  @endif
  <input name="code" placeholder="VD: an-toan9" value="{{ old('code') }}">
  <button class="btn primary" type="submit">Tra cứu</button>
  <div class="or">— hoặc —</div>
  <div style="font-size:12.5px;color:var(--muted)">Mở trực tiếp link cô giáo gửi:<br><b style="color:var(--brand)">lopthem.vn/co-lan/an-toan9</b></div>
</form>
@endsection
