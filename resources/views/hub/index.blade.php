@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 flex flex-col">
            <div class="flex-1 w-full flex gap-8">
                <div class="flex-1"></div>
                <div class="bg-[#f3f8f8] w-[300px] rounded-4xl p-8">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center p-2 mx-auto">
                        <img src="/assets/eazyonline/memojis/boyd.webp">
                    </div>
                    <h1 class="text-[#215558] font-bold text-base shrink-0 text-center mt-4 mb-2">Goeiedag {{ $user->name }} 👋</h1>
                    <p class="text-xs text-[#215558] font-semibold text-center mb-4">Laten we gaan knallen vandaag!</p>
                    <div class="w-full rounded-4xl p-4 bg-white flex flex-col">
                        <h2 class="text-[#215558] font-bold text-sm shrink-0">Vandaag</h2>
                        <p class="text-[#215558] font-black text-xl shrink-0 mb-2">09:00-17:00</p>
                        <a href="#" class="px-2.5 py-1 text-white font-semibold text-sm bg-[#0F9B9F] w-full rounded-full text-center">Inklokken</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection