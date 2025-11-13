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
  <body class="max-h-screen flex bg-cover bg-center" style="background-image: url('/assets/app-bg-1920.webp')">
    <!-- SIDEBAR -->
    <aside class="w-fit p-4 h-screen">
      <div class="w-fit p-2 h-full bg-white rounded-xl flex flex-col justify-between">
        <!-- Logo -->
        <a href="{{ url('/app') }}" class="px-1 pt-1 cursor-pointer">
          <img class="max-w-7" src="/assets/logo.webp" alt="Eazyonline">
        </a>

        <!-- Main icons -->
        <nav class="flex flex-col gap-0">
          <!-- Dashboard -->
          <a href="{{ url('/app') }}"
             class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
            <i class="fa-solid fa-house text-[#215558] fa-sm"></i>
            <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
            <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                        opacity-0 invisible translate-x-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                        transition-all duration-300 ease-out">
              <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.overview">
                {{ __('sidebar.overview') }}
              </p>
            </div>
          </a>

          <!-- Support -->
          <a href="{{ url('/app/support') }}"
             class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
            <i class="fa-solid fa-ticket text-[#215558] fa-sm"></i>
            <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
            <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                        opacity-0 invisible translate-x-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                        transition-all duration-300 ease-out">
              <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.support">
                {{ __('sidebar.support') }}
              </p>
            </div>
          </a>

          @if ($user->rol === 'admin')
            <!-- Leads -->
            <a href="{{ url('/app/potentiele-klanten') }}"
               class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
              <i class="fa-solid fa-bolt text-[#215558] fa-sm"></i>
              <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
              <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                          opacity-0 invisible translate-x-1 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                          transition-all duration-300 ease-out">
                <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.leads">
                  {{ __('sidebar.leads') }}
                </p>
              </div>
            </a>

            <!-- Projecten -->
            <a href="{{ url('/app/projecten') }}"
               class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
              <i class="fa-solid fa-diagram-project text-[#215558] fa-sm"></i>
              <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
              <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                          opacity-0 invisible translate-x-1 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                          transition-all duration-300 ease-out">
                <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.projects">
                  {{ __('sidebar.projects') }}
                </p>
              </div>
            </a>

            <!-- Marketing -->
            <a href="{{ url('/app/marketing') }}"
               class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
              <i class="fa-solid fa-at text-[#215558] fa-sm"></i>
              <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
              <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                          opacity-0 invisible translate-x-1 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                          transition-all duration-300 ease-out">
                <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.marketing">
                  {{ __('sidebar.marketing') }}
                </p>
              </div>
            </a>
          @endif

          @if ($user->rol === 'admin' || $user->rol === 'medewerker')
            <!-- Gebruikers -->
            <a href="{{ url('/app/gebruikers/klanten') }}"
               class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
              <i class="fa-solid fa-user text-[#215558] fa-sm"></i>
              <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
              <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                          opacity-0 invisible translate-x-1 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                          transition-all duration-300 ease-out">
                <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.users">
                  {{ __('sidebar.users') }}
                </p>
              </div>
            </a>
          @endif

          @if ($user->rol === 'klant')
            <!-- Module toevoegen -->
            <a href="{{ url('/app/add') }}"
               class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
              <i class="fa-solid fa-plus text-[#215558] fa-sm"></i>
              <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
              <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                          opacity-0 invisible translate-x-1 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                          transition-all duration-300 ease-out">
                <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.add_module">
                  {{ __('sidebar.add_module') }}
                </p>
              </div>
            </a>
          @endif
        </nav>

        <!-- Bottom (audit / spacer) -->
        @if ($user->rol === 'admin' || $user->rol === 'medewerker')
          <a href="{{ url('/app/audit-log') }}"
             class="relative group w-full aspect-square rounded-xl flex items-center justify-center hover:bg-gray-200 transition duration-300">
            <i class="fa-solid fa-timer text-[#215558] fa-sm"></i>
            <span class="absolute left-full top-1/2 -translate-y-1/2 w-3 h-8 opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>
            <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                        opacity-0 invisible translate-x-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                        transition-all duration-300 ease-out">
              <p class="text-[#215558] text-xs font-semibold whitespace-nowrap" data-i18n="sidebar.audit_log">
                {{ __('sidebar.audit_log') }}
              </p>
            </div>
          </a>
        @else
          <div></div>
        @endif
      </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 h-screen py-4 pr-4 flex flex-col gap-4">
      <!-- Top bar -->
      <div class="shrink-0 w-full flex items-center justify-end p-2 bg-[#215558] rounded-xl">
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

        <!-- Avatar dropdown (with hover bridge) -->
        <div class="relative inline-block group">
          <div class="w-8 h-8 rounded-full bg-[#3b8b8f] hover:bg-[#ffffff75] transition duration-300 cursor-pointer flex items-center justify-center"
               aria-haspopup="true" aria-expanded="false" role="button" tabindex="0">
            <p class="text-sm text-[#215558] font-semibold">{{ $init }}</p>
          </div>

          <span class="absolute right-0 top-8 h-4 min-w-[300px] block opacity-0 pointer-events-none group-hover:pointer-events-auto"></span>

          <div class="min-w-[300px] px-1 py-3 rounded-xl bg-white border border-gray-200 shadow-md absolute right-0 top-12 z-50
                      opacity-0 invisible translate-y-1 pointer-events-none
                      group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                      group-focus-within:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:pointer-events-auto
                      transition-all duration-300 ease-out"
               role="menu" aria-label="Account menu">
            <p class="px-3 text-base text-[#215558] font-bold mb-1">{{ $user->name }}</p>

            <div>
              <a href="{{ url('/app/instellingen') }}" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                <i class="min-w-[16px] fa-solid fa-user text-[#215558] fa-sm"></i>
                <p class="px-1 text-sm text-[#215558] font-semibold" data-i18n="profile_dropdown.persoonlijke_gegevens">
                  {{ __('profile_dropdown.persoonlijke_gegevens') }}
                </p>
              </a>

              @if($user->rol === 'klant' && $user->is_company_admin)
                <a href="{{ url('/app/instellingen') }}" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                  <i class="min-w-[16px] fa-solid fa-wrench text-[#215558] fa-sm"></i>
                  <p class="px-1 text-sm text-[#215558] font-semibold" data-i18n="profile_dropdown.bedrijfsinstellingen">
                    {{ __('profile_dropdown.bedrijfsinstellingen') }}
                  </p>
                </a>
                <a href="{{ url('/app/instellingen') }}" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                  <i class="min-w-[16px] fa-solid fa-credit-card text-[#215558] fa-sm"></i>
                  <p class="px-1 text-sm text-[#215558] font-semibold" data-i18n="profile_dropdown.abonnement_betaling">
                    {{ __('profile_dropdown.abonnement_betaling') }}
                  </p>
                </a>
              @endif
            </div>

            @if($user->rol === 'klant')
              <div class="px-3 my-1"><hr class="border-gray-100"></div>
              <a href="#" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
                <i class="min-w-[16px] fa-solid fa-ticket text-[#215558] fa-sm"></i>
                <p class="px-1 text-sm text-[#215558] font-semibold" data-i18n="profile_dropdown.support">
                  {{ __('profile_dropdown.support') }}
                </p>
              </a>
            @endif

            <div class="px-3 my-1"><hr class="border-gray-100"></div>

            <a href="#" role="menuitem" class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300 -mb-2">
              <i class="min-w-[16px] fa-solid fa-right-from-bracket text-[#215558] fa-sm"></i>
              <p class="px-1 text-sm text-[#215558] font-semibold" data-i18n="profile_dropdown.uitloggen">
                {{ __('profile_dropdown.uitloggen') }}
              </p>
            </a>
          </div>
        </div>
      </div>

      <!-- Content area -->
      <div class="flex-1 grid grid-cols-5 min-h-0 w-full gap-4 overflow-y-visible">
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
            <h2 class="text-2xl font-black text-[#215558] text-center mt-6 mb-4 pointer-events-none">Welcome to your environment!</h2>
            <p class="text-sm font-medium text-[#215558] opacity-80 text-center max-w-[80%] mx-auto mb-8 pointer-events-none">We look forward to seeing what beautiful things you create. One of our team members will contact you soon to discuss the next steps.</p>

            <div class="flex items-center justify-end gap-2">
              <button
                id="first-login-go"
                hx-patch="{{ route('support.first_login.dismiss') }}"
                hx-target="#first-login-overlay"
                hx-swap="outerHTML"
                class="px-4 py-2 rounded-full text-sm font-semibold bg-[#0F9B9F] hover:bg-[#215558] text-white transition cursor-pointer"
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
  </body>
</html>
