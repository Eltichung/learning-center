@extends('layouts.teacher')
@section('title','Cài đặt QR chuyển khoản — LớpThêm')

@php($navActive = 'settings-qr')
@php($qrUrl = $me->qr_image_path ? asset('storage/'.$me->qr_image_path) : null)

@section('content')
<div class="pagehead">
  <div><h1>QR chuyển khoản</h1>
    <p>Tải lên ảnh QR để phụ huynh quét chuyển học phí.</p></div>
</div>

<div class="twocol">
  <form class="panel" method="POST" action="{{ route('teacher.settings.qr.update', [], false) }}" enctype="multipart/form-data">
    @csrf
    <div class="ph"><h3>Tải lên ảnh QR</h3></div>
    <div class="pb" style="padding:16px">
      <div class="field">
        <label>Chọn ảnh QR</label>
        <input type="file" name="qr_image" accept="image/*">
        <div class="r" style="font-size:12px;margin-top:4px">Chụp/tải QR từ app ngân hàng. Tối đa 2MB.</div>
      </div>

      @if ($me->qr_image_path)
        <label style="display:flex;align-items:center;gap:6px;margin-top:8px;font-size:13px">
          <input type="checkbox" name="remove_qr_image" value="1"> Xoá ảnh QR hiện tại
        </label>
      @endif

      <div style="margin-top:14px"><button type="submit" class="btn primary">💾 Lưu</button></div>
    </div>
  </form>

  <div class="panel">
    <div class="ph"><h3>Xem trước</h3></div>
    <div class="pb" style="padding:16px;text-align:center">
      @if ($qrUrl)
        <img src="{{ $qrUrl }}" alt="QR" style="max-width:280px;width:100%;border:1px dashed var(--line);border-radius:12px;padding:8px;background:#fafbfc">
      @else
        <div class="r" style="padding:40px 10px">Chưa có ảnh QR. Upload để xem trước.</div>
      @endif
    </div>
  </div>
</div>
@endsection
