@extends('layouts.teacher')
@section('title','Báo cáo — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead"><div><h1>Báo cáo học phí</h1><p>Tháng {{ $month->format('m/Y') }}{{ $classId ? ' · 1 lớp' : ' · tất cả lớp' }}</p></div></div>

<form class="filterbar" method="GET" action="{{ route('teacher.reports') }}">
  <label style="font-size:12.5px;color:var(--muted)">Tháng</label>
  <input type="month" name="month" value="{{ $monthStr }}" onchange="this.form.submit()">
  <label style="font-size:12.5px;color:var(--muted)">Lớp</label>
  <select name="class_id" onchange="this.form.submit()">
    <option value="">Tất cả lớp</option>
    @foreach ($classList as $c)<option value="{{ $c->id }}" @selected($classId === $c->id)>{{ $c->name }}</option>@endforeach
  </select>
  <button class="btn primary sm" type="submit">Lọc</button>
{{--  @if ($classId || $monthStr !== now()->format('Y-m'))<a class="btn ghost sm" href="{{ route('teacher.reports') }}">Tháng này</a>@endif--}}
</form>

<div class="cards" style="grid-template-columns:repeat(3,1fr)">
  <div class="card"><div class="lbl">Phát sinh tháng {{ $month->format('m') }}</div><div class="val">{{ Money::short($cardCharged) }}</div><div class="sub">Tiền học tính theo buổi</div></div>
  <div class="card"><div class="lbl">Đã thu tháng {{ $month->format('m') }}</div><div class="val green">{{ Money::short($cardCollected) }}</div></div>
  <div class="card"><div class="lbl">Đang nợ (hiện tại)</div><div class="val red">{{ Money::short($cardOwed) }}</div></div>
</div>

@forelse ($report as $r)
  <div class="panel">
    <div class="ph">
      <h3>{{ $r->class->name }} <span class="chip {{ $r->class->statusChip() }}" style="margin-left:6px">{{ $r->class->statusLabel() }}</span></h3>
      @if ($report->count() > 1)
        {{-- Chỉ hiện tổng riêng từng lớp khi xem nhiều lớp; lọc 1 lớp thì 3 thẻ trên đã đủ --}}
        <div style="font-size:12.5px;color:var(--muted)">Phát sinh tháng: <b style="color:var(--ink)">{{ Money::vnd($r->chargedMonth) }}</b> · Đang nợ: <b style="color:var(--red)">{{ Money::vnd($r->owed) }}</b></div>
      @endif
    </div>
    <div class="pb">
      <div class="tablewrap">
      <table>
        <thead><tr><th>Học sinh</th><th>Đơn giá/buổi</th><th>Số buổi T{{ $month->format('m') }}</th><th>Phát sinh T{{ $month->format('m') }}</th><th>Đã thu (tổng)</th><th>Đang nợ</th></tr></thead>
        <tbody>
          @forelse ($r->rows as $row)
            <tr>
              <td><div class="stud"><div class="savatar">{{ $row->student->initials() }}</div>
                <div>
                  <a href="{{ route('teacher.student', $row->student->id) }}" style="font-weight:600;color:var(--ink);text-decoration:none">{{ $row->student->full_name }}</a>
                  <div class="r"><a href="{{ route('teacher.student', $row->student->id) }}" style="color:var(--brand);text-decoration:none">Chi tiết →</a></div>
                </div></div></td>
              <td class="money">{{ Money::vnd($row->price) }}</td>
              <td>{{ $row->sessionsMonth }} buổi</td>
              <td class="money">{{ Money::vnd($row->chargedMonth) }}</td>
              <td class="money" style="color:var(--green)">{{ Money::vnd($row->paid) }}</td>
              <td>
                @if ($row->balance > 0)<span class="chip r">−{{ Money::vnd($row->balance) }}</span>
                @else<span class="chip g">Đã đóng</span>@endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="r" style="padding:16px">Lớp chưa có học sinh.</td></tr>
          @endforelse
        </tbody>
      </table>
      </div>
    </div>
  </div>
@empty
  <div class="panel"><div class="pb" style="padding:18px 16px" class="r">Không có lớp nào.</div></div>
@endforelse
@endsection
