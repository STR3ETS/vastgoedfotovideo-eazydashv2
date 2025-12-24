@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0">
            <div class="h-full min-h-0 overflow-y-auto pr-3">
                {{-- Header --}}
                <div class="w-full flex items-center justify-between gap-2 min-w-0 mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">SEO projecten</p>
                        <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                            {{ $isEdit ? 'SEO project bewerken' : 'Nieuw SEO project' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Vul alleen de basis in. De rest doe je in het project zelf, stap voor stap.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('support.seo.projects.index') }}"
                           class="px-4 py-2 text-xs font-semibold rounded-full border border-gray-200 text-[#215558] bg-white hover:bg-gray-100 transition">
                            Terug
                        </a>

                        @if($isEdit)
                            <a href="{{ route('support.seo.projects.show', $project) }}"
                               class="px-4 py-2 text-xs font-semibold rounded-full bg-[#f3f8f8] text-[#215558] hover:bg-[#e5f1f1] transition">
                                Bekijk
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Status melding --}}
                @if (session('status'))
                    <div class="mb-4 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 border border-red-200 text-[11px] text-red-700">
                        <p class="font-semibold mb-1">Er ging iets mis.</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $formAction = $isEdit
                        ? route('support.seo.projects.update', $project)
                        : route('support.seo.projects.store');

                    $inputClass = 'w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-[#215558] placeholder-gray-400 outline-none focus:border-[#0F9B9F] focus:ring-2 focus:ring-[#0F9B9F]/20 transition';
                    $labelClass = 'block text-xs font-semibold text-[#215558] mb-1';
                    $helpClass  = 'text-[11px] text-gray-500 mt-1';
                @endphp

                <form method="POST" action="{{ $formAction }}" class="max-w-3xl">
                    @csrf
                    @if($isEdit) @method('PATCH') @endif

                    <div class="bg-[#f3f8f8] rounded-4xl p-6">
                        <h4 class="text-sm font-bold text-[#215558] mb-4">Basis</h4>

                        {{-- Klant --}}
                        <div class="mb-5">
                            <label class="{{ $labelClass }}">
                                Klant / bedrijf <span class="text-red-500">*</span>
                            </label>
                            <select name="company_id" class="{{ $inputClass }}">
                                <option value="">Maak een keuze</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}"
                                        @selected(old('company_id', $project->company_id) == $company->id)>
                                        {{ $company->name ?? ('Bedrijf #' . $company->id) }}
                                        @if($company->website)
                                            ({{ $company->website }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="{{ $helpClass }}">Voor welke klant is dit traject.</p>
                        </div>

                        {{-- Domein --}}
                        <div class="mb-5">
                            <label class="{{ $labelClass }}">
                                Domein <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="domain"
                                value="{{ old('domain', $project->domain) }}"
                                placeholder="bijv. klant.nl"
                                class="{{ $inputClass }}"
                            >
                            <p class="{{ $helpClass }}">Zonder https://, dus alleen het domein.</p>
                        </div>

                        {{-- Naam --}}
                        <div>
                            <label class="{{ $labelClass }}">Projectnaam (optioneel)</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $project->name) }}"
                                placeholder="bijv. Landingspages Q1"
                                class="{{ $inputClass }}"
                            >
                            <p class="{{ $helpClass }}">Handig als je meerdere trajecten voor 1 klant hebt.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button
                            type="submit"
                            class="px-5 py-3 rounded-full text-xs font-semibold text-white bg-[#0F9B9F] hover:bg-[#215558] transition"
                        >
                            {{ $isEdit ? 'Opslaan' : 'Project aanmaken' }}
                        </button>

                        <p class="text-[11px] text-gray-500">
                            Daarna doe je koppeling, keywords en nulmeting in het project zelf.
                        </p>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
