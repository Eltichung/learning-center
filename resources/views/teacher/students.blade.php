@extends('layouts.teacher')
@section('title','Học sinh — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead"><div><h1>Học sinh</h1><p>{{ $students->total() }} học sinh</p></div><button class="btn primary" type="button" onclick="openModal('m-student')">+ Thêm học sinh</button></div>

<form class="filterbar" method="GET" action="{{ route('teacher.students') }}">
  <select name="class_id" onchange="this.form.submit()">
    <option value="">Tất cả lớp</option>
    @foreach ($classList as $c)<option value="{{ $c->id }}" @selected($classId === $c->id)>{{ $c->name }}</option>@endforeach
  </select>
  <select name="status" onchange="this.form.submit()">
    <option value="active" @selected($status === 'active')>Hoạt động</option>
    <option value="inactive" @selected($status === 'inactive')>Ngừng HĐ</option>
    <option value="" @selected($status === '')>Tất cả trạng thái</option>
  </select>
  <select name="pay_status" onchange="this.form.submit()">
    <option value="">Tất cả công nợ</option>
    <option value="unpaid" @selected($payStatus === 'unpaid')>Còn nợ</option>
    <option value="paid" @selected($payStatus === 'paid')>Đã đóng</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên / mã...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($classId || $status !== 'active' || $payStatus || $q !== '')<a class="btn ghost sm" href="{{ route('teacher.students') }}">Xoá lọc</a>@endif
</form>

<div class="panel"><div class="pb">
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
</div></div>

{{-- Popup thêm học sinh --}}
<div class="modal-backdrop" id="m-student">
  <form class="modal" method="POST" action="{{ route('teacher.students.store') }}">
    @csrf
    <div class="mh"><h3>Thêm học sinh</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb">
      <div class="field"><label>Họ tên <span style="color:var(--red)">*</span></label><input name="full_name" required placeholder="VD: Nguyễn Bảo An"></div>
      <div class="grid2">
        <div class="field"><label>Mã tra cứu (slug) <span style="color:var(--red)">*</span></label><input name="student_code" required placeholder="VD: an-toan9"></div>
        <div class="field"><label>SĐT phụ huynh <span style="color:var(--red)">*</span></label><input name="parent_phone" required placeholder="09xxxxxxxx"></div>
      </div>
      <div class="field"><label>Kênh liên lạc PH (Zalo / link Facebook)</label><input name="parent_contact" placeholder="VD: 0900xxx (Zalo) hoặc fb.com/..."></div>
      <div class="grid2">
        <div class="field"><label>Thêm vào lớp <span style="color:var(--red)">*</span></label>
          <select name="class_id" required onchange="fillClassPrice(this)"><option value="">— Chọn lớp —</option>@foreach ($classList as $c)<option value="{{ $c->id }}" data-price="{{ $c->default_price ?: 120000 }}">{{ $c->name }}</option>@endforeach</select></div>
        <div class="field"><label>Đơn giá / buổi (VNĐ) <span style="color:var(--red)">*</span></label>
          <input class="money-input" data-target="price_per_session" inputmode="numeric" placeholder="120.000" required>
          <input type="hidden" name="price_per_session" value="120000">
        </div>
      </div>
    </div>
    <div class="mf"><button type="button" class="btn ghost" onclick="closeModal(this)">Huỷ</button><button type="submit" class="btn primary">Thêm học sinh</button></div>
  </form>
</div>

@push('scripts')
<script>
// Copy link trang tra cứu của phụ huynh
function copyLookup(url, el){
  var done = function(){ toast('✓ Đã copy link tra cứu', 'success'); };
  var fail = function(){ window.prompt('Copy link tra cứu:', url); };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(done).catch(fail);
  } else {
    fail();
  }
}
// Chọn lớp -> tự fill đơn giá mặc định của lớp đó
function fillClassPrice(sel){
  var opt = sel.options[sel.selectedIndex];
  var p = opt ? opt.getAttribute('data-price') : null;
  if(!p) return;
  var form = sel.closest('form');
  var hidden = form.querySelector('input[type=hidden][name="price_per_session"]');
  var disp = form.querySelector('.money-input[data-target="price_per_session"]');
  if(hidden) hidden.value = p;
  if(disp) disp.value = window.fmtMoney ? window.fmtMoney(p) : p;
}
</script>
@endpush
@endsection
