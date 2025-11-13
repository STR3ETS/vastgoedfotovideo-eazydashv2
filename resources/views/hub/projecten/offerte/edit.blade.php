@extends('hub.projecten.offerte.layouts.guest')

@section('content')
<div class="w-full max-w-6xl mx-auto py-8">
    {{-- Breadcrumb / header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-[#215558]/60 font-semibold mb-1">
                Project #{{ $project->id }}
            </p>
            <h1 class="text-2xl md:text-3xl font-black text-[#215558] leading-tight">
                Offerte voor {{ $project->company ?? 'Onbekend bedrijf' }}
            </h1>
            <p class="text-xs md:text-sm text-[#215558]/70 mt-1">
                Contact: {{ $project->contact_name ?? '—' }} · {{ $project->contact_email ?? '—' }}
            </p>
        </div>

        <a href="{{ route('support.projecten.index') }}"
           class="inline-flex items-center gap-2 text-xs md:text-sm text-[#215558] hover:underline">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            Terug naar projecten
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    {{-- 2 kolommen: links bewerken, rechts preview --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-6 items-start">
        {{-- Linker kolom: form --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 md:p-5">
            <form method="POST"
                  action="{{ route('support.offertes.update', $offerte) }}"
                  class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Titel --}}
                <div>
                    <label class="block text-xs font-semibold text-[#215558]/70 mb-1">
                        Titel
                    </label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $offerte->title) }}"
                           class="w-full py-2.5 px-3 text-sm rounded-xl border border-gray-200 focus:border-[#0F9B9F] outline-none text-[#215558] font-semibold">
                </div>

                {{-- Status --}}
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-[#215558]/70">
                            Status
                        </label>
                        <select name="status"
                                class="py-1.5 px-3 rounded-full border border-gray-200 text-xs text-[#215558] font-semibold bg-white">
                            <option value="draft" @selected(old('status', $offerte->status) === 'draft')>
                                Concept
                            </option>
                            <option value="ready_to_send" @selected(old('status', $offerte->status) === 'ready_to_send')>
                                Klaar om te versturen
                            </option>
                        </select>
                    </div>

                    {{-- Later kun je hier bv. een "Versturen" knop toevoegen als status ready_to_send is --}}
                    <p class="text-[11px] text-[#215558]/60">
                        Zet de status op <span class="font-semibold">“Klaar om te versturen”</span> als de tekst definitief is.
                    </p>
                </div>

                {{-- Body --}}
                <div>
                    <label class="block text-xs font-semibold text-[#215558]/70 mb-1">
                        Inhoud offerte
                    </label>
                    <textarea name="body"
                              rows="18"
                              class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm leading-relaxed text-[#215558] font-normal focus:border-[#0F9B9F] outline-none">{{ old('body', $offerte->body) }}</textarea>
                    <p class="mt-1 text-[11px] text-[#215558]/60">
                        Je kunt hier HTML gebruiken. De rechts getoonde preview laat zien hoe het eruit ziet.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 bg-[#0F9B9F] hover:bg-[#215558] text-white text-sm font-semibold px-6 py-2.5 rounded-full transition">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        Offerte opslaan
                    </button>
                </div>
            </form>
        </div>

        {{-- Rechter kolom: interne preview --}}
        <div class="bg-gray-50 rounded-2xl border border-gray-200 p-4 md:p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.18em] text-[#215558]/60 font-semibold">
                        Interne preview
                    </p>
                    <p class="text-xs text-[#215558]/70 mt-0.5">
                        Dit is hoe de offerte er globaal uit komt te zien.
                    </p>
                </div>

                {{-- Later kun je hier bv. een knop toevoegen om klant-preview te openen --}}
                {{-- <button class="text-xs text-[#0F9B9F] hover:underline">
                    Open klant-preview
                </button> --}}
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-5 md:p-6 text-sm leading-relaxed text-[#215558] prose prose-sm max-w-none">
                {!! $offerte->body !!}
            </div>
        </div>
    </div>
</div>
@endsection
