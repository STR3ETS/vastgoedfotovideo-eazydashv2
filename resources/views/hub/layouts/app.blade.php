<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="yes">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.webp">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <link rel="preload" href="{{ asset('fontawesome/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}"></noscript>

    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>

    <script src="https://cdn.jsdelivr.net/npm/js-confetti@latest/dist/js-confetti.browser.js"></script>
    <style>
      canvas#confetti {
        position: fixed; inset: 0;
        width: 100%; height: 100%;
        z-index: 10000; pointer-events: none;
      }
    </style>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
      {{-- jouw bestaande inline Tailwind fallback CSS (ongewijzigd) --}}
      <style>/* ... ongewijzigd ... */</style>
    @endif
  </head>
  <!-- <body class="max-h-screen flex bg-cover bg-center" style="background-image: url('/assets/app-bg-1920.webp')"> -->
  <body class="max-h-screen flex bg-[#F5EFED]">
    <!-- SIDEBAR -->
    <aside class="w-fit h-screen pl-2 py-2">
      <div class="flex h-full">
        <div class="w-fit p-2 h-full rounded-xl flex flex-col justify-between pr-8">
          <div class="grid gap-11">
            <!-- Logo -->
            <a href="{{ url('/app') }}" class="cursor-pointer">
              <img class="max-h-9" src="/assets/vastgoedfotovideo/logo-full.png" alt="Eazyonline">
            </a>

            <ul class="grid gap-4">
              <li>
                <a href="{{ url('/app') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                  <i class="fa-solid fa-house text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                  <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">Dashboard</p>
                </a>
              </li>
              <li>
                <a href="{{ url('/app/support') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                  <i class="fa-solid fa-message-lines text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                  <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">Ondersteuning</p>
                </a>
              </li>
              @if((int) auth()->user()->company_id === 1)
                <li>
                  <a href="{{ url('/app/potentiele-klanten') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                    <i class="fa-solid fa-bolt text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                    <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">Potentiele klanten</p>
                  </a>
                </li>
                <li>
                  <a href="{{ url('/app/projecten') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                    <i class="fa-solid fa-folders text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                    <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">Projecten</p>
                  </a>
                </li>
                <li>
                  <a href="{{ url('/app/marketing') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                    <i class="fa-solid fa-at text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                    <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">Marketing</p>
                  </a>
                </li>
                <li>
                  <a href="{{ route('support.seo.projects.index') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                    <i class="fa-brands fa-google text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                    <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">SEO-strategie</p>
                  </a>
                </li>
                <li>
                  <a href="{{ route('support.gebruikers.index') }}" class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                    <i class="fa-solid fa-user text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
                    <p class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200 ml-2">Gebruikers</p>
                  </a>
                </li>
              @endif
            </ul>
          </div>
          <!-- ONDERSTE CONTENT SIDEBAR -->
          <a href="{{ url('/app/gebruikers') }}" class="w-9 h-9 border border-gray-200 bg-white rounded-full flex items-center justify-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
            <i class="fa-solid fa-gear text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
          </a>
        </div>
        <div class="min-w-[300px] h-full pr-8">
          <h1 class="text-base font-black text-[#191D38] pt-3.5 mb-4">VastgoedFotoVideo Workspace</h1>
          <ul class="grid gap-2">
            <li class="flex items-center gap-2 opacity-50">
              <i class="fa-solid fa-star text-[#191D38] fa-xs"></i>
              <p href="#" class="text-[#191D38] font-semibold text-sm">Favorieten</p>
            </li>
            <li class="flex items-center gap-2 opacity-50">
              <i class="fa-solid fa-history text-[#191D38] fa-xs"></i>
              <p href="#" class="text-[#191D38] font-semibold text-sm">Recent</p>
            </li>
            @if (request()->is('app') || request()->is('app/sales/offertes') || request()->is('app/overzicht/facturen'))
              <li class="grid gap-1" x-data="{ openSales: true }">
                <!-- Rij: Sales + plusje -->
                <div class="flex items-center justify-between gap-2">
                  <a href="{{ url('/app/sales/offertes') }}"
                    class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                    Sales
                  </a>
                  <button
                    type="button"
                    class="w-4 h-4 bg-white rounded-full flex items-center justify-center cursor-pointer"
                    @click="openSales = !openSales"
                    :aria-expanded="openSales.toString()"
                  >
                    <i
                      class="fa-solid fa-plus text-gray-500 text-[11px] pr-0.25 pb-0.25 transition-transform duration-200"
                      :class="openSales ? 'rotate-45 text-[#009AC3]' : ''"
                    ></i>
                  </button>
                </div>

                <!-- Uitklap: Facturen & Offertes -->
                <div x-show="openSales" x-transition>
                  <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                    <div class="flex items-center gap-2">
                      <hr class="w-[10px] border-1 border-[#191D38]/25">
                      <a href="{{ url('/app/overzicht/facturen') }}"
                        class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                        Facturen
                      </a>
                    </div>
                    <div class="flex items-center gap-2">
                      <hr class="w-[10px] border-1 border-[#191D38]/25">
                      <a href="{{ url('/app/sales/offertes') }}"
                        class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                        Offertes
                      </a>
                    </div>
                  </div>
                </div>
              </li>
              <li class="flex"><a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">Doelen</a></li>
              <ul class="grid gap-2" x-data="{ openDashboard: true }">
                  <!-- Rij: Dashboard + plusje -->
                  <li class="flex items-center justify-between gap-2">
                      <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                          Dashboard
                      </a>
                      <!-- Plusje als toggle -->
                      <button
                          type="button"
                          class="w-4 h-4 bg-white rounded-full flex items-center justify-center cursor-pointer"
                          @click="openDashboard = !openDashboard"
                          :aria-expanded="openDashboard.toString()"
                      >
                          <i
                              class="fa-solid fa-plus text-gray-500 text-[11px] pr-0.25 pb-0.25 transition-transform duration-200"
                              :class="openDashboard ? 'rotate-45 text-[#009AC3]' : ''"
                          ></i>
                      </button>
                  </li>
                  <!-- UITKLAPBAAR BLOK ONDER DASHBOARD -->
                  <li x-show="openDashboard" x-transition>
                      <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                          <!-- Mijn planning -->
                          <div class="flex items-center gap-2">
                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                  Mijn planning
                              </a>
                          </div>
                          @php
                            $myAanvragen = \App\Models\AanvraagWebsite::query()
                              ->where('owner_id', auth()->id())
                              ->select('id', 'company', 'status', 'choice', 'created_at')
                              ->latest()
                              ->limit(10)
                              ->get();

                            $myAanvragenCount = \App\Models\AanvraagWebsite::query()
                              ->where('owner_id', auth()->id())
                              ->count();
                          @endphp
                          <!-- Mijn aanvragen -->
                          <ul class="grid gap-2">
                            <li class="flex items-center justify-between gap-2">
                              <div class="flex items-center gap-2">
                                <hr class="w-[10px] border-1 border-[#191D38]/25">
                                <a href="{{ url('/app/potentiele-klanten') }}"
                                  class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                  Mijn aanvragen
                                </a>
                              </div>

                              <div class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center">
                                {{ $myAanvragenCount }}
                              </div>
                            </li>
                            <li>
                              <div class="ml-[18px] border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                                @forelse($myAanvragen as $aanvraag)
                                  <div class="flex items-center gap-2">
                                    <hr class="w-[10px] border-1 border-[#191D38]/25">
                                    <a href="{{ route('support.potentiele-klanten.show', ['aanvraag' => $aanvraag->id]) }}"
                                      class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300 truncate">
                                      {{ $aanvraag->company }}
                                      @php
                                        $choiceLabel = match (($aanvraag->choice ?? '')) {
                                          'new'   => 'Nieuwe website',
                                          'renew' => 'Vernieuw huidige website',
                                          default => '',
                                        };
                                      @endphp
                                      @if($choiceLabel !== '')
                                        <span class="ml-2 shrink-0 inline-flex items-center gap-1.5 px-2 py-[2px] rounded-full text-[10px] font-semibold bg-[#191D38]/10 text-[#191D38]/70">
                                          {{ $choiceLabel }}
                                        </span>
                                      @endif
                                    </a>
                                  </div>
                                @empty
                                  <div class="flex items-center gap-2">
                                    <hr class="w-[10px] border-1 border-[#191D38]/25">
                                    <span class="text-[#191D38]/60 text-sm font-semibold">Nog geen aanvragen</span>
                                  </div>
                                @endforelse
                              </div>
                            </li>
                          </ul>
                          <!-- Mijn projecten -->
                          <ul class="grid gap-2">
                              <li class="flex items-center justify-between gap-2">
                                  <div class="flex items-center gap-2">
                                      <hr class="w-[10px] border-1 border-[#191D38]/25">
                                      <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                          Mijn projecten
                                      </a>
                                  </div>
                                  <div
                                      class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center"
                                  >
                                      2
                                  </div>
                              </li>
                              <!-- Subprojecten -->
                              <li>
                                  <div class="ml-[18px] border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Projectnaam 1
                                          </a>
                                      </div>
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Projectnaam 2
                                          </a>
                                      </div>
                                  </div>
                              </li>
                          </ul>
                          <ul class="grid gap-2">
                              <li class="flex items-center justify-between gap-2">
                                  <div class="flex items-center gap-2">
                                      <hr class="w-[10px] border-1 border-[#191D38]/25">
                                      <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                          Mijn support-tickets
                                      </a>
                                  </div>
                                  <div
                                      class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center"
                                  >
                                      4
                                  </div>
                              </li>
                              <!-- Subprojecten -->
                              <li>
                                  <div class="ml-[18px] border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                                      <div class="flex items-center justify-between gap-2">
                                          <div class="flex items-center gap-2">
                                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                                  Openstaand
                                              </a>
                                          </div>
                                          <div
                                              class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center"
                                          >
                                              2
                                          </div>
                                      </div>
                                      <div class="flex items-center justify-between gap-2">
                                          <div class="flex items-center gap-2">
                                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                                  In behandeling
                                              </a>
                                          </div>
                                          <div
                                              class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center"
                                          >
                                              1
                                          </div>
                                      </div>
                                      <div class="flex items-center justify-between gap-2">
                                          <div class="flex items-center gap-2">
                                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                                  Gesloten
                                              </a>
                                          </div>
                                          <div
                                              class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center"
                                          >
                                              1
                                          </div>
                                      </div>
                                  </div>
                              </li>
                          </ul>
                      </div>
                  </li>
              </ul>
            @endif

@php
  /** @var array $statusMap label => value */
  $statusMap = $statusMap ?? [
    'Prospect' => 'prospect',
    'Contact'  => 'contact',
    'Intake'   => 'intake',
    'Dead'     => 'dead',
    'Lead'     => 'lead',
  ];

  $colors = $colors ?? [
    'prospect' => [
      'bg'    => 'bg-[#b3e6ff]',
      'border'=> 'border-[#92cbe8]',
      'text'  => 'text-[#0f6199]',
      'dot'   => 'bg-[#0f6199]',
    ],
    'contact' => [
      'bg'    => 'bg-[#C2F0D5]',
      'border'=> 'border-[#a1d3b6]',
      'text'  => 'text-[#20603a]',
      'dot'   => 'bg-[#20603a]',
    ],
    'intake' => [
      'bg'    => 'bg-[#ffdfb3]',
      'border'=> 'border-[#e8c392]',
      'text'  => 'text-[#a0570f]',
      'dot'   => 'bg-[#a0570f]',
    ],
    'dead' => [
      'bg'    => 'bg-[#ffb3b3]',
      'border'=> 'border-[#e09494]',
      'text'  => 'text-[#8a2a2d]',
      'dot'   => 'bg-[#8a2a2d]',
    ],
    'lead' => [
      'bg'    => 'bg-[#e0d4ff]',
      'border'=> 'border-[#c3b4f0]',
      'text'  => 'text-[#4c2a9b]',
      'dot'   => 'bg-[#4c2a9b]',
    ],
  ];

  $sidebarAanvragen = \App\Models\AanvraagWebsite::query()
    ->select('id', 'company', 'status')
    ->orderByDesc('created_at')
    ->limit(10)
    ->get();

  $routeAanvraag = request()->route('aanvraag');
  $activeAanvraagId = is_object($routeAanvraag)
    ? (int) $routeAanvraag->id
    : (is_numeric($routeAanvraag) ? (int) $routeAanvraag : null);
@endphp
@if (request()->is('app/potentiele-klanten*'))
  <ul>
    <li class="grid gap-1" x-data="{ openPotentieleKlanten: true }">
      <!-- Rij: Potentiele Klanten + plusje -->
      <div class="flex items-center justify-between gap-2">
        <a href="{{ url('/app/potentiele-klanten') }}"
          class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
          Website Aanvragen
        </a>
        <button
          type="button"
          class="w-4 h-4 bg-white rounded-full flex items-center justify-center cursor-pointer"
          @click="openPotentieleKlanten = !openPotentieleKlanten"
          :aria-expanded="openPotentieleKlanten.toString()"
        >
          <i
            class="fa-solid fa-plus text-gray-500 text-[11px] pr-0.25 pb-0.25 transition-transform duration-200"
            :class="openPotentieleKlanten ? 'rotate-45 text-[#009AC3]' : ''"
          ></i>
        </button>
      </div>
      <!-- Uitklap: Aanvragen -->
      <div x-show="openPotentieleKlanten" x-transition>
        <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
          @forelse($sidebarAanvragen as $aanvraag)
            @php
              $status = strtolower(trim($aanvraag->status ?? 'prospect'));
              $c = $colors[$status] ?? $colors['prospect'];
            @endphp
            <div class="flex items-center justify-between gap-3">
              <div class="flex items-center gap-2 min-w-0">
                <hr class="w-[10px] border-1 border-[#191D38]/25 shrink-0">
                @php $isActive = ($activeAanvraagId && (int)$aanvraag->id === (int)$activeAanvraagId); @endphp
                <a
                  href="{{ route('support.potentiele-klanten.show', ['aanvraag' => $aanvraag->id]) }}"
                  data-potkl-open="{{ $aanvraag->id }}"
                  class="{{ $isActive ? 'text-[#009AC3]' : 'text-[#191D38]' }} font-semibold text-sm hover:text-[#009AC3] transition duration-300 truncate"
                >
                  {{ $aanvraag->company }}
                </a>
              </div>
              <span class="shrink-0 inline-flex items-center gap-1.5 px-2 py-[2px] rounded-full text-[10px] font-semibold {{ $c['bg'] }} {{ $c['text'] }}">
                {{ __('potentiele_klanten.statuses.' . $status) }}
              </span>
            </div>
          @empty
            <div class="flex items-center gap-2">
              <hr class="w-[10px] border-1 border-[#191D38]/25">
              <span class="text-[#191D38]/60 text-sm font-semibold">Nog geen aanvragen</span>
            </div>
          @endforelse
        </div>
      </div>
    </li>
  </ul>
@endif

            @if (request()->is('app/marketing*'))
              <ul class="grid gap-2" x-data="{ openMailing: true }">
                  <li class="flex items-center justify-between gap-2">
                      <a href="{{ url('/app/marketing/mailing') }}" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                          Mailing
                      </a>
                      <button
                          type="button"
                          class="w-4 h-4 bg-white rounded-full flex items-center justify-center cursor-pointer"
                          @click="openMailing = !openMailing"
                          :aria-expanded="openMailing.toString()"
                      >
                          <i
                              class="fa-solid fa-plus text-gray-500 text-[11px] pr-0.25 pb-0.25 transition-transform duration-200"
                              :class="openMailing ? 'rotate-45 text-[#009AC3]' : ''"
                          ></i>
                      </button>
                  </li>
                  <li x-show="openMailing" x-transition>
                      <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                          <div class="flex items-center gap-2">
                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                              <a href="{{ url('/app/marketing/mailing/nieuwsbrieven') }}" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                  Nieuwsbrieven
                              </a>
                          </div>
                          <ul class="grid gap-2">
                              <div class="flex items-center gap-2">
                                  <hr class="w-[10px] border-1 border-[#191D38]/25">
                                  <a href="{{ url('/app/marketing/mailing/templates') }}" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                      Templates
                                  </a>
                              </div>
                              <li>
                                  <div class="ml-[18px] border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="{{ url('/app/marketing/mailing/templates/nieuwsbrief-templates') }}" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Nieuwsbrief-templates
                                          </a>
                                      </div>
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="{{ url('/app/marketing/mailing/templates/actie-aanbod-templates') }}" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Actie / aanbod-templates
                                          </a>
                                      </div>
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="{{ url('/app/marketing/mailing/templates/onboarding-opvolg-templates') }}" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Onboarding / opvolg-templates
                                          </a>
                                      </div>
                                  </div>
                              </li>
                          </ul>
                          <ul class="grid gap-2">
                              <li class="flex items-center justify-between gap-2">
                                  <div class="flex items-center gap-2">
                                      <hr class="w-[10px] border-1 border-[#191D38]/25">
                                      <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                          Campagnes
                                      </a>
                                  </div>
                                  <div
                                      class="w-4 h-4 bg-[#009AC3] font-semibold text-[11px] rounded-full text-white flex items-center justify-center"
                                  >
                                      2
                                  </div>
                              </li>
                              <li>
                                  <div class="ml-[18px] border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Winteractie
                                          </a>
                                      </div>
                                      <div class="flex items-center gap-2">
                                          <hr class="w-[10px] border-1 border-[#191D38]/25">
                                          <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                              Nieuwjaarskorting
                                          </a>
                                      </div>
                                  </div>
                              </li>
                          </ul>
                      </div>
                  </li>
              </ul>
              <ul class="grid gap-2" x-data="{ openSocial: true }">
                  <li class="flex items-center justify-between gap-2">
                      <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                          Socials
                      </a>
                      <button
                          type="button"
                          class="w-4 h-4 bg-white rounded-full flex items-center justify-center cursor-pointer"
                          @click="openSocial = !openSocial"
                          :aria-expanded="openSocial.toString()"
                      >
                          <i
                              class="fa-solid fa-plus text-gray-500 text-[11px] pr-0.25 pb-0.25 transition-transform duration-200"
                              :class="openSocial ? 'rotate-45 text-[#009AC3]' : ''"
                          ></i>
                      </button>
                  </li>
                  <li x-show="openSocial" x-transition>
                      <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                          <div class="flex items-center gap-2">
                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                  Contentkalender
                              </a>
                          </div>
                          <div class="flex items-center gap-2">
                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                  Posts
                              </a>
                          </div>
                          <div class="flex items-center gap-2">
                              <hr class="w-[10px] border-1 border-[#191D38]/25">
                              <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                  Activiteiten
                              </a>
                          </div>
                      </div>
                  </li>
              </ul>
            @endif
            @if (request()->is('app/gebruikers*'))
                <ul class="grid gap-2" x-data="{ openUser: true }">
                    <li class="flex items-center justify-between gap-2">
                        <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                            Gebruikers
                        </a>
                        <button
                            type="button"
                            class="w-4 h-4 bg-white rounded-full flex items-center justify-center cursor-pointer"
                            @click="openUser = !openUser"
                            :aria-expanded="openUser.toString()"
                        >
                            <i
                                class="fa-solid fa-plus text-gray-500 text-[11px] pr-0.25 pb-0.25 transition-transform duration-200"
                                :class="openUser ? 'rotate-45 text-[#009AC3]' : ''"
                            ></i>
                        </button>
                    </li>
                    <li x-show="openUser" x-transition>
                        <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                            <div class="flex items-center gap-2">
                                <hr class="w-[10px] border-1 border-[#191D38]/25">
                                <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                    Admin
                                </a>
                            </div>
                        </div>
                        <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                            <div class="flex items-center gap-2">
                                <hr class="w-[10px] border-1 border-[#191D38]/25">
                                <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                    Klant
                                </a>
                            </div>
                        </div>
                        <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                            <div class="flex items-center gap-2">
                                <hr class="w-[10px] border-1 border-[#191D38]/25">
                                <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                    Team manager
                                </a>
                            </div>
                        </div>
                        <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                            <div class="flex items-center gap-2">
                                <hr class="w-[10px] border-1 border-[#191D38]/25">
                                <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                    Klant manager
                                </a>
                            </div>
                        </div>
                        <div class="border-l-2 border-l-[#191D38]/25 py-2 grid gap-2">
                            <div class="flex items-center gap-2">
                                <hr class="w-[10px] border-1 border-[#191D38]/25">
                                <a href="#" class="text-[#191D38] font-semibold text-sm hover:text-[#009AC3] transition duration-300">
                                    Fotograaf
                                </a>
                            </div>
                        </div>
                    </li>
                </ul>
            @endif
        </ul>
    </div>
    </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 h-screen py-4 pr-4 flex flex-col gap-4">
      <!-- Top bar -->
      <div class="shrink-0 w-full flex items-center justify-end rounded-xl pt-1 gap-2 pr-2">
        @php
          $naam = trim((string) ($user->name ?? ''));
          $init = '?';
          if ($naam !== '') {
              $parts = preg_split('/\s+/u', $naam, -1, PREG_SPLIT_NO_EMPTY);
              $tussenvoegsels = ['van','de','der','den','het',"'t",'te','ten','ter','op','aan','bij',"van't",'van','van de','van der','van den'];
              $filtered = array_values(array_filter($parts, fn($p) => !in_array(mb_strtolower($p), $tussenvoegsels, true)));
              $first = $filtered[0] ?? ($parts[0] ?? '');
              $last  = $filtered[count($filtered)-1] ?? '';
              $init = mb_strtoupper($last === $first ? mb_substr($first,0,2) : (mb_substr($first,0,1).mb_substr($last,0,1)));
          }
        @endphp

        <div class="flex-1">
          <input type="text" placeholder="Zoeken in mijn systeem..." class="h-9 bg-white border border-gray-200 flex items-center px-4 w-[300px] rounded-full text-xs text-[#191D38] font-medium outline-none">
        </div>

        {{-- 🔔 Notifications bell (hover open zoals profiel dropdown) --}}
        <div
          class="relative inline-block group"
          x-data="notifBell({
            csrf: '{{ csrf_token() }}',
            indexUrl: '{{ route('app.notifications.index') }}',
            readUrlBase: '{{ url('/app/notifications') }}',
            readAllUrl: '{{ route('app.notifications.readAll') }}',
          })"
          x-init="init()"
        >
          <button
            type="button"
            class="w-9 h-9 cursor-pointer border border-gray-200 bg-white rounded-full flex items-center justify-center group transition duration-300 relative
                  hover:bg-[#009AC3] hover:border-[#009AC3]"
            aria-label="Notifications"
          >
            <i class="fa-regular fa-bell text-[#191D38] group-hover:text-white transition duration-200 text-base"></i>
            <template x-if="unreadCount > 0">
              <span
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#009AC3] text-white text-[10px] font-black flex items-center justify-center"
                x-text="unreadCount"
              ></span>
            </template>
          </button>
          {{-- hover bridge (zoals profiel dropdown) --}}
          <span class="absolute right-0 top-8 h-4 min-w-[340px] block opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
          <div
            x-cloak
            class="min-w-[340px] max-w-[420px] px-1 py-2 rounded-xl bg-white border border-gray-200 shadow-md absolute right-0 top-10 z-50
                  opacity-0 invisible translate-y-1 pointer-events-none
                  group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                  group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:pointer-events-auto
                  transition-all duration-300 ease-out"
          >
            <div class="flex items-center justify-between px-3 pt-1 mb-1">
              <p class="text-base text-[#191D38] font-bold">Meldingen</p>
              <button
                type="button"
                class="text-[11px] font-bold text-[#191D38]/60 hover:text-[#191D38] transition duration-300"
                @click="readAll()"
                x-show="unreadCount > 0"
              >
                Alles markeren als gelezen
              </button>
            </div>
            <div class="px-3 pb-2 pt-0 mt-3 max-h-[320px] overflow-auto
                        [&>a+a]:border-t [&>a+a]:border-[#191D38]/20
                        [&>a+a]:pt-3
                        [&>a:not(:last-of-type)]:pb-3">
              <template x-if="items.length === 0">
                <p class="text-xs font-semibold text-[#191D38]/60">Geen meldingen.</p>
              </template>
              <template x-for="n in items" :key="n.id">
                <a
                  class="block"
                  :href="n.data.url"
                  @click.prevent="openNotification(n)"
                >
                  <div class="flex items-start justify-between gap-3 relative">
                    <div class="flex-1 min-w-0">
                      <p class="text-xs font-bold text-[#191D38] truncate mb-1" x-text="n.data.title"></p>
                      <p class="text-xs font-medium text-[#191D38] leading-[20px] opacity-75" x-text="n.data.body"></p>
                      <p class="text-[11px] font-semibold opacity-50 text-[#191D38] mt-1" x-text="n.created_at"></p>
                    </div>

                    <template x-if="!n.read_at">
                      <span class="absolute top-1/2 -translate-y-1/2 right-1 w-2 h-2 rounded-full bg-orange-500 shrink-0"></span>
                    </template>
                  </div>
                </a>
              </template>
            </div>
          </div>
        </div>

        <!-- Avatar dropdown (with hover bridge) -->
        <div class="relative inline-block group">
          <div class="min-w-9 min-h-9 rounded-full bg-white border border-gray-200 transition duration-300 cursor-pointer flex items-center justify-center"
               aria-haspopup="true" aria-expanded="false" role="button" tabindex="0">
               <img src="/assets/eazyonline/memojis/boyd.webp" class="max-h-6">
          </div>

          <span class="absolute right-0 top-8 h-4 min-w-[300px] block opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>

          <div class="min-w-[300px] px-1 py-3 rounded-xl bg-white border border-gray-200 shadow-md absolute right-0 top-10 z-50
                      opacity-0 invisible translate-y-1 pointer-events-none
                      group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                      group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:pointer-events-auto
                      transition-all duration-300 ease-out"
               role="menu" aria-label="Account menu">
            <p class="px-3 text-base text-[#191D38] font-bold mb-1">{{ $user->name }}</p>

            <div>
              <a href="{{ url('/app/instellingen') }}" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                <i class="min-w-[16px] fa-solid fa-user text-[#191D38] fa-sm"></i>
                <p class="px-1 text-sm text-[#191D38] font-semibold" data-i18n="profile_dropdown.persoonlijke_gegevens">
                  {{ __('profile_dropdown.persoonlijke_gegevens') }}
                </p>
              </a>

              @if($user->rol === 'klant' && $user->is_company_admin)
                <a href="{{ url('/app/instellingen') }}" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                  <i class="min-w-[16px] fa-solid fa-wrench text-[#191D38] fa-sm"></i>
                  <p class="px-1 text-sm text-[#191D38] font-semibold" data-i18n="profile_dropdown.bedrijfsinstellingen">
                    {{ __('profile_dropdown.bedrijfsinstellingen') }}
                  </p>
                </a>
                <a href="{{ url('/app/instellingen') }}" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                  <i class="min-w-[16px] fa-solid fa-credit-card text-[#191D38] fa-sm"></i>
                  <p class="px-1 text-sm text-[#191D38] font-semibold" data-i18n="profile_dropdown.abonnement_betaling">
                    {{ __('profile_dropdown.abonnement_betaling') }}
                  </p>
                </a>
              @endif
            </div>

            @if($user->rol === 'klant')
              <div class="px-3 my-1"><hr class="border-gray-100"></div>
              <a href="#" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                <i class="min-w-[16px] fa-solid fa-ticket text-[#191D38] fa-sm"></i>
                <p class="px-1 text-sm text-[#191D38] font-semibold" data-i18n="profile_dropdown.support">
                  {{ __('profile_dropdown.support') }}
                </p>
              </a>
            @endif

            <div class="px-3 my-1"><hr class="border-gray-100"></div>

            <a href="#" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300 -mb-2">
              <i class="min-w-[16px] fa-solid fa-right-from-bracket text-[#191D38] fa-sm"></i>
              <p class="px-1 text-sm text-[#191D38] font-semibold" data-i18n="profile_dropdown.uitloggen">
                {{ __('profile_dropdown.uitloggen') }}
              </p>
            </a>
          </div>
        </div>
        <div class="w-8 h-8 rounded-full bg-[#191D38] flex items-center justify-center">
          <i class="fa-solid fa-plus text-sm text-white"></i>
        </div>
      </div>

      <!-- Content area -->
      <div class="flex-1 grid grid-cols-5 min-h-0 w-full gap-4">
        @yield('content')
      </div>
    </main>

    @if(auth()->check() && auth()->user()->first_login && auth()->user()->rol !== "admin")
      <div id="first-login-overlay" class="fixed inset-0 z-[9999]">
        <!-- Confetti canvas IN de overlay, zodat het boven de dimmer ligt -->
        <canvas id="confetti"></canvas>
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
          <div class="bg-white p-6 rounded-2xl shadow-xl max-w-lg w-full relative">

            <img src="/assets/memoji-row.png" class="max-w-[300px] mx-auto">
            <h2 class="text-2xl font-black text-[#191D38] text-center mt-6 mb-4 pointer-events-none">Welcome to your environment!</h2>
            <p class="text-sm font-medium text-[#191D38] opacity-80 text-center max-w-[80%] mx-auto mb-8 pointer-events-none">We look forward to seeing what beautiful things you create. One of our team members will contact you soon to discuss the next steps.</p>

            <div class="flex items-center justify-end gap-2">
              <button
                id="first-login-go"
                hx-patch="{{ route('support.first_login.dismiss') }}"
                hx-target="#first-login-overlay"
                hx-swap="outerHTML"
                class="px-4 py-2 rounded-full text-sm font-semibold bg-[#009AC3] hover:bg-[#191D38] text-white transition cursor-pointer"
                type="button"
              >
                Start using My Eazyonline
              </button>
            </div> 
          </div>
        </div>
      </div>
      <script>
        (function() {
          // Safeguard: alleen draaien als overlay aanwezig is
          const overlay = document.getElementById('first-login-overlay');
          if (!overlay || typeof JSConfetti === 'undefined') return;

          const confetti = new JSConfetti({ canvas: document.getElementById('confetti') });

          // 1) Auto burst bij tonen
          confetti.addConfetti({
            confettiColors: ['#76e2e8', '#398387', '#1d9ca3'],
            confettiNumber: 220
          });

          // 2) Klein toastje laten zien en automatisch verbergen
          const toast = document.getElementById('first-login-toast');
          if (toast) {
            toast.style.opacity = '0';
            toast.classList.remove('hidden');
            requestAnimationFrame(() => {
              toast.style.transition = 'opacity .25s ease, transform .25s ease';
              toast.style.opacity = '1';
              toast.style.transform = 'translateY(-110%)';
            });
            setTimeout(() => {
              toast.style.opacity = '0';
              toast.style.transform = 'translateY(-120%)';
              setTimeout(() => toast.remove(), 250);
            }, 3500);
          }
        })();
      </script>
    @endif

    {{-- Globale i18n helper: werkt alle [data-i18n] bij, en ook attributes via data-i18n-attr --}}
    <script>
      window.applyI18n = function(dict) {
        try {
          if (dict && dict.__lang) {
            document.documentElement.setAttribute('lang', dict.__lang);
          }
          document.querySelectorAll('[data-i18n]').forEach(function(el){
            var key  = el.getAttribute('data-i18n');
            var attr = el.getAttribute('data-i18n-attr'); // bv. "placeholder"
            if (!key || !(key in dict)) return;
            var val = dict[key];
            if (attr) { el.setAttribute(attr, val); }
            else { el.textContent = val; }
          });
        } catch (e) {
          console.error('applyI18n failed', e);
        }
      };

      document.body.addEventListener('htmx:configRequest', (e) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) e.detail.headers['X-CSRF-TOKEN'] = token;
      });
    </script>

    <script>
      document.addEventListener('click', function (e) {
        const a = e.target.closest('a[data-potkl-open]');
        if (!a) return;

        // Alleen soft navigeren als we echt op de potentiële klanten pagina zijn
        if (!document.querySelector('[data-potkl-page]')) return;

        // normale browser acties blijven werken
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button === 1) return;

        e.preventDefault();

        window.dispatchEvent(new CustomEvent('potkl-open-aanvraag', {
          detail: {
            id: a.getAttribute('data-potkl-open'),
            href: a.href
          }
        }));
      });
    </script>
    <script>
      (function () {
        function currentIdFromUrl() {
          const m = window.location.pathname.match(/potentiele-klanten\/(\d+)/);
          return m && m[1] ? m[1] : null;
        }

        function setActiveInSidebar(id) {
          document.querySelectorAll('a[data-potkl-open]').forEach((a) => {
            const isActive = String(a.getAttribute('data-potkl-open')) === String(id);
            a.classList.toggle('text-[#009AC3]', isActive);
            a.classList.toggle('text-[#191D38]', !isActive);
          });
        }

        function sync() {
          const id = currentIdFromUrl();
          if (id) setActiveInSidebar(id);
        }

        const _pushState = history.pushState;
        history.pushState = function () {
          _pushState.apply(history, arguments);
          sync();
        };

        window.addEventListener('popstate', sync);
        window.addEventListener('DOMContentLoaded', sync);

        window.addEventListener('potkl-open-aanvraag', (e) => {
          if (e.detail && e.detail.id) setActiveInSidebar(e.detail.id);
        });
      })();
    </script>
    <script>
      window.notifBell = function ({ csrf, indexUrl, readUrlBase, readAllUrl }) {
        return {
          unreadCount: 0,
          items: [],

          async init() {
            const res = await fetch(indexUrl, {
              headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
              credentials: 'same-origin',
            });
            const data = await res.json();
            this.unreadCount = data.unread_count || 0;
            this.items = data.items || [];
          },

          async openNotification(n) {
            await fetch(`${readUrlBase}/${encodeURIComponent(n.id)}`, {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
              credentials: 'same-origin',
            });
            if (n?.data?.url) window.location.href = n.data.url;
          },

          async readAll() {
            await fetch(readAllUrl, {
              method: 'POST',
              headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
              credentials: 'same-origin',
            });
            this.unreadCount = 0;
            const now = new Date().toISOString();
            this.items = this.items.map(i => ({ ...i, read_at: i.read_at || now }));
          },
        };
      };
    </script>
  </body>
</html>
