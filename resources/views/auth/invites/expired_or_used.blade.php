@extends('layouts.guest')

@section('content')
  <style>
  @keyframes btn-wiggle {0%,100%{transform:translateX(0)}20%{transform:translateX(-2px)}40%{transform:translateX(2px)}60%{transform:translateX(-1px)}80%{transform:translateX(1px)}}
  .btn-wiggle{animation:btn-wiggle .6s ease-in-out}
  </style>
  <div class="btn-wiggle col-span-3 bg-white rounded-xl p-6">
    <img src="/assets/memoji-yael-sad.png" class="max-w-[3rem] mx-auto mb-3">
    <p class="text-sm text-[#215558] opacity-80 font-semibold">
      {{ __('instellingen.invite.messages.expired') }}
    </p>
  </div>
@endsection
