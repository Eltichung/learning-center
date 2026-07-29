@extends('layouts.base')

@section('body')
<div class="tdash">
  @include('partials.teacher-nav', ['active' => $active ?? ''])
  <div class="navscrim" onclick="toggleSidebar(false)"></div>
  <div class="tmain">
    <button type="button" class="navtoggle" aria-label="Mở menu" onclick="toggleSidebar(true)"><span class="ic">☰</span> Menu</button>
    @if (session('ok'))<div class="flash ok">✓ {{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="flash err">⚠ {{ $errors->first() }}</div>@endif

    @auth
      @php($me = auth()->user())
      @if ($me && $me->role === 'owner')
        @php($daysLeft = $me->subscriptionDaysLeft())
        @php($active = $me->subscriptionActive())
        @if (! $active)
          <div class="plan-banner err">
            ⚠ <b>Gói của bạn đã hết hạn.</b> Bạn có thể xem dữ liệu nhưng không thể tạo lớp, thêm học sinh hay điểm danh.
            <a href="{{ route('billing.index') }}">→ Nâng cấp gói</a>
          </div>
        @elseif ($daysLeft !== null && $daysLeft <= 3)
          <div class="plan-banner warn">
            🔔 Gói <b>{{ $me->currentPlan()->name }}</b> sẽ hết hạn sau <b>{{ max($daysLeft, 0) }} ngày</b>.
            <a href="{{ route('billing.index') }}">→ Gia hạn ngay</a>
          </div>
        @endif
      @endif
    @endauth

    @yield('content')
  </div>
</div>
@endsection
