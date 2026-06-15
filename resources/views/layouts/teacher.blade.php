@extends('layouts.base')

@section('body')
<div class="tdash">
  @include('partials.teacher-nav', ['active' => $active ?? ''])
  <div class="tmain">
    @if (session('ok'))<div class="flash ok">✓ {{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="flash err">⚠ {{ $errors->first() }}</div>@endif
    @yield('content')
  </div>
</div>
@endsection
