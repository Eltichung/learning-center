@php($wdShort = [1=>'T2',2=>'T3',3=>'T4',4=>'T5',5=>'T6',6=>'T7',7=>'CN'])
@php($todayDate = now()->toDateString())
@php($ws = \Illuminate\Support\Carbon::parse($weekDates[1]))
<div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap">
  <a class="btn ghost sm" href="{{ route('teacher.dashboard', ['week' => $ws->copy()->subWeek()->toDateString()]) }}" data-refetch="#dash-tkb">◀</a>
  <b style="font-size:13px">Tuần {{ $ws->format('d/m') }} – {{ $ws->copy()->addDays(6)->format('d/m/Y') }}</b>
  <a class="btn ghost sm" href="{{ route('teacher.dashboard', ['week' => $ws->copy()->addWeek()->toDateString()]) }}" data-refetch="#dash-tkb">▶</a>
  @if (! $ws->equalTo(now()->startOfWeek()))
    <a class="btn ghost sm" href="{{ route('teacher.dashboard', ['week' => now()->startOfWeek()->toDateString()]) }}" data-refetch="#dash-tkb">Tuần này</a>
  @endif
</div>
<div class="tkb-wrap">
  <div class="tkb">
    @foreach (range(1,7) as $d)
      @php($dayDate = \Illuminate\Support\Carbon::parse($weekDates[$d]))
      <div class="tkb-col{{ $weekDates[$d] === $todayDate ? ' today' : '' }}">
        <div class="tkb-day">{{ $wdShort[$d] }} · {{ $dayDate->format('d/m') }}</div>
        @forelse ($weekSlots[$d] as $it)
          @php($isDone = $it->session && $it->session->attendance_submitted_at)
          @php($isOff = $it->type === 'off')
          <a class="tkb-slot {{ $isDone ? 'is-done' : '' }} {{ $isOff ? 'is-off' : '' }}" href="{{ route('teacher.class', $it->class_id) }}">
            <div class="t">
              <span>{{ \Illuminate\Support\Carbon::parse($it->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($it->end_time)->format('H:i') }}</span>
              @if ($isOff)
                <span class="tkb-ic x" title="Đã nghỉ">✕</span>
              @elseif ($isDone)
                <span class="tkb-ic v" title="Đã điểm danh">✓</span>
              @endif
            </div>
            <div class="c">
              <span class="c-name">{{ $it->classroom->name }}</span>
              @switch($it->type)
                @case('boost')<span class="tkb-chip p">Tăng cường</span>@break
                @case('makeup')<span class="tkb-chip b">Bù</span>@break
                @case('off')@if ($it->session && $it->session->off_kind === 'holiday')<span style="background:var(--amber-soft);color:var(--amber);font-size:10px;font-weight:700;padding:1px 6px;border-radius:6px">Nghỉ lễ</span>@endif @break
              @endswitch
            </div>
            <div class="s">{{ $it->classroom->class_students_count }} học sinh</div>
          </a>
        @empty
          <div class="tkb-empty">—</div>
        @endforelse
      </div>
    @endforeach
  </div>
</div>
