@extends('layouts.teacher')
@section('title','Giáo án — LớpThêm')
@php($navActive = 'lessons')
@php($base = route('teacher.lessons'))
@php($wdFull = [1=>'Thứ Hai',2=>'Thứ Ba',3=>'Thứ Tư',4=>'Thứ Năm',5=>'Thứ Sáu',6=>'Thứ Bảy',7=>'Chủ Nhật'])

@section('content')
<div class="pagehead">
  <div><h1>Giáo án</h1>
    <p>{{ $class?->name ?? 'Chưa có lớp' }} · Tuần {{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m/Y') }}</p>
  </div>
</div>

<div class="filterbar">
  <select onchange="location.href='{{ $base }}?week={{ $weekStart->toDateString() }}&class_id='+this.value">
    @foreach ($classList as $c)
      <option value="{{ $c->id }}" @selected($class && $c->id === $class->id)>{{ $c->name }}</option>
    @endforeach
  </select>
  <div class="weeknav" style="margin:0">
    <a class="btn ghost sm" href="{{ $base }}?class_id={{ $class?->id }}&week={{ $weekStart->copy()->subWeek()->toDateString() }}">◀</a>
    <span class="wlabel" style="padding:0 6px">{{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m') }}</span>
    <a class="btn ghost sm" href="{{ $base }}?class_id={{ $class?->id }}&week={{ $weekStart->copy()->addWeek()->toDateString() }}">▶</a>
  </div>
</div>

@if ($days->isEmpty())
  <div class="note">Lớp này chưa có lịch học nào trong tuần đã chọn.</div>
@else
  <form method="POST" action="{{ route('teacher.lessons.save', [], false) }}">
    @csrf
    <input type="hidden" name="class_id" value="{{ $class->id }}">
    <input type="hidden" name="week" value="{{ $weekStart->toDateString() }}">

    <div class="lesson-grid">
      @foreach ($days as $i => $d)
        <div class="lesson-card">
          <div class="lesson-head">
            <div>
              <b>{{ $wdFull[$d->date->dayOfWeekIso] }}</b>
              <span class="r" style="font-size:12px;margin-left:6px">{{ $d->date->format('d/m/Y') }}</span>
              @if ($d->submitted)<span class="chip g" style="margin-left:8px">✓ Đã dạy</span>@endif
             @if ($d->type === 'off')<span class="chip r" style="margin-left:6px">Nghỉ</span>@endif
              @if ($d->type === 'makeup')<span class="chip b" style="margin-left:6px">Bù</span>@endif
            </div>
          </div>
          <input type="hidden" name="lessons[{{ $i }}][date]" value="{{ $d->date->toDateString() }}">
          <div class="field">
            <textarea name="lessons[{{ $i }}][title]" rows="6" maxlength="100" placeholder="Tiêu đề bài học (tối đa 100 kí tự)">{{ $d->title }}</textarea>
          </div>
          <div class="field" style="margin-bottom:0">
            <textarea name="lessons[{{ $i }}][content]" rows="12" maxlength="5000" placeholder="Chi tiết bài học...">{{ $d->content }}</textarea>
          </div>
        </div>
      @endforeach
    </div>

    <div style="margin-top:14px;display:flex;justify-content:flex-end">
      <button type="submit" class="btn primary">💾 Lưu giáo án tuần</button>
    </div>
  </form>
@endif

@push('scripts')
<style>
  .lesson-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
  .lesson-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px}
  .lesson-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:8px}
</style>
@endpush
@endsection
