<div class="modal-backdrop" id="m-class">
  <form class="modal" id="class-form" method="POST" action="{{ route('teacher.classes.store') }}">
    @csrf
    <input type="hidden" name="_method" id="cf-method" value="POST">
    <div class="mh"><h3 id="cf-title">Tạo lớp mới</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="field"><label>Tên lớp <span style="color:var(--red)">*</span></label><input id="cf-name" name="name" required placeholder="VD: Toán 9 — Nhóm A"></div>
      <div class="grid2">
        <div class="field"><label>Loại lớp <span style="color:var(--red)">*</span></label>
          <select id="cf-type" name="type" required><option value="group">Học thêm (nhóm)</option><option value="tutor_1on1">Gia sư 1-1</option></select></div>
        <div class="field"><label>Khối <span style="color:var(--red)">*</span></label>
          <select id="cf-grade" name="grade" required><option value="">— Chọn khối —</option>@for ($g = 1; $g <= 12; $g++)<option value="{{ $g }}">Lớp {{ $g }}</option>@endfor</select></div>
      </div>
      <div class="grid2">
        <div class="field"><label>Môn học <span style="color:var(--red)">*</span></label><input id="cf-subject" name="subject" required placeholder="VD: Toán"></div>
        <div class="field"><label>Ngày bắt đầu <span style="color:var(--red)">*</span></label><input type="date" id="cf-start-date" name="start_date" value="{{ now()->toDateString() }}" required></div>
      </div>
      <div class="field"><label>Trạng thái</label>
        <select id="cf-status" name="status"><option value="active">Hoạt động</option><option value="paused">Tạm dừng (deactive)</option></select></div>
      <div class="field"><label>Lịch học cố định — chọn ít nhất 1 thứ <span style="color:var(--red)">*</span></label>
        <div class="wdays">
          @foreach ([1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'] as $w => $l)
            <label><input type="checkbox" name="weekdays[]" value="{{ $w }}"> {{ $l }}</label>
          @endforeach
        </div>
      </div>
      <div class="grid2">
        <div class="field"><label>Giờ bắt đầu (24h)</label>
          <div class="timepick">
            <select id="cf-start-h" onchange="syncTime('cf-start')" aria-label="Giờ">@for ($h = 0; $h <= 23; $h++)<option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}h</option>@endfor</select>
            <span>:</span>
            <select id="cf-start-m" onchange="syncTime('cf-start')" aria-label="Phút">@for ($m = 0; $m < 60; $m += 5)<option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>@endfor</select>
          </div>
          <input type="hidden" name="start_time" id="cf-start" value="17:30">
        </div>
        <div class="field"><label>Giờ kết thúc (24h)</label>
          <div class="timepick">
            <select id="cf-end-h" onchange="syncTime('cf-end')" aria-label="Giờ">@for ($h = 0; $h <= 23; $h++)<option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}h</option>@endfor</select>
            <span>:</span>
            <select id="cf-end-m" onchange="syncTime('cf-end')" aria-label="Phút">@for ($m = 0; $m < 60; $m += 5)<option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>@endfor</select>
          </div>
          <input type="hidden" name="end_time" id="cf-end" value="19:00">
        </div>
      </div>
      <div id="cf-create-only">
        <div class="field"><label>Thêm học sinh vào lớp (tuỳ chọn)</label>
          <div class="ssel" data-url="{{ route('api.students.search') }}" id="class-ssel">
            <input class="ssel-input" placeholder="Gõ tên/mã để thêm..." autocomplete="off"><div class="ssel-list"></div>
          </div>
          <div id="class-students-chips" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:6px"></div>
        </div>
        <div class="field"><label>Đơn giá / buổi (VNĐ)</label>
          <input class="money-input" data-target="price_per_session" inputmode="numeric" placeholder="120.000">
          <input type="hidden" name="price_per_session" id="cf-price" value="120000">
        </div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary" id="cf-submit">Tạo lớp</button></div>
  </form>
</div>

<script>
(function(){
  var STORE = @json(route('teacher.classes.store'));
  var BASE  = @json(url('classes'));
  function moneyDisplay(){ var mi=document.querySelector('#m-class .money-input'); var h=document.getElementById('cf-price'); if(mi&&h) mi.value=window.fmtMoney(h.value||'0'); }

  // Gộp 2 select giờ:phút -> hidden input "HH:MM"
  window.syncTime = function(prefix){
    var h=document.getElementById(prefix+'-h').value, m=document.getElementById(prefix+'-m').value;
    document.getElementById(prefix).value = h+':'+m;
  };
  window.setTime = function(prefix, val){
    var p=String(val||'').split(':');
    var h=(p[0]||'17').padStart(2,'0'), m=(p[1]||'30').padStart(2,'0');
    var hs=document.getElementById(prefix+'-h'), ms=document.getElementById(prefix+'-m');
    if(hs) hs.value=h; if(ms) ms.value=m;
    document.getElementById(prefix).value=h+':'+m;
  };

  // Sửa lớp: cho đổi trạng thái + lịch học (thứ) + giờ bắt đầu/kết thúc; các field khác hiển thị nhưng disabled
  function setEditLock(lock){
    ['cf-name','cf-type','cf-grade','cf-subject','cf-start-date'].forEach(function(id){
      var el=document.getElementById(id); if(el) el.disabled=lock;
    });
  }

  window.newClass = function(){
    var f=document.getElementById('class-form'); f.reset();
    f.action=STORE; document.getElementById('cf-method').value='POST';
    document.getElementById('cf-title').textContent='Tạo lớp mới';
    document.getElementById('cf-submit').textContent='Tạo lớp';
    document.getElementById('cf-create-only').style.display='';
    document.getElementById('class-students-chips').innerHTML='';
    setTime('cf-start','17:30');
    setTime('cf-end','19:00');
    document.getElementById('cf-price').value='120000'; moneyDisplay();
    setEditLock(false);
    openModal('m-class');
  };
  window.editClass = function(d){
    var f=document.getElementById('class-form');
    f.action=BASE+'/'+d.id; document.getElementById('cf-method').value='PUT';
    document.getElementById('cf-title').textContent='Sửa lớp';
    document.getElementById('cf-submit').textContent='Lưu thay đổi';
    document.getElementById('cf-create-only').style.display='none';
    document.getElementById('cf-name').value=d.name||'';
    document.getElementById('cf-type').value=d.type||'group';
    document.getElementById('cf-grade').value=d.grade||'';
    document.getElementById('cf-subject').value=d.subject||'';
    document.getElementById('cf-status').value=(d.status==='paused'?'paused':'active');
    document.getElementById('cf-start-date').value=d.start_date||'';
    setTime('cf-start', d.start_time||'17:30');
    setTime('cf-end', d.end_time||'19:00');
    f.querySelectorAll('input[name="weekdays[]"]').forEach(function(cb){ cb.checked=(d.weekdays||[]).indexOf(parseInt(cb.value,10))>-1; });
    setEditLock(true); // khóa tên/loại/khối/môn/ngày bắt đầu; cho sửa trạng thái + lịch học + giờ
    openModal('m-class');
  };
  document.addEventListener('DOMContentLoaded', function(){
    moneyDisplay();
    setTime('cf-start','17:30'); setTime('cf-end','19:00');
    // Validate front-end: bắt buộc ít nhất 1 thứ
    var cf=document.getElementById('class-form');
    if(cf){ cf.addEventListener('submit', function(e){
      // Tạo lẫn sửa đều bắt buộc chọn ít nhất 1 thứ cho lịch học
      if(cf.querySelectorAll('input[name="weekdays[]"]:checked').length===0){
        e.preventDefault(); e.stopImmediatePropagation();
        alert('Vui lòng chọn ít nhất 1 thứ cho lịch học.');
      }
    }); }
    var ssel=document.getElementById('class-ssel');
    if(ssel){ ssel.addEventListener('ssel:select', function(e){
      var id=e.detail.id, label=String(e.detail.label), chips=document.getElementById('class-students-chips');
      if(chips.querySelector('[data-id="'+id+'"]')) return;
      var chip=document.createElement('span'); chip.className='chip n'; chip.dataset.id=id;
      chip.style.cssText='display:inline-flex;align-items:center;gap:6px';
      chip.innerHTML = label.replace(/</g,'&lt;') + '<input type="hidden" name="students[]" value="'+id+'"><a href="#" style="color:var(--red);text-decoration:none" onclick="this.parentNode.remove();return false">×</a>';
      chips.appendChild(chip);
      ssel.querySelector('.ssel-input').value='';
    }); }
  });
})();
</script>
