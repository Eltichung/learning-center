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
    <div class="weeklegend-wrap">
      <button type="button" class="legend-toggle" onclick="toggleLegend(this)" aria-expanded="false"><span class="lbl">Xem chú thích màu</span><span class="caret">▾</span></button>
      <div class="weeklegend-grid">
        <div class="lg-box">
          <div class="lg-head" style="color:var(--green)"><span class="d" style="background:var(--green)"></span>Đã học</div>
          <div class="lg-item"><i class="lgi lgi-present">✓</i><span class="lg-t">Có mặt</span></div>
          <div class="lg-item"><i class="lgi lgi-makeup">↻</i><span class="lg-t">Học bù</span></div>
        </div>
        <div class="lg-box">
          <div class="lg-head" style="color:var(--red)"><span class="d" style="background:var(--red)"></span>Vắng</div>
          <div class="lg-item"><i class="lgi lgi-absent">✕</i><span class="lg-t">Không phép<span class="n"></span></span></div>
          <div class="lg-item"><i class="lgi lgi-excused">△</i><span class="lg-t">Có phép<span class="n"></span></span></div>
        </div>
        <div class="lg-box">
          <div class="lg-head" style="color:var(--muted)"><span class="d" style="background:var(--muted)"></span>Không có buổi</div>
          <div class="lg-item"><i class="lgi lgi-off">–</i><span class="lg-t">nghỉ</span></div>
          <div class="lg-item"><i class="lgi lgi-study">•</i><span class="lg-t">Sắp học</span></div>
          <div class="lg-item muted"><i class="lgi lgi-none"></i><span class="lg-t">Không có lịch<span class="n">Ô mờ</span></span></div>
        </div>
        <div class="lg-box">
          <div class="lg-head" style="color:var(--amber)"><span class="d" style="background:var(--amber)"></span>Đặc biệt</div>
          <div class="lg-item"><i class="lgi lgi-holiday">⚑</i><span class="lg-t">Nghỉ lễ</span></div>
        </div>
      </div>
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
