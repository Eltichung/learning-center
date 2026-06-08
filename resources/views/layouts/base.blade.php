<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'LớpThêm')</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="@yield('bodyClass')">
  @yield('body')
  @stack('scripts')
</body>
</html>
