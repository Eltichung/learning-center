@extends('layouts.base')

@section('body')
<div class="tdash">
  @include('partials.teacher-nav', ['active' => $navActive ?? ''])
  <main class="stage">
    <div class="stage-bar">
      <h2>{{ $stageTitle ?? 'Phụ huynh' }}</h2>
      <span class="tag mob">Mobile</span>
    </div>
    <div class="stage-body">
      <div class="phone-stage">
        <div class="pscreen">
          {{-- Gợi ý cài lên màn hình chính cho iOS (Android tự hiện prompt riêng) --}}
          <div id="ios-install-hint" style="display:none;background:#fff;border:1px solid var(--line);border-radius:12px;padding:10px 12px;margin:0 0 12px;font-size:12.5px;color:var(--ink);align-items:center;gap:8px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
            <span style="flex:1">📲 Mở nhanh lần sau: bấm <b>Chia sẻ&nbsp;⬆️</b> rồi chọn <b>"Thêm vào Màn hình chính"</b>.</span>
            <button type="button" aria-label="Đóng" onclick="this.parentNode.remove();try{localStorage.setItem('lt_ios_hint','1')}catch(e){}" style="border:0;background:transparent;color:var(--muted);font-size:18px;line-height:1;cursor:pointer">&times;</button>
          </div>
          <script>
          (function(){
            try{
              var ua = navigator.userAgent || '';
              var isIOS = /iphone|ipad|ipod/i.test(ua);
              var standalone = ('standalone' in navigator) && navigator.standalone;
              var dismissed = false;
              try{ dismissed = localStorage.getItem('lt_ios_hint') === '1'; }catch(e){}
              if(isIOS && !standalone && !dismissed){
                var el = document.getElementById('ios-install-hint');
                if(el) el.style.display = 'flex';
              }
            }catch(e){}
          })();
          </script>
          @yield('content')
        </div>
      </div>
    </div>
  </main>
</div>
@endsection
