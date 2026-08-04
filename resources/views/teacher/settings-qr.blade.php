@extends('layouts.teacher')
@section('title','Cài đặt QR chuyển khoản — LớpThêm')

@php($navActive = 'settings-qr')
@php($qrUrl = $me->qr_image_path ? asset('storage/'.$me->qr_image_path) : null)

@section('content')
<div class="pagehead">
  <div><h1>QR chuyển khoản</h1>
    <p>Tải lên ảnh QR để phụ huynh quét chuyển học phí.</p></div>
</div>

<div id="qr-body" data-partial-url="{{ route('teacher.settings.qr.partial') }}">
  @include('teacher.partials.settings-qr-body', compact('me','qrUrl'))
</div>

@endsection
