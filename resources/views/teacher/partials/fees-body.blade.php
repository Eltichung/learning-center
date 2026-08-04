@use('App\Support\Money')
<div class="cards" style="grid-template-columns:repeat(3,1fr)">
  <div class="card"><div class="lbl">Đã thu tháng này</div><div class="val green">{{ Money::short($collectedMonth) }}</div></div>
  <div class="card"><div class="lbl">Còn phải thu</div><div class="val red">{{ Money::short($outstanding) }}</div></div>
  <div class="card"><div class="lbl">Học sinh còn nợ</div><div class="val">{{ $debtorCount }}</div></div>
</div>

<div class="panel"><div class="ph"><h3>Danh sách học phí ({{ $rows->count() }})</h3></div><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Học sinh</th><th>Số buổi chưa đóng</th><th>Công nợ</th><th>Lần đóng gần nhất</th><th></th></tr></thead>
    <tbody>
      @forelse ($rows as $row)
        <tr>
          <td><div class="stud" style="width:100%"><div class="savatar">{{ $row->student->initials() }}</div>
            <div>
              <a href="{{ route('teacher.student', $row->student->id) }}" style="font-weight:700;color:var(--ink);text-decoration:none">{{ $row->student->full_name }}</a>
              <div class="r">{{ $row->student->student_code }}</div>
            </div>
            <span class="row-acts">
              <a class="icon-act" href="{{ route('teacher.student', $row->student->id) }}" data-tip="Chi tiết">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a class="icon-act" href="#" onclick='copyLookup(@json(route("parent.info", $row->student->student_code)), this); return false;' data-tip="Copy link tra cứu">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="14" height="14" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
              </a>
            </span></div></td>
          <td>{{ $row->paid ? '—' : $row->sessions . ' buổi' }}</td>
          <td>@if ($row->paid)<span class="chip g">Đã đóng</span>@else<span class="chip r">−{{ Money::vnd($row->balance) }}</span>@endif</td>
          <td class="r">{{ $row->lastPaid ? \Illuminate\Support\Carbon::parse($row->lastPaid)->format('d/m/Y') : '—' }}</td>
          <td style="text-align:right;white-space:nowrap">
            <button class="btn ghost sm" type="button" onclick="openMonthly({{ $row->student->id }})">Chi tiết</button>
            @unless ($row->paid)<button class="btn primary sm" type="button" onclick='payFor({{ $row->student->id }}, @json($row->student->full_name), {{ $row->balance }})'>Thu tiền</button>@endunless
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="r" style="padding:16px">Không có học sinh phù hợp bộ lọc.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div></div>
