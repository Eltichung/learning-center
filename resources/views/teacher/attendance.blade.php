@extends('layouts.teacher')
@section('title','Điểm danh — LớpThêm')
@use('App\Support\Money')

@section('content')
@php($base = route('teacher.attendance'))
<div id="att-body" data-partial-url="{{ route('teacher.attendance.partial', request()->query()) }}">
  @include('teacher.partials.attendance-body', compact('classList','class','sessions','session','rows','total','weekStart','weekEnd','weekLabel','logs','pendingOffs'))
</div>

@push('scripts')
<script>
  function openOffModal(dateLabel){
    var el = document.getElementById('off-date');
    if (el) el.textContent = dateLabel || '';
    openModal('m-off');
  }
</script>
<style>
  #lesson-title:disabled, #lesson-content:disabled{background:#fafafa;color:var(--ink);cursor:default;opacity:1}
  .tab-time{font-size:10.5px;color:var(--muted);margin-top:2px;font-weight:500}
  .tab.on .tab-time{color:rgba(255,255,255,.85)}
</style>
<script>
  function buildMakeupConfirm(f){
    var d = f.querySelector('input[name=makeup_date]')?.value;
    var s = f.querySelector('input[name=start_time]')?.value;
    var e = f.querySelector('input[name=end_time]')?.value;
    var msg = 'Xác nhận thêm buổi học bù';
    if(d) msg += ' vào ngày ' + d.split('-').reverse().join('/');
    if(s && e) msg += ' (' + s + '–' + e + ')';
    f.dataset.confirm = msg + '?';
    f.dataset.confirmed = ''; // reset để confirm lại nếu user đổi thông tin sau khi confirm rồi huỷ
  }
</script>
<script src="{{ asset('js/attendance.js') }}?v={{ filemtime(public_path('js/attendance.js')) }}" defer></script>
<script>
  var lessonOrig = null;

  function editLesson(){
    var t = document.getElementById('lesson-title');
    var c = document.getElementById('lesson-content');
    lessonOrig = { title: t.value, content: c.value };
    t.disabled = false; c.disabled = false;
    document.getElementById('lesson-edit-btn').style.display = 'none';
    document.getElementById('lesson-actions').style.display = 'flex';
    t.focus();
  }
  function cancelLesson(){
    if(!lessonOrig) return resetLesson();
    document.getElementById('lesson-title').value = lessonOrig.title;
    document.getElementById('lesson-content').value = lessonOrig.content;
    resetLesson();
  }
  function resetLesson(){
    document.getElementById('lesson-title').disabled = true;
    document.getElementById('lesson-content').disabled = true;
    document.getElementById('lesson-edit-btn').style.display = '';
    document.getElementById('lesson-actions').style.display = 'none';
  }

</script>
@endpush
@endsection
