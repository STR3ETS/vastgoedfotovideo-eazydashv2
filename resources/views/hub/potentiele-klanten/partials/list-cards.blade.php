<div
    class="grid grid-cols-3 gap-8 mt-3"
    x-data="{ activeId: @json(optional($aanvragen->first())->id) }"
>
    {{-- LINKERBLOK: LIJST --}}
    <div class="p-8 bg-[#f3f8f8] rounded-4xl">
        <div class="grid grid-cols-8 gap-2 pb-4 border-b border-b-gray-200 mb-4">
            <p class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none" @click="sortByFact()">
                Bedrijfsnaam
            </p>
            <p class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none" @click="sortByCompany()">
                Gekozen voor
            </p>
            <p class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none" @click="sortByContact()">
                Contactpersoon
            </p>
            <p class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none" @click="sortByVervalDate()">
                Telefoonnummer
            </p>
            <p class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none" @click="sortByOfferteDate()">
                E-mailadres
            </p>
            <p class="text-xs font-bold text-[#215558]">
                Aangevraagd op
            </p>
            <p class="text-xs font-bold text-[#215558]">
                Status
            </p>
            <p class="text-xs font-bold text-[#215558] text-right">
                Acties
            </p>
        </div>

        <div class="w-full flex flex-col gap-2 divide-y divide-gray-200/50">
            @forelse($aanvragen as $aanvraag)
                @php
                    $valueToLabel = $statusByValue ?? [];

                    $allowedStatuses = ['prospect', 'contact', 'intake', 'dead', 'lead'];
                    $rawStatus = $aanvraag->status;

                    $currentValue = in_array($rawStatus, $allowedStatuses, true)
                        ? $rawStatus
                        : 'prospect';

                    $currentLabel = $valueToLabel[$currentValue] ?? 'Prospect';

                    $badgeColors = [
                        'prospect' => [
                            'bg'   => 'bg-[#b3e6ff]',
                            'text' => 'text-[#0f6199]',
                        ],
                        'contact' => [
                            'bg'   => 'bg-[#C2F0D5]',
                            'text' => 'text-[#20603a]',
                        ],
                        'intake' => [
                            'bg'   => 'bg-[#ffdfb3]',
                            'text' => 'text-[#a0570f]',
                        ],
                        'dead' => [
                            'bg'   => 'bg-[#ffb3b3]',
                            'text' => 'text-[#8a2a2d]',
                        ],
                        'lead' => [
                            'bg'   => 'bg-[#e0d4ff]',
                            'text' => 'text-[#4c2a9b]',
                        ],
                    ];

                    $badge = $badgeColors[$currentValue] ?? [
                        'bg'   => 'bg-slate-100',
                        'text' => 'text-slate-700',
                    ];

                    $choiceMap = [
                        'new'   => __('potentiele_klanten.choices.new'),
                        'renew' => __('potentiele_klanten.choices.renew'),
                    ];

                    $choiceTitle = $choiceMap[$aanvraag->choice]
                        ?? __('potentiele_klanten.choices.default');
                @endphp

                <button
                    type="button"
                    class="w-full grid grid-cols-8 items-center gap-2 py-2 px-2 rounded-2xl text-left transition border border-transparent"
                    :class="activeId === {{ $aanvraag->id }}
                        ? 'bg-white shadow-sm border-[#0F9B9F]/30'
                        : 'hover:bg-white/80'"
                    @click="activeId = {{ $aanvraag->id }}"
                >
                    <p class="text-sm font-medium text-[#215558] truncate">
                        {{ $aanvraag->company }}
                    </p>
                    <span class="truncate text-sm font-bold text-[#215558]">
                        {{ $choiceTitle }}
                    </span>
                    <p class="text-sm font-medium text-[#215558] truncate">
                        {{ $aanvraag->contactName }}
                    </p>
                    <p class="text-sm font-medium text-[#215558] truncate">
                        {{ $aanvraag->contactPhone }}
                    </p>
                    <p class="text-sm font-medium text-[#215558] truncate">
                        {{ $aanvraag->contactEmail }}
                    </p>
                    <p class="text-sm font-medium text-[#215558] truncate">
                        {{ $aanvraag->created_at }}
                    </p>
                    <div class="flex items-center">
                        <span class="inline-block text-xs px-2.5 py-0.5 font-semibold rounded-full {{ $badge['bg'] }} {{ $badge['text'] }}">
                            {{ $currentLabel }}
                        </span>
                    </div>
                    <div class="flex items-center justify-end">
                        <i class="fa-solid fa-chevron-right text-[#215558] fa-xs"></i>
                    </div>
                </button>
            @empty
                <div class="text-[#215558] text-xs font-semibold opacity-75">
                    {{ __('potentiele_klanten.list.no_requests') }}
                </div>
            @endforelse
        </div>
    </div>

    {{-- RECHTERBLOK: DETAILPANEEL --}}
    <div class="col-span-2 p-8 bg-[#f3f8f8] rounded-4xl">
        @foreach($aanvragen as $aanvraag)
            <div x-show="activeId === {{ $aanvraag->id }}" x-transition>
                @include('hub.potentiele-klanten.partials.card', [
                    'aanvraag'      => $aanvraag,
                    'statusByValue' => $statusByValue ?? [],
                ])
            </div>
        @endforeach
    </div>
</div>
