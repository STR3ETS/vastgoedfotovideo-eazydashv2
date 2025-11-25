@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 flex flex-col">
            <div class="w-6 h-6 rounded-full bg-[#215558] flex items-center justify-center mb-4 shrink-0">
                <i class="fa-solid fa-plus text-xs text-white"></i>
            </div>
            <div class="grid grid-cols-4 gap-8 flex-1">
                <div class="bg-[#f3f8f8] rounded-4xl p-8"></div>
                <div class="bg-[#f3f8f8] rounded-4xl p-8 col-span-3"></div>
            </div>
        </div>
    </div>
@endsection