@extends('layouts.teacher')
@section('title','Hồ sơ học sinh — LớpThêm')
@use('App\Support\Money')

@section('content')
<div class="pagehead">
  <div>
    <a class="backlink" href="{{ route('teacher.students') }}">← Học sinh</a>
    <h1>{{ $student->full_name }}</h1>
    <p>{{ $grade ? 'Lớp '.$grade : '—' }}{{ $student->school ? ' · '.$student->school : '' }}</p>
  </div>
  <button class="btn primary" type="button" onclick='payFor({{ $student->id }}, @json($student->full_name), {{ $balance }})'>+ Ghi nhận đóng tiền</button>
</div>

<div class="twocol">
  <div>
    <form class="panel" method="POST" action="{{ route('teacher.students.update', $student->id) }}">
      @csrf
      @method('PUT')
      <div class="ph"><h3>Thông tin & link tra cứu</h3></div>
      <div class="pb" style="padding:16px">
        <div class="field"><label>Họ tên <span style="color:var(--red)">*</span></label><input name="full_name" value="{{ old('full_name', $student->full_name) }}" required></div>
        <div class="field"><label>SĐT phụ huynh <span style="color:var(--red)">*</span></label><input name="parent_phone" value="{{ old('parent_phone', $student->parent_phone) }}" required></div>
        <div class="field"><label>Kênh liên lạc PH (Zalo / Facebook)</label>
          <input name="parent_contact" value="{{ old('parent_contact', $student->parent_contact) }}" placeholder="VD: zalo.me/0900..., fb.com/...">
          @php($pc = $student->parent_contact)
          @if ($pc)
            @php($url = \Illuminate\Support\Str::startsWith($pc, ['http://','https://']) ? $pc : (\Illuminate\Support\Str::contains($pc, ['fb.com','facebook.com','zalo.me']) ? 'https://'.$pc : null))
            @if ($url)<a href="{{ $url }}" target="_blank" style="font-size:12px;color:var(--brand)">↗ Mở liên lạc</a>@endif
          @endif
        </div>
        <div class="field">
          <label>Link tra cứu (slug do bạn tự đặt) <span style="color:var(--red)">*</span></label>
          <div class="slugrow"><div class="pre">lopthem.vn/{{ $prefix }}/</div><input name="student_code" value="{{ old('student_code', $student->student_code) }}" style="flex:1" required></div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;margin-top:6px">
          <button class="btn primary" type="submit">💾 Lưu thông tin</button>
          <a class="btn ghost sm" href="{{ route('parent.info', $student->student_code) }}" target="_blank">📋 Xem trang phụ huynh</a>
        </div>
      </div>
    </form>
  </div>
  <div>
    <div class="panel"><div class="ph"><h3>Công nợ</h3></div><div class="pb" style="padding:16px">
      @if ($balance > 0)
        <div style="font-size:28px;font-weight:800;color:var(--red)">−{{ Money::vnd($balance) }}</div>
        <div class="r" style="margin-top:4px">{{ $unpaidSessions }} buổi chưa đóng × {{ Money::vnd($primaryPrice) }}</div>
      @else
        <div style="font-size:28px;font-weight:800;color:var(--green)">Đã đóng đủ</div>
        <div class="r" style="margin-top:4px">Không còn công nợ</div>
      @endif
    </div></div>
    <div class="panel"><div class="ph"><h3>Đơn giá / buổi</h3></div><div class="pb" style="padding:16px">
      @forelse ($enrollments as $en)
        <div class="prow"><div>{{ $en->name }}</div><b>{{ Money::vnd($en->price) }}</b></div>
      @empty
        <div class="prow r">Chưa ghi danh lớp nào.</div>
      @endforelse
    </div></div>
  </div>
</div>

<div class="panel"><div class="ph"><h3>Lịch sử đóng tiền</h3></div><div class="pb">
  <table>
    <thead><tr><th>Ngày</th><th>Số tiền</th><th>Hình thức</th><th>Ghi chú</th></tr></thead>
    <tbody>
      @forelse ($student->payments as $p)
        <tr>
          <td>{{ \Illuminate\Support\Carbon::parse($p->paid_at)->format('d/m/Y') }}</td>
          <td class="money">{{ Money::vnd($p->amount) }}</td>
          <td>@if ($p->method === 'transfer')<span class="chip b">Chuyển khoản</span>@else<span class="chip n">Tiền mặt</span>@endif</td>
          <td class="r">{{ $p->note }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="r" style="padding:16px">Chưa có lần đóng tiền nào.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>

{{-- Bảng chấm công: lịch sử các buổi học --}}
<div class="panel"><div class="ph"><h3>Lịch sử điểm danh</h3>
  <div style="font-size:12px;color:var(--muted)">
    <b style="color:var(--green)">{{ $attSummary->present }}</b> có mặt ·
    <b style="color:var(--blue)">{{ $attSummary->makeup }}</b> học bù ·
    <b style="color:var(--amber)">{{ $attSummary->excused }}</b> vắng phép ·
    <b style="color:var(--red)">{{ $attSummary->absent }}</b> vắng
  </div>
</div><div class="pb">
  <div class="tablewrap">
  <table>
    <thead><tr><th>Ngày</th><th>Lớp</th><th>Trạng thái</th><th>Thành tiền</th></tr></thead>
    <tbody>
      @forelse ($attendance as $a)
        @php($st = ['present'=>['Có mặt','g'],'makeup'=>['Học bù','b'],'excused'=>['Vắng phép','a'],'absent'=>['Vắng','r']][$a->status] ?? ['—','n'])
        <tr>
          <td>{{ $a->date ? \Illuminate\Support\Carbon::parse($a->date)->format('d/m/Y') : '—' }}</td>
          <td>{{ $a->class }}</td>
          <td><span class="chip {{ $st[1] }}">{{ $st[0] }}</span></td>
          <td class="money">{{ $a->amount > 0 ? Money::vnd($a->amount) : '0đ' }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="r" style="padding:16px">Chưa có buổi học nào được điểm danh.</td></tr>
      @endforelse
    </tbody>
  </table>
  </div>
</div></div>

@include('partials.payment-modal')
@endsection
