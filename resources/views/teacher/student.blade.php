@extends('layouts.teacher')
@section('title','Hồ sơ học sinh — LớpThêm')
@use('App\Support\Money')

@section('content')
<div id="student-body" data-partial-url="{{ route('teacher.student.partial', ['id' => $student->id]) }}">
  @include('teacher.partials.student-body', compact('student','enrollments','balance','unpaidSessions','grade','primaryPrice','prefix','attendance','attSummary','comments','statusLogs'))
</div>

@include('partials.payment-modal', ['payRefetch' => '#student-body'])
@endsection
