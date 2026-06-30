@extends('layouts.teacher')
@section('title','Điểm danh — LớpThêm')
@use('App\Support\Money')

@section('content')
@php($base = route('teacher.attendance'))
<div class="pagehead">
  <div><h1>Điểm danh</h1>
    <p>{{ $class?->name ?? 'Chưa có lớp' }} · {{ $weekLabel }}
      @if ($session && $session->attendance_submitted_at)
        · <span style="color:var(--green)">đã điểm danh {{ \Illuminate\Support\Carbon::parse($session->attendance_submitted_at)->format('H:i d/m/Y') }}</span>
      @endif
    </p>
  </div>
  @if ($session && $session->type !== 'off')
    @if ($session->attendance_submitted_at)
      <span class="r" style="font-size:12.5px;color:var(--muted)" title="Bỏ điểm danh trước nếu muốn báo nghỉ">Đã điểm danh — không thể báo nghỉ</span>
    @else
      <button type="button" class="btn ghost" onclick="openOffModal('{{ \Illuminate\Support\Carbon::parse($session->date)->format('d/m/Y') }}')">🔴 Báo cả lớp nghỉ</button>
    @endif
  @endif
</div>

<div class="filterbar">
  <select onchange="location.href='{{ $base }}?week={{ $weekStart->toDateString() }}&class_id='+this.value">
    @foreach ($classList as $c)
      <option value="{{ $c->id }}" @selected($class && $c->id === $class->id)>{{ $c->name }}</option>
    @endforeach
  </select>
  <div class="weeknav" style="margin:0">
    <a class="btn ghost sm" href="{{ $base }}?class_id={{ $class?->id }}&week={{ $weekStart->copy()->subWeek()->toDateString() }}">◀</a>
    <span class="wlabel" style="padding:0 6px">{{ $weekStart->format('d/m') }} – {{ $weekEnd->format('d/m') }}</span>
    <a class="btn ghost sm" href="{{ $base }}?class_id={{ $class?->id }}&week={{ $weekStart->copy()->addWeek()->toDateString() }}">▶</a>
  </div>
</div>

@if ($sessions->isEmpty())
  <div class="note">Tuần này lớp chưa có buổi học nào. Chọn tuần khác hoặc tạo buổi học.</div>
@else
  {{-- Tabs: các buổi trong tuần --}}
  <div class="tabs">
    @foreach ($sessions as $s)
      @php($offNoMakeup = $s->type === 'off' && (int) $s->makeups_count === 0)
      <a class="tab {{ $session && $s->id === $session->id ? 'on' : '' }} {{ $offNoMakeup ? 'pending-makeup' : '' }}"
         href="{{ $base }}?class_id={{ $class->id }}&week={{ $weekStart->toDateString() }}&session_id={{ $s->id }}"
         @if ($offNoMakeup) title="Buổi nghỉ chưa xếp lịch học bù" @endif>
        {{ \Illuminate\Support\Carbon::parse($s->date)->format('d/m') }}
        @if ($s->type === 'makeup') ( Bù ) @elseif ($s->type === 'off') ( Nghỉ ){!! $offNoMakeup ? ' ⚠' : '' !!} @endif
        @if ($s->attendance_submitted_at)<span class="dot-done">✓</span>@endif
      </a>
    @endforeach
  </div>

  @if ($session && $session->type !== 'off')
    <div class="note">💡 Mặc định tất cả <b>Có mặt</b>. Có mặt / học bù / <b>vắng không phép</b> đều tính <b>1 buổi</b> theo đơn giá. Chỉ <b>vắng có phép</b> được miễn. Sửa người vắng rồi Lưu.</div>

    <div class="att-cols">
      <div>
        <form id="att-form" method="POST" action="{{ route('teacher.attendance.submit', ['session' => $session->id], false) }}"
              data-confirm="Xác nhận lưu điểm danh buổi {{ \Illuminate\Support\Carbon::parse($session->date)->format('d/m/Y') }}?">
          @csrf
          <div class="panel"><div class="pb">
            <div class="tablewrap">
            <table class="attgrid" id="att-table">
              <thead><tr><th style="width:50%">Học sinh · Điểm danh</th><th style="width:120px">Đơn giá</th><th>Thành tiền</th></tr></thead>
              <tbody>
                @forelse ($rows as $row)
                  <tr data-price="{{ $row->price }}">
                    <td>
                      <div style="display:flex;align-items:center;gap:14px">
                        <div class="stud" style="flex:1;min-width:0"><div class="savatar">{{ $row->student->initials() }}</div>{{ $row->student->full_name }}</div>
                        <input type="hidden" name="status[{{ $row->student->id }}]" value="{{ $row->status }}">
                        <div class="seg" style="flex:0 0 auto">
                          <span data-val="present" class="p {{ $row->status==='present' ? 'on' : '' }}">Có mặt</span>
                          <span data-val="excused" class="e {{ $row->status==='excused' ? 'on' : '' }}">Vắng có phép</span>
                          <span data-val="absent"  class="a {{ $row->status==='absent'  ? 'on' : '' }}">Vắng không phép</span>
                        </div>
                      </div>
                    </td>
                    <td class="money">{{ Money::vnd($row->price) }}</td>
                    <td class="money thanhtien">{{ Money::vnd($row->price) }}</td>
                  </tr>
                @empty
                  <tr><td colspan="3" class="r" style="padding:16px">Lớp chưa có học sinh.</td></tr>
                @endforelse
              </tbody>
            </table>
            </div>
          </div></div>

          {{-- Nút submit ngay dưới bảng điểm danh --}}
          <div style="display:flex;justify-content:space-between;align-items:center">
              <button type="submit" class="btn primary">{{ $session->attendance_submitted_at ? 'Cập nhật điểm danh' : 'Lưu điểm danh' }}</button>
            <div style="font-size:13px;color:var(--muted)">Tổng buổi này: <b style="color:var(--ink)" id="att-total">{{ $rows->whereIn('status', \App\Models\StudentSession::BILLABLE)->count() }} buổi · {{ Money::vnd($total) }}</b></div>
          </div>
        </form>
      </div>

      {{-- Log lịch sử — ngang hàng với bảng điểm danh, mỗi submit ghi thêm 1 dòng --}}
      <div>
        <div class="panel"><div class="ph"><h3>Lịch sử điểm danh{{ $logs->count() ? ' (' . $logs->count() . ' lần)' : '' }}</h3></div>
          <div class="pb" style="padding:6px 14px">
            @forelse ($logs as $lg)
              <div class="prow">
                <div>{{ $lg->created_at->format('H:i · d/m/Y') }}
                  <div class="r">{{ $lg->present_count }} buổi · {{ \App\Support\Money::vnd($lg->total_amount) }}{{ $lg->user?->name ? ' · ' . $lg->user->name : '' }}</div>
                </div>
                @if ($lg->action === 'submit')<span class="chip g">Lần đầu</span>@else<span class="chip a">Cập nhật</span>@endif
              </div>
            @empty
              <div class="r" style="padding:10px 0">Chưa điểm danh lần nào.</div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  @elseif ($session && $session->type === 'off')
    <div class="note">🔴 Buổi này là <b>buổi nghỉ</b> — không điểm danh, không tính tiền.
      @if ($session->note) <br>Lý do: {{ $session->note }}@endif
    </div>
    @php($makeups = $session->makeups()->orderBy('date')->get())
    @if ($makeups->isNotEmpty())
      <div class="note">🔵 Buổi học bù:
        @foreach ($makeups as $mk)
          <a href="{{ $base }}?class_id={{ $class->id }}&week={{ \Illuminate\Support\Carbon::parse($mk->date)->startOfWeek()->toDateString() }}&session_id={{ $mk->id }}">{{ \Illuminate\Support\Carbon::parse($mk->date)->format('d/m/Y') }}</a>@if (! $loop->last), @endif
        @endforeach
      </div>
    @else
      {{-- Chưa có buổi bù: cho phép xếp lịch bù hoặc hoàn tác --}}
      <form method="POST" action="{{ route('teacher.attendance.makeup', ['session' => $session->id], false) }}"
            data-confirm="Xác nhận thêm buổi học bù?"
            style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin:10px 0">
        @csrf
        <label style="font-size:13px;color:var(--muted)">Xếp buổi học bù vào ngày:</label>
        <input type="date" name="makeup_date" required
               oninput="this.form.dataset.confirm = 'Xác nhận thêm buổi học bù' + (this.value ? ' vào ngày ' + this.value.split('-').reverse().join('/') : '') + '?'">
        <button type="submit" class="btn ghost sm">➕ Thêm buổi học bù</button>
      </form>

      <form method="POST" action="{{ route('teacher.attendance.unoff', ['session' => $session->id], false) }}"
            data-confirm="Hoàn tác buổi nghỉ {{ \Illuminate\Support\Carbon::parse($session->date)->format('d/m/Y') }} về buổi học bình thường?"
            style="margin-top:6px">
        @csrf
        <button type="submit" class="btn ghost">↩ Hoàn tác (chuyển về buổi học)</button>
      </form>
    @endif
  @endif
@endif

{{-- Modal: báo cả lớp nghỉ --}}
@if ($session)
<div class="modal-backdrop" id="m-off">
  <form class="modal" method="POST" action="{{ route('teacher.attendance.off', ['session' => $session->id], false) }}" style="width:460px">
    @csrf
    <div class="mh"><h3>Báo cả lớp nghỉ</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="note" style="margin-top:0">Buổi <b id="off-date"></b> sẽ được đánh dấu <b>nghỉ</b> — cả lớp không bị tính tiền buổi này.</div>
      <div class="field"><label>Lý do nghỉ (tuỳ chọn)</label>
        <input name="reason" placeholder="VD: Cô bận việc, nghỉ lễ..." autocomplete="off"></div>
      <div class="field"><label>Ngày học bù (tuỳ chọn)</label>
        <input type="date" name="makeup_date">
        <div class="r" style="font-size:12px;color:var(--muted);margin-top:4px">Để trống nếu chưa xếp được lịch bù. Buổi bù sẽ tính tiền như buổi học bình thường.</div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary">Xác nhận nghỉ</button></div>
  </form>
</div>
<script>
  function openOffModal(dateLabel){
    document.getElementById('off-date').textContent = dateLabel || '';
    openModal('m-off');
  }
</script>
@endif

@push('scripts')
<script src="{{ asset('js/attendance.js') }}?v={{ filemtime(public_path('js/attendance.js')) }}" defer></script>
@endpush
@endsection
