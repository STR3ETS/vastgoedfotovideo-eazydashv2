@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 flex flex-col">
            <button class="cursor-pointer w-6 h-6 rounded-full bg-[#0F9B9F] flex items-center justify-center mb-4 shrink-0  relative group">
                <i class="fa-solid fa-plus text-xs text-white"></i>
                <div
                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] left-0
                        opacity-0 invisible translate-y-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                        transition-all duration-300 ease-out z-10">
                    <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">Nieuwe template maken</p>
                </div>
            </button>
            <div class="grid grid-cols-4 gap-8 flex-1">
                <div class="bg-[#f3f8f8] rounded-4xl p-8">
                    <p class="text-xs font-semibold text-[#215558]/50">Nog geen templates aangemaakt.</p>
                </div>
                <div class="bg-[#f3f8f8] rounded-4xl p-8 col-span-3">
                    <div class="flex items-center gap-4">
                        <span class="text-4xl">👈</span>
                        <p class="text-base font-bold text-[#215558]/80 mt-1">Selecteer een template om te beginnen.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection