@extends('layouts.base')

@section('body')
<div class="tdash">
  @include('partials.teacher-nav', ['active' => $active ?? ''])
  <div class="navscrim" onclick="toggleSidebar(false)"></div>
  <div class="tmain">
    <button type="button" class="navtoggle" aria-label="Mở menu" onclick="toggleSidebar(true)"><span class="ic">☰</span> Menu</button>
    @if (session('ok'))<div class="flash ok">✓ {{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="flash err">⚠ {{ $errors->first() }}</div>@endif
    @yield('content')
  </div>
</div>
@endsection
