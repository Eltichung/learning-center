@use('App\Support\Money')
@php($wd = [1=>'Thứ 2',2=>'Thứ 3',3=>'Thứ 4',4=>'Thứ 5',5=>'Thứ 6',6=>'Thứ 7',7=>'CN'])
@forelse ($months as $m)
  <div class="fm-card">
    <button type="button" class="fm-head" onclick="toggleFeeMonth(this)" aria-expanded="false">
      <span class="fm-title">{{ $m->label }}@if ($m->isCurrent)<span class="fm-cur">tháng này</span>@endif</span>
      <span class="fm-right">
        @if ($m->owed > 0)<span class="fm-badge due">Nợ {{ Money::short($m->owed) }}</span>
        @else<span class="fm-badge ok">Đã đủ</span>@endif
        <span class="fm-chev">▾</span>
      </span>
    </button>
    <div class="fm-sum">Phát sinh <b>{{ Money::vnd($m->charged) }}</b> · Đã đóng <b class="g">{{ Money::vnd($m->paid) }}</b> · Còn nợ <b class="{{ $m->owed > 0 ? 'rd' : '' }}">{{ Money::vnd(max($m->owed, 0)) }}</b></div>
    <div class="fm-body" hidden>
      <div class="fm-sec-h g">✓ Đi học · {{ $m->attended->count() }} buổi</div>
      @forelse ($m->attended as $a)
        <div class="fm-sess"><span class="fm-dot g"></span>{{ $wd[$a->date->dayOfWeekIso] }}, {{ $a->date->format('d/m') }} · {{ $a->start }}@if ($a->end)–{{ $a->end }}@endif @if ($a->makeup)<span class="fm-tag">học bù</span>@endif</div>
      @empty
        <div class="fm-sess r">— Không có buổi đi học —</div>
      @endforelse
      @if ($m->absent->isNotEmpty())
        <div class="fm-sec-h rd">✕ Nghỉ · {{ $m->absent->count() }} buổi</div>
        @foreach ($m->absent as $ab)
          <div class="fm-sess"><span class="fm-dot {{ $ab->excused ? 'am' : 'rd' }}"></span>{{ $wd[$ab->date->dayOfWeekIso] }}, {{ $ab->date->format('d/m') }} · {{ $ab->start }} — {{ $ab->excused ? 'có phép (miễn)' : 'vắng không phép (tính tiền)' }}</div>
        @endforeach
      @endif
    </div>
  </div>
@empty
  <div class="fm-empty">Chưa có dữ liệu học phí.</div>
@endforelse
