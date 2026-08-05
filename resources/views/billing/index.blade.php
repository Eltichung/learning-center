@extends('layouts.teacher')
@section('title','Gói & thanh toán — Học Chưa')
@use('App\Support\Money')

@section('content')
@php($limit = $current->limits ?? [])
@php($classesUnlimited = $current->isUnlimited('classes'))
@php($studentsUnlimited = $current->isUnlimited('students'))
@php($classLimit = (int) ($limit['classes'] ?? 0))
@php($studentLimit = (int) ($limit['students'] ?? 0))
@php($sub = $sub ?? null)
<div class="pagehead">
  <div>
    <h1>💳 Chọn gói vừa với lớp của bạn</h1>
    <p>
      Đang dùng: <b>{{ $current->name }}</b>
      @if ($current->slug === 'vip')<span class="chip p" style="margin-left:6px">👑 Gói đặc biệt</span>@endif
      @if ($sub && $sub->current_period_end && $current->slug !== 'vip')
        · {{ $current->slug === 'trial' ? 'Dùng thử miễn phí đến' : 'Hạn dùng đến' }}
        {{ \Illuminate\Support\Carbon::parse($sub->current_period_end)->format('d/m/Y') }}
      @endif
      · Đổi gói bất cứ lúc nào, huỷ ngang không mất phí.
    </p>
  </div>
</div>

@if ($current->slug === 'vip')
  <div class="panel" style="border-color:#c39bd3;background:linear-gradient(135deg,#faf5ff,#f3e5f5)">
    <div class="pb" style="padding:20px;text-align:center">
      <div style="font-size:34px">👑</div>
      <div style="font-size:20px;font-weight:800;margin:6px 0 4px">Bạn đang dùng gói Yêu Anh</div>
      <div style="color:var(--muted);font-size:13.5px">Không giới hạn lớp · Không giới hạn học sinh · Cảm ơn bạn đã đồng hành 💜</div>
    </div>
  </div>
@endif

{{-- Usage bar --}}
<div class="panel">
  <div class="pb" style="padding:16px 18px">
    <div class="usage-row">
      <div class="usage-label">Số lớp</div>
      <div class="usage-bar"><div class="usage-fill" style="width:{{ $classesUnlimited ? 100 : ($classLimit ? min(100, round($usage['classes'] / $classLimit * 100)) : 0) }}%{{ $classesUnlimited ? ';background:linear-gradient(90deg,#6a1b9a,#c39bd3)' : '' }}"></div></div>
      <div class="usage-count">{{ $usage['classes'] }} / {{ $classesUnlimited ? '∞' : $classLimit }}</div>
    </div>
    <div class="usage-row">
      <div class="usage-label">Học sinh</div>
      <div class="usage-bar"><div class="usage-fill" style="width:{{ $studentsUnlimited ? 100 : ($studentLimit ? min(100, round($usage['students'] / $studentLimit * 100)) : 0) }}%{{ $studentsUnlimited ? ';background:linear-gradient(90deg,#6a1b9a,#c39bd3)' : '' }}"></div></div>
      <div class="usage-count">{{ $usage['students'] }} / {{ $studentsUnlimited ? '∞' : $studentLimit }}</div>
    </div>
  </div>
</div>

{{-- 3 gói --}}
@php($copy = [
  'trial' => [
    'tagline' => 'Nếm thử cho biết',
    'sub' => 'Vừa đủ để cảm giác trước khi cam kết',
    'perDay' => null,
    'feats' => [
      '👥 1 lớp · 10 học sinh',
      '🎁 Miễn phí 2 tháng đầu',
      '💯 Đầy đủ chức năng cốt lõi',
      '📱 Có PWA cho phụ huynh',
    ],
    'footer' => 'Đủ để mở 1 lớp gia sư 1-1 hoặc nhóm tới 10 em, dùng thử 2 tháng.',
  ],
  'plus' => [
    'tagline' => 'GV dạy thêm tại nhà',
    'sub' => 'Đầu tư 1 ly cafe/ngày, quản lý 30 học sinh nhàn tênh',
    'perDay' => 2600,
    'feats' => [
      '👥 3 lớp · 30 học sinh',
      '💸 Tính học phí + công nợ tự động',
      '📊 Báo cáo doanh thu theo tháng',
      '📱 PWA phụ huynh tra mã · xem tiến độ',
      '📝 Giáo án · nhận xét học sinh',
    ],
    'footer' => '⭐ Chọn nhiều nhất bởi giáo viên dạy 20-30 HS.',
  ],
  'pro' => [
    'tagline' => 'Trung tâm mini · GV nhiều lớp',
    'sub' => 'Rảnh tay dạy — hệ thống lo phần sổ sách',
    'perDay' => 5967,
    'feats' => [
      '👥 10 lớp · 100 học sinh',
      '🚀 Không lo chạm giới hạn giữa mùa cao điểm',
      '💰 Tương đương thu nhập 15-30tr/tháng',
      '🎁 Tất cả tính năng của Plus',
      '⚡ Ưu tiên nghe phản hồi để nâng cấp',
    ],
    'footer' => '💡 Tiết kiệm hơn nhiều so với thuê 1 buổi thư ký/tuần.',
  ],
])
<div class="plans-grid">
  @foreach ($plans as $p)
    @php($isCurrent = $p->id === $current->id)
    @php($isBest = $p->slug === 'pro')
    @php($c = $copy[$p->slug] ?? null)
    <div class="plan-card {{ $isCurrent ? 'is-current' : '' }} {{ $isBest ? 'is-best' : '' }}">
      @if ($isBest)<div class="plan-badge">🔥 Lựa chọn nhiều nhất</div>@endif
      <div class="plan-name">{{ $p->name }}</div>
      @if ($c)<div class="plan-tagline">{{ $c['tagline'] }}</div>@endif
      <div class="plan-price">
        @if ($p->price > 0)
          <span class="amt">{{ number_format($p->price, 0, ',', '.') }}đ</span>
          <span class="per">/ tháng</span>
          @if (! empty($c['perDay']))
            <div class="per-day">≈ {{ number_format($c['perDay'], 0, ',', '.') }}đ / ngày ☕</div>
          @endif
        @else
          <span class="amt">Miễn phí</span>
          <span class="per">mãi mãi</span>
        @endif
      </div>
      @if ($c)<div class="plan-sub">{{ $c['sub'] }}</div>@endif
      <ul class="plan-feats">
        @foreach (($c['feats'] ?? []) as $f)
          <li>{{ $f }}</li>
        @endforeach
      </ul>
      @if (! empty($c['footer']))<div class="plan-footer-hint">{{ $c['footer'] }}</div>@endif
      @if ($isCurrent)
        <button class="btn ghost" disabled style="width:100%">Đang dùng</button>
      @elseif ($p->slug === 'trial')
        <button class="btn ghost" disabled style="width:100%">Gói mặc định</button>
      @else
        <button type="button" class="btn {{ $isBest ? 'primary' : 'ghost' }}" style="width:100%"
                onclick='openBuyModal(@json($p->slug), @json($p->name), {{ $p->price }})'>
          {{ $current->slug === 'pro' && $p->slug === 'plus' ? 'Hạ gói · giữ dữ liệu' : ($isBest ? 'Nâng cấp Pro ngay 🚀' : 'Nâng cấp Plus →') }}
        </button>
      @endif
    </div>
  @endforeach
</div>

{{-- Reassurance strip --}}
<div class="plans-reassure">
  <div><b>♻️ Đổi / huỷ bất cứ lúc nào</b><br><span>Dữ liệu giữ nguyên, không mất</span></div>
  <div><b>🔒 Chuyển khoản QR</b><br><span>Không cần điền thẻ, không tự động trừ tiền</span></div>
  <div><b>🇻🇳 Hỗ trợ tiếng Việt</b><br><span>Nhắn thẳng người làm sản phẩm</span></div>
</div>

{{-- Lịch sử đơn --}}
<div class="panel">
  <div class="ph"><h3>📜 Lịch sử đơn</h3></div>
  <div class="pb">
    <div class="scrolllist">
      <table>
        <thead><tr><th>Ngày</th><th>Mã đơn</th><th>Gói</th><th>Tháng</th><th>Số tiền</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
          @forelse ($orders as $o)
            <tr>
              <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
              <td><code style="font-size:11px">{{ $o->code }}</code></td>
              <td>{{ $o->plan->name }}</td>
              <td>{{ $o->months }}</td>
              <td class="money">{{ number_format($o->amount, 0, ',', '.') }}đ</td>
              <td>
                @switch($o->status)
                  @case('pending')<span class="chip a">Chờ duyệt</span>@break
                  @case('approved')<span class="chip g">Đã kích hoạt</span>@break
                  @case('rejected')<span class="chip r">Từ chối</span>@break
                  @case('cancelled')<span class="chip n">Đã huỷ</span>@break
                @endswitch
              </td>
              <td style="text-align:right">
                @if ($o->status === 'pending')
                  <form method="POST" action="{{ route('billing.order.cancel', $o->code) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn ghost sm" data-confirm="Huỷ đơn này?">Huỷ</button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="r" style="padding:18px 16px;text-align:center">Chưa có đơn nào.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal mua gói --}}
<div class="modal-backdrop" id="m-buy">
  <div class="modal" style="width:440px">
    <div class="mh"><h3 id="buy-title">Mua gói</h3><button type="button" class="x" onclick="closeBuyModal()">&times;</button></div>
    <div class="mb" id="buy-step-1">
      <div class="field"><label>Kỳ hạn</label>
        <select id="buy-months">
          <option value="1" selected>1 tháng</option>
          <option value="3">3 tháng</option>
          <option value="6">6 tháng</option>
          <option value="12">12 tháng</option>
        </select>
      </div>
      <div class="field"><label>Số tiền</label>
        <div style="font-size:22px;font-weight:800;color:var(--primary)"><span id="buy-amount">—</span> đ</div>
      </div>
      <div class="mf" style="justify-content:flex-end">
        <button type="button" class="btn ghost" onclick="closeBuyModal()">Huỷ</button>
        <button type="button" class="btn primary" id="buy-continue">Tiếp tục</button>
      </div>
    </div>
    <div class="mb" id="buy-step-2" hidden>
      <div style="text-align:center;margin:4px 0 10px">
        <img id="qr-img" alt="QR" style="width:220px;height:220px;border:1px dashed var(--line);border-radius:12px;padding:6px;background:#fff">
      </div>
      <div class="field"><label>Ngân hàng</label>
        <div><b id="qr-bank"></b> · STK: <b id="qr-account"></b> · <span id="qr-name"></span></div>
      </div>
      <div class="field"><label>Nội dung chuyển khoản (chính xác)</label>
        <div style="display:flex;gap:6px">
          <input id="qr-code" readonly style="flex:1">
          <button type="button" class="btn ghost sm" onclick="copyOrderCode()">Copy</button>
        </div>
      </div>
      <div class="r" style="font-size:12px">Sau khi chuyển tiền, bấm nút <b>"Đã chuyển khoản"</b>. Admin sẽ duyệt trong 24h.</div>
      <div class="mf" style="justify-content:space-between">
        <button type="button" class="btn ghost" onclick="closeBuyModal()">Đóng</button>
        <button type="button" class="btn primary" id="buy-notify">Đã chuyển khoản</button>
      </div>
    </div>
  </div>
</div>

<style>
  .usage-row{display:flex;align-items:center;gap:12px;margin-bottom:8px}
  .usage-row:last-child{margin-bottom:0}
  .usage-label{width:90px;font-weight:600;font-size:13px}
  .usage-bar{flex:1;height:10px;background:#eef0f3;border-radius:6px;overflow:hidden}
  .usage-fill{height:100%;background:linear-gradient(90deg,var(--green),#4caf50);transition:width .3s}
  .usage-count{min-width:80px;text-align:right;font-variant-numeric:tabular-nums;font-size:13px;color:var(--muted)}

  .plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin:16px 0}
  .plan-card{position:relative;background:#fff;border:1px solid var(--line);border-radius:14px;padding:20px 18px;display:flex;flex-direction:column}
  .plan-card.is-best{border-color:var(--primary);border-width:2px;box-shadow:0 4px 16px rgba(201,100,66,.08)}
  .plan-card.is-current{background:#f6faff;border-color:#1e88e5}
  .plan-badge{position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:var(--primary);color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:12px}
  .plan-name{font-size:20px;font-weight:800}
  .plan-tagline{font-size:12px;color:var(--primary);font-weight:600;text-transform:uppercase;letter-spacing:.3px;margin-top:2px}
  .plan-price{margin:14px 0 4px}
  .plan-price .amt{font-size:28px;font-weight:800;color:var(--ink)}
  .plan-price .per{color:var(--muted);font-size:13px;margin-left:4px}
  .plan-price .per-day{font-size:11.5px;color:var(--muted);margin-top:2px}
  .plan-sub{font-size:12.5px;color:var(--muted);margin:0 0 12px;line-height:1.5;font-style:italic}
  .plan-feats{list-style:none;padding:0;margin:0 0 12px;flex:1}
  .plan-feats li{padding:5px 0;font-size:13.5px;line-height:1.45}
  .plan-footer-hint{background:#fff8e1;border:1px dashed #f0c76e;color:#7a4d00;padding:8px 10px;border-radius:8px;font-size:12px;margin-bottom:12px;line-height:1.4}
  .plan-card.is-best .plan-footer-hint{background:#fef1eb;border-color:var(--primary);color:#8a3d20}

  .plans-reassure{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin:8px 0 20px;padding:14px;background:#f6faff;border:1px solid #d9e6f2;border-radius:12px}
  .plans-reassure > div{font-size:13px;line-height:1.5}
  .plans-reassure > div span{color:var(--muted);font-size:12px}
</style>

<script>
  const PLAN_PRICES = @json($plans->pluck('price', 'slug'));
  let BUY_SLUG = null, BUY_ORDER_CODE = null;

  function fmt(n){ return n.toLocaleString('vi-VN'); }

  function openBuyModal(slug, name, price){
    BUY_SLUG = slug;
    document.getElementById('buy-title').textContent = 'Mua gói ' + name;
    document.getElementById('buy-step-1').hidden = false;
    document.getElementById('buy-step-2').hidden = true;
    document.getElementById('buy-months').value = '1';
    document.getElementById('buy-amount').textContent = fmt(price);
    document.getElementById('m-buy').classList.add('show');
    document.body.style.overflow = 'hidden';
  }
  function closeBuyModal(){
    document.getElementById('m-buy').classList.remove('show');
    document.body.style.overflow = '';
    BUY_ORDER_CODE = null; BUY_SLUG = null;
  }
  document.getElementById('buy-months').addEventListener('change', e => {
    const m = parseInt(e.target.value, 10);
    const price = PLAN_PRICES[BUY_SLUG] || 0;
    document.getElementById('buy-amount').textContent = fmt(price * m);
  });

  document.getElementById('buy-continue').addEventListener('click', async () => {
    if (!BUY_SLUG) return;
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const months = parseInt(document.getElementById('buy-months').value, 10);
    const res = await fetch(@json(route('billing.order.create')), {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest','Accept':'application/json'},
      body: new URLSearchParams({_token: csrf, plan: BUY_SLUG, months})
    });
    const data = await res.json();
    if (!res.ok || !data.ok) { alert(data.message || 'Không tạo được đơn.'); return; }
    BUY_ORDER_CODE = data.order.code;
    document.getElementById('buy-step-1').hidden = true;
    document.getElementById('buy-step-2').hidden = false;
    document.getElementById('qr-img').src = data.qr.url;
    document.getElementById('qr-bank').textContent = data.qr.bank_code;
    document.getElementById('qr-account').textContent = data.qr.account;
    document.getElementById('qr-name').textContent = data.qr.name;
    document.getElementById('qr-code').value = data.order.code;
  });

  function copyOrderCode(){
    const inp = document.getElementById('qr-code');
    inp.select(); document.execCommand('copy');
  }

  document.getElementById('buy-notify').addEventListener('click', async () => {
    if (!BUY_ORDER_CODE) return;
    const csrf = document.querySelector('meta[name=csrf-token]').content;
    const res = await fetch('/billing/order/' + encodeURIComponent(BUY_ORDER_CODE) + '/notify', {
      method: 'POST',
      headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json','Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({_token: csrf})
    });
    const data = await res.json();
    if (res.ok) { alert(data.ok || 'Đã ghi nhận.'); window.location.reload(); }
    else { alert(data.message || 'Lỗi'); }
  });
</script>
@endsection
