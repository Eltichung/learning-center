@extends('layouts.base')

@section('body')
<div class="tdash">
  @include('partials.teacher-nav', ['active' => $navActive ?? ''])
  <main class="stage">
    <div class="stage-bar">
      <h2>{{ $stageTitle ?? 'Phụ huynh' }}</h2>
      <span class="tag mob">Mobile</span>
    </div>
    <div class="stage-body">
      <div class="phone-stage">
        <div class="phone">
          <div class="notch"></div>
          <div class="pscreen">
            @yield('content')
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
@endsection
