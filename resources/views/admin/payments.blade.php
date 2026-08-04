@extends('layouts.admin')
@section('title','Thanh toán — Quản trị')

@section('content')
<div class="pagehead">
  <div>
    <h1>💳 Thanh toán</h1>
    <p>{{ $orders->total() }} đơn @if ($pendingCount > 0) · <span style="color:var(--red)">{{ $pendingCount }} đang chờ duyệt</span> @endif</p>
  </div>
</div>

<form class="filterbar" method="GET" action="{{ route('admin.payments') }}" data-refetch="#admin-payments">
  <select name="status" onchange="this.form.requestSubmit()">
    <option value="pending" @selected($status === 'pending')>Chờ duyệt</option>
    <option value="approved" @selected($status === 'approved')>Đã duyệt</option>
    <option value="rejected" @selected($status === 'rejected')>Từ chối</option>
    <option value="cancelled" @selected($status === 'cancelled')>Đã huỷ</option>
  </select>
</form>

<div id="admin-payments" data-partial-url="{{ route('admin.payments', array_merge(request()->query(), ['partial' => 1])) }}">
  @include('admin.partials.payments-list', compact('orders','pendingCount'))
</div>

<script>
  function rejectPrompt(id){
    const reason = window.prompt('Lý do từ chối (có thể để trống):', '');
    if (reason === null) return;
    const f = document.getElementById('rej-'+id);
    f.querySelector('input[name=reason]').value = reason;
    f.submit();
  }
</script>
@endsection
