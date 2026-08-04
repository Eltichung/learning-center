@use('App\Support\Money')
@use('App\Models\Classroom')
<div class="twocol">
  <div class="panel">
    <div class="ph"><h3>Học sinh trong lớp</h3><button class="btn ghost sm" type="button" onclick="openModal('m-addstudent')">+ Thêm</button></div>
    <div class="pb">
      <div class="tablewrap">
      <table>
        <thead><tr><th>Học sinh</th><th>Đơn giá/buổi</th><th>Công nợ lớp này</th></tr></thead>
        <tbody>
          @forelse ($students as $row)
            <tr onclick="location.href='{{ route('teacher.student', $row->student->id) }}'" style="cursor:pointer">
              <td><div class="stud" style="width:100%"><div class="savatar">{{ $row->student->initials() }}</div>
                <div>{{ $row->student->full_name }}</div>
                <span class="row-acts">
                  <a class="icon-act" href="{{ route('teacher.student', $row->student->id) }}" data-tip="Chi tiết" onclick="event.stopPropagation()">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  </a>
                  <a class="icon-act" href="#" onclick='event.stopPropagation(); copyLookup(@json(route("parent.info", $row->student->student_code)), this); return false;' data-tip="Copy link tra cứu">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                  </a>
                  <a class="icon-act" href="#" data-tip="Lịch sử sửa giá"
                     onclick='event.stopPropagation(); viewPriceHistory(@json($row->student->id), @json($row->student->full_name)); return false;'>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 7v5l3 2"/></svg>
                  </a>
                </span>
              </div></td>
              <td class="money">
                {{ Money::vnd($row->price) }}
                <a class="icon-act" href="#" data-tip="Sửa đơn giá"
                   onclick='event.stopPropagation(); editPrice(@json($row->student->id), @json($row->student->full_name), @json((int) $row->price)); return false;'>
                  <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </a>
              </td>
              <td>
                @if ($row->balanceClass > 0)<span class="chip r">−{{ Money::vnd($row->balanceClass) }}</span>
                @else<span class="chip g">Đã đóng</span>@endif
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="r" style="padding:16px">Lớp chưa có học sinh.</td></tr>
          @endforelse
        </tbody>
      </table>
      </div>
      <div class="r" style="padding:8px 16px 4px;line-height:1.5">💡 Công nợ tính <b>riêng cho lớp này</b>, không phải tổng các lớp. Tiền phụ huynh đóng được ưu tiên trừ vào <b>lớp ghi danh trước</b>, nên một học sinh có thể đã đủ ở lớp này nhưng còn nợ ở lớp khác (xem "Chi tiết" ở màn Học phí).</div>
    </div>
  </div>
  <div>
    <div class="panel"><div class="ph"><h3>Lịch học cố định</h3></div><div class="pb" style="padding:14px 16px">
      @forelse ($class->schedules->sortBy([['weekday', 'asc'], ['start_time', 'asc']]) as $sc)
        <div class="prow"><div>{{ Classroom::weekdayLabel((int) $sc->weekday) }}</div><div class="r">{{ \Illuminate\Support\Carbon::parse($sc->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($sc->end_time)->format('H:i') }}</div></div>
      @empty
        <div class="prow r">Chưa đặt lịch.</div>
      @endforelse
    </div></div>
    <div class="panel">
      <div class="ph"><h3>Buổi học · {{ $periodLabel }}</h3>
        <div style="display:flex;gap:6px">
          <a class="btn {{ $period==='week' ? 'primary' : 'ghost' }} sm" href="{{ route('teacher.class', ['id' => $class->id, 'period' => 'week']) }}" data-refetch="#class-detail-body">Tuần</a>
          <a class="btn {{ $period==='month' ? 'primary' : 'ghost' }} sm" href="{{ route('teacher.class', ['id' => $class->id, 'period' => 'month']) }}" data-refetch="#class-detail-body">Tháng</a>
        </div>
      </div>
      <div class="pb" style="padding:14px 16px">
        <div class="prow"><div>Đã dạy</div><b>{{ $taught }} buổi</b></div>
        <div class="prow"><div>🔴 Nghỉ</div><b>{{ $offs->count() }}</b></div>
        <div class="prow"><div>🔵 Học bù</div><b>{{ $makeups->count() }}</b></div>
      </div>
    </div>
  </div>
</div>

{{-- Danh sách buổi trong kỳ --}}
<div class="panel"><div class="ph"><h3>Các buổi trong {{ $periodLabel }}</h3></div><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Ngày</th><th>Giờ</th><th>Loại</th><th>Điểm danh</th></tr></thead>
    <tbody>
      @forelse ($sessions as $s)
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($s->date)->format('d/m/Y') }}</td>
          <td>{{ $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i') : '—' }}</td>
          <td>
            @switch($s->type)
              @case('off')<span class="chip r">Nghỉ</span>@break
              @case('makeup')<span class="chip b">Học bù</span>@break
              @case('boost')<span class="chip p">Tăng cường</span>@break
              @default<span class="chip n">Buổi thường</span>
            @endswitch
          </td>
          <td>
            @if ($s->type === 'off')<span class="r">—</span>
            @elseif ($s->attendance_submitted_at)<span class="chip g">✓ {{ \Illuminate\Support\Carbon::parse($s->attendance_submitted_at)->format('H:i d/m') }}</span>
            @else<span class="chip a">Chưa điểm danh</span>@endif
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="r" style="padding:16px">Không có buổi nào trong kỳ này.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div></div>
