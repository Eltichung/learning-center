@php($rn = request()->route()?->getName())
@php($active = match (true) {
    $rn === 'teacher.dashboard' => 'dashboard',
    in_array($rn, ['teacher.classes', 'teacher.class', 'teacher.classes.store']) => 'classes',
    in_array($rn, ['teacher.students', 'teacher.student', 'teacher.students.store']) => 'students',
    $rn === 'teacher.attendance' => 'attendance',
    $rn === 'teacher.fees' => 'fees',
    $rn === 'teacher.reports' => 'reports',
    in_array($rn, ['teacher.settings.qr', 'teacher.settings.qr.update']) => 'settings-qr',
    $rn === 'parent.search' => 'p-search',
    $rn === 'parent.info' => 'p-info',
    $rn === 'parent.history' => 'p-history',
    default => ($active ?? ''),
})
<aside class="tside">
  <div class="brand"><span class="mark">L</span> Lớp Tăng Lực
    <button type="button" class="navclose" aria-label="Đóng menu" onclick="toggleSidebar(false)">&times;</button>
  </div>

  @auth
    @php($me = auth()->user())
    @php($initials = \Illuminate\Support\Str::upper(collect(explode(' ', trim($me->name)))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('')))
    @php($planName = optional(optional($me->subscription)->plan)->name)
    <div class="u">
      <div class="avatar">{{ $initials }}</div>
      <div>
        <div class="nm">{{ $me->name }}</div>
        <div class="sb">{{ $planName ? 'Gói '.$planName : 'Gói Free' }}</div>
      </div>
    </div>
  @endauth

  <div class="group">👩‍🏫 Giáo viên · Desktop</div>
  <nav class="tnav">
    <a href="{{ route('teacher.dashboard') }}"  class="{{ $active==='dashboard'  ? 'on':'' }}"><span class="ic">🏠</span> Tổng quan (Hôm nay)</a>
    <a href="{{ route('teacher.classes') }}"    class="{{ $active==='classes'    ? 'on':'' }}"><span class="ic">📚</span> Danh sách lớp</a>
    <a href="{{ route('teacher.students') }}"   class="{{ $active==='students'   ? 'on':'' }}"><span class="ic">🎓</span> Danh sách học sinh</a>
    <a href="{{ route('teacher.attendance') }}" class="{{ $active==='attendance' ? 'on':'' }}"><span class="ic">✅</span> Điểm danh</a>
    <a href="{{ route('teacher.fees') }}"       class="{{ $active==='fees'       ? 'on':'' }}"><span class="ic">💰</span> Học phí &amp; công nợ</a>
    <a href="{{ route('teacher.reports') }}"    class="{{ $active==='reports'    ? 'on':'' }}"><span class="ic">📊</span> Báo cáo</a>
    <a href="{{ route('teacher.settings.qr') }}" class="{{ $active==='settings-qr' ? 'on':'' }}"><span class="ic">🏦</span> QR chuyển khoản</a>
  </nav>

  <div class="group">👨‍👩‍👧 Phụ huynh · Mobile</div>
  <nav class="tnav">
    <a href="{{ route('parent.search') }}"               class="{{ $active==='p-search'  ? 'on':'' }}"><span class="ic">🔍</span> Trang tra cứu</a>
{{--    <a href="{{ route('parent.info', 'an-toan9') }}"      class="{{ $active==='p-info'    ? 'on':'' }}"><span class="ic">📄</span> Thông tin học sinh</a>--}}
{{--    <a href="{{ route('parent.history', 'an-toan9') }}"   class="{{ $active==='p-history' ? 'on':'' }}"><span class="ic">🗓️</span> Lịch sử học (theo tuần)</a>--}}
  </nav>

  @auth
  <form method="POST" action="{{ route('teacher.logout', [], false) }}" class="logout-form" data-no-toast>
    @csrf
    <button type="submit" class="logout-btn"><span class="ic">⎋</span> Đăng xuất</button>
  </form>
  @endauth
</aside>
