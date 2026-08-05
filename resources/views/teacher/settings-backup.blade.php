@extends('layouts.teacher')
@section('title','Sao lưu dữ liệu — LớpThêm')

@section('content')
<div class="pagehead">
  <div><h1>Sao lưu dữ liệu</h1>
    <p>Tải toàn bộ dữ liệu của bạn về máy — 1 file .zip gồm nhiều file CSV.</p></div>
</div>

@if (session('error'))
  <div class="auth-error" style="margin-bottom:16px">{{ session('error') }}</div>
@endif

<div class="panel" style="padding: 0 16px"><div class="pb">
  <p style="margin:0 0 14px;color:var(--muted);line-height:1.6">
    Bản sao lưu gồm: <b>học sinh</b>, <b>lớp học</b> (kèm lịch), <b>ghi danh</b> (HS ↔ lớp + đơn giá),
    <b>buổi học</b>, <b>điểm danh</b>, <b>thanh toán</b>, <b>nhận xét</b> và bảng <b>công nợ</b>.
    Các file CSV mã hoá UTF-8, mở trực tiếp bằng Excel (tiếng Việt hiển thị đúng).
  </p>

  @if ($backedUpToday)
    <span class="btn primary" aria-disabled="true"
          style="opacity:.5;pointer-events:none;cursor:not-allowed">⬇ Tải bản sao lưu (.zip)</span>
    <p style="margin:14px 0 0;font-size:13px;color:var(--green);font-weight:600">
      ✓ Hôm nay đã sao lưu lúc {{ $lastBackupAt->format('H:i') }} — tải lại được từ 00:00 ngày mai.
    </p>
  @else
    <a class="btn primary" href="{{ route('teacher.settings.backup.download') }}">⬇ Tải bản sao lưu (.zip)</a>
    <p style="margin:14px 0 0;font-size:12.5px;color:var(--muted)">
      Mỗi ngày tải được <b>1 lần</b>.
      Lần sao lưu gần nhất: {{ $lastBackupAt ? $lastBackupAt->format('d/m/Y H:i') : 'chưa sao lưu lần nào' }}.
    </p>
  @endif

  <p style="margin:10px 0 0;font-size:12.5px;color:var(--muted)">
    Dữ liệu là của bạn — có thể tải kể cả khi gói đã hết hạn.
  </p>
</div></div>
@endsection
