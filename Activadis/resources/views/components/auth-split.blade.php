@props(['title'])

<x-auth-visual-layout :title="$title">
  <div class="login-page">
    <div class="login-visual">
      <div class="hero-anim-slot">
        <img src="{{ asset('images/hero-animation.gif') }}" alt="">
      </div>
      <p class="login-visual-caption">Activadis</p>
    </div>

    <div class="login-panel">
      <div class="login-panel-inner">
        <h1>{{ $title }}</h1>

        {{ $slot }}
      </div>
    </div>
  </div>
</x-auth-visual-layout>
