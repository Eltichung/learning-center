@extends('layouts.base')

@section('body')
<div class="tdash">
  @include('partials.teacher-nav', ['active' => $active ?? ''])
  <div class="tmain">
    @yield('content')
  </div>
</div>
@endsection
