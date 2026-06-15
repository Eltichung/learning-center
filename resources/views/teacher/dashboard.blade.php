@extends('layouts.teacher')
@section('title','Tổng quan — LớpThêm')
@use('App\Support\Money')

@section('content')
@php($wd = [1=>'Thứ Hai',2=>'Thứ Ba',3=>'Thứ Tư',4=>'Thứ Năm',5=>'Thứ Sáu',6=>'Thứ Bảy',7=>'Chủ Nhật'][now()->dayOfWeekIso])
<div class="pagehead">
  <div><h1>Xin chào, {{ auth()->user()->name }} 👋</h1>
    <p>{{ $wd }}, {{ now()->format('d/m/Y') }} — Hôm nay có {{ $todayClasses->count() }} buổi học</p></div>
  <a class="btn primary" href="{{ route('teacher.attendance') }}">+ Điểm danh nhanh</a>
</div>

<div class="cards">
  <div class="card"><div class="lbl">Lớp đang dạy</div><div class="val">{{ $classesActive }}</div><div class="sub">{{ $studentsCount }} học sinh</div></div>
  <div class="card"><div class="lbl">Buổi hôm nay</div><div class="val">{{ $todayClasses->count() }}</div><div class="sub">{{ $notDoneToday }} chưa điểm danh</div></div>
  <div class="card"><div class="lbl">Doanh thu tháng {{ now()->month }}</div><div class="val green">{{ Money::short($revenueMonth) }}</div><div class="sub">Tổng tiền đã tính theo buổi</div></div>
  <div class="card"><div class="lbl">Đang nợ học phí</div><div class="val red">{{ Money::short($debtTotal) }}</div><div class="sub">{{ $debtorCount }} học sinh</div></div>
</div>

<div class="panel">
  <div class="ph"><h3>Buổi học hôm nay</h3><a class="btn ghost sm" href="{{ route('teacher.classes') }}">Xem tất cả lớp</a></div>
  <div class="pb">
    <table>
      <thead><tr><th>Giờ</th><th>Lớp</th><th>Sĩ số</th><th>Trạng thái</th><th></th></tr></thead>
      <tbody>
        @forelse ($todayClasses as $row)
          <tr>
            <td><b>{{ \Illuminate\Support\Carbon::parse($row->start)->format('H:i') }}</b><div class="r">– {{ \Illuminate\Support\Carbon::parse($row->end)->format('H:i') }}</div></td>
            <td>{{ $row->class->name }}</td>
            <td>{{ $row->count }} học sinh</td>
            <td>
              @if ($row->done)<span class="chip g">Đã điểm danh</span>
              @else<span class="chip a">Chưa điểm danh</span>@endif
            </td>
            <td style="text-align:right">
              <a class="btn {{ $row->done ? 'ghost' : 'primary' }} sm" href="{{ route('teacher.attendance', ['class_id' => $row->class->id]) }}">{{ $row->done ? 'Xem' : 'Điểm danh' }}</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="r" style="padding:18px 16px">Hôm nay không có buổi học nào theo lịch.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
