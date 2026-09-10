@extends('layouts.teacher')
@section('title','Báo cáo — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead"><div><h1>Báo cáo học phí</h1><p>{{ $monthLabel }} · {{ $classIds->count() }} lớp</p></div></div>

<form class="rpt-filter" method="GET" action="{{ route('teacher.reports') }}" data-refetch="#reports-body">
  <input type="hidden" name="class_ids" value="{{ $classIds->implode(',') }}">
  <input type="hidden" name="months" value="{{ $monthStrs->implode(',') }}">

  {{-- Lớp: select box đa chọn (checkbox trong dropdown) --}}
  <div class="rpt-ms" data-rpt-ms="class">
    <span class="rpt-flabel">Lớp</span>
    <div class="rpt-ms-box">
      <button type="button" class="rpt-ms-btn" data-rpt-toggle>
        <span class="rpt-ms-summary">{{ $classSummary }}</span><span class="rpt-ms-caret">▾</span>
      </button>
      <div class="rpt-ms-panel" hidden>
        <label class="rpt-ms-opt rpt-ms-all"><input type="checkbox" data-rpt-all> <span>Tất cả lớp</span></label>
        <div class="rpt-ms-sep"></div>
        @foreach ($classList as $c)
          <label class="rpt-ms-opt"><input type="checkbox" class="rpt-ms-item" value="{{ $c->id }}" @checked($classIds->contains($c->id))> <span>{{ $c->name }}</span></label>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Tháng: select box đa chọn --}}
  <div class="rpt-ms" data-rpt-ms="month" data-now="{{ now()->format('Y-m') }}">
    <span class="rpt-flabel">Tháng</span>
    <div class="rpt-ms-box">
      <button type="button" class="rpt-ms-btn" data-rpt-toggle>
        <span class="rpt-ms-summary">{{ $monthSummary }}</span><span class="rpt-ms-caret">▾</span>
      </button>
      <div class="rpt-ms-panel" hidden>
        <div class="rpt-ms-presets">
          <button type="button" class="rpt-ms-preset" data-preset="this-month">Tháng này</button>
          <button type="button" class="rpt-ms-preset" data-preset="last-3">3 tháng gần nhất</button>
          <button type="button" class="rpt-ms-preset" data-preset="last-6">6 tháng gần nhất</button>
          <button type="button" class="rpt-ms-preset" data-preset="this-year">Cả năm nay</button>
          <button type="button" class="rpt-ms-preset" data-preset="last-year">Năm ngoái</button>
        </div>
        <div class="rpt-ms-sep"></div>
        <div class="rpt-ms-months">
          @foreach ($monthChips as $mc)
            <label class="rpt-ms-opt"><input type="checkbox" class="rpt-ms-item" value="{{ $mc->format('Y-m') }}" @checked($monthStrs->contains($mc->format('Y-m')))> <span>T{{ $mc->format('m/Y') }}</span></label>
          @endforeach
        </div>
        <button type="button" class="rpt-ms-morebtn">⋯ Xem thêm tháng cũ</button>
      </div>
    </div>
  </div>
</form>

<div id="reports-body" data-partial-url="{{ route('teacher.reports.partial', request()->query()) }}">
  @include('teacher.partials.reports-body')
</div>
@endsection
