@extends('hub.projecten.offerte.layouts.guest')

@section('content')
<div class="w-full max-w-4xl mx-auto py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-[#215558]/60 font-semibold mb-1">
                Project #{{ $project->id }}
            </p>
            <h1 class="text-2xl font-black text-[#215558]">
                Offerte voor {{ $project->company ?? 'Onbekend bedrijf' }}
            </h1>
        </div>

        <a href="{{ route('support.offertes.edit', $offerte) }}"
           class="inline-flex items-center gap-2 text-xs md:text-sm text-[#215558] hover:underline">
            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
            Bewerken
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl p-6 text-sm leading-relaxed text-[#215558]">
        {!! $offerte->body !!}
    </div>
</div>
@endsection
