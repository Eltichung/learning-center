<div class="modal-backdrop" id="m-class">
  <form class="modal" id="class-form" method="POST" action="{{ route('teacher.classes.store') }}" style="width:820px;max-width:100%">
    @csrf
    <input type="hidden" name="_method" id="cf-method" value="POST">
    <div class="mh"><h3 id="cf-title">Tạo lớp mới</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">

        {{-- CỘT TRÁI: thông tin lớp --}}
        <div style="flex:1 1 320px;min-width:0">
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

        {{-- CỘT PHẢI: lịch học cố định --}}
        <div style="flex:1 1 320px;min-width:0">
          <div class="field"><label>Lịch học cố định — chọn thứ, mỗi thứ đặt giờ riêng (24h) <span style="color:var(--red)">*</span></label>
            <div class="wdays">
              @foreach ([1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'] as $w => $l)
                <label><input type="checkbox" name="weekdays[]" value="{{ $w }}" id="wd-{{ $w }}" onchange="toggleDay({{ $w }})"> {{ $l }}</label>
              @endforeach
            </div>
            <div class="sched-times" style="margin-top:10px;display:flex;flex-wrap:wrap;gap:10px">
              @foreach ([1=>'Thứ Hai',2=>'Thứ Ba',3=>'Thứ Tư',4=>'Thứ Năm',5=>'Thứ Sáu',6=>'Thứ Bảy',7=>'Chủ Nhật'] as $w => $l)
                <div class="sched-trow" id="row-{{ $w }}" style="display:none;flex-direction:column;gap:5px;width:150px;flex:none;border:1px solid var(--line);border-radius:9px;padding:8px 9px">
                  <span style="font-size:12.5px;font-weight:600;color:var(--ink)">{{ $l }}</span>
                  <div style="display:flex;align-items:center;gap:5px">
                    <span style="font-size:11px;color:var(--muted);width:16px;flex:none">BĐ</span>
                    <div class="timepick" style="flex:1 1 0;min-width:0">
                      <select id="ts-h-{{ $w }}" onchange="syncDay('ts',{{ $w }})" aria-label="Giờ bắt đầu">@for ($h = 0; $h <= 23; $h++)<option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}h</option>@endfor</select>
                      <span>:</span>
                      <select id="ts-m-{{ $w }}" onchange="syncDay('ts',{{ $w }})" aria-label="Phút bắt đầu">@for ($m = 0; $m < 60; $m += 5)<option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>@endfor</select>
                    </div>
                  </div>
                  <div style="display:flex;align-items:center;gap:5px">
                    <span style="font-size:11px;color:var(--muted);width:16px;flex:none">KT</span>
                    <div class="timepick" style="flex:1 1 0;min-width:0">
                      <select id="te-h-{{ $w }}" onchange="syncDay('te',{{ $w }})" aria-label="Giờ kết thúc">@for ($h = 0; $h <= 23; $h++)<option value="{{ sprintf('%02d', $h) }}">{{ sprintf('%02d', $h) }}h</option>@endfor</select>
                      <span>:</span>
                      <select id="te-m-{{ $w }}" onchange="syncDay('te',{{ $w }})" aria-label="Phút kết thúc">@for ($m = 0; $m < 60; $m += 5)<option value="{{ sprintf('%02d', $m) }}">{{ sprintf('%02d', $m) }}</option>@endfor</select>
                    </div>
                  </div>
                  <input type="hidden" name="time_start[{{ $w }}]" id="ts-{{ $w }}" value="17:30" disabled>
                  <input type="hidden" name="time_end[{{ $w }}]" id="te-{{ $w }}" value="19:00" disabled>
                </div>
              @endforeach
            </div>
          </div>
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

  // Gộp 2 select giờ:phút của 1 thứ -> hidden input "HH:MM"
  window.syncDay = function(prefix, w){
    var h=document.getElementById(prefix+'-h-'+w).value, m=document.getElementById(prefix+'-m-'+w).value;
    document.getElementById(prefix+'-'+w).value = h+':'+m;
  };
  function setDayTime(prefix, w, val){
    var p=String(val||'').split(':');
    var h=(p[0]||'17').padStart(2,'0'), m=(p[1]||'30').padStart(2,'0');
    var hs=document.getElementById(prefix+'-h-'+w), ms=document.getElementById(prefix+'-m-'+w);
    if(hs) hs.value=h; if(ms) ms.value=m;
    document.getElementById(prefix+'-'+w).value=h+':'+m;
  }
  // Tích/bỏ thứ -> hiện/ẩn dòng giờ + bật/tắt hidden input để submit hay không
  window.toggleDay = function(w){
    var cb=document.getElementById('wd-'+w), row=document.getElementById('row-'+w);
    var on = cb && cb.checked;
    if(row) row.style.display = on ? 'flex' : 'none';
    var ts=document.getElementById('ts-'+w), te=document.getElementById('te-'+w);
    if(ts) ts.disabled=!on;
    if(te) te.disabled=!on;
  };
  function resetDays(){
    for(var w=1; w<=7; w++){
      var cb=document.getElementById('wd-'+w); if(cb) cb.checked=false;
      setDayTime('ts', w, '17:30'); setDayTime('te', w, '19:00');
      var row=document.getElementById('row-'+w); if(row) row.style.display='none';
      var ts=document.getElementById('ts-'+w), te=document.getElementById('te-'+w);
      if(ts) ts.disabled=true; if(te) te.disabled=true;
    }
  }
  function applyDays(schedules){
    resetDays();
    (schedules||[]).forEach(function(s){
      var w=parseInt(s.weekday,10);
      var cb=document.getElementById('wd-'+w); if(cb) cb.checked=true;
      setDayTime('ts', w, s.start||'17:30'); setDayTime('te', w, s.end||'19:00');
      var row=document.getElementById('row-'+w); if(row) row.style.display='flex';
      var ts=document.getElementById('ts-'+w), te=document.getElementById('te-'+w);
      if(ts) ts.disabled=false; if(te) te.disabled=false;
    });
  }

  // Sửa lớp: cho đổi trạng thái + lịch học (thứ/giờ); khoá tên/loại/khối/môn/ngày bắt đầu
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
    resetDays();
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
    applyDays(d.schedules);
    setEditLock(true);
    openModal('m-class');
  };
  document.addEventListener('DOMContentLoaded', function(){
    moneyDisplay();
    resetDays();
    // Validate front-end: bắt buộc ít nhất 1 thứ (cả tạo lẫn sửa)
    var cf=document.getElementById('class-form');
    if(cf){ cf.addEventListener('submit', function(e){
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
