<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/x-icon" href="/assets/favicon.webp">
  @vite(['resources/css/app.css','resources/js/app.js'], 'build')
  <link rel="preload" href="{{ asset('fontawesome/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}"></noscript>
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&display=swap"
    media="print" onload="this.media='all'">
  <style>
    .caveat-font { font-family: 'Caveat', cursive !important; }
  </style>
</head>
<body class="min-h-dvh text-[#215558] flex justify-center relative pb-5">
  <div class="fixed left-0 top-0 z-1 w-full h-dvh bg-cover bg-center" style="background-image: url('/assets/app-bg-1920.webp')"></div>
  <div class="relative z-2">
    @yield('content')
  </div>
</body>
</html>
