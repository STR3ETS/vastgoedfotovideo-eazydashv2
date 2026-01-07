@extends('hub.layouts.app')

@section('content')
    @php
        $statusOptions = [
            'new' => 'Nieuw',
            'planned' => 'Ingepland',
            'done' => 'Afgerond',
            'cancelled' => 'Geannuleerd',
        ];

        $address = trim(($onboardingRequest->address ?? '') . ' ' . ($onboardingRequest->postcode ?? '') . ' ' . ($onboardingRequest->city ?? ''));
        $contact = trim(($onboardingRequest->contact_first_name ?? '') . ' ' . ($onboardingRequest->contact_last_name ?? ''));
    @endphp

    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

            <div class="flex items-start justify-between gap-6 mb-6">
                <div>
                    <p class="text-sm opacity-80 text-[#191D38] font-semibold uppercase tracking-[5px] mb-2">Planning</p>
                    <h1 class="text-[#191D38] font-black text-3xl leading-tight">Planning bewerken</h1>
                    <p class="text-[#191D38]/70 text-sm font-semibold mt-2">
                        Aanvraag #{{ $onboardingRequest->id }} • {{ $address !== '' ? $address : 'Geen adres' }}
                    </p>
                </div>

                <a href="{{ route('support.planning.index', ['section' => 'today', 'date' => optional($onboardingRequest->shoot_date)->toDateString()]) }}"
                   class="px-6 h-10 text-[#191D38] font-semibold text-sm bg-white border border-gray-200 hover:bg-[#009AC3] hover:border-[#009AC3] hover:text-white transition duration-300 rounded-full flex items-center">
                    Terug
                </a>
            </div>

            <div class="flex-1 min-h-0 grid grid-cols-5 gap-6">
                <div class="col-span-3 bg-[#191D38]/5 rounded-2xl p-6">
                    <form method="POST" action="{{ route('support.planning.update', $onboardingRequest) }}" class="grid gap-5">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#191D38]/60 mb-2">Datum</label>
                                <input
                                    type="date"
                                    name="shoot_date"
                                    value="{{ old('shoot_date', optional($onboardingRequest->shoot_date)->toDateString()) }}"
                                    class="h-11 w-full bg-white border border-gray-200 rounded-xl px-4 text-sm font-semibold text-[#191D38] outline-none"
                                    required
                                >
                                @error('shoot_date')
                                    <p class="mt-2 text-xs font-semibold text-[#DF2935]">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#191D38]/60 mb-2">Tijdslot</label>
                                <input
                                    type="text"
                                    name="shoot_slot"
                                    value="{{ old('shoot_slot', $onboardingRequest->shoot_slot) }}"
                                    placeholder="Bijv. 08:00-10:00"
                                    class="h-11 w-full bg-white border border-gray-200 rounded-xl px-4 text-sm font-semibold text-[#191D38] outline-none"
                                    required
                                >
                                @error('shoot_slot')
                                    <p class="mt-2 text-xs font-semibold text-[#DF2935]">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#191D38]/60 mb-2">Status</label>
                            <select
                                name="status"
                                class="h-11 w-full bg-white border border-gray-200 rounded-xl px-4 text-sm font-semibold text-[#191D38] outline-none appearance-none cursor-pointer"
                                required
                            >
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected(old('status', $onboardingRequest->status) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-2 text-xs font-semibold text-[#DF2935]">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button
                                type="submit"
                                class="px-6 h-11 text-white font-semibold text-sm bg-[#191D38] hover:bg-[#191D38]/80 transition duration-300 rounded-full"
                            >
                                Opslaan
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-span-2 flex flex-col gap-4">
                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <p class="text-xs font-bold text-[#191D38]/60 mb-2">Klant</p>
                        <p class="text-[#191D38] font-black text-xl">{{ $contact !== '' ? $contact : 'Onbekend' }}</p>
                        <p class="text-[#191D38]/70 text-sm font-semibold mt-2">{{ $onboardingRequest->contact_phone ?? '-' }}</p>
                        <p class="text-[#191D38]/70 text-sm font-semibold">{{ $onboardingRequest->contact_email ?? '-' }}</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-2xl p-6">
                        <p class="text-xs font-bold text-[#191D38]/60 mb-2">Pakket</p>
                        <p class="text-[#191D38] font-black text-xl">{{ $onboardingRequest->package ?? '-' }}</p>
                    </div>

                    <div class="bg-[#DF2935]/10 border border-[#DF2935]/20 rounded-2xl p-6">
                        <p class="text-[#DF2935] font-black text-lg mb-2">Verwijderen</p>
                        <p class="text-[#191D38]/70 text-sm font-semibold mb-4">
                            Dit verwijdert de planning definitief.
                        </p>

                        <form method="POST" action="{{ route('support.planning.destroy', $onboardingRequest) }}"
                              onsubmit="return confirm('Weet je zeker dat je deze planning wilt verwijderen?');">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="px-6 h-11 text-white font-semibold text-sm bg-[#DF2935] hover:bg-[#DF2935]/80 transition duration-300 rounded-full w-full"
                            >
                                Verwijderen
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
