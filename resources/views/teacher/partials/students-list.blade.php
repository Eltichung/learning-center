@use('App\Support\Money')
<div class="tablewrap">
<table>
  <thead><tr><th>Học sinh</th><th>Lớp</th><th>SĐT phụ huynh</th><th>Mã tra cứu</th><th>Công nợ</th><th></th></tr></thead>
  <tbody>
    @forelse ($students as $row)
      <tr>
        <td>
          <div class="stud" style="width:100%"><div class="savatar">{{ $row->student->initials() }}</div>
            <div><b>{{ $row->student->full_name }}</b>@if ($row->student->status !== 'active')<span class="chip n" style="margin-left:6px;font-size:11px">Ngừng HĐ</span>@endif
              <div class="r">{{ $row->grade ? 'Lớp '.$row->grade : '—' }}{{ $row->student->school ? ' · '.$row->student->school : '' }}</div>
            </div>
            <span class="row-acts">
              <a class="icon-act" href="{{ route('teacher.student', $row->student->id) }}" data-tip="Chi tiết">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a class="icon-act" href="#" onclick='copyLookup(@json(route("parent.info", $row->student->student_code)), this); return false;' data-tip="Copy link tra cứu">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
              </a>
            </span>
          </div>
        </td>
        <td>{{ $row->classes->pluck('name')->join(', ') ?: '—' }}</td>
        <td>{{ $row->student->parent_phone ?: '—' }}</td>
        <td><span class="chip n">{{ $row->student->student_code }}</span></td>
        <td>
          @if ($row->balance > 0)<span class="chip r">−{{ Money::vnd($row->balance) }}</span>
          @else<span class="chip g">Đã đóng</span>@endif
        </td>
        <td style="text-align:right"><a class="btn ghost sm" href="{{ route('teacher.student', $row->student->id) }}">Chi tiết</a></td>
      </tr>
    @empty
      <tr><td colspan="6" class="r" style="padding:18px 16px">Không có học sinh phù hợp bộ lọc.</td></tr>
    @endforelse
  </tbody>
</table>
</div>
@include('partials.pagination', ['paginator' => $students])
