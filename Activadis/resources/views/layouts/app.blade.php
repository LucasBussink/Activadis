<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Activadis') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('images/covadis-logo.png') }}">

  <!-- Fonts en iconen -->
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Scripts en styles -->
  @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/layouts/app-layout.css'])
  @stack('styles')
</head>

<body>
  <div class="app-shell">
    @include('layouts.navigation')

    <!-- Page Heading -->
    @isset($header)
      <header class="page-heading">
        <div class="page-heading-inner">
          {{ $header }}
        </div>
      </header>
    @endisset

    <!-- Page Content -->
    <main>
      {{ $slot }}
    </main>
  </div>
</body>

</html>
