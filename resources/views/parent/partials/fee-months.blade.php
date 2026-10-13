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
    <div class="fm-learned">Đã học: <b>{{ $m->attendedCount }} buổi</b>@if ($m->pricePerSession) ({{ Money::vnd($m->pricePerSession) }}/buổi)@endif</div>
    <div class="fm-body" hidden>
      @forelse ($m->sessions->reverse() as $s)
        @php($dot = $s->status === 'absent' ? 'rd' : ($s->status === 'excused' ? 'am' : 'g'))
        <div class="fm-sess">
          <span class="fm-dot {{ $dot }}"></span>
          <span class="fm-sess-main">@if ($s->num)<b>Buổi {{ $s->num }}</b> @endif<span class="fm-sess-date">{{ $wd[$s->date->dayOfWeekIso] }}, {{ $s->date->format('d/m') }}</span> · {{ $s->statusLabel }}@if ($s->isNew)<span class="fm-new">Mới</span>@endif</span>
          @if ($s->billable)<b class="fm-amt">+{{ Money::vnd($s->amount) }}</b>@else<span class="fm-amt mut">miễn</span>@endif
        </div>
      @empty
        <div class="fm-sess r">— Chưa có buổi học nào —</div>
      @endforelse
    </div>
  </div>
@empty
  <div class="fm-empty">Chưa có dữ liệu học phí.</div>
@endforelse
