@extends('layouts.teacher')
@section('title','Báo cáo — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead"><div><h1>Báo cáo học phí</h1><p>Tháng {{ $month->format('m/Y') }}{{ $classId ? ' · 1 lớp' : ' · tất cả lớp' }}</p></div></div>

<form class="filterbar" method="GET" action="{{ route('teacher.reports') }}" data-refetch="#reports-body">
  <label style="font-size:12.5px;color:var(--muted)">Tháng</label>
  <input type="month" name="month" value="{{ $monthStr }}" onchange="this.form.requestSubmit()">
  <label style="font-size:12.5px;color:var(--muted)">Lớp</label>
  <select name="class_id" onchange="this.form.requestSubmit()">
    <option value="">Tất cả lớp</option>
    @foreach ($classList as $c)<option value="{{ $c->id }}" @selected($classId === $c->id)>{{ $c->name }}</option>@endforeach
  </select>
  <button class="btn primary sm" type="submit">Lọc</button>
</form>

<div id="reports-body" data-partial-url="{{ route('teacher.reports.partial', request()->query()) }}">
  @include('teacher.partials.reports-body', compact('report','month','cardCharged','cardCollected','cardOwed'))
</div>
@endsection
