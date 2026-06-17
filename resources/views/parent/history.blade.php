@extends('layouts.parent')
@section('title','Lịch sử học — LớpThêm')

@section('content')
<div class="ptop" style="padding-bottom:16px">
  <a class="small" href="{{ route('parent.info', $slug) }}">← {{ $student->full_name }}</a>
  <h2>Lịch sử học</h2>
</div>
<div class="pbody">
  <div class="weeknav">
    <button id="wprev" onclick="weekStep(-1)">◀</button>
    <div class="wlabel" id="weekLabel"></div>
    <button id="wnext" onclick="weekStep(1)">▶</button>
  </div>
  <div class="pcard">
    <div class="weekgrid" id="histGrid"></div>
    <div class="weeklegend">
      <span><i class="dot" style="background:var(--green)"></i>Có mặt</span>
      <span><i class="dot" style="background:var(--amber)"></i>Vắng có phép</span>
      <span><i class="dot" style="background:var(--red)"></i>Vắng không phép</span>
      <span><i class="dot" style="background:var(--blue)"></i>Học bù</span>
      <span><i class="dot" style="background:var(--red)"></i>Nghỉ</span>
      <span><i class="dot" style="background:var(--amber)"></i>Sắp học</span>
    </div>
  </div>
  <div class="pcard">
    <h4>Chi tiết buổi học</h4>
    <div id="histDetail"></div>
  </div>
  <div class="pcard">
    <h4>Tổng kết tuần</h4>
    <div id="histSummary"></div>
  </div>
</div>

@push('scripts')
<script>
  window.LT_WEEKS = @json($weeks);
  window.LT_PRICE_K = {{ (int) ($price / 1000) }};
  window.LT_WEEK_INDEX = {{ $weekIndex }};
</script>
<script src="{{ asset('js/parent-week.js') }}?v={{ filemtime(public_path('js/parent-week.js')) }}"></script>
<script>renderHistory();</script>
@endpush
@endsection
