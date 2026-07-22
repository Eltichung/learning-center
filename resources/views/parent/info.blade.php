@extends('layouts.parent')
@section('title','Thông tin học sinh — LớpThêm')
@use('App\Support\Money')

@section('content')
@php($wdShort = [1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'])
@php($wdFull = [1=>'Thứ Hai',2=>'Thứ Ba',3=>'Thứ Tư',4=>'Thứ Năm',5=>'Thứ Sáu',6=>'Thứ Bảy',7=>'Chủ Nhật'])
<div class="ptop">
  <div class="small">{{ $className }} — {{ $teacherName }}</div>
  <h2>{{ $student->full_name }}</h2>
</div>
<div class="pbody">
  @php($qrUrl = optional($student->teacher)->qr_image_path ? asset('storage/'.$student->teacher->qr_image_path) : null)

  {{-- Tổng nợ --}}
  <div class="due-card">
    <div class="due-total">💰 Tổng học phí chưa đóng</div>
    @if ($balance > 0)
      <div class="amt">{{ Money::vnd($balance) }}</div>
      <div class="meta">{{ $unpaidSessions }} buổi chưa đóng × {{ Money::vnd($price) }}</div>
    @else
      <div class="amt">Đã đóng đủ</div>
      <div class="meta">Không còn công nợ. Cảm ơn quý phụ huynh!</div>
    @endif
    @if ($qrUrl)
      <button type="button" class="due-qr-btn" onclick="openTeacherQr()">Chuyển khoản qua QR</button>
    @endif
  </div>

  @if ($qrUrl)
    {{-- Modal QR --}}
    <div class="qr-modal" id="qr-modal" onclick="if(event.target===this) closeTeacherQr()">
      <div class="qr-modal-inner">
        <button type="button" class="qr-close" onclick="closeTeacherQr()" aria-label="Đóng">×</button>
        <div class="qr-modal-title">QR chuyển khoản</div>
        <div class="qr-modal-sub">{{ $teacherName }}</div>
        <img id="qr-img" src="{{ $qrUrl }}" alt="QR chuyển khoản">
        <div class="qr-modal-note">Quét bằng app ngân hàng để chuyển học phí.</div>
        <a class="btn primary qr-dl" href="{{ $qrUrl }}" download="qr-{{ \Illuminate\Support\Str::slug($teacherName) }}.png">⬇️ Tải xuống</a>
      </div>
    </div>
  @endif

  {{-- Lịch học cố định --}}
  <div class="pcard">
    <h4>📅 Lịch học cố định</h4>
    @forelse ($schedules as $sc)
      <div class="sched-day"><div class="day-pill">{{ $wdShort[$sc->weekday] }}</div><div class="day-info">{{ $wdFull[$sc->weekday] }}<div class="t">{{ $sc->start }} – {{ $sc->end }} · {{ $sc->class }}</div></div></div>
    @empty
      <div class="prow r">Chưa có lịch học.</div>
    @endforelse
  </div>

  {{-- Buổi học bù (lịch một lần, không thuộc lịch cố định) --}}
  @if ($makeups->isNotEmpty())
  <div class="pcard">
    <h4>🔵 Buổi học bù</h4>
    @foreach ($makeups as $mk)
      <div class="sched-day">
        <div class="day-pill" style="background:var(--blue-soft);color:var(--blue)">{{ $wdShort[$mk->date->dayOfWeekIso] }}</div>
        <div class="day-info">{{ $mk->date->format('d/m/Y') }} ({{ $wdFull[$mk->date->dayOfWeekIso] }})
          <div class="t">{{ $mk->start }} – {{ $mk->end }} · {{ $mk->class }}@if ($mk->forDate) · bù cho buổi {{ $mk->forDate->format('d/m') }}@endif</div>
        </div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- Giáo án tuần này --}}
  @if ($lessons->isNotEmpty())
  <div class="pcard">
    <h4>📚 Giáo án tuần này</h4>
    @foreach ($lessons as $ls)
      <div class="prow" style="align-items:center">
        <div style="flex:1;min-width:0">
          <div class="r" style="font-size:12px">
            {{ $wdFull[$ls->date->dayOfWeekIso] }} · {{ $ls->date->format('d/m/Y') }}
            @if ($ls->submitted)<span class="chip g" style="margin-left:4px;font-size:10px">✓ Đã dạy</span>@endif
          </div>
          <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $ls->title ?: '(chưa đặt tiêu đề)' }}</div>
        </div>
        <button type="button" class="btn ghost sm" onclick='openLesson(@json($ls->id))'>Xem chi tiết →</button>
      </div>
    @endforeach
  </div>

  {{-- Modal chi tiết bài học --}}
  <div class="lesson-modal" id="lesson-modal" onclick="if(event.target===this) closeLesson()">
    <div class="lesson-modal-inner">
      <button type="button" class="lesson-close" onclick="closeLesson()">×</button>
      <div class="r" id="lesson-date" style="font-size:12px"></div>
      <h3 id="lesson-title" style="margin:2px 0 12px"></h3>
      <div id="lesson-content" style="white-space:pre-line;line-height:1.6;font-size:14px"></div>
    </div>
  </div>
  @endif

  {{-- Nhận xét của giáo viên (3 mới nhất) --}}
  @if ($comments->isNotEmpty())
  <div class="pcard">
    <h4>📝 Nhận xét của giáo viên</h4>
    @foreach ($comments as $c)
      <div class="prow" style="display:block">
        <div class="r" style="margin-bottom:2px">{{ \Illuminate\Support\Carbon::parse($c->comment_date)->format('d/m/Y') }}</div>
        <div style="white-space:pre-line">{{ $c->body }}</div>
      </div>
    @endforeach
  </div>
  @endif

  {{-- Bật thông báo trước 1 tiếng --}}
  <div class="pcard" id="push-card" style="display:none">
    <div class="pcard-head">
      <h4>🔔 Nhắc trước giờ học</h4>
      <span id="push-state" class="r" style="font-size:12px"></span>
    </div>
    <p style="font-size:13px;color:var(--muted);margin:6px 0 10px;line-height:1.5">
      Nhận thông báo <b>2 tiếng trước</b> mỗi buổi học của con. Chỉ hoạt động sau khi
      <b>“Thêm vào Màn hình chính”</b>
    </p>
    <button type="button" class="btn primary" id="push-btn" onclick="typeof togglePush==='function' ? togglePush() : alert('JS chưa load xong. Reload lại trang.')" style="width:100%;padding:11px">Bật thông báo</button>
  </div>

  {{-- Tuần này --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🗓️ Tuần này</h4><a class="linklike" href="{{ route('parent.history', $slug) }}">Lịch sử →</a></div>
    <div class="weekgrid" id="thisweek-grid"></div>
    <div class="weeklegend">
      <span><i class="dot" style="background:var(--green)"></i>Có mặt</span>
      <span><i class="dot" style="background:var(--blue)"></i>Học bù</span>
      <span><i class="dot" style="background:var(--red)"></i>Nghỉ</span>
      <span><i class="dot" style="background:var(--amber)"></i>Sắp học</span>
    </div>
  </div>

  {{-- Đóng tiền gần đây --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🧾 Đóng tiền gần đây</h4><a class="linklike" href="{{ route('parent.history', $slug) }}">Tất cả →</a></div>
    @forelse ($payments->take(3) as $p)
      <div class="prow"><div>{{ \Illuminate\Support\Carbon::parse($p->paid_at)->format('d/m/Y') }}<div class="r">{{ $p->method === 'transfer' ? 'Chuyển khoản' : 'Tiền mặt' }}{{ $p->note ? ' · '.$p->note : '' }}</div></div><b>{{ Money::vnd($p->amount) }}</b></div>
    @empty
      <div class="prow r">Chưa có lần đóng tiền nào.</div>
    @endforelse
  </div>

  <div style="text-align:center;color:var(--muted);font-size:11px;padding:8px 0 20px">Cập nhật bởi {{ $teacherName }} ·</div>
</div>

@push('scripts')
<style>
  .due-qr-btn{margin-top:12px;width:100%;padding:11px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);color:#fff;font-size:13.5px;font-weight:600;border-radius:10px;cursor:pointer}
  .due-qr-btn:hover{background:rgba(255,255,255,.28)}
  .qr-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100;align-items:center;justify-content:center;padding:20px}
  .qr-modal.show{display:flex}
  .qr-modal-inner{background:#fff;border-radius:16px;max-width:340px;width:100%;padding:22px 20px;text-align:center;position:relative}
  .qr-close{position:absolute;top:8px;right:8px;background:transparent;border:0;font-size:26px;line-height:1;color:var(--muted);cursor:pointer;width:36px;height:36px;border-radius:8px}
  .qr-close:hover{background:#f5f6f8;color:var(--ink)}
  .qr-modal-title{font-size:16px;font-weight:700}
  .qr-modal-sub{font-size:13px;color:var(--muted);margin:2px 0 14px}
  .qr-modal-inner img{display:block;margin:0 auto;max-width:280px;width:100%;border:1px dashed var(--line);border-radius:12px;padding:8px;background:#fafbfc}
  .qr-modal-note{font-size:11.5px;color:var(--muted);margin:12px 0;line-height:1.5}
  .qr-dl{display:inline-block;width:100%;padding:12px;font-size:14px;text-decoration:none;text-align:center}

  .lesson-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:100;align-items:flex-start;justify-content:center;padding:20px;overflow-y:auto}
  .lesson-modal.show{display:flex}
  .lesson-modal-inner{background:#fff;border-radius:16px;max-width:480px;width:100%;padding:22px 20px;position:relative;margin-top:40px}
  .lesson-close{position:absolute;top:8px;right:8px;background:transparent;border:0;font-size:26px;line-height:1;color:var(--muted);cursor:pointer;width:36px;height:36px;border-radius:8px}
  .lesson-close:hover{background:#f5f6f8;color:var(--ink)}
</style>
<script>
  function openTeacherQr(){ document.getElementById('qr-modal').classList.add('show'); document.body.style.overflow='hidden'; }
  function closeTeacherQr(){ document.getElementById('qr-modal')?.classList.remove('show'); document.body.style.overflow=''; }

  window.LT_LESSONS = @json($lessons ?? []);
  function openLesson(id){
    var ls = (window.LT_LESSONS || []).find(function(x){ return x.id === id; });
    if(!ls) return;
    var WD = ['Chủ Nhật','Thứ Hai','Thứ Ba','Thứ Tư','Thứ Năm','Thứ Sáu','Thứ Bảy'];
    var d = ls.date ? new Date(ls.date) : null;
    document.getElementById('lesson-date').textContent = d ? (WD[d.getDay()] + ' · ' + d.toLocaleDateString('vi-VN')) : '';
    document.getElementById('lesson-title').textContent = ls.title || '';
    document.getElementById('lesson-content').textContent = ls.content || '';
    document.getElementById('lesson-modal').classList.add('show');
    document.body.style.overflow='hidden';
  }
  function closeLesson(){ document.getElementById('lesson-modal')?.classList.remove('show'); document.body.style.overflow=''; }
  document.addEventListener('keydown', e => { if(e.key==='Escape'){ closeTeacherQr(); closeLesson(); } });
</script>
<script>
  window.LT_WEEKS = @json($weeks);
  window.LT_PRICE_K = {{ (int) ($price / 1000) }};
  window.LT_WEEK_INDEX = {{ $weekIndex }};
</script>
<script src="{{ asset('js/parent-week.js') }}?v={{ filemtime(public_path('js/parent-week.js')) }}"></script>
<script>renderThisWeek();</script>

<script>
/* ===== Web Push subscribe / unsubscribe ===== */
(function(){
  var VAPID_PUBLIC = @json(config('webpush.public_key'));
  var URL_SUB = @json(route('parent.push.subscribe', $slug, false));
  var URL_UNSUB = @json(route('parent.push.unsubscribe', $slug, false));
  var CSRF = @json(csrf_token());

  var supported = ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);
  var card = document.getElementById('push-card');
  var btn = document.getElementById('push-btn');
  var stateEl = document.getElementById('push-state');
  if (!supported || !VAPID_PUBLIC) return;
  card.style.display = '';

  function urlB64ToUint8(b64){
    var pad = '='.repeat((4 - b64.length % 4) % 4);
    var s = (b64 + pad).replace(/-/g,'+').replace(/_/g,'/');
    var raw = atob(s), out = new Uint8Array(raw.length);
    for (var i=0;i<raw.length;i++) out[i] = raw.charCodeAt(i);
    return out;
  }
  async function post(url, body){
    return fetch(url, {
      method:'POST', credentials:'same-origin',
      headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
      body: JSON.stringify(body||{})
    });
  }
  function setState(sub){
    if (Notification.permission === 'denied') {
      btn.textContent = 'Bị chặn ở trình duyệt';
      btn.disabled = true;
      stateEl.textContent = 'Đã tắt';
      return;
    }
    if (sub) {
      btn.textContent = 'Tắt thông báo';
      btn.classList.remove('primary'); btn.classList.add('ghost');
      stateEl.textContent = 'Đang bật';
      stateEl.style.color = 'var(--green)';
    } else {
      btn.textContent = 'Bật thông báo';
      btn.classList.remove('ghost'); btn.classList.add('primary');
      stateEl.textContent = 'Đang tắt';
      stateEl.style.color = 'var(--muted)';
    }
  }
  async function currentSub(){
    var reg = await navigator.serviceWorker.ready;
    return reg.pushManager.getSubscription();
  }
  function notify(msg, type){
    if (window.toast) toast(msg, type);
    else alert(msg);
  }
  function withTimeout(promise, ms, label){
    return Promise.race([promise, new Promise(function(_, rej){ setTimeout(function(){ rej(new Error('Timeout '+ms+'ms tại: '+label)); }, ms); })]);
  }
  window.togglePush = async function(){
    notify('1/6 Đã bấm', 'success');
    try{
      btn.disabled = true;
      if (!('serviceWorker' in navigator)) { notify('Không hỗ trợ Service Worker', 'error'); return; }
      if (!('PushManager' in window))     { notify('Không hỗ trợ Push API', 'error'); return; }
      if (Notification.permission === 'denied') { notify('Đã bị chặn — mở Cài đặt Chrome > Site settings > Notifications > Allow', 'error'); return; }

      notify('2/6 Chờ SW ready…', 'success');
      var reg = await withTimeout(navigator.serviceWorker.ready, 5000, 'serviceWorker.ready');
      notify('3/6 SW OK, check sub…', 'success');
      var sub = await withTimeout(reg.pushManager.getSubscription(), 5000, 'getSubscription');

      if (sub) {
        notify('4/6 Đã có sub — huỷ đăng ký…', 'success');
        var endpoint = sub.endpoint;
        await sub.unsubscribe();
        await post(URL_UNSUB, {endpoint: endpoint});
        setState(null);
        notify('✓ Đã TẮT thông báo', 'success');
      } else {
        notify('4/6 Xin quyền…', 'success');
        var perm = await Notification.requestPermission();
        if (perm !== 'granted') { setState(null); notify('Bị từ chối: perm='+perm, 'error'); return; }
        notify('5/6 Subscribe với VAPID…', 'success');
        var newSub = await withTimeout(reg.pushManager.subscribe({userVisibleOnly:true, applicationServerKey: urlB64ToUint8(VAPID_PUBLIC)}), 15000, 'pushManager.subscribe');
        notify('6/6 Gửi lên server…', 'success');
        var json = newSub.toJSON();
        var res = await withTimeout(post(URL_SUB, {endpoint: json.endpoint, keys: json.keys}), 10000, 'POST subscribe');
        if (!res.ok) { await newSub.unsubscribe(); throw new Error('Server reject '+res.status); }
        setState(newSub);
        notify('✓ Đã BẬT thông báo!', 'success');
      }
    } catch(e){
      notify('❌ ' + (e && (e.message || e.name) || e), 'error');
    }
    finally { btn.disabled = false; }
  };
  navigator.serviceWorker.ready.then(function(){ return currentSub(); }).then(setState).catch(function(){});
})();
</script>
@endpush
@endsection
