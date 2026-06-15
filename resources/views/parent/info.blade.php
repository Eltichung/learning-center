@extends('layouts.parent')
@section('title','Thông tin học sinh — LớpThêm')
@use('App\Support\Money')

@section('content')
@php($wdShort = [1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'])
@php($wdFull = [1=>'Thứ Hai',2=>'Thứ Ba',3=>'Thứ Tư',4=>'Thứ Năm',5=>'Thứ Sáu',6=>'Thứ Bảy',7=>'Chủ Nhật'])
<div class="ptop">
  <div class="small">{{ $className }} — {{ $teacherName }}</div>
  <h2>{{ $student->full_name }}</h2>
</div>
<div class="pbody">
  {{-- Tổng nợ --}}
  <div class="due-card">
    <div class="due-total">💰 Tổng học phí chưa đóng</div>
    @if ($balance > 0)
      <div class="amt">{{ Money::vnd($balance) }}</div>
      <div class="meta">{{ $unpaidSessions }} buổi chưa đóng × {{ Money::vnd($price) }}</div>
    @else
      <div class="amt">Đã đóng đủ</div>
      <div class="meta">Không còn công nợ. Cảm ơn quý phụ huynh!</div>
    @endif
  </div>

  {{-- Lịch học cố định --}}
  <div class="pcard">
    <h4>📅 Lịch học cố định</h4>
    @forelse ($schedules as $sc)
      <div class="sched-day"><div class="day-pill">{{ $wdShort[$sc->weekday] }}</div><div class="day-info">{{ $wdFull[$sc->weekday] }}<div class="t">{{ $sc->start }} – {{ $sc->end }} · {{ $sc->class }}</div></div></div>
    @empty
      <div class="prow r">Chưa có lịch học.</div>
    @endforelse
  </div>

  {{-- Tuần này --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🗓️ Tuần này</h4><a class="linklike" href="{{ route('parent.history', $slug) }}">Lịch sử →</a></div>
    <div class="weekgrid" id="thisweek-grid"></div>
    <div class="weeklegend">
      <span><i class="dot" style="background:var(--green)"></i>Có mặt</span>
      <span><i class="dot" style="background:var(--blue)"></i>Học bù</span>
      <span><i class="dot" style="background:var(--red)"></i>Nghỉ</span>
      <span><i class="dot" style="background:var(--amber)"></i>Sắp học</span>
    </div>
  </div>

  {{-- Đóng tiền gần đây --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🧾 Đóng tiền gần đây</h4><a class="linklike" href="{{ route('parent.history', $slug) }}">Tất cả →</a></div>
    @forelse ($payments->take(3) as $p)
      <div class="prow"><div>{{ \Illuminate\Support\Carbon::parse($p->paid_at)->format('d/m/Y') }}<div class="r">{{ $p->method === 'transfer' ? 'Chuyển khoản' : 'Tiền mặt' }}{{ $p->note ? ' · '.$p->note : '' }}</div></div><b>{{ Money::vnd($p->amount) }}</b></div>
    @empty
      <div class="prow r">Chưa có lần đóng tiền nào.</div>
    @endforelse
  </div>

  <div style="text-align:center;color:var(--muted);font-size:11px;padding:8px 0 20px">Cập nhật bởi {{ $teacherName }} · LớpThêm</div>
</div>

@push('scripts')
<script>
  window.LT_WEEKS = @json($weeks);
  window.LT_PRICE_K = {{ (int) ($price / 1000) }};
  window.LT_WEEK_INDEX = {{ $weekIndex }};
</script>
<script src="{{ asset('js/parent-week.js') }}"></script>
<script>renderThisWeek();</script>
@endpush
@endsection
