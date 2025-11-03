<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  <link rel="icon" type="image/x-icon" href="/assets/favicon.webp">
  @vite(['resources/css/app.css','resources/js/app.js'], 'build')
  <link rel="preload" href="{{ asset('fontawesome/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}"></noscript>
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
</head>
<body class="min-h-dvh text-[#215558] flex items-center justify-center bg-cover bg-center" style="background-image: url('/assets/app-bg-1920.webp')">
  <div class="max-w-xl mx-auto p-6">
    @yield('content')
  </div>
</body>
</html>
