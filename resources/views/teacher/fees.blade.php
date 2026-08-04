@extends('layouts.teacher')
@section('title','Học phí & công nợ — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead"><div><h1>Học phí & công nợ</h1><p>Tháng {{ now()->format('m/Y') }}</p></div><button class="btn primary" type="button" onclick="payNew()">+ Ghi nhận đóng tiền</button></div>

<form class="filterbar" method="GET" action="{{ route('teacher.fees') }}" data-refetch="#fees-body">
  <select name="class_id" onchange="this.form.requestSubmit()">
    <option value="">Tất cả lớp</option>
    @foreach ($classList as $c)<option value="{{ $c->id }}" @selected($classId === $c->id)>{{ $c->name }}</option>@endforeach
  </select>
  <select name="status" onchange="this.form.requestSubmit()">
    <option value="">Tất cả trạng thái</option>
    <option value="unpaid" @selected($status === 'unpaid')>Chưa đóng</option>
    <option value="paid" @selected($status === 'paid')>Đã đóng</option>
  </select>
  <input class="search-box" name="q" value="{{ $q }}" placeholder="Tên / mã...">
  <button class="btn primary sm" type="submit">Lọc</button>
  @if ($classId || $status || $q !== '')<a class="btn ghost sm" href="{{ route('teacher.fees') }}" data-refetch="#fees-body">Xoá lọc</a>@endif
</form>

<div id="fees-body" data-partial-url="{{ route('teacher.fees.partial', request()->query()) }}">
  @include('teacher.partials.fees-body', compact('collectedMonth','outstanding','debtorCount','rows'))
</div>

{{-- Popup chi tiết nợ theo tháng --}}
<div class="modal-backdrop" id="m-monthly">
  <div class="modal" style="width:840px;max-width:100%">
    <div class="mh"><h3 id="mm-title">Chi tiết học phí</h3><button type="button" class="x" onclick="closeModal(this)">&times;</button></div>
    <div class="mb" id="mm-body">Đang tải…</div>
  </div>
</div>

@include('partials.payment-modal', ['payRefetch' => '#fees-body'])

@push('scripts')
<script>
function copyLookup(url, el){
  var done = function(){ toast('✓ Đã copy link tra cứu', 'success'); };
  var fail = function(){ window.prompt('Copy link tra cứu:', url); };
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(done).catch(fail);
  } else {
    fail();
  }
}
function openMonthly(id){
  openModal('m-monthly');
  var body = document.getElementById('mm-body');
  body.innerHTML = 'Đang tải…';
  fetch('{{ url('/api/students') }}/'+id+'/monthly', {headers:{'X-Requested-With':'XMLHttpRequest'}})
    .then(function(r){ return r.json(); })
    .then(function(d){
      document.getElementById('mm-title').textContent = 'Chi tiết học phí — ' + d.name;
      var vnd = function(n){ return Number(n || 0).toLocaleString('vi-VN') + 'đ'; };
      var sectionHead = function(t){ return '<div style="font-size:12px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em;margin:0 0 6px">' + t + '</div>'; };

      var statBox = function(label, value, color){
        return '<div style="flex:1;min-width:130px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:12px 14px">'
          + '<div style="font-size:11.5px;color:var(--muted)">' + label + '</div>'
          + '<div style="font-size:19px;font-weight:800;margin-top:4px;' + (color ? 'color:' + color : '') + '">' + value + '</div></div>';
      };
      var head = '<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px">'
        + statBox('Tổng tiền đã học', vnd(d.totalCharged), '')
        + statBox('Tổng tiền đã đóng', vnd(d.totalPaid), 'var(--green)')
        + statBox('Tổng còn nợ', (d.balance > 0 ? vnd(d.balance) : 'Đã đóng đủ'), 'var(--red)')
        + '</div>'
        + (d.monthsBehind > 0 ? '<div class="note" style="margin-bottom:14px">⚠ Chậm khoảng ' + d.monthsBehind + ' tháng</div>' : '');

      // Cột trái: các lớp đang học — đã học + đã thu (ưu tiên lớp đầu)
      var cls = (d.classes || []).map(function(c){
        return '<div class="prow"><div>' + c.name
          + '<div class="r">' + c.sessionsMonth + ' buổi · ' + vnd(c.price) + '/buổi</div></div>'
          + '<b>' + vnd(c.chargedMonth) + '</b></div>';
      }).join('');
      var classCol = sectionHead('Các lớp đang học (Tháng ' + d.month + ')') + cls;

      // Cột phải: học phí theo tháng
      var months = (d.months || []).map(function(m){
        var right = m.owed > 0
          ? '<span class="chip r">−' + vnd(m.owed) + '</span>'
          : (m.charged > 0 ? '<span class="chip g">đủ</span>' : '<span class="chip n">—</span>');
        return '<div class="prow"><div>' + m.label
          + '<div class="r">Phát sinh ' + vnd(m.charged) + ' · Đã đóng ' + vnd(m.paid) + '</div></div>'
          + right + '</div>';
      }).join('');
      var monthCol = sectionHead('Học phí theo tháng') + months;

      body.innerHTML = head + '<div class="mm-cols"><div>' + classCol + '</div><div>' + monthCol + '</div></div>';
    })
    .catch(function(){ body.innerHTML = '<div class="r">Lỗi tải dữ liệu.</div>'; });
}
</script>
@endpush
@endsection
