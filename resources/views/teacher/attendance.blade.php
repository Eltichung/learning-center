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
      <a class="tab {{ $session && $s->id === $session->id ? 'on' : '' }}"
         href="{{ $base }}?class_id={{ $class->id }}&week={{ $weekStart->toDateString() }}&session_id={{ $s->id }}">
        {{ \Illuminate\Support\Carbon::parse($s->date)->format('d/m') }}
        @if ($s->type === 'makeup') (bù) @elseif ($s->type === 'off') (nghỉ) @endif
        @if ($s->attendance_submitted_at)<span class="dot-done">✓</span>@endif
      </a>
    @endforeach
  </div>

  @if ($session && $session->type !== 'off')
    <div class="note">💡 Mặc định tất cả <b>Có mặt</b>. Mỗi buổi có mặt/học bù tính <b>1 buổi</b> theo đơn giá học sinh. Sửa người vắng rồi Lưu.</div>

    <div class="att-cols">
      <div>
        <form id="att-form" method="POST" action="{{ route('teacher.attendance.submit', $session->id) }}"
              data-confirm="Xác nhận lưu điểm danh buổi {{ \Illuminate\Support\Carbon::parse($session->date)->format('d/m/Y') }}?">
          @csrf
          <div class="panel"><div class="pb">
            <div class="tablewrap">
            <table class="attgrid" id="att-table">
              <thead><tr><th style="width:360px">Học sinh · Điểm danh</th><th style="width:120px">Đơn giá</th><th>Thành tiền</th></tr></thead>
              <tbody>
                @forelse ($rows as $row)
                  <tr data-price="{{ $row->price }}">
                    <td>
                      <div style="display:flex;align-items:center;gap:14px">
                        <div class="stud" style="flex:1;min-width:0"><div class="savatar">{{ $row->student->initials() }}</div>{{ $row->student->full_name }}</div>
                        <input type="hidden" name="status[{{ $row->student->id }}]" value="{{ $row->status }}">
                        <div class="seg" style="flex:0 0 auto">
                          <span data-val="present" class="p {{ $row->status==='present' ? 'on' : '' }}">Có mặt</span>
                          <span data-val="excused" class="e {{ $row->status==='excused' ? 'on' : '' }}">Vắng phép</span>
                          <span data-val="absent"  class="a {{ $row->status==='absent'  ? 'on' : '' }}">Vắng</span>
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
            <div style="font-size:13px;color:var(--muted)">Tổng buổi này: <b style="color:var(--ink)" id="att-total">{{ $rows->whereIn('status', ['present','makeup'])->count() }} buổi · {{ Money::vnd($total) }}</b></div>
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
    <div class="note">Buổi này là <b>buổi nghỉ</b> — không điểm danh, không tính tiền.</div>
  @endif
@endif

@push('scripts')
<script src="{{ asset('js/attendance.js') }}"></script>
@endpush
@endsection
