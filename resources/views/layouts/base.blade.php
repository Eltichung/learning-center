<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'LớpThêm')</title>
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  {{-- PWA --}}
  <link rel="manifest" href="{{ route('pwa.manifest') }}?v={{ filemtime(base_path('routes/web.php')) }}">
  <meta name="theme-color" content="#c96442">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="Học Chưa">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
  <script src="{{ asset('js/app-ui.js') }}?v={{ filemtime(public_path('js/app-ui.js')) }}" defer></script>
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function () {});
      });
    }
  </script>
</head>
<body class="@yield('bodyClass')">
  @yield('body')
  @stack('scripts')
</body>
</html>
