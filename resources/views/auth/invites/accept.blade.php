@extends('layouts.guest')

@section('content')
  <div class="col-span-3 bg-white rounded-xl p-6">
    <h1 class="text-xl font-black text-[#215558] mb-2">
      {{ __('instellingen.invite.accept.title') }}
    </h1>
    <p class="text-sm text-[#215558] opacity-80 mb-6">
      {{ __('instellingen.invite.accept.subtitle', ['company' => $invite->company->name]) }}
    </p>

    <form method="post" action="{{ route('support.instellingen.team.invite.handle', $invite->token) }}" class="grid gap-3 max-w-md min-w-md">
      @csrf

      <label class="block text-xs text-[#215558] opacity-70 -mb-2">{{ __('instellingen.invite.accept.fields.email') }}</label>
      <input type="email" value="{{ $invite->email }}" disabled class="py-3 px-4 text-sm rounded-xl border border-gray-200 bg-gray-100">

      <label class="block text-xs text-[#215558] opacity-70 -mb-2">{{ __('instellingen.invite.accept.fields.name') }}</label>
      <input name="name" type="text" required class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300" autofocus>

      @error('name')  <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
      @error('email') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
      @error('token') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror

      <button class="bg-[#0F9B9F] hover:bg-[#215558] text-white px-6 py-3 rounded-full font-semibold transition mt-3 cursor-pointer">
        {{ __('instellingen.invite.accept.actions.create') }}
      </button>
    </form>
  </div>
@endsection
