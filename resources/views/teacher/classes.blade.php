@extends('layouts.teacher')
@section('title','Lớp học — LớpThêm')

@section('content')
<div class="pagehead"><div><h1>Lớp học</h1><p>{{ $activeCount }} lớp đang hoạt động</p></div><button class="btn primary" type="button" onclick="newClass()">+ Tạo lớp mới</button></div>

<form class="filterbar" method="GET" action="{{ route('teacher.classes') }}">
  <select name="grade" onchange="this.form.submit()">
    <option value="">Tất cả khối</option>
    @for ($g = 1; $g <= 12; $g++)<option value="{{ $g }}" @selected($grade === $g)>Lớp {{ $g }}</option>@endfor
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên lớp...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($grade || $q !== '')<a class="btn ghost sm" href="{{ route('teacher.classes') }}">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Tên lớp</th><th>Loại</th><th>Khối</th><th>Lịch học</th><th>Khai giảng</th><th>Sĩ số</th><th>Trạng thái</th><th></th></tr></thead>
    <tbody>
      @forelse ($classes as $c)
        @php($cdata = [
          'id' => $c->id, 'name' => $c->name, 'type' => $c->type, 'grade' => $c->grade,
          'subject' => $c->subject, 'status' => $c->status,
          'start_date' => optional($c->start_date)->toDateString(),
          'schedules' => $c->schedules->sortBy('weekday')->map(fn ($s) => [
            'weekday' => (int) $s->weekday,
            'start' => $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') : '17:30',
            'end' => $s->end_time ? \Illuminate\Support\Carbon::parse($s->end_time)->format('H:i') : '19:00',
          ])->values(),
        ])
        <tr>
          <td><b>{{ $c->name }}</b></td>
          <td><span class="chip {{ $c->typeChip() }}">{{ $c->typeLabel() }}</span></td>
          <td>{{ $c->gradeLabel() }}</td>
          <td>{{ $c->scheduleLabel() }}</td>
          <td>{{ $c->start_date ? $c->start_date->format('d/m/Y') : '—' }}</td>
          <td>{{ $c->class_students_count }}</td>
          <td>
            <span class="chip {{ $c->statusChip() }}">{{ $c->statusLabel() }}</span>
            @if ($c->status === 'paused' && $c->ended_at)<div class="r" style="font-size:11px">KT: {{ $c->ended_at->format('d/m/Y') }}</div>@endif
          </td>
          <td style="text-align:right;white-space:nowrap">
            <a class="btn ghost sm" href="{{ route('teacher.class', $c->id) }}">Chi tiết</a>
            <a class="btn ghost sm" href="{{ route('teacher.attendance', ['class_id' => $c->id]) }}">Điểm danh</a>
            <button class="btn ghost sm" type="button" onclick='editClass(@json($cdata))'>Sửa</button>
          </td>
        </tr>
      @empty
        <tr><td colspan="8" class="r" style="padding:18px 16px">Chưa có lớp nào.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div></div>

@include('partials.class-modal')
@endsection
