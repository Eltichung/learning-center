@php($active = $active ?? '')
<aside class="tside">
  <div class="brand"><span class="mark">L</span> LớpThêm</div>
  <div class="subtitle">Prototype giao diện — bấm để xem từng màn.</div>

  <div class="u">
    <div class="avatar">CL</div>
    <div><div class="nm">Cô Lan</div><div class="sb">Gói Pro</div></div>
  </div>

  <div class="group">👩‍🏫 Giáo viên · Desktop</div>
  <nav class="tnav">
    <a href="{{ route('teacher.login') }}"      class="{{ $active==='login'      ? 'on':'' }}"><span class="ic">🔑</span> Đăng nhập</a>
    <a href="{{ route('teacher.dashboard') }}"  class="{{ $active==='dashboard'  ? 'on':'' }}"><span class="ic">🏠</span> Tổng quan (Hôm nay)</a>
    <a href="{{ route('teacher.classes') }}"    class="{{ $active==='classes'    ? 'on':'' }}"><span class="ic">📚</span> Danh sách lớp</a>
    <a href="{{ route('teacher.class', 1) }}"   class="{{ $active==='class'      ? 'on':'' }}"><span class="ic">📘</span> Chi tiết lớp</a>
    <a href="{{ route('teacher.students') }}"   class="{{ $active==='students'   ? 'on':'' }}"><span class="ic">🎓</span> Danh sách học sinh</a>
    <a href="{{ route('teacher.student', 1) }}" class="{{ $active==='student'    ? 'on':'' }}"><span class="ic">👤</span> Hồ sơ học sinh</a>
    <a href="{{ route('teacher.attendance') }}" class="{{ $active==='attendance' ? 'on':'' }}"><span class="ic">✅</span> Điểm danh</a>
    <a href="{{ route('teacher.fees') }}"       class="{{ $active==='fees'       ? 'on':'' }}"><span class="ic">💰</span> Học phí &amp; công nợ</a>
    <a href="{{ route('teacher.reports') }}"    class="{{ $active==='reports'    ? 'on':'' }}"><span class="ic">📊</span> Báo cáo</a>
  </nav>

  <div class="group">👨‍👩‍👧 Phụ huynh · Mobile</div>
  <nav class="tnav">
    <a href="{{ route('parent.search') }}"               class="{{ $active==='p-search'  ? 'on':'' }}"><span class="ic">🔍</span> Trang tra cứu</a>
    <a href="{{ route('parent.info', 'an-toan9') }}"      class="{{ $active==='p-info'    ? 'on':'' }}"><span class="ic">📄</span> Thông tin học sinh</a>
    <a href="{{ route('parent.history', 'an-toan9') }}"   class="{{ $active==='p-history' ? 'on':'' }}"><span class="ic">🗓️</span> Lịch sử học (theo tuần)</a>
  </nav>
</aside>
