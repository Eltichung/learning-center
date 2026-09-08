@extends('layouts.teacher')
@section('title','Sao lưu dữ liệu — LớpThêm')

@section('content')
<div class="pagehead">
  <div><h1>Sao lưu dữ liệu</h1>
    <p>Tải toàn bộ dữ liệu của bạn về máy — 1 file .zip gồm nhiều file CSV.</p></div>
</div>

<div id="backup-body" data-partial-url="{{ route('teacher.settings.backup.partial') }}">
  @include('teacher.partials.settings-backup-body', compact('backedUpToday','lastBackupAt'))
</div>
@endsection
