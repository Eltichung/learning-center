@extends('layouts.parent')
@section('title','Thông tin học sinh — LớpThêm')

@section('content')
<div class="ptop">
  <div class="small">Lớp Toán 9 — Cô Lan</div>
  <h2>Nguyễn Bảo An</h2>
</div>
<div class="pbody">
  {{-- Tổng nợ --}}
  <div class="due-card">
    <div class="due-total">💰 Tổng học phí chưa đóng</div>
    <div class="amt">480.000đ</div>
    <div class="meta">4 buổi chưa đóng × 120.000đ</div>
  </div>

  {{-- Lịch học cố định --}}
  <div class="pcard">
    <h4>📅 Lịch học cố định</h4>
    <div class="sched-day"><div class="day-pill">T2</div><div class="day-info">Thứ Hai<div class="t">17:30 – 19:00 · Toán 9</div></div></div>
    <div class="sched-day"><div class="day-pill">T4</div><div class="day-info">Thứ Tư<div class="t">17:30 – 19:00 · Toán 9</div></div></div>
    <div class="sched-day"><div class="day-pill">T6</div><div class="day-info">Thứ Sáu<div class="t">17:30 – 19:00 · Toán 9</div></div></div>
  </div>

  {{-- Tuần này --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🗓️ Tuần này · 01–07/06</h4><a class="linklike" href="{{ route('parent.history', $slug ?? 'an-toan9') }}">Lịch sử →</a></div>
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
    <div class="pcard-head"><h4>🧾 Đóng tiền gần đây</h4><a class="linklike" href="{{ route('parent.history', $slug ?? 'an-toan9') }}">Tất cả →</a></div>
    <div class="prow"><div>05/05/2026<div class="r">Chuyển khoản · tháng 5</div></div><b>1.200.000đ</b></div>
    <div class="prow"><div>06/04/2026<div class="r">Tiền mặt · tháng 4</div></div><b>1.080.000đ</b></div>
  </div>

  <div style="text-align:center;color:var(--muted);font-size:11px;padding:8px 0 20px">Cập nhật bởi Cô Lan · LớpThêm</div>
</div>

@push('scripts')
<script src="{{ asset('js/parent-week.js') }}"></script>
<script>renderThisWeek();</script>
@endpush
@endsection
