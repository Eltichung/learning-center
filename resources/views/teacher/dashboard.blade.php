@extends('layouts.teacher')
@section('title','Tổng quan — LớpThêm')
@use('App\Support\Money')

@section('content')
@php($wdMap = [1=>'Thứ Hai',2=>'Thứ Ba',3=>'Thứ Tư',4=>'Thứ Năm',5=>'Thứ Sáu',6=>'Thứ Bảy',7=>'Chủ Nhật'])
@php($wdShort = [1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'])
@php($todayWd = now()->dayOfWeekIso)
@php($wd = $wdMap[$todayWd])
<div class="pagehead">
{{--  <div><h1>Xin chào, {{ auth()->user()->name }} 👋</h1>--}}
    <div><h1>Xin chào, công chúa của anh 👋</h1>
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
        <div class="scrolllist">
        <table>
            <thead><tr><th>Giờ</th><th>Lớp</th><th>Sĩ số</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
            @forelse ($todayClasses as $row)
                <tr>
                    <td><b>{{ \Illuminate\Support\Carbon::parse($row->start)->format('H:i') }}</b><div class="r">– {{ \Illuminate\Support\Carbon::parse($row->end)->format('H:i') }}</div></td>
                    <td>{{ $row->class->name }}@if ($row->boost)<span class="chip p" style="margin-left:6px;font-size:11px">Tăng cường</span>@elseif ($row->makeup)<span class="chip b" style="margin-left:6px;font-size:11px">Bù</span>@endif</td>
                    <td>{{ $row->count }} học sinh</td>
                    <td>
                        @if ($row->off)<span class="chip r">Nghỉ</span>
                        @elseif ($row->done)<span class="chip g">Đã điểm danh</span>
                        @else<span class="chip a">Chưa điểm danh</span>@endif
                    </td>
                    <td style="text-align:right">
                        <a class="btn {{ $row->done || $row->off ? 'ghost' : 'primary' }} sm" href="{{ route('teacher.attendance', array_filter(['class_id' => $row->class->id, 'session_id' => $row->session_id])) }}">{{ $row->done || $row->off ? 'Xem' : 'Điểm danh' }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="r" style="padding:18px 16px">Hôm nay không có buổi học nào theo lịch.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>

@if ($missedAttendanceCount > 0 || $pendingMakeupCount > 0)
<div class="warn-cols">

@if ($missedAttendanceCount > 0)
<div class="panel" style="border-color:#e7b3b3">
  <div class="ph" style="background:#fdecec">
    <h3>⚠️ Buổi đã qua chưa điểm danh <span style="color:var(--red)">({{ $missedAttendanceCount }})</span></h3>
    <a class="btn ghost sm" href="{{ route('teacher.attendance') }}">Tới điểm danh</a>
  </div>
  <div class="pb">
    <div class="note" style="margin:0 0 6px">Các buổi này <b>chưa tính tiền vào doanh thu/công nợ</b>. Vào điểm danh để chốt tiền.</div>
    <div class="scrolllist">
    <table>
      <thead><tr><th>Ngày học</th><th>Lớp</th><th>Loại</th><th></th></tr></thead>
      <tbody>
        @foreach ($missedAttendance as $ms)
          @php($wk = \Illuminate\Support\Carbon::parse($ms->date)->startOfWeek()->toDateString())
          <tr>
            <td><b>{{ \Illuminate\Support\Carbon::parse($ms->date)->format('d/m/Y') }}</b></td>
            <td>{{ $ms->classroom->name }}</td>
            <td>{{ $ms->type === 'makeup' ? 'Học bù' : ($ms->type === 'boost' ? 'Tăng cường' : 'Buổi thường') }}</td>
            <td style="text-align:right">
              <a class="btn primary sm" href="{{ route('teacher.attendance', ['class_id' => $ms->class_id, 'week' => $wk, 'session_id' => $ms->id]) }}">Điểm danh ngay</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    </div>
  </div>
</div>
@endif

@if ($pendingMakeupCount > 0)
<div class="panel" style="border-color:#f0c9a8">
  <div class="ph" style="background:#fdf3ea">
    <h3>🔴 Buổi nghỉ chưa xếp lịch học bù <span style="color:var(--red)">({{ $pendingMakeupCount }})</span></h3>
    <a class="btn ghost sm" href="{{ route('teacher.attendance') }}">Tới điểm danh</a>
  </div>
  <div class="pb">
    <div class="scrolllist">
    <table>
      <thead><tr><th>Ngày nghỉ</th><th>Lớp</th><th>Lý do</th><th></th></tr></thead>
      <tbody>
        @foreach ($pendingMakeups as $off)
          @php($wk = \Illuminate\Support\Carbon::parse($off->date)->startOfWeek()->toDateString())
          <tr>
            <td><b>{{ \Illuminate\Support\Carbon::parse($off->date)->format('d/m/Y') }}</b></td>
            <td>{{ $off->classroom->name }}</td>
            <td class="r">{{ $off->note ?: '—' }}</td>
            <td style="text-align:right">
              <a class="btn primary sm" href="{{ route('teacher.attendance', ['class_id' => $off->class_id, 'week' => $wk, 'session_id' => $off->id]) }}">Xếp lịch bù</a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    </div>
  </div>
</div>
@endif

</div>
@endif

<div class="panel">
    <div class="ph"><h3>Thời khóa biểu tuần</h3><a class="btn ghost sm" href="{{ route('teacher.classes') }}">Quản lý lịch</a></div>
    <div class="pb">
        <div class="tkb-wrap">
            <div class="tkb">
                @foreach (range(1,7) as $d)
                    @php($dayDate = \Illuminate\Support\Carbon::parse($weekDates[$d]))
                    <div class="tkb-col{{ $d === $todayWd ? ' today' : '' }}">
                        <div class="tkb-day">{{ $wdShort[$d] }} · {{ $dayDate->format('d/m') }}</div>
                        @forelse ($weekSlots[$d] as $it)
                            @php($isDone = $it->session && $it->session->attendance_submitted_at)
                            @php($isOff = $it->type === 'off')
                            <a class="tkb-slot {{ $isDone ? 'is-done' : '' }} {{ $isOff ? 'is-off' : '' }}" href="{{ route('teacher.class', $it->class_id) }}">
                                <div class="t">
                                    <span>{{ \Illuminate\Support\Carbon::parse($it->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($it->end_time)->format('H:i') }}</span>
                                    @if ($isOff)
                                        <span class="tkb-ic x" title="Đã nghỉ">✕</span>
                                    @elseif ($isDone)
                                        <span class="tkb-ic v" title="Đã điểm danh">✓</span>
                                    @endif
                                </div>
                                <div class="c">
                                    <span class="c-name">{{ $it->classroom->name }}</span>
                                    @switch($it->type)
                                        @case('boost')<span class="tkb-chip p">Tăng cường</span>@break
                                        @case('makeup')<span class="tkb-chip b">Bù</span>@break
                                    @endswitch
                                </div>
                                <div class="s">{{ $it->classroom->class_students_count }} học sinh</div>
                            </a>
                        @empty
                            <div class="tkb-empty">—</div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .tkb-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
    .tkb{display:grid;grid-template-columns:repeat(7,minmax(130px,1fr));gap:8px;min-width:820px}
    .tkb-col{background:#fafbfc;border:1px solid var(--line);border-radius:10px;padding:8px}
    .tkb-col.today{background:#fff8e6;border-color:#f0c76e}
    .tkb-day{font-weight:600;font-size:12.5px;padding:2px 4px 8px;color:var(--muted);text-align:center;border-bottom:1px dashed var(--line);margin-bottom:8px}
    .tkb-col.today .tkb-day{color:var(--ink)}
    .tkb-slot{display:block;background:#fff;border:1px solid var(--line);border-radius:8px;padding:8px;margin-bottom:6px;font-size:12.5px;text-decoration:none;color:inherit;transition:.15s}
    .tkb-slot:hover{border-color:var(--primary);box-shadow:0 1px 4px rgba(0,0,0,.06)}
    .tkb-slot.is-done{background:#effcf3;border-color:#b7e6c7}
    .tkb-slot.is-off{background:#fdecec;border-color:#e7b3b3;opacity:.85}
    .tkb-chip{display:inline-block;padding:1px 6px;border-radius:999px;font-size:10.5px;font-weight:700;margin-left:4px;vertical-align:middle}
    .tkb-chip.b{background:#e3f2fd;color:#1565c0}
    .tkb-chip.p{background:#f3e5f5;color:#6a1b9a}
    .tkb-slot .t{font-weight:700;display:flex;align-items:center;justify-content:space-between;gap:6px}
    .tkb-slot .c{color:var(--ink);font-size:12px;margin-top:2px;display:flex;flex-wrap:wrap;align-items:center;gap:4px}
    .tkb-slot .c-name{flex:1 1 auto;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .tkb-slot .s{color:var(--muted);font-size:11px;margin-top:2px}
    .tkb-ic{display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;font-size:11px;font-weight:800;line-height:1;flex-shrink:0}
    .tkb-ic.v{background:var(--green,#2ea44f);color:#fff}
    .tkb-ic.x{background:var(--red,#d1242f);color:#fff}
    .tkb-empty{color:var(--muted);font-size:11.5px;text-align:center;padding:8px 4px}
</style>
@endsection
