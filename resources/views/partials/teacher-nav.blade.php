@php($active = $active ?? '')
<aside class="tside">
  <div class="brand"><span class="mark">L</span> LớpThêm</div>
  <div class="u">
    <div class="avatar">CL</div>
    <div><div class="nm">Cô Lan</div><div class="sb">Gói Pro</div></div>
  </div>
  <nav class="tnav">
    <a href="{{ route('teacher.dashboard') }}"  class="{{ $active==='dashboard'  ? 'on':'' }}"><span class="ic">🏠</span> Tổng quan</a>
    <a href="{{ route('teacher.classes') }}"    class="{{ $active==='classes'    ? 'on':'' }}"><span class="ic">📚</span> Lớp học</a>
    <a href="{{ route('teacher.students') }}"   class="{{ $active==='students'   ? 'on':'' }}"><span class="ic">🎓</span> Học sinh</a>
    <a href="{{ route('teacher.attendance') }}" class="{{ $active==='attendance' ? 'on':'' }}"><span class="ic">✅</span> Điểm danh</a>
    <a href="{{ route('teacher.fees') }}"       class="{{ $active==='fees'       ? 'on':'' }}"><span class="ic">💰</span> Học phí</a>
    <a href="{{ route('teacher.reports') }}"    class="{{ $active==='reports'    ? 'on':'' }}"><span class="ic">📊</span> Báo cáo</a>
  </nav>
</aside>
