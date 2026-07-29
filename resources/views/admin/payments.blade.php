@extends('layouts.admin')
@section('title','Thanh toán — Quản trị')

@section('content')
<div class="pagehead">
  <div>
    <h1>💳 Thanh toán</h1>
    <p>{{ $orders->total() }} đơn @if ($pendingCount > 0) · <span style="color:var(--red)">{{ $pendingCount }} đang chờ duyệt</span> @endif</p>
  </div>
</div>

<form class="filterbar" method="GET" action="{{ route('admin.payments') }}">
  <select name="status" onchange="this.form.submit()">
    <option value="pending" @selected($status === 'pending')>Chờ duyệt</option>
    <option value="approved" @selected($status === 'approved')>Đã duyệt</option>
    <option value="rejected" @selected($status === 'rejected')>Từ chối</option>
    <option value="cancelled" @selected($status === 'cancelled')>Đã huỷ</option>
  </select>
</form>

<div class="panel"><div class="pb">
  <div class="tablewrap">
    <table>
      <thead><tr>
        <th>Ngày tạo</th>
        <th>GV</th>
        <th>Gói</th>
        <th>Tháng</th>
        <th>Số tiền</th>
        <th>Nội dung CK</th>
        <th>Trạng thái</th>
        <th>PH đã CK?</th>
        <th></th>
      </tr></thead>
      <tbody>
        @forelse ($orders as $o)
          <tr>
            <td>{{ $o->created_at->format('d/m H:i') }}</td>
            <td>
              <a href="{{ route('admin.teacher', $o->user_id) }}"><b>{{ $o->user->name }}</b></a>
              <div class="r">{{ $o->user->email }}</div>
            </td>
            <td>{{ $o->plan->name }}</td>
            <td>{{ $o->months }}</td>
            <td class="money">{{ number_format($o->amount,0,',','.') }}đ</td>
            <td>
              <code style="font-size:11px" id="code-{{ $o->id }}">{{ $o->code }}</code>
              <button type="button" class="btn ghost sm" style="padding:2px 6px;font-size:11px" onclick="navigator.clipboard.writeText(document.getElementById('code-{{ $o->id }}').textContent)">Copy</button>
            </td>
            <td>
              @switch($o->status)
                @case('pending')<span class="chip a">Chờ</span>@break
                @case('approved')<span class="chip g">Đã duyệt</span>@break
                @case('rejected')<span class="chip r">Từ chối</span>{{ $o->rejected_reason ? ' — '.$o->rejected_reason : '' }}@break
                @case('cancelled')<span class="chip n">Huỷ</span>@break
              @endswitch
            </td>
            <td>{{ $o->notified_at ? $o->notified_at->format('d/m H:i') : '—' }}</td>
            <td style="text-align:right">
              @if ($o->status === 'pending')
                <form method="POST" action="{{ route('admin.payment.approve', $o->id) }}" style="display:inline" data-confirm="Duyệt đơn này? Sub của GV sẽ được kích hoạt / cộng thêm {{ $o->months }} tháng.">
                  @csrf
                  <button class="btn primary sm" type="submit">Duyệt</button>
                </form>
                <button type="button" class="btn ghost sm" onclick="rejectPrompt({{ $o->id }})">Từ chối</button>
                <form id="rej-{{ $o->id }}" method="POST" action="{{ route('admin.payment.reject', $o->id) }}" style="display:none">
                  @csrf
                  <input type="hidden" name="reason">
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="r" style="padding:18px 16px;text-align:center">Không có đơn nào.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $orders])
</div></div>

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
