@extends('layouts.teacher')
@section('title','Giáo án — LớpThêm')
@php($navActive = 'lessons')

@section('content')
<div class="pagehead">
  <div><h1>Giáo án</h1>
    <p>{{ $class?->name ?? 'Chưa có lớp' }} · Tuần {{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m/Y') }}</p>
  </div>
</div>

<div id="lessons-body" data-partial-url="{{ route('teacher.lessons.partial', request()->query()) }}">
  @include('teacher.partials.lessons-body', compact('classList','class','weekStart','weekEnd','days'))
</div>

@push('scripts')
<style>
  .lesson-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px}
  .lesson-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:14px}
  .lesson-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;gap:8px}
</style>
@endpush
@endsection
