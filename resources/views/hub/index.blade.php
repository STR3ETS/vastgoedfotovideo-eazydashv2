@extends('hub.layouts.app')

@section('content')
    <style>
        /* Dunne, subtiele scrollbar zonder pijltjes */
        .custom-scroll {
            scrollbar-width: thin; /* Firefox */
            scrollbar-color: #21555820 transparent; /* thumb + track */
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
            background-color: #21555820; /* jouw kleur */
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
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 flex flex-col">
            <div class="flex-1 min-h-0 w-full flex gap-8">
                <div class="flex-1 min-h-0 flex flex-col">
                    <div class="w-full shrink-0 rounded-4xl p-8 bg-[#0F9B9F] flex items-center justify-between relative overflow-hidden">
                        <div>
                            <p class="text-sm opacity-80 text-white font-semibold uppercase tracking-[5px] mb-2">Update</p>
                            <h1 class="text-[#fff] font-bold text-3xl shrink-0 leading-tight mb-8">EazyDash V2.0<br>Nieuwe layout. Nieuwe mogelijkheden.</h1>
                            <a href="#" class="px-6 py-3 text-white font-semibold text-sm bg-[#215558] hover:bg-[#215558]/80 transition duration-300 w-full rounded-full text-center">Lees alles over de update</a>
                        </div>
                        <svg class="h-[150%] absolute z-1 right-0 top-1/2 -translate-y-1/2" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path fill="#ffffff25" d="M237.4 20.73c-6.1 42.1-26.8 64.2-63.9 64 31.6 4.5 63.8 8 63.9 64.07-.6-46.1 24.5-63.07 64.1-64.07-38-1.5-64.9-16.3-64.1-64zm127.8 11.58c-9.1 14.25-20.8 21.29-38.9 10.28 14.9 11.79 18.6 24.76 10.2 38.97 8.9-11.18 17.5-22.73 39-10.27-17.8-10.06-18.8-23.57-10.3-38.98zM59.68 41.69c-2.7 18.8-12 28.6-28.5 28.5 14.1 2 28.4 3.6 28.5 28.52-.3-20.5 10.9-28.12 28.5-28.52-16.9-.7-28.9-7.3-28.5-28.5zM431 66.28c-2.7 18.8-12 28.6-28.5 28.5 14.1 2 28.4 3.6 28.5 28.52-.3-20.5 10.9-28.12 28.5-28.52-16.9-.7-28.9-7.3-28.5-28.5zM120.3 116.4c-15.8 53.7-47.76 48-79.35 43.4C76.6 170 90.3 197.1 84.28 239.2c12.66-46 42.62-52.6 79.42-43.4-37.6-12.1-56.9-35.4-43.4-79.4zm187 5c-8.8 61.6-39.3 94-93.6 93.7 46.2 6.5 93.6 11.7 93.6 93.7-.8-67.3 35.9-92.2 93.8-93.7-55.5-2.2-94.9-23.9-93.8-93.7zm136.8 38.3c-13.1 21.6-29.5 28.8-49.7 20.1 16.3 9.7 33 19.1 20.1 49.6 10.3-25.2 27.9-28.7 49.7-20-20.3-9.7-31.6-23.9-20.1-49.7zM50.7 243.2c9.16 16.7 7.63 30.1-5.61 40 12.46-6.9 24.85-14.3 39.91 5.6-12.57-16.2-8.2-29 5.61-40-13.92 9.7-27.47 11.6-39.91-5.6zm137.2.3c11.4 26.8-.5 41.3-21.7 50.9 22.7-8.5 40.8-4.5 50.9 21.7-12.7-31.8 4.8-41.2 21.7-50.9-21 8.5-37.8.9-50.9-21.7zm228 12.6c-26.6 64.7-68.7 91.7-127.8 76.4 48.6 19.8 98.8 38.5 76.4 127.9 17.5-73.7 64.4-90.7 127.9-76.5-59.9-17.5-96.9-52-76.5-127.8zM99.94 295.5c15.66 57.8.86 98.1-47.32 118.5 43.46-11.8 87.38-25.2 118.68 47.4-26.4-59.3-3.4-95.4 47.3-118.8-50 19.2-93.1 15-118.66-47.1zm169.36 61c-21.8 20.6-43 23.6-63.2 7.3 15.5 16.3 31.6 32.4 7.2 63.3 19.8-25.6 41.2-24.1 63.3-7.3-20.2-17.4-28.6-37.5-7.3-63.3zM443.2 404c-2.7 18.8-12 28.6-28.5 28.5 14.1 2 28.4 3.6 28.5 28.5-.3-20.5 10.9-28.1 28.5-28.5-16.9-.7-28.9-7.3-28.5-28.5zm-169.7 36c-2.7 18.8-12 28.6-28.5 28.5 14.1 2 28.4 3.6 28.5 28.5-.3-20.5 10.9-28.1 28.5-28.5-16.9-.7-28.9-7.3-28.5-28.5z"/></svg>
                        <img src="/assets/hub-update-memojis.png" class="absolute z-2 right-8 max-h-[12rem]">
                    </div>
                    <div class="mt-6 shrink-0 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-[#f3f8f8] rounded-4xl p-4 flex items-center justify-between">
                            <div>
                                <p class="text-[#215558] font-semibold text-sm shrink-0">Vandaag</p>
                                <p class="text-[#215558] font-black text-xl shrink-0">
                                    {{ $formatDuration($todaySeconds ?? 0) }}
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-2xl bg-[#0F9B9F]/10 flex items-center justify-center">
                                <i class="fa-solid fa-clock text-[#0F9B9F]"></i>
                            </div>
                        </div>
                        <div class="bg-[#f3f8f8] rounded-4xl p-4 flex items-center justify-between">
                            <div>
                                <p class="text-[#215558] font-semibold text-sm shrink-0">Deze week</p>
                                <p class="text-[#215558] font-black text-xl shrink-0">
                                    {{ $formatDuration($weekSeconds ?? 0) }}
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-2xl bg-[#0F9B9F]/10 flex items-center justify-center">
                                <i class="fa-solid fa-calendar-week text-[#0F9B9F]"></i>
                            </div>
                        </div>
                        <div class="bg-[#f3f8f8] rounded-4xl p-4 flex items-center justify-between">
                            <div>
                                <p class="text-[#215558] font-semibold text-sm shrink-0">Deze maand</p>
                                <p class="text-[#215558] font-black text-xl shrink-0">
                                    {{ $formatDuration($monthSeconds ?? 0) }}
                                </p>
                            </div>
                            <div class="w-10 h-10 rounded-2xl bg-[#0F9B9F]/10 flex items-center justify-center">
                                <i class="fa-solid fa-calendar-days text-[#0F9B9F]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 mt-6 flex-1 min-h-0">
                        <div class="bg-[#f3f8f8] rounded-4xl p-8 h-full min-h-0 flex flex-col">
                            <div class="flex-1 min-h-0 grid gap-2 overflow-y-auto pr-3 -mr-3 custom-scroll">
                                @forelse($teamMembers ?? [] as $member)
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white border-gray-200 p-1 relative">
                                                <div class="{{ $member->is_online ? 'hidden' : 'flex' }} w-3 h-3 absolute z-1 right-0 top-0 items-center justify-center">
                                                    <div class="w-3 h-3 rounded-full bg-red-500 absolute z-2"></div>
                                                    <div class="w-2 h-2 rounded-full bg-red-500 animate-ping"></div>
                                                </div>
                                                <div class="{{ $member->is_online ? 'flex' : 'hidden' }} w-3 h-3 absolute z-1 right-0 top-0 items-center justify-center">
                                                    <div class="w-3 h-3 rounded-full bg-green-500 absolute z-2"></div>
                                                    <div class="w-2 h-2 rounded-full bg-green-500 animate-ping"></div>
                                                </div>

                                                <img src="{{ $member->avatar }}" alt="{{ $member->name }}">
                                            </div>
                                            <div>
                                                <p class="text-[#215558] font-bold text-sm shrink-0">
                                                    {{ $member->name }}
                                                </p>
                                                <p class="text-[#215558] font-semibold text-xs shrink-0">
                                                    {{ $member->status_text }}
                                                </p>
                                            </div>
                                        </div>
                                        @if($member->is_online)
                                            <span class="text-[11px] font-semibold text-green-700 bg-green-200 px-2.5 py-0.5 rounded-full">
                                                Beschikbaar
                                            </span>
                                        @else
                                            <span class="text-[11px] font-semibold text-red-700 bg-red-200 px-2.5 py-0.5 rounded-full">
                                                Niet beschikbaar
                                            </span>
                                        @endif
                                    </div>
                                    @if (! $loop->last)
                                        <hr class="border-[#215558]/10">
                                    @endif
                                @empty
                                    <p class="text-xs text-[#215558] font-semibold">
                                        Nog geen teamleden gevonden.
                                    </p>
                                @endforelse
                            </div>
                        </div>
                        <div class="bg-[#f3f8f8] rounded-4xl p-8">
                            @php
                                $timeline  = $intakeTimeline ?? [];
                                $startHour = $timeline['startHour'] ?? 9;
                                $endHour   = $timeline['endHour'] ?? 17;
                            @endphp

                            <div class="relative">
                                @if(($intakeCards ?? collect())->isNotEmpty())
                                    <div class="absolute inset-0">
                                        @foreach($intakeCards as $card)
                                            <div
                                                class="absolute z-10 right-0 pr-2"
                                                style="left: {{ $card->leftOffsetPx }}px; top: {{ $card->topPx }}px; height: {{ $card->heightPx }}px;"
                                            >
                                                <a href="{{ $card->url }}" class="block h-full">
                                                    <div class="h-full rounded-2xl bg-white/95 border border-[#215558]/10 px-4 py-2
                                                                hover:bg-white transition cursor-pointer flex items-center justify-between">
                                                        <div class="h-full flex items-center gap-2">
                                                            <p class="text-[11px] font-bold text-[#215558] truncate">
                                                                Intakegesprek met {{ $card->company }}
                                                            </p>
                                                            <p class="text-[10px] font-semibold text-[#21555880] truncate">
                                                                Start: {{ $card->start->format('H:i') }}
                                                            </p>
                                                        </div>
                                                        <a href="#" class="w-6 h-6 rounded-full flex items-center justify-center bg-[#215558]/20">
                                                            <i class="fa-solid fa-phone text-[11px] text-[#215558] mb-0.5"></i>
                                                        </a>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="grid gap-4">
                                    @for($h = $startHour; $h <= $endHour; $h++)
                                        <div class="flex items-center gap-4 h-7 relative">
                                            <p class="w-[40px] text-[#21555880] text-xs font-semibold">
                                                {{ sprintf('%02d:00', $h) }}
                                            </p>
                                            <div class="flex-1 h-px bg-[#21555810]"></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-[#f3f8f8] w-[350px] rounded-4xl p-8">
                    @php
                        $firstName  = strtolower(explode(' ', trim($user->name))[0] ?? '');
                        $userAvatar = "/assets/eazyonline/memojis/{$firstName}.webp";
                    @endphp
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center p-2 mx-auto border-2 border-[#0F9B9F50] relative">
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
                    <h2 class="text-[#215558] font-bold text-base shrink-0 text-center mt-4 mb-2">Goeiedag {{ $user->name }} 👋</h2>
                    <p class="text-xs text-[#215558] font-semibold text-center mb-6">Laten we gaan knallen vandaag!</p>
                    <div class="w-full rounded-4xl p-4 bg-white flex flex-col mb-2">
                        <h2 class="text-[#215558] font-semibold text-sm shrink-0">Vandaag</h2>
                        <p class="text-[#215558] font-black text-xl shrink-0 mb-1">
                            09:00-17:00
                        </p>
                        @php
                            $formatDuration = function (int $seconds) {
                                $h = floor($seconds / 3600);
                                $m = floor(($seconds % 3600) / 60);
                                return sprintf('%du %02dm', $h, $m);
                            };
                        @endphp
                        @if(isset($activeSession) && $activeSession)
                            {{-- Ingeklokt --}}
                            <p class="text-[#215558] font-semibold text-xs shrink-0 mb-4">
                                Ingeklokt sinds {{ $activeSession->clock_in_at->format('H:i') }}
                            </p>
                            <form method="POST" action="{{ route('support.work.clock_out') }}">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 text-white font-semibold text-sm bg-[#ef4444] hover:bg-[#dc2626] transition duration-300 w-full rounded-full text-center">
                                    Uitklokken
                                </button>
                            </form>
                        @else
                            {{-- Niet ingeklokt --}}
                            <p class="text-[#215558] font-semibold text-xs shrink-0 mb-4">
                                Je bent nog niet ingeklokt.
                            </p>
                            <form method="POST" action="{{ route('support.work.clock_in') }}">
                                @csrf
                                <button type="submit"
                                    class="px-3 py-2 cursor-pointer text-white font-semibold text-sm bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-full rounded-full text-center">
                                    Inklokken
                                </button>
                            </form>
                        @endif
                    </div>
                    <div class="w-full rounded-4xl p-4 bg-white flex flex-col mb-2">
                        @php
                            $reminderCount = ($intakesToday ?? collect())->count();
                        @endphp
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-[#215558] font-semibold text-sm shrink-0">Reminders</h2>

                            @if($reminderCount > 0)
                                <div class="w-4 h-4 bg-[#0F9B9F] font-semibold text-[11px] rounded-full text-white flex items-center justify-center">
                                    {{ $reminderCount }}
                                </div>
                            @endif
                        </div>
                        <div class="grid gap-2 mb-4">
                            @forelse($intakesToday as $intake)
                                <div class="flex items-center gap-2">
                                    <div class="min-w-8 max-w-8 min-h-8 max-h-8 bg-[#215558]/20 rounded-full flex items-center justify-center">
                                        <i class="fa-solid fa-phone fa-sm text-[#215558]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[#215558] font-bold text-sm shrink-0">
                                            Intakegesprek
                                            @if(!empty($intake->company))
                                                met {{ $intake->company }}
                                            @endif
                                        </p>
                                        <p class="text-[#215558] font-semibold text-xs shrink-0">
                                            Vandaag om {{ \Carbon\Carbon::parse($intake->intake_at)->format('H:i') }}
                                        </p>
                                    </div>
                                </div>

                                @if (! $loop->last)
                                    <hr class="border-[#215558]/10">
                                @endif
                            @empty
                                <p class="text-xs text-[#215558] font-semibold">
                                    Geen reminders voor vandaag.
                                </p>
                            @endforelse
                        </div>
                        <a href="{{ route('support.intake.availability') }}"
                        class="px-3 py-2 text-white font-semibold text-sm bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-full rounded-full text-center">
                            Mijn planning
                        </a>
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