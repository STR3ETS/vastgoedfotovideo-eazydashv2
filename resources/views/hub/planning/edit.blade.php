@extends('hub.layouts.app')

@section('content')
    @php
        use Carbon\Carbon;

        $project = $planningItem->project;
        $title = $project?->title ?: 'Project';
        $client = $project?->client?->name ?: '-';

        $start = $planningItem->start_at ? Carbon::parse($planningItem->start_at) : null;
        $end   = $planningItem->end_at ? Carbon::parse($planningItem->end_at) : null;
    @endphp

    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
            <div class="flex items-start justify-between gap-6 border-b border-gray-200 pb-6 mb-6">
                <div class="min-w-0">
                    <p class="text-[#009AC3] text-xs font-bold mb-2">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Planning bewerken
                    </p>
                    <h1 class="text-[#191D38] font-black text-xl truncate">{{ $title }}</h1>
                    <p class="text-[#191D38]/70 text-sm font-semibold mt-1">Klant: {{ $client }}</p>
                </div>

                <a href="{{ route('support.planning.index', ['section' => 'today', 'date' => ($start ? $start->toDateString() : now()->toDateString())]) }}"
                   class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#191D38] hover:border-[#191D38] group transition duration-300">
                    <i class="fa-solid fa-arrow-left text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                    <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                        Terug
                    </span>
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 px-4 py-3 rounded-xl bg-[#87A878]/15 text-[#191D38] text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-[#DF2935]/10 text-[#191D38] text-sm font-semibold">
                    <p class="font-black mb-1">Er ging iets mis:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ✅ UPDATE FORM (alleen opslaan) --}}
            <form method="POST" action="{{ route('support.planning.update', $planningItem) }}" class="grid grid-cols-2 gap-6">
                @csrf
                @method('PATCH')

                <div class="col-span-2 grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-[#191D38] mb-2">Start</label>
                        <input
                            type="datetime-local"
                            name="start_at"
                            value="{{ old('start_at', $start ? $start->format('Y-m-d\TH:i') : '') }}"
                            class="w-full h-11 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-[#191D38] outline-none focus:border-[#009AC3]"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#191D38] mb-2">Einde</label>
                        <input
                            type="datetime-local"
                            name="end_at"
                            value="{{ old('end_at', $end ? $end->format('Y-m-d\TH:i') : '') }}"
                            class="w-full h-11 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-[#191D38] outline-none focus:border-[#009AC3]"
                            required
                        >
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-[#191D38] mb-2">Fotograaf</label>
                    <select
                        name="assignee_user_id"
                        class="w-full h-11 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-[#191D38] outline-none focus:border-[#009AC3]"
                    >
                        <option value="">Niet toegewezen</option>
                        @foreach($photographers as $p)
                            <option value="{{ $p->id }}" @selected((string)old('assignee_user_id', $planningItem->assignee_user_id) === (string)$p->id)>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-[#191D38] mb-2">Locatie</label>
                    <input
                        type="text"
                        name="location"
                        value="{{ old('location', $planningItem->location) }}"
                        class="w-full h-11 px-4 rounded-xl border border-gray-200 text-sm font-semibold text-[#191D38] outline-none focus:border-[#009AC3]"
                        placeholder="Bijv. straat + plaats"
                    >
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-[#191D38] mb-2">Notities</label>
                    <textarea
                        name="notes"
                        rows="5"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-[#191D38] outline-none focus:border-[#009AC3]"
                        placeholder="Interne notities"
                    >{{ old('notes', $planningItem->notes) }}</textarea>
                </div>

                <div class="col-span-2 flex items-center justify-between pt-2">
                    <button
                        type="submit"
                        class="px-6 h-11 rounded-full bg-[#009AC3] text-white font-black text-sm hover:opacity-90 transition"
                    >
                        Opslaan
                    </button>

                    {{-- ✅ Delete knop submit losse delete form (geen nested form!) --}}
                    <button
                        type="submit"
                        form="deletePlanningItemForm"
                        onclick="return confirm('Weet je zeker dat je deze planning wilt verwijderen?');"
                        class="px-6 h-11 rounded-full border border-gray-200 bg-white text-[#191D38] font-black text-sm hover:bg-[#DF2935] hover:text-white hover:border-[#DF2935] transition"
                    >
                        Verwijderen
                    </button>
                </div>
            </form>

            {{-- ✅ LOSSE DELETE FORM (buiten update form) --}}
            <form id="deletePlanningItemForm" method="POST" action="{{ route('support.planning.destroy', $planningItem) }}" class="hidden">
                @csrf
                @method('DELETE')
            </form>

        </div>
    </div>
@endsection
