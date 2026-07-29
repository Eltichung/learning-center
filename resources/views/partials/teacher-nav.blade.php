@php($rn = request()->route()?->getName())
@php($active = match (true) {
    $rn === 'teacher.dashboard' => 'dashboard',
    in_array($rn, ['teacher.classes', 'teacher.class', 'teacher.classes.store']) => 'classes',
    in_array($rn, ['teacher.students', 'teacher.student', 'teacher.students.store']) => 'students',
    $rn === 'teacher.attendance' => 'attendance',
    $rn === 'teacher.fees' => 'fees',
    $rn === 'teacher.reports' => 'reports',
    in_array($rn, ['teacher.settings.qr', 'teacher.settings.qr.update']) => 'settings-qr',
    in_array($rn, ['teacher.lessons', 'teacher.lessons.save']) => 'lessons',
    $rn === 'parent.search' => 'p-search',
    $rn === 'parent.info' => 'p-info',
    $rn === 'parent.history' => 'p-history',
    default => ($active ?? ''),
})
<aside class="tside">
  <div class="brand"><span class="mark">L</span> Học Chưa?
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
        @if ($me->role === 'super_admin')
          <div class="sb">Quản trị hệ thống</div>
        @elseif ($me->email === 'ninhtrang@gmail.com')
          <div class="sb">Gói Yêu Anh</div>
        @elseif ($planName === 'Full VIP')
          <div class="sb" style="color:#c39bd3;font-weight:700">👑 Full VIP</div>
        @else
          <div class="sb">{{ $planName ? 'Gói '.$planName : 'Trial' }}</div>
        @endif
      </div>
    </div>
  @endauth

  @if (auth()->user()?->role === 'super_admin')
    @php($allTeachers = \App\Models\User::where('role', 'owner')->orderBy('name')->get(['id', 'name']))
    @php($viewingId = (int) (session('admin_teacher_id') ?: optional($allTeachers->first())->id))
    <div class="group">👁️ Đang xem dữ liệu GV</div>
    <select onchange="location.href='{{ route('admin.viewAs') }}?teacher='+this.value"
            style="width:100%;margin:0 0 8px;padding:8px 10px;border-radius:8px;border:1px solid #2a2e38;background:#1e212a;color:#fff;font-size:13px">
      @forelse ($allTeachers as $tt)
        <option value="{{ $tt->id }}" @selected($viewingId === $tt->id)>{{ $tt->name }}</option>
      @empty
        <option value="">— Chưa có GV —</option>
      @endforelse
    </select>
  @endif

  <div class="group">👩‍🏫 Giáo viên · Desktop</div>
  <nav class="tnav">
    <a href="{{ route('teacher.dashboard') }}"  class="{{ $active==='dashboard'  ? 'on':'' }}"><span class="ic">🏠</span> Tổng quan (Hôm nay)</a>
    <a href="{{ route('teacher.classes') }}"    class="{{ $active==='classes'    ? 'on':'' }}"><span class="ic">📚</span> Danh sách lớp</a>
    <a href="{{ route('teacher.students') }}"   class="{{ $active==='students'   ? 'on':'' }}"><span class="ic">🎓</span> Danh sách học sinh</a>
    <a href="{{ route('teacher.attendance') }}" class="{{ $active==='attendance' ? 'on':'' }}"><span class="ic">✅</span> Điểm danh</a>
    <a href="{{ route('teacher.lessons') }}"    class="{{ $active==='lessons'    ? 'on':'' }}"><span class="ic">📚</span> Giáo án</a>
    <a href="{{ route('teacher.fees') }}"       class="{{ $active==='fees'       ? 'on':'' }}"><span class="ic">💰</span> Học phí &amp; công nợ</a>
    <a href="{{ route('teacher.reports') }}"    class="{{ $active==='reports'    ? 'on':'' }}"><span class="ic">📊</span> Báo cáo</a>
    <a href="{{ route('teacher.settings.qr') }}" class="{{ $active==='settings-qr' ? 'on':'' }}"><span class="ic">🏦</span> QR chuyển khoản</a>
    <a href="{{ route('billing.index') }}" class="{{ \Illuminate\Support\Str::startsWith((string) $rn, 'billing.') ? 'on' : '' }}"><span class="ic">💳</span> Gói / Nâng cấp</a>
  </nav>

  <div class="group">👨‍👩‍👧 Phụ huynh · Mobile</div>
  <nav class="tnav">
    <a href="{{ route('parent.search') }}"               class="{{ $active==='p-search'  ? 'on':'' }}"><span class="ic">🔍</span> Trang tra cứu</a>
{{--    <a href="{{ route('parent.info', 'an-toan9') }}"      class="{{ $active==='p-info'    ? 'on':'' }}"><span class="ic">📄</span> Thông tin học sinh</a>--}}
{{--    <a href="{{ route('parent.history', 'an-toan9') }}"   class="{{ $active==='p-history' ? 'on':'' }}"><span class="ic">🗓️</span> Lịch sử học (theo tuần)</a>--}}
  </nav>

  @if (auth()->user()?->role === 'super_admin')
    <div class="group">🛡️ Quản trị</div>
    <nav class="tnav">
      <a href="{{ route('admin.dashboard') }}" class="{{ $rn === 'admin.dashboard' ? 'on' : '' }}"><span class="ic">📊</span> Tổng quan hệ thống</a>
      <a href="{{ route('admin.teachers') }}" class="{{ \Illuminate\Support\Str::startsWith((string) $rn, 'admin.teacher') ? 'on' : '' }}"><span class="ic">👨‍🏫</span> Quản lý giáo viên</a>
      <a href="{{ route('admin.payments') }}" class="{{ \Illuminate\Support\Str::startsWith((string) $rn, 'admin.payment') ? 'on' : '' }}"><span class="ic">💳</span> Thanh toán</a>
    </nav>
  @endif

  @auth
  <form method="POST" action="{{ route('teacher.logout', [], false) }}" class="logout-form" data-no-toast>
    @csrf
    <button type="submit" class="logout-btn"><span class="ic">⎋</span> Đăng xuất</button>
  </form>
  @endauth
</aside>
