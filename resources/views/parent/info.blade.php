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

  @if ($showFees ?? true)
  {{-- Học phí — nợ tháng hiện tại + chi tiết từng tháng --}}
  <div class="due-card {{ $feeTotalOwed > 0 ? 'has-due' : 'no-due' }}">
    <div class="due-total">Học phí</div>
    @if ($feeTotalOwed > 0)
      @if ($feeCurrentOwed > 0)
        {{-- Nợ ngay tháng hiện tại --}}
        <div class="fee-cur-lbl">⚠️ Nợ tháng này · {{ $feeCurrentShort }}</div>
        <div class="amt">{{ Money::vnd($feeCurrentOwed) }}</div>
        <div class="meta">Phát sinh {{ Money::vnd($feeCurrentCharged) }} · đã đóng {{ Money::vnd($feeCurrentPaid) }}</div>
      @else
        {{-- Tháng này đã đủ, chỉ còn nợ các tháng trước (không hiện số lớn, để note bên dưới lo) --}}
        <div class="fee-cur-ok">✅ Tháng {{ $feeCurrentShort }} đã đóng đủ</div>
      @endif

      <div class="fee-unpaid">
        <div class="fee-unpaid-h">Các tháng chưa đóng</div>
        @foreach ($feeUnpaidMonths as $um)
          <div class="fee-unpaid-row"><span>{{ $um->label }}@if ($um->isCurrent) <span class="r">(tháng này)</span>@endif</span><b>{{ Money::vnd($um->owed) }}</b></div>
        @endforeach
        @if ($feeUnpaidMonths->count() > 1)
          <div class="fee-unpaid-row total"><span>Tổng còn nợ</span><b>{{ Money::vnd($feeTotalOwed) }}</b></div>
        @endif
      </div>

    @else
      <div class="amt no-debt">Đã đóng đủ ✓</div>
      <div class="meta">Cảm ơn quý phụ huynh!</div>
    @endif

    <div class="fee-actions">
      @if ($qrUrl && $feeTotalOwed > 0)
        <button type="button" class="due-qr-btn" onclick="openTeacherQr()">💳 Chuyển khoản</button>
      @endif
      <button type="button" class="fee-detail-toggle" onclick="toggleFeeDetail(this)" aria-expanded="false">
        <span>📄 Chi tiết tháng</span><span class="caret">▾</span>
      </button>
    </div>
    <div class="fee-detail" id="fee-detail" hidden>
      <div id="fee-months" data-url="{{ route('parent.fees.months', $slug) }}">
        @include('parent.partials.fee-months', ['months' => $feeMonths])
      </div>
      @if ($feeHasMore)
        <button type="button" class="fee-more-btn" id="fee-more-btn" data-page="1">Xem thêm tháng cũ</button>
      @endif
    </div>
  </div>
  @endif

  {{-- Nhận xét của giáo viên (3 mới nhất) --}}
  @if ($comments->isNotEmpty())
  <div class="pcard">
    <h4>📝 Nhận xét của giáo viên</h4>
    @foreach ($comments as $c)
      <div class="prow" style="display:block">
        <div style="margin-bottom:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span class="r">{{ \Illuminate\Support\Carbon::parse($c->comment_date)->format('d/m/Y') }}</span>
          @if ($c->type)<span class="chip {{ $c->type->color }}">{{ $c->type->icon }} {{ $c->type->name }}</span>@endif
          @if ($c->ratingLabel())<span class="chip {{ $c->ratingChip() }}">{{ $c->ratingLabel() }}</span>@endif
        </div>
        <div style="white-space:pre-line">{{ $c->body }}</div>
      </div>
    @endforeach
  </div>
  @endif

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

  {{-- Tuần này --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🗓️ Tuần này</h4><a class="linklike" href="{{ route('parent.history', $slug) }}">Lịch sử →</a></div>
    <div class="weekgrid" id="thisweek-grid"></div>
    <div class="weeklegend-wrap">
      <button type="button" class="legend-toggle" onclick="toggleLegend(this)" aria-expanded="false"><span class="lbl">Xem chú thích màu</span><span class="caret">▾</span></button>
      <div class="weeklegend-grid">
        <div class="lg-box">
          <div class="lg-head" style="color:var(--green)"><span class="d" style="background:var(--green)"></span>Đã học</div>
          <div class="lg-item"><i class="lgi lgi-present">✓</i><span class="lg-t">Có mặt</span></div>
          <div class="lg-item"><i class="lgi lgi-makeup">↻</i><span class="lg-t">Học bù</span></div>
        </div>
        <div class="lg-box">
          <div class="lg-head" style="color:var(--red)"><span class="d" style="background:var(--red)"></span>Vắng</div>
          <div class="lg-item"><i class="lgi lgi-absent">✕</i><span class="lg-t">Không phép<span class="n">Tính tiền</span></span></div>
          <div class="lg-item"><i class="lgi lgi-excused">△</i><span class="lg-t">Có phép<span class="n">Được miễn</span></span></div>
        </div>
        <div class="lg-box">
          <div class="lg-head" style="color:var(--muted)"><span class="d" style="background:var(--muted)"></span>Không có buổi</div>
          <div class="lg-item"><i class="lgi lgi-off">–</i><span class="lg-t">Buổi nghỉ</span></div>
          <div class="lg-item"><i class="lgi lgi-holiday">⚑</i><span class="lg-t">Nghỉ lễ</span></div>
          <div class="lg-item muted"><i class="lgi lgi-none"></i><span class="lg-t">Không có lịch<span class="n">Ô mờ</span></span></div>
        </div>
        <div class="lg-box">
          <div class="lg-head" style="color:var(--blue)"><span class="d" style="background:var(--blue)"></span>Sắp tới</div>
          <div class="lg-item"><i class="lgi lgi-study">•</i><span class="lg-t">Sắp học<span class="n">Chưa diễn ra</span></span></div>
        </div>
      </div>
    </div>
  </div>

  @if ($showFees ?? true)
  {{-- Đóng tiền gần đây --}}
  <div class="pcard">
    <div class="pcard-head"><h4>🧾 Đóng tiền gần đây</h4><a class="linklike" href="{{ route('parent.history', $slug) }}">Tất cả →</a></div>
    @forelse ($payments->take(3) as $p)
      <div class="prow"><div>{{ \Illuminate\Support\Carbon::parse($p->paid_at)->format('d/m/Y') }}<div class="r">{{ $p->method === 'transfer' ? 'Chuyển khoản' : 'Tiền mặt' }}{{ $p->note ? ' · '.$p->note : '' }}</div></div><b>{{ Money::vnd($p->amount) }}</b></div>
    @empty
      <div class="prow r">Chưa có lần đóng tiền nào.</div>
    @endforelse
  </div>
  @endif

  <div style="text-align:center;color:var(--muted);font-size:11px;padding:8px 0 20px">Cập nhật bởi {{ $teacherName }} ·</div>
</div>

@push('scripts')
<style>
  .fee-actions{display:flex;gap:8px;margin-top:12px}
  .due-qr-btn{flex:1;padding:11px 10px;background:var(--brand);border:0;color:#fff;font-size:13px;font-weight:600;border-radius:10px;cursor:pointer;white-space:nowrap}
  .due-qr-btn:hover{background:var(--brand-ink)}
  .fee-actions .fee-detail-toggle{flex:1;margin-top:0;justify-content:center;gap:6px;padding:11px 8px;font-size:13px;white-space:nowrap}

  /* Học phí: nợ tháng hiện tại + chi tiết từng tháng */
  .fee-cur-lbl{font-size:12px;font-weight:600;color:var(--red);margin-top:4px}
  .fee-cur-ok{font-size:12.5px;font-weight:600;color:var(--green);margin-top:4px}
  .due-card .amt{color:var(--red)}
  .fee-unpaid{margin-top:12px;border-top:1px dashed #e7d3cc;padding-top:10px}
  .fee-unpaid-h{font-size:11.5px;color:var(--muted);font-weight:600;margin-bottom:5px}
  .fee-unpaid-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:3px 0}
  .fee-unpaid-row b{color:var(--red)}
  .fee-unpaid-row .r{font-size:11px;color:var(--muted)}
  .fee-unpaid-row.total{border-top:1px solid #f0e6e2;margin-top:5px;padding-top:7px;font-weight:600}
  .fee-unpaid-row.total b{font-size:15px}
  .fee-detail-toggle{margin-top:12px;width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;background:#fff;border:1px solid var(--line);border-radius:10px;font-size:13px;font-weight:600;color:var(--ink);cursor:pointer}
  .fee-detail-toggle:hover{border-color:var(--brand)}
  .fee-detail-toggle .caret{color:var(--muted);transition:transform .15s}
  .fee-detail{margin-top:10px}
  .fm-card{background:#fff;border:1px solid var(--line);border-radius:11px;padding:10px 12px;margin-bottom:8px}
  .fm-head{width:100%;display:flex;align-items:center;justify-content:space-between;gap:8px;background:none;border:0;padding:0;cursor:pointer;text-align:left}
  .fm-title{font-size:13.5px;font-weight:600;color:var(--ink)}
  .fm-cur{font-size:10.5px;font-weight:600;color:var(--brand-ink);background:var(--brand-soft);padding:1px 7px;border-radius:20px;margin-left:6px}
  .fm-right{display:flex;align-items:center;gap:8px}
  .fm-badge{font-size:11px;font-weight:600;padding:2px 9px;border-radius:20px;white-space:nowrap}
  .fm-badge.due{background:var(--red-soft);color:var(--red)}
  .fm-badge.ok{background:var(--green-soft);color:var(--green)}
  .fm-chev{color:var(--muted);transition:transform .15s}
  .fm-head[aria-expanded="true"] .fm-chev{transform:rotate(180deg)}
  .fm-sum{font-size:11.5px;color:var(--muted);margin-top:6px}
  .fm-sum b{color:var(--ink);font-weight:600}
  .fm-sum b.g{color:var(--green)}
  .fm-sum b.rd{color:var(--red)}
  .fm-body{margin-top:8px;border-top:1px solid #f0f1f4;padding-top:8px}
  .fm-sec-h{font-size:12px;font-weight:600;margin:6px 0 3px}
  .fm-sec-h.g{color:var(--green)}
  .fm-sec-h.rd{color:var(--red)}
  .fm-sess{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--ink);padding:4px 0}
  .fm-sess.r{color:var(--muted)}
  .fm-dot{width:7px;height:7px;border-radius:50%;flex:none}
  .fm-dot.g{background:var(--green)}
  .fm-dot.rd{background:var(--red)}
  .fm-dot.am{background:var(--amber)}
  .fm-tag{font-size:10px;font-weight:600;color:var(--blue);background:var(--blue-soft);padding:1px 6px;border-radius:20px}
  .fm-more-btn,.fee-more-btn{width:100%;padding:10px;background:#fff;border:1px solid var(--line);border-radius:10px;color:var(--brand);font-size:12.5px;font-weight:600;cursor:pointer}
  .fee-more-btn:hover{border-color:var(--brand)}
  .fm-empty{font-size:12.5px;color:var(--muted);text-align:center;padding:14px}
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

  /* Học phí: mở/đóng chi tiết + accordion từng tháng + load thêm (gọi server) */
  function toggleFeeDetail(btn){
    var d = document.getElementById('fee-detail');
    var open = d.hasAttribute('hidden');
    if(open) d.removeAttribute('hidden'); else d.setAttribute('hidden','');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    var c = btn.querySelector('.caret'); if(c) c.style.transform = open ? 'rotate(180deg)' : '';
  }
  function toggleFeeMonth(btn){
    var body = btn.parentNode.querySelector('.fm-body');
    if(!body) return;
    var open = body.hasAttribute('hidden');
    if(open) body.removeAttribute('hidden'); else body.setAttribute('hidden','');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  (function(){
    var btn = document.getElementById('fee-more-btn');
    if(!btn) return;
    btn.addEventListener('click', async function(){
      var wrap = document.getElementById('fee-months');
      var page = btn.dataset.page, old = btn.textContent;
      btn.disabled = true; btn.textContent = 'Đang tải…';
      try{
        var r = await fetch(wrap.dataset.url + '?page=' + page, {headers:{'X-Requested-With':'XMLHttpRequest'}});
        var j = await r.json();
        wrap.insertAdjacentHTML('beforeend', j.html);
        if(j.hasMore){ btn.dataset.page = String(parseInt(page,10)+1); btn.disabled = false; btn.textContent = old; }
        else { btn.remove(); }
      }catch(e){ btn.disabled = false; btn.textContent = old; }
    });
  })();
</script>
<script>
  window.LT_WEEKS = @json($weeks);
  window.LT_PRICE_K = {{ (int) ($price / 1000) }};
  window.LT_WEEK_INDEX = {{ $weekIndex }};
</script>
<script src="{{ asset('js/parent-week.js') }}?v={{ filemtime(public_path('js/parent-week.js')) }}"></script>
<script>renderThisWeek();</script>
@endpush
@endsection
