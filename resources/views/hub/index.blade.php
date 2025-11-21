@extends('hub.layouts.app')

@section('content')
    {{-- Linker kolom – kan later gevuld worden met widgets / stats --}}
    <div class="col-span-1 w-full p-4 h-full bg-white rounded-xl">
        {{-- Bijvoorbeeld: korte stats, snelle links, etc. --}}
    </div>

    {{-- Rechter kolom – Offertes overzicht --}}
    <div class="col-span-4 flex-1 min-h-0">
        <div class="w-full p-4 bg-white rounded-xl h-full min-h-0"
            x-data="{
                openOverzichtSection: true,
                openStatistiekenSection: true,
            }">
            <div class="h-full min-h-0 overflow-y-auto pr-3">
                <div class="w-full flex items-center gap-2 min-w-0 mb-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Overzicht
                    </h3>
                    <button type="button"
                        class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                        @click="openOverzichtSection = !openOverzichtSection">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                        :class="openOverzichtSection ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>
                <div x-show="openOverzichtSection" x-transition>
                    @if(isset($offertes) && $offertes->count())
                        @php
                            // Basisstructuur voor de badges
                            $summary = [
                                'concept' => [
                                    'label' => 'Offertes in concept:',
                                    'bg'    => 'bg-cyan-500',
                                    'count' => 0,
                                    'sum'   => 0,
                                ],
                                'pending' => [
                                    'label' => 'Offertes nog niet getekend:',
                                    'bg'    => 'bg-orange-500',
                                    'count' => 0,
                                    'sum'   => 0,
                                ],
                                'signed' => [
                                    'label' => 'Offertes getekend:',
                                    'bg'    => 'bg-green-500',
                                    'count' => 0,
                                    'sum'   => 0,
                                ],
                                'expired' => [
                                    'label' => 'Offertes verlopen:',
                                    'bg'    => 'bg-red-500',
                                    'count' => 0,
                                    'sum'   => 0,
                                ],
                                'total' => [
                                    'label' => 'Totaal verstuurde offertes:',
                                    'bg'    => 'bg-gray-500',
                                    'count' => 0,
                                    'sum'   => 0,
                                ],
                            ];
    
                            $perMonth = [];
    
                            $now = now();
    
                            foreach ($offertes as $offerte) {
                                /** @var \App\Models\Offerte $offerte */
                                $offerteDate = $offerte->created_at ?? $now;
                                $vervalDatum = $offerteDate->copy()->addMonthNoOverflow();
    
                                // Zelfde status-logica als in de tabel
                                if ($offerte->status === 'draft') {
                                    $statusKey = 'concept';
                                } elseif (!empty($offerte->signed_at)) {
                                    $statusKey = 'signed';
                                } elseif ($vervalDatum->isPast()) {
                                    $statusKey = 'expired';
                                } else {
                                    $statusKey = 'pending';
                                }
    
                                // Investment ophalen (override > generated)
                                $investment = data_get($offerte->content_overrides, 'investment')
                                    ?? data_get($offerte->generated, 'investment');
    
                                $setupPrice = 0;
    
                                if (is_array($investment)) {
                                    // 1) probeer setup_price_eur (numeriek veld)
                                    $setupPrice = (float) data_get($investment, 'setup_price_eur', 0);
    
                                    // 2) zo niet, parse total_setup_amount string "€ 1.500,- eenmalig"
                                    if (!$setupPrice) {
                                        $setupFormatted = data_get($investment, 'total_setup_amount');
                                        if (is_string($setupFormatted)) {
                                            $numeric = preg_replace('/[^\d,\.]/', '', $setupFormatted); // alleen cijfers, . en ,
                                            $numeric = str_replace('.', '', $numeric); // duizendtallen weg
                                            $numeric = str_replace(',', '.', $numeric); // komma -> punt
                                            $setupPrice = (float) $numeric;
                                        }
                                    }
                                }
    
                                // Naar status + totaal sommeren
                                $summary[$statusKey]['count']++;
                                $summary[$statusKey]['sum'] += $setupPrice;
    
                                $summary['total']['count']++;
                                $summary['total']['sum'] += $setupPrice;
    
                                // --- Per maand aggregeren voor de lijn-grafieken ---
                                $monthKey = $offerteDate->format('Y-m'); // bijv. "2025-01"
    
                                if (! isset($perMonth[$monthKey])) {
                                    $perMonth[$monthKey] = [
                                        'label'        => $offerteDate->format('M Y'), // bijv. "jan 2025"
                                        'total_count'  => 0,
                                        'signed_count' => 0,
                                        'sum_setup'    => 0,
                                    ];
                                }
    
                                $perMonth[$monthKey]['total_count']++;
                                $perMonth[$monthKey]['sum_setup'] += $setupPrice;
    
                                if ($statusKey === 'signed') {
                                    $perMonth[$monthKey]['signed_count']++;
                                }
                            }
    
                            ksort($perMonth);
    
                            // Kleine helper om netjes als € x.xxx,- te tonen
                            $formatAmount = function (float $amount): string {
                                if ($amount <= 0) {
                                    return '€ 0,-';
                                }
    
                                return '€ ' . number_format($amount, 0, ',', '.') . ',-';
                            };
                        @endphp
                        <div class="overflow-x-auto"
                            x-data="{
                                activeStatus: 'all',
                                searchTerm: '',
                                sortKey: null,
                                sortDir: null,
    
                                matches(row) {
                                    if (!this.searchTerm) return true;
                                    const term = this.searchTerm.toLowerCase();
                                    return (row.dataset.fact || '').toLowerCase().includes(term)
                                        || (row.dataset.company || '').toLowerCase().includes(term)
                                        || (row.dataset.contact || '').toLowerCase().includes(term);
                                },
    
                                sortByCompany() {
                                    // 1e klik: A → Z
                                    if (this.sortKey !== 'company') {
                                        this.sortKey = 'company';
                                        this.sortDir = 'asc';
                                    }
                                    // 2e klik: Z → A
                                    else if (this.sortDir === 'asc') {
                                        this.sortDir = 'desc';
                                    }
                                    // 3e klik: reset naar originele volgorde
                                    else {
                                        this.sortKey = null;
                                        this.sortDir = null;
                                    }
    
                                    this.applySort();
                                },
    
                                sortByFact() {
                                    if (this.sortKey !== 'fact') {
                                        this.sortKey = 'fact';
                                        this.sortDir = 'asc';
                                    } else if (this.sortDir === 'asc') {
                                        this.sortDir = 'desc';
                                    } else {
                                        this.sortKey = null;
                                        this.sortDir = null;
                                    }
                                    this.applySort();
                                },
    
                                sortByContact() {
                                    if (this.sortKey !== 'contact') {
                                        this.sortKey = 'contact';
                                        this.sortDir = 'asc';
                                    } else if (this.sortDir === 'asc') {
                                        this.sortDir = 'desc';
                                    } else {
                                        this.sortKey = null;
                                        this.sortDir = null;
                                    }
                                    this.applySort();
                                },
    
                                sortByOfferteDate() {
                                    if (this.sortKey !== 'offerte_date') {
                                        this.sortKey = 'offerte_date';
                                        this.sortDir = 'asc';
                                    } else if (this.sortDir === 'asc') {
                                        this.sortDir = 'desc';
                                    } else {
                                        this.sortKey = null;
                                        this.sortDir = null;
                                    }
                                    this.applySort();
                                },
    
                                sortByVervalDate() {
                                    if (this.sortKey !== 'verval_date') {
                                        this.sortKey = 'verval_date';
                                        this.sortDir = 'asc';
                                    } else if (this.sortDir === 'asc') {
                                        this.sortDir = 'desc';
                                    } else {
                                        this.sortKey = null;
                                        this.sortDir = null;
                                    }
                                    this.applySort();
                                },
    
                                applySort() {
                                    const tbody = this.$refs.offersBody;
                                    if (!tbody) return;
    
                                    const rows = Array.from(tbody.children);
    
                                    // Reset → terug naar oorspronkelijke index
                                    if (!this.sortKey || !this.sortDir) {
                                        rows.sort((a, b) => {
                                            return (parseInt(a.dataset.index) || 0) - (parseInt(b.dataset.index) || 0);
                                        });
                                    } else if (this.sortKey === 'company') {
                                        rows.sort((a, b) => {
                                            const av = (a.dataset.company || '').toLowerCase();
                                            const bv = (b.dataset.company || '').toLowerCase();
                                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                                            return 0;
                                        });
                                    } else if (this.sortKey === 'fact') {
                                        rows.sort((a, b) => {
                                            const av = (a.dataset.fact || '').toLowerCase();
                                            const bv = (b.dataset.fact || '').toLowerCase();
                                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                                            return 0;
                                        });
                                    } else if (this.sortKey === 'contact') {
                                        rows.sort((a, b) => {
                                            const av = (a.dataset.contact || '').toLowerCase();
                                            const bv = (b.dataset.contact || '').toLowerCase();
                                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                                            return 0;
                                        });
                                    } else if (this.sortKey === 'offerte_date') {
                                        rows.sort((a, b) => {
                                            const av = (a.dataset.offertedate || '');
                                            const bv = (b.dataset.offertedate || '');
                                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                                            return 0;
                                        });
                                    } else if (this.sortKey === 'verval_date') {
                                        rows.sort((a, b) => {
                                            const av = (a.dataset.vervaldate || '');
                                            const bv = (b.dataset.vervaldate || '');
                                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                                            return 0;
                                        });
                                    }
    
                                    rows.forEach(row => tbody.appendChild(row));
                                }
                            }">
                            <div class="flex flex-wrap items-center justify-between">
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Concept --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['concept']['bg'] }} text-white">
                                        {{ $summary['concept']['label'] }}
                                        &nbsp;&nbsp;
                                        {{ $summary['concept']['count'] }}
                                        &nbsp;&nbsp;≈&nbsp;&nbsp;
                                        {{ $formatAmount($summary['concept']['sum']) }}
                                    </span>
                                    {{-- Nog niet getekend --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['pending']['bg'] }} text-white">
                                        {{ $summary['pending']['label'] }}
                                        &nbsp;&nbsp;
                                        {{ $summary['pending']['count'] }}
                                        &nbsp;&nbsp;≈&nbsp;&nbsp;
                                        {{ $formatAmount($summary['pending']['sum']) }}
                                    </span>
                                    {{-- Getekend --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['signed']['bg'] }} text-white">
                                        {{ $summary['signed']['label'] }}
                                        &nbsp;&nbsp;
                                        {{ $summary['signed']['count'] }}
                                        &nbsp;&nbsp;≈&nbsp;&nbsp;
                                        {{ $formatAmount($summary['signed']['sum']) }}
                                    </span>
                                    {{-- Verlopen --}}
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['expired']['bg'] }} text-white">
                                        {{ $summary['expired']['label'] }}
                                        &nbsp;&nbsp;
                                        {{ $summary['expired']['count'] }}
                                        &nbsp;&nbsp;≈&nbsp;&nbsp;
                                        {{ $formatAmount($summary['expired']['sum']) }}
                                    </span>
                                </div>
                                {{-- Totaal --}}
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $summary['total']['bg'] }} text-white">
                                    {{ $summary['total']['label'] }}
                                    &nbsp;&nbsp;&nbsp;
                                    {{ $summary['total']['count'] }}
                                    &nbsp;&nbsp;≈&nbsp;&nbsp;
                                    {{ $formatAmount($summary['total']['sum']) }}
                                </span>
                            </div>
    
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    {{-- Concept --}}
                                    <button
                                        type="button"
                                        @click="activeStatus = activeStatus === 'concept' ? 'all' : 'concept'"
                                        :class="[
                                            'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                            activeStatus === 'concept'
                                                ? 'bg-[#b3e6ff] border-[#0f6199] text-[#0f6199]'
                                                : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-700"></span>
                                        <span>Concept</span>
                                    </button>
    
                                    {{-- Nog niet ondertekend --}}
                                    <button
                                        type="button"
                                        @click="activeStatus = activeStatus === 'pending' ? 'all' : 'pending'"
                                        :class="[
                                            'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                            activeStatus === 'pending'
                                                ? 'bg-[#ffdfb3] border-[#a0570f] text-[#a0570f]'
                                                : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#a0570f]"></span>
                                        <span>Nog niet ondertekend</span>
                                    </button>
    
                                    {{-- Getekend --}}
                                    <button
                                        type="button"
                                        @click="activeStatus = activeStatus === 'signed' ? 'all' : 'signed'"
                                        :class="[
                                            'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                            activeStatus === 'signed'
                                                ? 'bg-[#C2F0D5] border-[#20603a] text-[#20603a]'
                                                : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#20603a]"></span>
                                        <span>Getekend</span>
                                    </button>
    
                                    {{-- Verlopen --}}
                                    <button
                                        type="button"
                                        @click="activeStatus = activeStatus === 'expired' ? 'all' : 'expired'"
                                        :class="[
                                            'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                            activeStatus === 'expired'
                                                ? 'bg-[#ffb3b3] border-[#8a2a2d] text-[#8a2a2d]'
                                                : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                        ]"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#8a2a2d]"></span>
                                        <span>Verlopen</span>
                                    </button>
                                </div>
    
                                <input
                                    type="text"
                                    x-model="searchTerm"
                                    class="w-[300px] p-2 text-xs text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                                    placeholder="Zoek op factuur, bedrijf of contactpersoon"
                                >
                            </div>
    
                            <div class="w-full p-4 border border-gray-200 rounded-xl mt-3">
                                <div class="grid grid-cols-9 gap-2 pb-4 border-b border-b-gray-200">
                                    <p
                                        class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                        @click="sortByFact()"
                                    >
                                        Factuurnummer
                                        <span class="inline-flex w-3 h-3 items-center justify-center">
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                                x-show="sortKey !== 'fact' || !sortDir"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px]"
                                                x-show="sortKey === 'fact' && sortDir === 'asc'"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-up text-[9px]"
                                                x-show="sortKey === 'fact' && sortDir === 'desc'"
                                            ></i>
                                        </span>
                                    </p>
                                    <p
                                        class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                        @click="sortByCompany()"
                                    >
                                        Bedrijfsnaam
                                        <span class="inline-flex w-3 h-3 items-center justify-center">
                                            {{-- default / reset --}}
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                                x-show="sortKey !== 'company' || !sortDir"
                                            ></i>
                                            {{-- A → Z --}}
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px]"
                                                x-show="sortKey === 'company' && sortDir === 'asc'"
                                            ></i>
                                            {{-- Z → A --}}
                                            <i
                                                class="fa-solid fa-chevron-up text-[9px]"
                                                x-show="sortKey === 'company' && sortDir === 'desc'"
                                            ></i>
                                        </span>
                                    </p>
                                    <p
                                        class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                        @click="sortByContact()"
                                    >
                                        Contactpersoon
                                        <span class="inline-flex w-3 h-3 items-center justify-center">
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                                x-show="sortKey !== 'contact' || !sortDir"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px]"
                                                x-show="sortKey === 'contact' && sortDir === 'asc'"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-up text-[9px]"
                                                x-show="sortKey === 'contact' && sortDir === 'desc'"
                                            ></i>
                                        </span>
                                    </p>
                                    <p
                                        class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                        @click="sortByOfferteDate()"
                                    >
                                        Offertedatum
                                        <span class="inline-flex w-3 h-3 items-center justify-center">
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                                x-show="sortKey !== 'offerte_date' || !sortDir"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px]"
                                                x-show="sortKey === 'offerte_date' && sortDir === 'asc'"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-up text-[9px]"
                                                x-show="sortKey === 'offerte_date' && sortDir === 'desc'"
                                            ></i>
                                        </span>
                                    </p>
                                    <p
                                        class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                        @click="sortByVervalDate()"
                                    >
                                        Vervaldatum
                                        <span class="inline-flex w-3 h-3 items-center justify-center">
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                                x-show="sortKey !== 'verval_date' || !sortDir"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-down text-[9px]"
                                                x-show="sortKey === 'verval_date' && sortDir === 'asc'"
                                            ></i>
                                            <i
                                                class="fa-solid fa-chevron-up text-[9px]"
                                                x-show="sortKey === 'verval_date' && sortDir === 'desc'"
                                            ></i>
                                        </span>
                                    </p>
                                    <p class="text-xs font-bold text-[#215558]">Totaal</p>
                                    <p class="text-xs font-bold text-[#215558]">Status</p>
                                    <p class="text-xs font-bold text-[#215558]">Ondertekend op</p>
                                    <p class="text-xs font-bold text-[#215558] text-right">Acties</p>
                                </div>
    
                                <div class="divide-gray-200 flex flex-col gap-2 pt-4" x-ref="offersBody">
                                    @foreach($offertes as $offerte)
                                        @php
                                            /** @var \App\Models\Offerte $offerte */
                                            $offerteDate = $offerte->created_at ?? now();
                                            $offerteNummer = $offerte->number
                                                ?? ('OF-' . $offerteDate->format('Ym') . str_pad($offerte->id ?? 1, 4, '0', STR_PAD_LEFT));
                                            $vervalDatum = $offerteDate->copy()->addMonthNoOverflow();
    
                                            // STATUS-logica + key voor filter
                                            if ($offerte->status === 'draft') {
                                                $statusKey     = 'concept';
                                                $statusLabel   = 'Concept';
                                                $statusClasses = 'bg-cyan-100 text-cyan-700';
                                            } elseif (!empty($offerte->signed_at)) {
                                                $statusKey     = 'signed';
                                                $statusLabel   = 'Getekend';
                                                $statusClasses = 'bg-green-100 text-green-700';
                                            } elseif ($vervalDatum->isPast()) {
                                                $statusKey     = 'expired';
                                                $statusLabel   = 'Verlopen';
                                                $statusClasses = 'bg-red-100 text-red-700';
                                            } else {
                                                $statusKey     = 'pending';
                                                $statusLabel   = 'Te ondertekenen';
                                                $statusClasses = 'bg-orange-100 text-orange-700';
                                            }
    
                                            // Bedrijfsnaam
                                            $companyName = optional($offerte->project)->company
                                                ?? $offerte->company_name
                                                ?? 'Onbekend bedrijf';
    
                                            // Investment override (eerst overrides, dan fallback naar generated)
                                            $investment = data_get($offerte->content_overrides, 'investment')
                                                ?? data_get($offerte->generated, 'investment');
    
                                            $setupPrice   = 0;
                                            $monthlyPrice = 0;
    
                                            if (is_array($investment)) {
                                                $setupPrice = (float) data_get($investment, 'setup_price_eur', 0);
    
                                                if (!$setupPrice) {
                                                    $setupFormatted = data_get($investment, 'total_setup_amount');
                                                    if (is_string($setupFormatted)) {
                                                        $numeric = preg_replace('/[^\d,\.]/', '', $setupFormatted);
                                                        $numeric = str_replace('.', '', $numeric);
                                                        $numeric = str_replace(',', '.', $numeric);
                                                        $setupPrice = (float) $numeric;
                                                    }
                                                }
    
                                                $monthlyPrice = (float) data_get($investment, 'monthly_price_eur', 0);
    
                                                if (!$monthlyPrice) {
                                                    $monthlyFormatted = data_get($investment, 'total_monthly_amount');
                                                    if (is_string($monthlyFormatted)) {
                                                        $numeric = preg_replace('/[^\d,\.]/', '', $monthlyFormatted);
                                                        $numeric = str_replace('.', '', $numeric);
                                                        $numeric = str_replace(',', '.', $numeric);
                                                        $monthlyPrice = (float) $numeric;
                                                    }
                                                }
                                            }
                                        @endphp
    
                                        <div
                                            class="grid grid-cols-9 gap-2 items-center text-sm text-[#215558] font-medium"
                                            data-index="{{ $loop->index }}"
                                            data-fact="{{ $offerteNummer }}"
                                            data-company="{{ $companyName }}"
                                            data-contact="{{ $offerte->project->contact_name }}"
                                            data-offertedate="{{ $offerteDate->format('Y-m-d') }}"
                                            data-vervaldate="{{ $vervalDatum->format('Y-m-d') }}"
                                            x-show="(activeStatus === 'all' || activeStatus === '{{ $statusKey }}') && matches($el)"
                                            x-cloak
                                        >
                                            <p>{{ $offerteNummer }}</p>
                                            <p class="font-bold">{{ $companyName }}</p>
                                            <p>{{ $offerte->project->contact_name }}</p>
                                            <p>{{ $offerteDate->format('d-m-Y') }}</p>
                                            <p>{{ $vervalDatum->format('d-m-Y') }}</p>
                                            <p>
                                                @if($setupPrice || $monthlyPrice)
                                                    @if($setupPrice)
                                                        {{ $formatAmount($setupPrice) }} eenmalig
                                                    @endif
    
                                                    @if($setupPrice && $monthlyPrice)
                                                        <br>
                                                    @endif
    
                                                    @if($monthlyPrice)
                                                        {{ $formatAmount($monthlyPrice) }} per maand
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Nog geen bedrag</span>
                                                @endif
                                            </p>
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClasses }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-400">
                                                    Nog niet ondertekend
                                                </span>
                                            </div>
                                            <div class="flex items-center justify-end">
                                                <button type="button"
                                                        class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer">
                                                    <i class="fa-solid fa-up-right-from-square text-[#215558] text-xs"></i>
                                                    <div
                                                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                                                            opacity-0 invisible translate-y-1 pointer-events-none
                                                            group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                                                            transition-all duration-200 ease-out z-10">
                                                        <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                                                            Bekijk offerte
                                                        </p>
                                                    </div>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-gray-500">
                            Er zijn nog geen offertes gevonden.
                        </p>
                    @endif
                </div>   
                <div class="w-full flex items-center gap-2 min-w-0 pt-3 border-t border-t-gray-200 mt-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Statistieken
                    </h3>
                    <button type="button"
                        class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                        @click="openStatistiekenSection = !openStatistiekenSection">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                        :class="openStatistiekenSection ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>           
                <div 
                    x-show="openStatistiekenSection" 
                    x-transition 
                    class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="grid gap-4">
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#215558] mb-4 text-center">
                                Verdeling offertes per status (aantal)
                            </p>
                            <canvas id="offertesStatusPie" class="w-full max-h-[150px]"></canvas>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#215558] mb-4 text-center">
                                Conversie: getekend vs totaal
                            </p>
                            <canvas id="offertesConversionDonut" class="w-full max-h-[150px]"></canvas>
                        </div>
                    </div>
                    <div class="grid gap-4">
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#215558] mb-4 text-center">
                                Totaal eenmalige investering per status
                            </p>
                            <canvas id="offertesStatusAmountBar" class="w-full max-h-[150px]"></canvas>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#215558] mb-4 text-center">
                                Gemiddelde eenmalige investering per status
                            </p>
                            <canvas id="offertesStatusAvgBar" class="w-full max-h-[150px]"></canvas>
                        </div>
                    </div>
                    <div class="col-span-2 grid gap-4">
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#215558] mb-4 text-center">
                                Totaal eenmalige investering per maand
                            </p>
                            <canvas id="offertesSetupPerMonthLine" class="w-full max-h-[150px]"></canvas>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <p class="text-xs font-bold text-[#215558] mb-4 text-center">
                                Conversie per maand (in %)
                            </p>
                            <canvas id="offertesConversionPerMonthLine" class="w-full max-h-[150px]"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- Chart.js via CDN (een keer laden is genoeg, haal weg als je 'm al globaal hebt) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const summary = @json($summary);
        const statusOrder = ['concept', 'pending', 'signed', 'expired'];

        const perMonth = @json($perMonth ?? []);
        const perMonthKeys = Object.keys(perMonth);
        const perMonthLabels = perMonthKeys.map(key => perMonth[key].label);
        const perMonthTotals = perMonthKeys.map(key => perMonth[key].sum_setup || 0);
        const perMonthConversion = perMonthKeys.map(key => {
            const total  = perMonth[key].total_count  || 0;
            const signed = perMonth[key].signed_count || 0;
            return total ? (signed / total) * 100 : 0;
        });

        const labelMap = {
            concept: 'Concept',
            pending: 'Nog niet ondertekend',
            signed: 'Getekend',
            expired: 'Verlopen',
        };

        const labels = statusOrder.map(key => labelMap[key] || key);
        const counts = statusOrder.map(key => (summary[key] && summary[key].count) ? summary[key].count : 0);
        const sums   = statusOrder.map(key => (summary[key] && summary[key].sum) ? summary[key].sum : 0);

        // ===== Taartdiagram: aantallen per status =====
        const pieEl = document.getElementById('offertesStatusPie');
        if (pieEl && window.Chart) {
            const totalCount = counts.reduce((acc, v) => acc + v, 0);

            new Chart(pieEl, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: counts,
                        backgroundColor: [
                            '#06b6d4', // concept  (cyan → bg-cyan-500)
                            '#f97316', // pending  (oranje → bg-orange-500)
                            '#22c55e', // signed   (groen → bg-green-500)
                            '#ef4444', // expired  (rood → bg-red-500)
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false, // labels onder de taart uit
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const value = ctx.raw || 0;
                                    const pct = totalCount ? ((value / totalCount) * 100).toFixed(1) : 0;
                                    return `${ctx.label}: ${value} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '55%',
                }
            });
        }

        // ===== Balkdiagram: totale eenmalige investering per status =====
        const amountBarEl = document.getElementById('offertesStatusAmountBar');
        if (amountBarEl && window.Chart) {
            new Chart(amountBarEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: sums,
                        backgroundColor: [
                            '#06b6d4', // concept
                            '#f97316', // pending
                            '#22c55e', // signed
                            '#ef4444', // expired
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const value = ctx.raw || 0;
                                    return '€ ' + value.toLocaleString('nl-NL', { maximumFractionDigits: 0 });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (val) {
                                    return '€ ' + val.toLocaleString('nl-NL', { maximumFractionDigits: 0 });
                                }
                            }
                        }
                    }
                }
            });
        }

        // ===== Donut: conversie getekend vs totaal =====
        const conversionEl = document.getElementById('offertesConversionDonut');
        if (conversionEl && window.Chart) {
            const totalCount = counts.reduce((acc, v) => acc + v, 0);
            const signedIndex = statusOrder.indexOf('signed');
            const signedCount = signedIndex >= 0 ? (counts[signedIndex] || 0) : 0;
            const otherCount  = Math.max(totalCount - signedCount, 0);

            new Chart(conversionEl, {
                type: 'doughnut',
                data: {
                    labels: ['Getekend', 'Overig'],
                    datasets: [{
                        data: [signedCount, otherCount],
                        backgroundColor: ['#22c55e', '#e5e7eb'], // groen + lichtgrijs
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false, // labels onder de donut uit
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const value = ctx.raw || 0;
                                    const pct = totalCount ? ((value / totalCount) * 100).toFixed(1) : 0;
                                    return `${ctx.label}: ${value} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '55%',
                }
            });
        }

        // ===== Balkdiagram: gemiddelde eenmalige investering per status =====
        const avgBarEl = document.getElementById('offertesStatusAvgBar');
        if (avgBarEl && window.Chart) {
            const avgs = sums.map((sum, idx) => {
                const count = counts[idx] || 0;
                return count ? (sum / count) : 0;
            });

            new Chart(avgBarEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: avgs,
                        backgroundColor: [
                            '#06b6d4', // concept
                            '#f97316', // pending
                            '#22c55e', // signed
                            '#ef4444', // expired
                        ],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const value = ctx.raw || 0;
                                    return '€ ' + value.toLocaleString('nl-NL', {
                                        maximumFractionDigits: 0,
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (val) {
                                    return '€ ' + val.toLocaleString('nl-NL', { maximumFractionDigits: 0 });
                                }
                            }
                        }
                    }
                }
            });
        }

        // ===== Lijn: totaal eenmalige investering per maand =====
        const setupPerMonthEl = document.getElementById('offertesSetupPerMonthLine');
        if (setupPerMonthEl && window.Chart && perMonthKeys.length) {
            new Chart(setupPerMonthEl, {
                type: 'line',
                data: {
                    labels: perMonthLabels,
                    datasets: [{
                        data: perMonthTotals,
                        tension: 0.3,
                        fill: false,
                        borderColor: '#0f9b9f',
                        pointRadius: 2,
                    }],
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const value = ctx.raw || 0;
                                    return '€ ' + value.toLocaleString('nl-NL', {
                                        maximumFractionDigits: 0,
                                    });
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (val) {
                                    return '€ ' + val.toLocaleString('nl-NL', {
                                        maximumFractionDigits: 0,
                                    });
                                },
                            },
                        },
                    },
                },
            });
        }

        // ===== Lijn: conversie per maand (in %) =====
        const conversionPerMonthEl = document.getElementById('offertesConversionPerMonthLine');
        if (conversionPerMonthEl && window.Chart && perMonthKeys.length) {
            new Chart(conversionPerMonthEl, {
                type: 'line',
                data: {
                    labels: perMonthLabels,
                    datasets: [{
                        data: perMonthConversion,
                        tension: 0.3,
                        fill: false,
                        borderColor: '#22c55e',
                        pointRadius: 2,
                    }],
                },
                options: {
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const value = ctx.raw || 0;
                                    return value.toFixed(1) + ' %';
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function (val) {
                                    return val + ' %';
                                },
                            },
                        },
                    },
                },
            });
        }
    });
</script>