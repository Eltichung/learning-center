<div class="tablewrap">
<table>
  <thead><tr><th>Tên lớp</th><th>Loại</th><th>Khối</th><th>Lịch học</th><th>Khai giảng</th><th>Sĩ số</th><th>Trạng thái</th><th></th></tr></thead>
  <tbody>
    @forelse ($classes as $c)
      @php($cdata = [
        'id' => $c->id, 'name' => $c->name, 'type' => $c->type, 'grade' => $c->grade,
        'subject' => $c->subject, 'status' => $c->status,
        'start_date' => optional($c->start_date)->toDateString(),
        'schedules' => $c->schedules->sortBy([['weekday', 'asc'], ['start_time', 'asc']])->map(fn ($s) => [
          'weekday' => (int) $s->weekday,
          'start' => $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') : '17:30',
          'end' => $s->end_time ? \Illuminate\Support\Carbon::parse($s->end_time)->format('H:i') : '19:00',
        ])->values(),
        'locked' => (int) ($c->submitted_count ?? 0) > 0,
      ])
      <tr>
        <td><b>{{ $c->name }}</b></td>
        <td><span class="chip {{ $c->typeChip() }}">{{ $c->typeLabel() }}</span></td>
        <td>{{ $c->gradeLabel() }}</td>
        <td>{{ $c->scheduleLabel() }}</td>
        <td>{{ $c->start_date ? $c->start_date->format('d/m/Y') : '—' }}</td>
        <td>{{ $c->class_students_count }}</td>
        <td>
          <span class="chip {{ $c->statusChip() }}">{{ $c->statusLabel() }}</span>
          @if ($c->status === 'paused' && $c->ended_at)<div class="r" style="font-size:11px">KT: {{ $c->ended_at->format('d/m/Y') }}</div>@endif
        </td>
        <td style="text-align:right;white-space:nowrap">
          <div class="row-menu-wrap" style="position:relative;display:inline-flex;gap:6px;align-items:center;justify-content:flex-end">
            <a class="btn ghost sm" href="{{ route('teacher.attendance', ['class_id' => $c->id]) }}">Điểm danh</a>
            <a class="btn ghost sm" href="{{ route('teacher.class', $c->id) }}">Chi tiết</a>
            <button class="btn ghost sm kebab" type="button" aria-label="Thêm thao tác" onclick="toggleRowMenu(this)" style="padding:6px 8px">
              <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg>
            </button>
            <div class="row-menu">
              <button type="button" class="rmi" id="editbtn-{{ $c->id }}" onclick='closeRowMenus(); editClass(@json($cdata))'>Sửa lớp</button>
              <form method="POST" action="{{ route('teacher.classes.duplicate', ['id' => $c->id], false) }}"
                    data-confirm="Nhân bản lớp “{{ $c->name }}” (kèm lịch học và toàn bộ học sinh)?"
                    data-refetch="#classes-list">
                @csrf
                <button type="submit" class="rmi">Nhân bản</button>
              </form>
            </div>
          </div>
        </td>
      </tr>
    @empty
      <tr><td colspan="8" class="r" style="padding:18px 16px">Chưa có lớp nào.</td></tr>
    @endforelse
  </tbody>
</table>
</div>
@include('partials.pagination', ['paginator' => $classes])
