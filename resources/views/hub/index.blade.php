@extends('hub.layouts.app')

@section('content')
    <style>
        /* Dunne, subtiele scrollbar zonder pijltjes */
        .custom-scroll {
            scrollbar-width: thin; /* Firefox */
            scrollbar-color: #191D3820 transparent; /* thumb + track */
        }

        /* WebKit (Chrome, Edge, etc.) */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;             /* dun lijntje */
            height: 4px;            /* voor horizontale varianten */
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: transparent; /* geen opvallende track */
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #191D3820; /* jouw kleur */
            border-radius: 9999px;
        }

        /* Pijltjes verbergen */
        .custom-scroll::-webkit-scrollbar-button {
            width: 0;
            height: 0;
            display: none;
        }
    </style>
    @php
        $formatDuration = function (int $seconds): string {
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            return sprintf('%d uur %02d minuten', $h, $m);
        };
    @endphp
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
            <div class="flex-1 min-h-0 w-full flex flex-col xl:flex-row gap-8 min-w-0">
                <div class="flex-1 min-h-0 flex flex-col min-w-0 gap-8">
                    <div class="w-full shrink-0 rounded-2xl p-8 bg-[#009AC3] flex items-center justify-between relative overflow-hidden">
                        <div>
                            <p class="text-sm opacity-80 text-white font-semibold uppercase tracking-[5px] mb-2">Vastgoed Foto Video</p>
                            <h1 class="text-[#fff] font-black text-3xl shrink-0 leading-tight mb-8">Welkom terug, {{ $user->name }}</h1>
                            <a href="#" class="px-6 py-3 text-white font-semibold text-sm bg-[#191D38] hover:bg-[#191D38]/80 transition duration-300 w-full rounded-full text-center">Bekijk mijn planning van vandaag</a>
                        </div>
                        <i class="fa-solid fa-house text-[300px] rotate-[-10deg] absolute z-1 -right-4 text-[#191D38]/10"></i>
                    </div>
                    <div class="flex-1 w-full overflow-hidden flex flex-col min-h-0">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <input
                                type="text"
                                placeholder="Zoeken op titel..."
                                class="h-9 bg-white border border-gray-200 flex items-center px-4 w-[300px] rounded-full text-xs text-[#191D38] font-medium outline-none"
                                >
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                <select
                                    class="h-9 bg-white border border-gray-200 pl-4 pr-10 rounded-full text-xs text-[#191D38] font-medium outline-none appearance-none cursor-pointer"
                                >
                                    <option value="newest">Nieuwste eerst</option>
                                    <option value="oldest">Oudste eerst</option>
                                    <option value="title_asc">Titel A–Z</option>
                                    <option value="title_desc">Titel Z–A</option>
                                    <option value="status">Status</option>
                                </select>
                                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#191D38]/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
                            <div class="grid grid-cols-[2.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                                <p class="text-[#191D38] font-bold text-xs opacity-50">Titel</p>
                                <p class="text-[#191D38] font-bold text-xs opacity-50">Aangemaakt door</p>
                                <p class="text-[#191D38] font-bold text-xs opacity-50">Aangemaakt op</p>
                                <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
                                <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
                            </div>
                        </div>
                        <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl">
                            <div class="px-6 py-5 divide-y divide-[#191D38]/10">
                                <div class="py-3 pt-0 grid grid-cols-[2.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                                    <div class="text-[#191D38] font-semibold text-sm">Lorem ipsum dolor sit amet consectetur.</div>
                                    <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                                    <div class="text-[#191D38] text-sm">05-01-2026</div>
                                    <div class="text-[#2A324B] bg-[#2A324B]/20 text-xs font-semibold rounded-full py-1.5 text-center">Concept</div>
                                    <div class="justify-end text-[#191D38] flex items-center gap-2">
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="py-3 grid grid-cols-[2.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                                    <div class="text-[#191D38] font-semibold text-sm">Lorem ipsum dolor sit amet consectetur.</div>
                                    <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                                    <div class="text-[#191D38] text-sm">05-01-2026</div>
                                    <div class="text-[#87A878] bg-[#87A878]/20 text-xs font-semibold rounded-full py-1.5 text-center">Open</div>
                                    <div class="justify-end text-[#191D38] flex items-center gap-2">
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="py-3 grid grid-cols-[2.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                                    <div class="text-[#191D38] font-semibold text-sm">Lorem ipsum dolor sit amet consectetur.</div>
                                    <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                                    <div class="text-[#191D38] text-sm">05-01-2026</div>
                                    <div class="text-[#DF2935] bg-[#DF2935]/20 text-xs font-semibold rounded-full py-1.5 text-center">Gesloten</div>
                                    <div class="justify-end text-[#191D38] flex items-center gap-2">
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="py-3 pb-0 grid grid-cols-[2.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                                    <div class="text-[#191D38] font-semibold text-sm">Lorem ipsum dolor sit amet consectetur.</div>
                                    <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                                    <div class="text-[#191D38] text-sm">05-01-2026</div>
                                    <div class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold rounded-full py-1.5 text-center">Gearchiveerd</div>
                                    <div class="justify-end text-[#191D38] flex items-center gap-2">
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                        <button class="cursor-pointer">
                                            <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-[#F5EFED70] w-full xl:w-[350px] shrink-0 rounded-2xl p-8 min-w-0 flex flex-col h-full min-h-0">
                    @php
                        $firstName  = strtolower(explode(' ', trim($user->name))[0] ?? '');
                        $userAvatar = "/assets/eazyonline/memojis/{$firstName}.webp";
                    @endphp
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center p-2 mx-auto border-2 border-[#009AC350] relative shrink-0">
                        <div class="{{ isset($activeSession) && $activeSession ? 'hidden' : 'flex' }} w-4 h-4 absolute z-1 right-1 top-1 items-center justify-center">
                            <div class="w-4 h-4 rounded-full bg-red-500 absolute z-2"></div>
                            <div class="w-3 h-3 rounded-full bg-red-500 animate-ping"></div>
                        </div>
                        <div class="{{ isset($activeSession) && $activeSession ? 'flex' : 'hidden' }} w-4 h-4 absolute z-1 right-1 top-1 items-center justify-center">
                            <div class="w-4 h-4 rounded-full bg-green-500 absolute z-2"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500 animate-ping"></div>
                        </div>
                        <img src="{{ $userAvatar }}" alt="{{ $user->name }}">
                    </div>
                    <h2 class="text-[#191D38] font-bold text-base text-center mt-4 mb-2 shrink-0">{{ $user->name }} 👋</h2>
                    <p class="text-xs text-[#191D38] font-semibold text-center mb-6 shrink-0">Laten we weer gaan knallen vandaag!</p>
                    <div class="w-full p-6 bg-white rounded-2xl flex flex-col mb-2 shrink-0">
                        <h2 class="text-[#191D38] font-semibold text-sm shrink-0">Actieve projecten</h2>
                        <p class="text-[#191D38] font-black text-3xl shrink-0 mb-1">40</p>
                        <h3 class="text-[#87A878] font-semibold text-xs shrink-0">+20% sinds vorige week</h3>
                    </div>
                    <div class="w-full p-6 bg-white rounded-2xl flex flex-col mb-2 shrink-0">
                        <h2 class="text-[#191D38] font-semibold text-sm shrink-0">Voltooide projecten</h2>
                        <p class="text-[#191D38] font-black text-3xl shrink-0 mb-1">40</p>
                        <h3 class="text-[#87A878] font-semibold text-xs shrink-0">+20% sinds vorige week</h3>
                    </div>
                    <div class="w-full flex-1 min-h-0 p-6 bg-white rounded-2xl flex flex-col">
                        <h2 class="text-[#191D38] font-semibold text-sm shrink-0 mb-2">Rit meldingen</h2>
                        {{-- optioneel: scrollbare inhoud --}}
                        <div class="flex-1 min-h-0 overflow-auto bg-[#F5EFED70] rounded-2xl p-6">
                            {{-- content --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var el = document.getElementById('clocked-in-duration');
            if (!el) return;

            var seconds = parseInt(el.dataset.seconds || '0', 10) || 0;

            function render() {
                var h = Math.floor(seconds / 3600);
                var m = Math.floor((seconds % 3600) / 60);
                el.textContent = h + 'u ' + (m < 10 ? '0' + m : m + 'm');
            }

            // Eerste render
            render();

            // Elke seconde +1s -> voelt echt live
            setInterval(function () {
                seconds++;
                render();
            }, 1000);
        })();
    </script>
@endsection