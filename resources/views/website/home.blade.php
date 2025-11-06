<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @if (!app()->isLocal())
            <!-- Microsoft Clarity -->
            <script type="text/javascript">
                (function(c,l,a,r,i,t,y){
                    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                })(window, document, "clarity", "script", "t3zp4dys40");
            </script>

            <!-- Bezoek-id + identify + consent -->
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof clarity === 'function') {
                    var cookieName = 'eo_visit_id';
                    function getCookie(name){return document.cookie.split('; ').find(r=>r.startsWith(name+'='))?.split('=')[1];}
                    function setCookie(name,val,sec){var maxAge=sec?'; max-age='+sec:'';document.cookie=name+'='+val+'; path=/'+maxAge;}
                    var visitId = getCookie(cookieName) || (crypto.randomUUID?crypto.randomUUID(): (Date.now()+'-'+Math.random().toString(16).slice(2)));
                    setCookie(cookieName, visitId, 60*30);
                    var userId = {!! auth()->check() ? json_encode((string)auth()->user()->id) : 'null' !!};
                    clarity('identify', userId || visitId, visitId);
                    try { clarity('consent'); } catch(e){}
                }
            });
            </script>
        @endif

        <!-- head: preload wijst naar 1280 i.p.v. 1920 -->
        <link rel="preload" as="image"
            href="{{ asset('assets/eazyonline/hero-bg-1920.avif') }}"
            imagesrcset="
                {{ asset('assets/eazyonline/hero-bg-768.avif') }} 768w,
                {{ asset('assets/eazyonline/hero-bg-1280.avif') }} 1280w,
                {{ asset('assets/eazyonline/hero-bg-1920.avif') }} 1920w"
            imagesizes="100vw"
            type="image/avif">


        <!-- Charset & viewport -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Google Tag Manager -->
        <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s), dl=l!='dataLayer'?'&l='+l:'';
        j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-KPFZ9KQC');
        </script>
        <!-- End GTM -->

        <!-- SEO: dynamische titel & description met veilige defaults -->
        @php
            use Illuminate\Support\Str;

            $brand       = 'Eazyonline';
            $fallbackKW  = 'Website laten maken voor Starters & MKB';

            $primaryKW   = ($keywords[0] ?? $metaKeyword ?? $fallbackKW);
            $rawTitle    = trim(($metaTitle ?? '') ?: ($title ?? ''));

            if ($rawTitle === '') {
                $composed = "{$primaryKW} | {$brand}";
            } else {
                $composed = $rawTitle;

                if (!Str::contains(Str::lower($composed), Str::lower($primaryKW))) {
                    $composed = "{$primaryKW} | {$composed}";
                }

                if (!Str::contains(Str::lower($composed), Str::lower(" | {$brand}"))) {
                    $composed .= " | {$brand}";
                }
            }

            $composed = preg_replace('/\s*\|\s*/', ' | ', $composed);
            $composed = preg_replace('/(\s*\|\s*)+/', ' | ', $composed);
            $composed = preg_replace('/(\s*\|\s*){2,}/', ' | ', $composed);

            $metaTitleFinal = Str::limit($composed, 60, '');

            $defaultDesc = 'Eazyonline bouwt converterende websites en regelt je social media & content. Full-service voor starters en MKB — snel online en klaar voor groei.';
            $descRaw     = trim(($metaDescription ?? '') ?: ($description ?? '')) ?: $defaultDesc;

            $metaDescFinal = Str::limit($descRaw, 160, '');

            $canonical = ($canonical ?? '') ?: request()->url();
            $ogImage   = ($ogImage ?? '') ?: asset('assets/eazyonline/og/og-default.jpg');
        @endphp

        <title>{{ $metaTitleFinal }}</title>
        <meta name="description" content="{{ $metaDescFinal }}">

        <link rel="canonical" href="{{ $canonical }}"/>

        <!-- Open Graph / Twitter volgen dezelfde waarden -->
        <meta property="og:locale" content="nl_NL">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Eazyonline">
        <meta property="og:title" content="{{ $metaTitleFinal }}">
        <meta property="og:description" content="{{ $metaDescFinal }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="Eazyonline — websites & social media">

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $metaTitleFinal }}">
        <meta name="twitter:description" content="{{ $metaDescFinal }}">
        <meta name="twitter:image" content="{{ $ogImage }}">


        <!-- Robots: index alleen in productie -->
        @if (app()->environment('production'))
            <meta name="robots" content="index,follow">
        @else
            <meta name="robots" content="noindex,nofollow">
        @endif

        <!-- Favicon / App Icons -->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/eazyonline/favicon.webp') }}">
        <link rel="apple-touch-icon" href="{{ asset('assets/eazyonline/apple-touch-icon.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" />


        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&display=swap"
            media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&display=swap">
        </noscript>

        <!-- Vite: één keer includen -->
        @vite('resources/js/app.js')

        <!-- Lazy CSS (FontAwesome / Swiper) -->
        <link rel="preload" href="{{ asset('fontawesome/css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="{{ asset('fontawesome/css/all.min.css') }}"></noscript>
        <link rel="preload" href="{{ asset('swiperjs/swiper-bundle.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="{{ asset('swiperjs/swiper-bundle.min.css') }}"></noscript>

        <script src="{{ asset('swiperjs/swiper-bundle.min.js') }}" defer></script>
        <script src="{{ asset('gsap/gsap.min.js') }}" defer></script>
        <script src="{{ asset('gsap/ScrollTrigger.min.js') }}" defer></script>



        <style>
            body { font-family: 'Inter Tight', sans-serif; overflow-x: hidden; }
            html { overflow-x: hidden; scroll-behavior: smooth; }
            .caveat-font { font-family: 'Caveat', cursive !important; }
            .custom-shape{ width:532px;height:181px;background-color:red;clip-path:path("M0 40C0 17.9086 17.9086 0 40 0H221.806C242.819 0 259.775 17.1798 259.501 38.1908C259.226 59.2475 276.254 76.4466 297.312 76.3814H532C532 115.624 532 103.484 532 125.624V141C532 163.091 514.091 181 492 181H210H40C17.9086 181 0 163.091 0 141V40Z"); -webkit-clip-path:path("M0 40C0 17.9086 17.9086 0 40 0H221.806C242.819 0 259.775 17.1798 259.501 38.1908C259.226 59.2475 276.254 76.4466 297.312 76.3814H532C532 115.624 532 103.484 532 125.624V141C532 163.091 514.091 181 492 181H210H40C17.9086 181 0 163.091 0 141V40Z"); }
            @keyframes marquee { 0%{transform:translateX(0%)} 100%{transform:translateX(-50%)} }
            .content-faq{ overflow:hidden; transition:max-height .3s ease; max-height:0;}
            .title-faq span{ opacity:50%; transition:.3s ease-in-out;}
            .title-faq svg{ transition:transform .3s ease;}
            .faq-item{ border-bottom-width:0;}
            .faq-item.active{ border-bottom-width:1px; border-color:#eeeeee;}
            .faq-item.active .title-faq span{ opacity:100%;}
            #header-desktop.scrolled{ border:1px solid #eeeeee; box-shadow:0px 1px 2px -1px #0000001A, 0px 1px 3px 0px #0000001A;}
            #header-desktop.scrolled h2{ color:#0F9B9F !important;}
            #header-desktop.scrolled a{ color:#215558 !important;}
            #header-desktop.scrolled button{ color:#215558 !important;}
            #team-swiper{ overflow:hidden;}
            #team-swiper .swiper-wrapper{ align-items:stretch;}
            #team-swiper .swiper-slide{ height:auto;}
            #mobile-menu{ opacity:0; z-index:-999; transition:.3s ease-in-out;}
            #mobile-menu.active{ opacity:100%; z-index:99999; transform:translateX(0%);}
            #header-desktop{ border:1px solid transparent; transition: box-shadow .2s, border-color .2s; }
            #header-desktop.scrolled{ border-color:#eee; box-shadow:0 1px 3px 0 #0000001A; }

            #hero{ aspect-ratio:16/9; }
            .shadow-ez { box-shadow: 0 1px 3px 0 #0000001A; }
            @media (min-width:768px){ #hero{ aspect-ratio:auto; } }
        </style>
    </head>

    <body class="bg-white" x-data="{ tab: 'vernieuwen', showOverlay: false, isFocused: false }">
        <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KPFZ9KQC"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        <!-- Overlay -->
        <div 
            x-show="showOverlay"
            @click="showOverlay = false; isFocused = false"
            class="fixed inset-0 bg-black/30 z-40"
            x-transition.opacity
        ></div>

        @include('website.layouts.header_mobile')

        <div>
            <div id="hero" class="relative md:min-h-[1263px] bg-black">
                <picture>
                    <source type="image/avif"
                        srcset="{{ asset('assets/eazyonline/hero-bg-768.avif') }} 768w,
                                {{ asset('assets/eazyonline/hero-bg-1280.avif') }} 1280w,
                                {{ asset('assets/eazyonline/hero-bg-1920.avif') }} 1920w"
                        sizes="(max-width: 1280px) 100vw, 1280px" />
                    <source type="image/webp"
                        srcset="{{ asset('assets/eazyonline/hero-bg-768.webp') }} 768w,
                                {{ asset('assets/eazyonline/hero-bg-1280.webp') }} 1280w,
                                {{ asset('assets/eazyonline/hero-bg-1920.webp') }} 1920w"
                        sizes="(max-width: 1280px) 100vw, 1280px" />
                    <img
                        src="{{ asset('assets/eazyonline/hero-bg-1280.avif') }}"
                        alt="Eazyonline – websites voor starters en MKB"
                        width="1920" height="1080"
                        fetchpriority="high" loading="eager" decoding="async"
                        class="absolute inset-0 w-full h-full object-cover" />
                </picture>

                <div class="w-full h-auto fixed z-[999] top-[1.5rem] px-[1rem] md:px-[7rem]" id="page-wrapper-2">
                    @include('website.layouts.header')
                </div>
                <div class="w-full pt-[10rem] pb-[5rem] px-[1rem] relative" id="page-wrapper">
                    <div class="fade-in-up max-w-[1100px] h-full mx-auto flex flex-col items-center justify-center">
                        <h1 class="text-white text-[45px] px-[1rem] md:text-[72px] font-black leading-[1.05] text-center relative">
                            <span class="text-[#215558]">Websites</span> voor<br>Starters en MKB
                            <div class="hidden w-20 h-20 rotate-[15deg] rounded-full bg-[#215558]/20 absolute z-10 right-[-3rem] top-[-2rem] text-white font-extrabold leading-tight text-xs text-center md:flex flex-col items-center justify-center">VANAF<span class="text-[18px]">39,95</span>P/M</div>
                            <svg class="hidden md:block absolute rotate-[-35deg] z-1 -left-[3rem] top-[4rem] w-[75px]" width="177" height="265" viewBox="0 0 177 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M31.6609 252.913C26.9982 250.913 22.3461 248.492 17.4805 247.088C15.266 246.47 11.1171 246.988 10.217 248.494C8.08411 251.854 10.6409 254.327 14.0978 255.293C21.7134 257.419 29.3655 259.384 37.0227 261.4C38.6394 261.816 40.381 261.905 41.8624 262.544C48.8485 265.729 51.589 264.249 51.6232 256.693C51.6948 246.877 51.7664 237.061 51.1101 227.318C50.9342 225.028 48.1646 221.475 46.1268 221.048C41.838 220.165 40.7551 224.049 40.6913 227.622C40.6113 232.613 40.7392 237.584 40.8094 244.081C31.5548 232.362 25.3988 220.65 21.0472 208.075C3.36551 157.123 10.508 108.155 34.2655 60.992C41.1492 47.3499 51.8891 37.1 67.8978 34.4025C71.6673 33.7645 75.5975 34.2119 80.6821 34.1246C71.8231 46.9145 65.6677 59.4348 63.7071 73.4778C62.6592 80.8731 62.209 88.4712 62.7883 95.8617C63.5022 104.603 67.3931 112.03 76.5993 114.836C85.9718 117.731 94.073 114.72 100.476 107.84C110.145 97.3823 114.054 84.4564 114.19 70.543C114.389 53.5809 107.61 39.8323 93.0418 29.5878C116.93 6.38264 144.856 -0.0207063 176.447 6.11382C176.552 5.57892 176.651 4.99207 176.755 4.45717C174.852 3.8076 173.038 2.99186 171.052 2.5604C141.042 -4.04644 114.348 2.60123 91.2768 22.945C88.3332 25.5463 86.0505 26.3509 82.0166 25.3893C67.1178 21.8392 53.5625 25.9706 42.3548 35.7427C35.6875 41.5477 29.238 48.485 25.2888 56.2745C-0.071148 106.325 -7.95309 158.409 11.3911 212.342C16.4024 226.267 24.5595 239.092 31.5672 253.027L31.6609 252.913ZM86.514 37.3197C113.807 53.0095 106.726 91.0091 92.2112 103.419C82.6314 111.612 71.9181 107.383 70.3013 94.3259C67.7364 73.3384 74.1647 54.6013 86.5088 37.2678L86.514 37.3197Z" fill="#fff"/>
                            </svg>
                        </h1>
                        <div id="reguliere-tool" class="max-w-[500px] w-full h-auto p-[1.5rem] rounded-3xl bg-white mt-12"
                            x-data="typeWriter({
                                texts: [
                                '<strong>Leuk je te zien en welkom bij Eazyonline!</strong> In drie korte stappen vraag je jouw gratis website preview aan. Dankzij onze nieuwe slimme tool zie je binnen 1 minuut een volledig op maat gemaakte preview van jouw nieuwe website.',
                                'Top! Laten we beginnen. Wil je een nieuwe website starten of je huidige vernieuwen? 👇',
                                'Vul hieronder je huidige website url in, dan halen we daar alvast de juiste informatie uit zoals jouw logo, slogan en huisstijl. 👇',
                                'Beschrijf hieronder kort jouw bedrijf en werkzaamheden, dan bouwen we daarop jouw preview 👇',
                                'Wat is je doel met de nieuwe website? Bijvoorbeeld: meer klanten, beter imago of juist online verkopen 👇',
                                'Optioneel: Vul 1 of 2 voorbeeldwebsites in die jij mooi vindt (niet verplicht, het helpt ons om jouw stijl te begrijpen) 👇',
                                'Lekker, dat ging snel! Vul je contactgegevens in zodat we je persoonlijke preview kunnen toesturen en je even kunnen bellen voor een kort adviesgesprek. Geen zorgen, je zit nergens aan vast. 👇',
                                'Hoppa! Sit back & relax, wij gaan voor je aan de slag. Mocht je in de tussentijd vragen of opmerkingen hebben? Twijfel dan niet en bel ons of stuur een berichtje. Wij maken het Eazy voor jou!'
                                ]
                            })"
                            x-init="start()">

                            <div class="mb-4 text-[#215558]">
                                <div class="flex items-center gap-2 mb-4">
                                    <div class="w-6 h-6 bg-[#215558]/10 rounded-full">
                                        <img src="{{ asset('assets/eazyonline/memojis/martijn.webp') }}" width="512" height="512" loading="lazy" alt="Martijn">
                                    </div> 
                                    <h4 class="leading-tight text-sm font-semibold text-[#343434]">Martijn, Team Eazy</h4>
                                </div>

                                <!-- Typing tekst -->
                                <p class="text-sm text-[#343434] min-h-[20px]" x-html="typed"></p>

                                <!-- Stap 1: startknop -->
                                <div x-show="doneTyping && current === 0"
                                    x-transition
                                    class="mt-8 grid grid-cols-1 gap-2">
                                    <div class="flex items-center gap-2">
                                        <a href="/ai"
                                        class="bg-[#0F9B9F] w-full text-center text-white text-base font-medium px-6 py-3 rounded-full">
                                            AI-preview binnen 1 minuut
                                        </a>
                                    </div>
                                    <a href="#"
                                    @click.prevent="next()"
                                    class="bg-[#215558] w-full text-center text-white text-base font-medium px-6 py-3 rounded-full">
                                        Handmatige preview aanvragen
                                    </a>
                                </div>

                                <!-- Stap 2: keuze -->
                                <div x-show="doneTyping && current === 1"
                                    x-transition
                                    class="grid grid-cols-1 md:grid-cols-2 gap-2 -mb-3 mt-5">
                                    <a href="#"
                                    @click.prevent="choice='renew'; next()"
                                    class="bg-[#0F9B9F] text-center text-white text-base font-medium px-6 py-3 rounded-full">
                                        Vernieuw huidige
                                    </a>
                                    <a href="#"
                                    @click.prevent="choice='new'; next(3)" 
                                    class="bg-[#215558] text-center text-white text-base font-medium px-6 py-3 rounded-full">
                                        Start nieuwe
                                    </a>
                                </div>

                                <!-- Stap 3a: url invoeren (renew) -->
                                <div x-show="doneTyping && current === 2 && choice === 'renew'"
                                    x-transition
                                    class="mt-6 -mb-4 flex flex-col gap-4">
                                    <input type="url"
                                        placeholder="Jouw huidige website URL"
                                        x-model="form.url"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300">
                                    <div class="w-full flex items-center gap-2">
                                        <button @click.prevent="prev()" 
                                                class="bg-[#d8d8d8] min-w-[48px] min-h-[48px] text-white text-base font-medium rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76559 13.3656C7.61557 13.5156 7.41212 13.5998 7.19999 13.5998C6.98786 13.5998 6.78441 13.5156 6.63439 13.3656L1.83439 8.56562C1.68441 8.4156 1.60016 8.21215 1.60016 8.00002C1.60016 7.78789 1.68441 7.58444 1.83439 7.43442L6.63439 2.63442C6.78527 2.48869 6.98735 2.40806 7.19711 2.40988C7.40687 2.4117 7.60752 2.49584 7.75584 2.64417C7.90417 2.79249 7.9883 2.99314 7.99013 3.2029C7.99195 3.41266 7.91131 3.61474 7.76559 3.76562L4.33119 7.20002H13.6C13.8122 7.20002 14.0156 7.28431 14.1657 7.43434C14.3157 7.58436 14.4 7.78785 14.4 8.00002C14.4 8.21219 14.3157 8.41568 14.1657 8.56571C14.0156 8.71574 13.8122 8.80002 13.6 8.80002H4.33119L7.76559 12.2344C7.91556 12.3844 7.99982 12.5879 7.99982 12.8C7.99982 13.0122 7.91556 13.2156 7.76559 13.3656V13.3656Z" fill="white"/>
                                            </svg>
                                        </button>
                                        <button @click.prevent="next()" 
                                                class="bg-[#215558] w-full text-white text-base font-medium px-6 py-3 rounded-full">
                                            Volgende
                                        </button>
                                    </div>
                                </div>

                                <!-- Stap 3b: omschrijving (new) -->
                                <div x-show="doneTyping && current === 3 && choice === 'new'"
                                    x-transition
                                    class="mt-6 -mb-4 flex flex-col gap-4">
                                    <div>
                                        <input type="text"
                                            placeholder="Jouw bedrijfsnaam"
                                            x-model="form.company"
                                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300 mb-2">
                                        <textarea placeholder="Beschrijf je bedrijf en werkzaamheden in het kort..."
                                                x-model="form.description"
                                                class="w-full min-h-[100px] max-h-[100px] rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300"></textarea>
                                    </div>
                                    <div class="w-full flex items-center gap-2">
                                        <button @click.prevent="prev()" 
                                                class="bg-[#d8d8d8] min-w-[48px] min-h-[48px] text-white text-base font-medium rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76559 13.3656C7.61557 13.5156 7.41212 13.5998 7.19999 13.5998C6.98786 13.5998 6.78441 13.5156 6.63439 13.3656L1.83439 8.56562C1.68441 8.4156 1.60016 8.21215 1.60016 8.00002C1.60016 7.78789 1.68441 7.58444 1.83439 7.43442L6.63439 2.63442C6.78527 2.48869 6.98735 2.40806 7.19711 2.40988C7.40687 2.4117 7.60752 2.49584 7.75584 2.64417C7.90417 2.79249 7.9883 2.99314 7.99013 3.2029C7.99195 3.41266 7.91131 3.61474 7.76559 3.76562L4.33119 7.20002H13.6C13.8122 7.20002 14.0156 7.28431 14.1657 7.43434C14.3157 7.58436 14.4 7.78785 14.4 8.00002C14.4 8.21219 14.3157 8.41568 14.1657 8.56571C14.0156 8.71574 13.8122 8.80002 13.6 8.80002H4.33119L7.76559 12.2344C7.91556 12.3844 7.99982 12.5879 7.99982 12.8C7.99982 13.0122 7.91556 13.2156 7.76559 13.3656V13.3656Z" fill="white"/>
                                            </svg>
                                        </button>
                                        <button @click.prevent="next()" 
                                                class="bg-[#215558] w-full text-white text-base font-medium px-6 py-3 rounded-full">
                                            Volgende
                                        </button>
                                    </div>
                                </div>

                                <!-- Stap 4: doel website -->
                                <div x-show="doneTyping && current === 4"
                                    x-transition
                                    class="mt-6 -mb-4 flex flex-col gap-4">
                                    <textarea placeholder="Beschrijf je doel(en) met de nieuwe website..."
                                            x-model="form.goal"
                                            class="w-full min-h-[100px] max-h-[100px] rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300"></textarea>
                                    <div class="w-full flex items-center gap-2 mt-2">
                                        <button @click.prevent="prev()" 
                                                class="bg-[#d8d8d8] min-w-[48px] min-h-[48px] text-white text-base font-medium rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76559 13.3656C7.61557 13.5156 7.41212 13.5998 7.19999 13.5998C6.98786 13.5998 6.78441 13.5156 6.63439 13.3656L1.83439 8.56562C1.68441 8.4156 1.60016 8.21215 1.60016 8.00002C1.60016 7.78789 1.68441 7.58444 1.83439 7.43442L6.63439 2.63442C6.78527 2.48869 6.98735 2.40806 7.19711 2.40988C7.40687 2.4117 7.60752 2.49584 7.75584 2.64417C7.90417 2.79249 7.9883 2.99314 7.99013 3.2029C7.99195 3.41266 7.91131 3.61474 7.76559 3.76562L4.33119 7.20002H13.6C13.8122 7.20002 14.0156 7.28431 14.1657 7.43434C14.3157 7.58436 14.4 7.78785 14.4 8.00002C14.4 8.21219 14.3157 8.41568 14.1657 8.56571C14.0156 8.71574 13.8122 8.80002 13.6 8.80002H4.33119L7.76559 12.2344C7.91556 12.3844 7.99982 12.5879 7.99982 12.8C7.99982 13.0122 7.91556 13.2156 7.76559 13.3656V13.3656Z" fill="white"/>
                                            </svg>
                                        </button>
                                        <button @click.prevent="next()" 
                                                class="bg-[#215558] w-full text-white text-base font-medium px-6 py-3 rounded-full">
                                            Volgende
                                        </button>
                                    </div>
                                </div>

                                <!-- Stap 5: voorbeeldwebsites (optioneel) -->
                                <div x-show="doneTyping && current === 5"
                                    x-transition
                                    class="mt-6 -mb-4 flex flex-col gap-2">
                                    <input type="url"
                                        placeholder="Voorbeeld website 1 (optioneel)"
                                        x-model="form.example1"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300">
                                    <input type="url"
                                        placeholder="Voorbeeld website 2 (optioneel)"
                                        x-model="form.example2"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300">
                                    <div class="w-full flex items-center gap-2 mt-4">
                                        <button @click.prevent="prev()" 
                                                class="bg-[#d8d8d8] min-w-[48px] min-h-[48px] text-white text-base font-medium rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76559 13.3656C7.61557 13.5156 7.41212 13.5998 7.19999 13.5998C6.98786 13.5998 6.78441 13.5156 6.63439 13.3656L1.83439 8.56562C1.68441 8.4156 1.60016 8.21215 1.60016 8.00002C1.60016 7.78789 1.68441 7.58444 1.83439 7.43442L6.63439 2.63442C6.78527 2.48869 6.98735 2.40806 7.19711 2.40988C7.40687 2.4117 7.60752 2.49584 7.75584 2.64417C7.90417 2.79249 7.9883 2.99314 7.99013 3.2029C7.99195 3.41266 7.91131 3.61474 7.76559 3.76562L4.33119 7.20002H13.6C13.8122 7.20002 14.0156 7.28431 14.1657 7.43434C14.3157 7.58436 14.4 7.78785 14.4 8.00002C14.4 8.21219 14.3157 8.41568 14.1657 8.56571C14.0156 8.71574 13.8122 8.80002 13.6 8.80002H4.33119L7.76559 12.2344C7.91556 12.3844 7.99982 12.5879 7.99982 12.8C7.99982 13.0122 7.91556 13.2156 7.76559 13.3656V13.3656Z" fill="white"/>
                                            </svg>
                                        </button>
                                        <button @click.prevent="next()" 
                                                class="bg-[#215558] w-full text-white text-base font-medium px-6 py-3 rounded-full">
                                            Volgende
                                        </button>
                                    </div>
                                </div>

                                <!-- Stap 6: contactgegevens -->
                                <div x-show="doneTyping && current === 6"
                                    x-transition
                                    class="mt-6 -mb-4 flex flex-col gap-2">
                                    <input type="text"
                                        placeholder="Naam contactpersoon"
                                        x-model="form.contactName"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300">

                                    <input type="email"
                                        placeholder="E-mailadres"
                                        x-model="form.contactEmail"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300">

                                    <input type="tel"
                                        placeholder="Telefoonnummer"
                                        x-model="form.contactPhone"
                                        class="w-full rounded-xl border border-gray-300 px-4 py-3 text-[16px] md:text-sm focus:outline-none focus:ring-1 focus:ring-[#0F9B9F] transition duration-300">

                                    <div class="w-full flex items-center gap-2 mt-4">
                                        <button @click.prevent="prev()" 
                                                class="bg-[#d8d8d8] min-w-[48px] min-h-[48px] text-white text-base font-medium rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.76559 13.3656C7.61557 13.5156 7.41212 13.5998 7.19999 13.5998C6.98786 13.5998 6.78441 13.5156 6.63439 13.3656L1.83439 8.56562C1.68441 8.4156 1.60016 8.21215 1.60016 8.00002C1.60016 7.78789 1.68441 7.58444 1.83439 7.43442L6.63439 2.63442C6.78527 2.48869 6.98735 2.40806 7.19711 2.40988C7.40687 2.4117 7.60752 2.49584 7.75584 2.64417C7.90417 2.79249 7.9883 2.99314 7.99013 3.2029C7.99195 3.41266 7.91131 3.61474 7.76559 3.76562L4.33119 7.20002H13.6C13.8122 7.20002 14.0156 7.28431 14.1657 7.43434C14.3157 7.58436 14.4 7.78785 14.4 8.00002C14.4 8.21219 14.3157 8.41568 14.1657 8.56571C14.0156 8.71574 13.8122 8.80002 13.6 8.80002H4.33119L7.76559 12.2344C7.91556 12.3844 7.99982 12.5879 7.99982 12.8C7.99982 13.0122 7.91556 13.2156 7.76559 13.3656V13.3656Z" fill="white"/>
                                            </svg>
                                        </button>
                                        <button @click.prevent="submitForm()"
                                                :disabled="!form.contactName.trim() || !form.contactEmail.trim() || !form.contactPhone.trim()"
                                                class="bg-[#215558] w-full text-white text-base font-medium px-6 py-3 rounded-full disabled:opacity-50 disabled:cursor-not-allowed transition duration-300">
                                            Versturen
                                        </button>
                                    </div>
                                </div>

                                <!-- Stap 7: Afsluiting -->
                                <div x-show="doneTyping && current === 7"
                                    x-transition
                                    class="relative">
                                    <video preload="metadata" src="{{ asset('assets/eazyonline/videos/martijn-website.mp4') }}" poster="{{ asset('assets/eazyonline/videos/gifs/martijn-gif-desktop.gif') }}" class="hidden md:block max-w-full rounded-3xl mt-6 -mb-4" controls></video>
                                    <video preload="metadata" src="{{ asset('assets/eazyonline/videos/martijn-website.mp4') }}" poster="{{ asset('assets/eazyonline/videos/gifs/martijn-gif-mobile.gif') }}" class="block md:hidden max-w-full rounded-3xl mt-6 -mb-4" controls></video>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-[#fff] font-semibold leading-tight mt-6 text-center flex items-center gap-4">
                            <span class="flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                    class="w-4 h-4 fill-current inline-block">
                                    <path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                    class="w-4 h-4 fill-current inline-block">
                                    <path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                    class="w-4 h-4 fill-current inline-block">
                                    <path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                    class="w-4 h-4 fill-current inline-block">
                                    <path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/>
                                </svg>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"
                                    class="w-4 h-4 fill-current inline-block">
                                    <path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/>
                                </svg>

                            </span>
                            <span>
                                200+ klanten gingen jou voor!
                            </span>
                        </p>
                        <script>
                        function typeWriter({ texts }) {
                            return {
                                texts,
                                current: 0,
                                fullText: "",
                                typed: "",
                                speed: 18,
                                doneTyping: false,
                                choice: null,
                                form: {
                                    url: "",
                                    company: "",
                                    description: "",
                                    goal: "",
                                    example1: "",
                                    example2: "",
                                    contactName: "",
                                    contactEmail: "",
                                    contactPhone: ""
                                },
                                start() {
                                    if (!this.texts[this.current]) return;
                                    this.fullText = this.texts[this.current];
                                    this.typed = "";
                                    this.doneTyping = false;
                                    let i = 0;
                                    let timer = setInterval(() => {
                                        this.typed += this.fullText[i] ?? "";
                                        i++;
                                        if (i >= this.fullText.length) {
                                            clearInterval(timer);
                                            setTimeout(() => {
                                                this.doneTyping = true;
                                            }, 200);
                                        }
                                    }, this.speed);
                                },
                                next(forceIndex = null) {
                                    if (forceIndex !== null) {
                                        this.current = forceIndex;
                                        this.start();
                                        return;
                                    }

                                    if (this.choice === 'renew' && this.current === 2) {
                                        this.current = 4; // skip naar doel
                                        this.start();
                                        return;
                                    }

                                    if (this.choice === 'new' && this.current === 3) {
                                        this.current = 4; // naar doel
                                        this.start();
                                        return;
                                    }

                                    if (this.current < this.texts.length - 1) {
                                        this.current++;
                                        this.start();
                                    }
                                },
                                prev() {
                                    if (this.current === 5) {
                                        this.current = 4; // terug naar doel
                                        this.start();
                                        return;
                                    }

                                    if (this.current === 4) {
                                        if (this.choice === 'renew') {
                                            this.current = 2;
                                        } else if (this.choice === 'new') {
                                            this.current = 3;
                                        }
                                        this.start();
                                        return;
                                    }

                                    if (this.choice === 'new' && this.current === 3) {
                                        this.current = 1;
                                        this.start();
                                        return;
                                    }

                                    if (this.choice === 'renew' && this.current === 2) {
                                        this.current = 1;
                                        this.start();
                                        return;
                                    }

                                    if (this.current > 0) {
                                        this.current--;
                                        this.start();
                                    }
                                },
                                submitForm() {
                                    if (!this.form.contactName || !this.form.contactEmail || !this.form.contactPhone) {
                                        alert("Vul alle velden in voordat je verder gaat 🚀");
                                        return;
                                    }

                                    fetch('/aanvraag/website', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                        },
                                        body: JSON.stringify({
                                            ...this.form,
                                            choice: this.choice
                                        })
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            console.log("Opgeslagen ✅ ID:", data.id);
                                            this.current = 7;
                                            this.start();
                                        } else {
                                            alert("Er ging iets mis, probeer opnieuw ❌");
                                        }
                                    })
                                    .catch(err => console.error(err));
                                }
                            }
                        }
                        </script>
                    </div>
                </div>
                <img src="{{ asset('assets/eazyonline/logos-klanten-4.webp') }}" loading="lazy" alt="Klanten" class="fade-in-up mx-auto pb-[5rem] px-[1rem]">
                <div class="overflow-hidden w-full">
                    <div class="flex w-[300%] pb-[8rem] animate-[marquee_10s_linear_infinite] lg:animate-[marquee_35s_linear_infinite] gap-[2rem]">
                        <div class="flex gap-[2rem]">
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/kapotsterk-website.webp') }}"
                                    alt="Website preview Kapotsterk"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/renovion-website.webp') }}"
                                    alt="Website preview Renovion"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/thegrind-website.webp') }}"
                                    alt="Website preview The Grind"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/zonlichtdirect-website.webp') }}"
                                    alt="Website preview Zonlicht Direct"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/barbarosdetailing-website.webp') }}"
                                    alt="Website preview Barbaros Detailing"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/vastgoedfotovideo-website.webp') }}"
                                    alt="Website preview Vastgoed Foto Video"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/teamkampanje-website.webp') }}"
                                    alt="Website preview Team Kampanje"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/lunovakeukens-website.webp') }}"
                                    alt="Website preview Lunova Keukens"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                            <div class="min-w-[350px] h-[250px] md:min-w-[550px] md:h-[300px] rounded-3xl overflow-hidden">
                                <img
                                    src="{{ asset('assets/eazyonline/website-previews/blowertechnic-website.webp') }}"
                                    alt="Website preview Blower Technic"
                                    loading="lazy"
                                    decoding="async"
                                    fetchpriority="low"
                                    class="w-full h-full object-cover" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full h-auto py-[4rem] px-[1rem] md:px-[7rem] bg-[#f5f5f7]">
                <div class="max-w-[1200px] w-full mx-auto h-full flex flex-col gap-8">
                    <div class="fade-in-up w-full bg-white p-[2.5rem] flex flex-col justify-center rounded-3xl relative shadow-ez">
                        <h3 class="flex items-center gap-2 mb-[1rem]">
                            <svg width="33" height="32" viewBox="0 0 33 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.3079 4.6377H11.3957H22.1501C23.3321 4.63763 24.3505 4.63758 25.1661 4.74723C26.0401 4.86474 26.8779 5.12973 27.5574 5.80927C28.237 6.48883 28.5019 7.32663 28.6195 8.20065C28.7291 9.01619 28.7291 10.0346 28.729 11.2166V16.7256C28.7291 17.9074 28.7291 18.9258 28.6195 19.7414C28.5019 20.6154 28.237 21.4533 27.5574 22.1328C26.8779 22.8124 26.0401 23.0773 25.1661 23.1949C24.3505 23.3045 23.3321 23.3045 22.1501 23.3044H11.3079C10.1259 23.3045 9.1075 23.3045 8.29195 23.1949C7.41794 23.0773 6.58014 22.8124 5.90058 22.1328C5.22103 21.4533 4.95605 20.6154 4.83854 19.7414C4.72889 18.9258 4.72894 17.9074 4.72901 16.7254V11.3044C4.72901 11.275 4.72901 11.2458 4.72901 11.2166C4.72894 10.0346 4.72889 9.01619 4.83854 8.20065C4.95605 7.32663 5.22103 6.48883 5.90058 5.80927C6.58014 5.12973 7.41794 4.86474 8.29195 4.74723C9.1075 4.63758 10.1259 4.63763 11.3079 4.6377ZM8.64729 7.39011C8.06897 7.46787 7.8877 7.59339 7.78621 7.6949C7.6847 7.79639 7.55918 7.97766 7.48142 8.55598C7.39851 9.17266 7.39567 10.0096 7.39567 11.3044V16.6377C7.39567 17.9325 7.39851 18.7694 7.48142 19.3861C7.55918 19.9644 7.6847 20.1457 7.78621 20.2472C7.8877 20.3486 8.06897 20.4742 8.64729 20.552C9.26397 20.6349 10.1009 20.6377 11.3957 20.6377H22.0623C23.3571 20.6377 24.1941 20.6349 24.8107 20.552C25.389 20.4742 25.5703 20.3486 25.6718 20.2472C25.7733 20.1457 25.8989 19.9644 25.9766 19.3861C26.0595 18.7694 26.0623 17.9325 26.0623 16.6377V11.3044C26.0623 10.0096 26.0595 9.17266 25.9766 8.55598C25.8989 7.97766 25.7733 7.79639 25.6718 7.6949C25.5703 7.59339 25.389 7.46787 24.8107 7.39011C24.1941 7.30721 23.3571 7.30437 22.0623 7.30437H11.3957C10.1009 7.30437 9.26397 7.30721 8.64729 7.39011Z" fill="#0F9B9F"/>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.39551 25.971C3.39551 25.2346 3.99247 24.6377 4.72884 24.6377H28.7288C29.4652 24.6377 30.0622 25.2346 30.0622 25.971C30.0622 26.7074 29.4652 27.3044 28.7288 27.3044H4.72884C3.99247 27.3044 3.39551 26.7074 3.39551 25.971Z" fill="#0F9B9F"/>
                            </svg>
                            <span class="leading-tight text-xl font-semibold text-[#0F9B9F]">Eazy Website</span>
                        </h3>
                        <h3 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Professionele<br>presentatie van<br>je bedrijf</h3>
                        <a href="#uitleg-tool" class="bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-fit text-white text-base font-medium px-6 py-3 rounded-full">Bekijk hoe het werkt</a>
                        <p class="text-lg leading-tight font-medium text-[#215558] mt-[1rem]">Al vanaf 39,95 p/m. Met Eazyonline kun je een <br><strong>website laten maken</strong> die vertrouwen wekt en leads oplevert.</p>
                        <img src="{{ asset('assets/eazyonline/home/renovion-preview-in-laptop.webp') }}" loading="lazy" alt="Preview" class="hidden md:block absolute z-1 right-[3rem] max-h-[70%]">
                        <img src="{{ asset('assets/eazyonline/home/raphael-praatwolk.webp') }}" loading="lazy" alt="Raphael" class="hidden md:block absolute z-2 right-[35%]">
                        <div class="w-full mt-16 relative block md:hidden">
                            <img src="{{ asset('assets/eazyonline/home/raphael-praatwolk.webp') }}" loading="lazy" alt="Raphael" class="absolute z-1 top-[-2rem] left-0">
                            <img src="{{ asset('assets/eazyonline/home/renovion-preview-in-laptop.webp') }}" loading="lazy" alt="Preview" class="max-w-full">
                        </div>
                    </div>
                    <div class="w-full grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="fade-in-up w-full bg-white p-[2.5rem] flex flex-col rounded-3xl relative" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
                            <h3 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Meer klanten<br>minder zorgen</h3>
                            <p class="text-lg leading-tight font-medium text-[#215558]">Een website laten maken die vertrouwen wekt en klanten laat converteren. Simpel, effectief en gericht op groei.</p>
                        </div>
                        <div class="fade-in-up w-full bg-white p-[2.5rem] flex flex-col rounded-3xl relative" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
                            <h3 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Jouw verhaal,<br>krachtig verteld</h3>
                            <p class="text-lg leading-tight font-medium text-[#215558]">Wil je een website laten maken die jouw verhaal krachtig vertelt? Met slimme tools vertalen we jouw input naar sterke teksten en beelden die overtuigen.</p>
                        </div>
                        <div class="fade-in-up w-full bg-white p-[2.5rem] flex flex-col rounded-3xl relative" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
                            <h3 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Eerste indruk<br>die blijft hangen</h3>
                            <p class="text-lg leading-tight font-medium text-[#215558]">Een strak, mobielvriendelijk design wanneer je bij Eazyonline een website laat maken passend bij je merk en direct indruk makend.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full h-auto py-[4rem] px-[1rem] md:px-[7rem] bg-[#f4f5f7]">
                <div class="max-w-[1200px] mx-auto flex flex-col md:flex-row gap-16 relative">
                    <div class="fade-in-left w-full md:w-1/2 bg-white rounded-3xl p-[2.5rem]" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
                        <h3 class="flex items-center gap-2 mb-[1rem]">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                class="w-6 h-6 text-[#0f9b9f] fill-current inline-block">
                                <path d="M120 56c0-30.9 25.1-56 56-56l24 0c17.7 0 32 14.3 32 32l0 448c0 17.7-14.3 32-32 32l-32 0c-29.8 0-54.9-20.4-62-48-.7 0-1.3 0-2 0-44.2 0-80-35.8-80-80 0-18 6-34.6 16-48-19.4-14.6-32-37.8-32-64 0-30.9 17.6-57.8 43.2-71.1-7.1-12-11.2-26-11.2-40.9 0-44.2 35.8-80 80-80l0-24zm272 0l0 24c44.2 0 80 35.8 80 80 0 15-4.1 29-11.2 40.9 25.7 13.3 43.2 40.1 43.2 71.1 0 26.2-12.6 49.4-32 64 10 13.4 16 30 16 48 0 44.2-35.8 80-80 80-.7 0-1.3 0-2 0-7.1 27.6-32.2 48-62 48l-32 0c-17.7 0-32-14.3-32-32l0-448c0-17.7 14.3-32 32-32l24 0c30.9 0 56 25.1 56 56z"/>
                            </svg>

                            <span class="leading-tight text-xl font-semibold text-[#0F9B9F]">Eazy AI</span>
                        </h3>
                        <h3 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Bouw slimmer met AI, afgestemd op jouw sector</h3>
                        <p class="text-lg leading-tight font-medium text-[#215558] mb-[2rem]">Onze AI tool maakt in slechts 3 vragen jouw gratis website-preview. Speciaal ontwikkeld voor Starters en MKB die zonder gedoe nieuwe klanten willen aantrekken.</p>
                        <ul class="flex flex-col gap-4 mb-[3.5rem]">
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <div class="min-w-[33.75px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"
                                        class="w-6 h-6 text-[#215558] fill-current inline-block">
                                        <path d="M0 80C0 53.5 21.5 32 48 32l256 0c26.5 0 48 21.5 48 48l0 160c0 26.5-21.5 48-48 48L48 288c-26.5 0-48-21.5-48-48L0 80zm80 0c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm96 0c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm96 0c-13.3 0-24 10.7-24 24l0 112c0 13.3 10.7 24 24 24s24-10.7 24-24l0-112c0-13.3-10.7-24-24-24zm144 80c0-17.7 14.3-32 32-32l82.7 0c17 0 33.3 6.7 45.3 18.7L621.3 192c12 12 18.7 28.3 18.7 45.3L640 384c0 30.9-21.9 56.6-50.9 62.7-10 37.6-44.3 65.3-85.1 65.3-40.3 0-74.2-27.1-84.7-64l-118.6 0c-10.4 36.9-44.4 64-84.7 64-25.2 0-48-10.6-64-27.6-16 17-38.8 27.6-64 27.6-48.6 0-88-39.4-88-88l0-40c0-26.5 21.5-48 48-48l368 0 0-176zm160 77.3l-45.3-45.3-50.7 0 0 96 96 0 0-50.7zM128 424a40 40 0 1 0 -80 0 40 40 0 1 0 80 0zm376 40a40 40 0 1 0 0-80 40 40 0 1 0 0 80zM256 424a40 40 0 1 0 -80 0 40 40 0 1 0 80 0z"/>
                                    </svg>
                                </div>
                                Afgestemd op jouw sector
                            </li>
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <div class="min-w-[33.75px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                        class="w-6 h-6 text-[#215558] fill-current inline-block">
                                        <path d="M64 32C28.7 32 0 60.7 0 96L0 352c0 35.3 28.7 64 64 64l144 0-16 48-72 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l272 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-72 0-16-48 144 0c35.3 0 64-28.7 64-64l0-256c0-35.3-28.7-64-64-64L64 32zM96 96l320 0c17.7 0 32 14.3 32 32l0 160c0 17.7-14.3 32-32 32L96 320c-17.7 0-32-14.3-32-32l0-160c0-17.7 14.3-32 32-32z"/>
                                    </svg>
                                </div>
                                Binnen 3 vragen resultaat
                            </li>
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <div class="min-w-[33.75px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                        class="w-6 h-6 text-[#215558] fill-current inline-block">
                                        <path d="M176 0c-30.9 0-56 25.1-56 56l0 24c-44.2 0-80 35.8-80 80 0 15 4.1 29 11.2 40.9-25.7 13.3-43.2 40.1-43.2 71.1 0 26.2 12.6 49.4 32 64-10 13.4-16 30-16 48 0 44.2 35.8 80 80 80 .7 0 1.3 0 2 0 7.1 27.6 32.2 48 62 48l32 0c17.7 0 32-14.3 32-32l0-172-56 0c-6.6 0-12 5.4-12 12l0 4.4c16.5 7.6 28 24.3 28 43.6 0 26.5-21.5 48-48 48s-48-21.5-48-48c0-19.4 11.5-36.1 28-43.6l0-4.4c0-28.7 23.3-52 52-52l56 0 0-72-44.4 0c-7.6 16.5-24.3 28-43.6 28-26.5 0-48-21.5-48-48s21.5-48 48-48c19.4 0 36.1 11.5 43.6 28l44.4 0 0-124c0-17.7-14.3-32-32-32L176 0zM280 196l0 200 56 0c6.6 0 12-5.4 12-12l0-4.4c-16.5-7.6-28-24.3-28-43.6 0-26.5 21.5-48 48-48s48 21.5 48 48c0 19.4-11.5 36.1-28 43.6l0 4.4c0 28.7-23.3 52-52 52l-56 0 0 44c0 17.7 14.3 32 32 32l32 0c29.8 0 54.9-20.4 62-48 .7 0 1.3 0 2 0 44.2 0 80-35.8 80-80 0-18-6-34.6-16-48 19.4-14.6 32-37.8 32-64 0-30.9-17.6-57.8-43.2-71.1 7.1-12 11.2-26 11.2-40.9 0-44.2-35.8-80-80-80l0-24c0-30.9-25.1-56-56-56L312 0c-17.7 0-32 14.3-32 32l0 124 44.4 0c7.6-16.5 24.3-28 43.6-28 26.5 0 48 21.5 48 48s-21.5 48-48 48c-19.4 0-36.1-11.5-43.6-28L280 196zm88-36a16 16 0 1 0 0 32 16 16 0 1 0 0-32zM352 336a16 16 0 1 0 32 0 16 16 0 1 0 -32 0zM128 368a16 16 0 1 0 32 0 16 16 0 1 0 -32 0zm0-192a16 16 0 1 0 32 0 16 16 0 1 0 -32 0z"/>
                                    </svg>
                                </div>
                                Slimme AI funnel
                            </li>
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <div class="min-w-[33.75px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                        class="w-6 h-6 text-[#215558] fill-current inline-block">
                                        <path d="M133.8 36.3c10.9 7.6 13.5 22.6 5.9 33.4l-56 80c-4.1 5.8-10.5 9.5-17.6 10.1S52 158 47 153L7 113C-2.3 103.6-2.3 88.4 7 79S31.6 69.7 41 79l19.8 19.8 39.6-56.6c7.6-10.9 22.6-13.5 33.4-5.9zm0 160c10.9 7.6 13.5 22.6 5.9 33.4l-56 80c-4.1 5.8-10.5 9.5-17.6 10.1S52 318 47 313L7 273c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l19.8 19.8 39.6-56.6c7.6-10.9 22.6-13.5 33.4-5.9zM224 96c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zm0 160c0-17.7 14.3-32 32-32l224 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-224 0c-17.7 0-32-14.3-32-32zM160 416c0-17.7 14.3-32 32-32l288 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-288 0c-17.7 0-32-14.3-32-32zM64 376a40 40 0 1 1 0 80 40 40 0 1 1 0-80z"/>
                                    </svg>
                                </div>
                                Meer opdrachten, minder moeite
                            </li>
                        </ul>
                        <a href="#hero" class="bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-fit text-white text-base font-medium px-6 py-3 rounded-full">Gebruik nu onze AI tool</a>
                    </div>
                    <div class="fade-in-right w-full md:w-1/2 flex flex-col md:flex-row gap-8">
                        <div class="w-full md:w-1/2 h-full rounded-3xl aspect-[1.5/2] md:aspect-auto overflow-hidden">
                            <video class="w-full h-full object-cover" autoplay loop controls muted src="https://cdn1.site-media.eu/images/0/18656837/Eazyonline-Video1-Binnen1minuutjouwnieuwe-JuiMas_RYgcxVgFqTQ-PJQ.mp4"></video>
                        </div>
                        <div class="w-full md:w-1/2 h-full flex md:flex-col flex-row gap-8">
                            <div class="w-1/2 md:w-full aspect-square md:aspect-auto md:h-1/2 rounded-3xl bg-[url(https://cdn1.site-media.eu/images/1920/11070371/eazyonline-webdesign-raphael-overleg.webp)] bg-cover bg-center"></div>
                            <div class="w-1/2 md:w-full aspect-square md:aspect-auto md:h-1/2 rounded-3xl bg-[url(https://cdn1.site-media.eu/images/1920/11070376/eazyonline-webdesign-team-meeting.webp)] bg-cover bg-center"></div>
                        </div>
                    </div>
                    <div class="fade-in-left-delay absolute z-[1] left-[65%] md:left-[-3rem] top-[-6.25rem] md:top-[-5rem]">
                        <p class="text-[#0F9B9F] text-2xl rotate-[10deg] md:rotate-[-15deg] caveat-font pb-[1rem] -ml-[-2rem] md:-ml-[6rem] text-center">Binnen 1<br>minuut gefixt!</p>
                        <svg class="rotate-[-180deg] scale-x-[-1] md:scale-x-[1]" width="64" height="75" viewBox="0 0 64 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M55.7651 56.674C56.9303 55.675 57.9599 54.784 58.9625 53.92C59.8296 55 61.2115 55.945 61.4554 57.16C62.4851 61.9121 63.1896 66.7182 63.9212 71.4973C64.3277 74.2513 63.1625 75.3584 60.3986 74.8994C56.9845 74.3323 53.5161 73.8733 50.2103 72.9553C49.1535 72.6583 48.4761 71.0383 47.609 70.0123C48.8012 69.4183 49.9664 68.4192 51.2128 68.3382C52.7574 68.2302 54.3561 68.8512 56.6322 69.2832C45.1432 40.0417 25.8232 18.0093 -5.31099e-08 1.21501C0.216774 0.810004 0.433548 0.405005 0.677422 -2.76792e-06C1.89677 0.378004 3.22451 0.566994 4.28129 1.188C16.0142 8.12713 26.3109 16.8213 35.3883 26.9465C43.5716 36.0726 50.427 46.1168 55.7922 56.674L55.7651 56.674Z" fill="#215558"/>
                        </svg>
                    </div>
                </div>
            </div>
            <h2 class="sr-only">Zo werkt onze tool</h2>
            <div id="uitleg-tool" class="w-full h-auto py-[4rem] px-[1rem] md:px-[7rem] bg-[#f4f5f7]">
                <div class="max-w-[1200px] mx-auto flex flex-col items-center gap-8">
                    <p class="fade-in-up bg-white rounded-full px-4 py-2 flex items-center gap-2 text-[#088C90] text-sm font-bold">
                        <svg width="12" height="13" viewBox="0 0 12 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_19423_9968)">
                            <path d="M10.2359 7.60778C10.0674 7.4471 9.8388 7.35683 9.60047 7.35683C9.36213 7.35683 9.13355 7.4471 8.965 7.60778L6.90399 9.57306V1.35708C6.90399 1.12977 6.8093 0.911767 6.64073 0.751033C6.47217 0.5903 6.24355 0.5 6.00517 0.5C5.76678 0.5 5.53817 0.5903 5.3696 0.751033C5.20104 0.911767 5.10634 1.12977 5.10634 1.35708V9.57306L3.04534 7.60778C2.96242 7.52592 2.86324 7.46062 2.75358 7.4157C2.64392 7.37078 2.52598 7.34714 2.40663 7.34615C2.28729 7.34516 2.16893 7.36685 2.05847 7.40994C1.94801 7.45304 1.84765 7.51668 1.76326 7.59715C1.67887 7.67763 1.61213 7.77332 1.56693 7.87865C1.52174 7.98398 1.499 8.09684 1.50003 8.21065C1.50107 8.32445 1.52587 8.43692 1.57297 8.54148C1.62008 8.64605 1.68855 8.74062 1.7744 8.81969L5.3697 12.248C5.45341 12.328 5.55303 12.3912 5.66272 12.434C5.77142 12.4776 5.88815 12.5 6.00607 12.5C6.12398 12.5 6.24071 12.4776 6.34942 12.434C6.4591 12.3912 6.55872 12.328 6.64243 12.248L10.2377 8.81969C10.406 8.65873 10.5003 8.44064 10.5 8.21338C10.4997 7.98611 10.4047 7.76827 10.2359 7.60778Z" fill="#088C90"/>
                            </g>
                            <defs>
                            <clipPath id="clip0_19423_9968">
                            <rect width="12" height="12" fill="white" transform="translate(0 0.5)"/>
                            </clipPath>
                            </defs>
                        </svg>
                        Zo werkt het
                    </p>
                    <div class="w-[80%] md:w-[60%] mx-auto relative">
                        <div class="p-[1.5rem] bg-white rounded-3xl relative z-10" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
                            <div class="fade-in-up w-full flex items-center justify-between mb-10">
                                <div class="w-1/3">
                                    <div class="w-[83px] h-[24px] bg-gray-100 rounded-lg"></div>
                                </div>
                                <div class="w-1/3 flex items-center justify-center gap-1">
                                    <div class="menu-item-mockup w-[70px] h-[19px] bg-gray-300 rounded-lg transition"></div>
                                    <div class="menu-item-mockup w-[70px] h-[19px] bg-gray-300 rounded-lg transition"></div>
                                    <div class="menu-item-mockup w-[70px] h-[19px] bg-gray-300 rounded-lg transition"></div>
                                </div>
                                <div class="w-1/3 flex justify-end">
                                    <div class="w-[70px] h-[19px] bg-gray-300 rounded-lg"></div>
                                </div>
                            </div>
                            <div class="fade-in-up w-full aspect-[4/1] bg-gray-100 rounded-lg p-[1.5rem] flex flex-col justify-center gap-2 mb-10">
                                <div class="w-full rounded-lg h-[19px] bg-gray-300"></div>
                                <div class="w-full rounded-lg h-[19px] bg-gray-300"></div>
                                <div class="w-[100px] rounded-lg h-[19px] bg-gray-300"></div>
                            </div>
                            <div class="fade-in-up w-full flex gap-4 mb-10">
                                <div class="w-1/2">
                                    <div class="w-full rounded-lg aspect-[3/1] bg-gray-100 flex items-end justify-end p-[0.75rem] mb-3">
                                        <div class="w-[62px] h-[19px] bg-gray-300 rounded-lg"></div>
                                    </div>
                                    <div class="w-[83px] h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2 block md:hidden"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2 block md:hidden"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100"></div>
                                </div>
                                <div class="w-1/2">
                                    <div class="w-full rounded-lg aspect-[3/1] bg-gray-100 flex items-end justify-end p-[0.75rem] mb-3">
                                        <div class="w-[62px] h-[19px] bg-gray-300 rounded-lg"></div>
                                    </div>
                                    <div class="w-[83px] h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2 block md:hidden"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2 block md:hidden"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100"></div>
                                </div>
                            </div>
                            <div class="fade-in-up w-full aspect-[4/1] bg-gray-100 rounded-lg p-[1.5rem] flex md:hidden flex-col justify-center gap-2 mb-10">
                                <div class="w-full rounded-lg h-[19px] bg-gray-300"></div>
                                <div class="w-full rounded-lg h-[19px] bg-gray-300"></div>
                                <div class="w-[100px] rounded-lg h-[19px] bg-gray-300"></div>
                            </div>
                            <div class="fade-in-up w-full flex md:hidden gap-4 mb-10">
                                <div class="w-1/2">
                                    <div class="w-full rounded-lg aspect-[3/1] bg-gray-100 flex items-end justify-end p-[0.75rem] mb-3">
                                        <div class="w-[62px] h-[19px] bg-gray-300 rounded-lg"></div>
                                    </div>
                                    <div class="w-[83px] h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100"></div>
                                </div>
                                <div class="w-1/2">
                                    <div class="w-full rounded-lg aspect-[3/1] bg-gray-100 flex items-end justify-end p-[0.75rem] mb-3">
                                        <div class="w-[62px] h-[19px] bg-gray-300 rounded-lg"></div>
                                    </div>
                                    <div class="w-[83px] h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100 mb-2"></div>
                                    <div class="w-full h-[19px] rounded-lg bg-gray-100"></div>
                                </div>
                            </div>
                            <div class="fade-in-up w-full aspect-[3/1] bg-gray-300 rounded-lg mb-10"></div>
                            <div class="fade-in-up w-full aspect-[8/1] bg-gray-100 rounded-lg flex items-center justify-around">
                                <div class="w-1/6 h-[19px] rounded-lg bg-gray-300"></div>
                                <div class="w-1/6 h-[19px] rounded-lg bg-gray-300"></div>
                                <div class="w-1/6 h-[19px] rounded-lg bg-gray-300"></div>
                                <div class="w-1/6 h-[19px] rounded-lg bg-gray-300"></div>
                            </div>
                            <div class="fade-in-right w-[275px] md:w-[333px] bg-white rounded-3xl p-[2.5rem] absolute z-1 top-[3rem] right-[-2rem] md:right-[-12rem]" style="box-shadow: 0px 10px 10px 0px #0000000A; box-shadow: 0px 20px 25px -5px #0000001A;">
                                <h3 class="text-sm font-semibold leading-tight text-[#215558] mb-[0.5rem]">Start met gratis preview</h3>
                                <p class="text-gray-700 text-sm font-normal mb-[2rem]">Kies een design dat aansluit bij je bedrijf en bekijk direct online hoe je nieuwe website eruit komt te zien!</p>
                                <a href="/website" class="px-5 py-3 text-base font-medium rounded-full bg-[#215558] hover:bg-gray-800 transition duration-300 text-white">Maak preview</a>
                                <div class="w-[24px] h-[24px] rounded-full absolute z-1 top-[-0.5rem] left-[-0.6rem] bg-[#0F9B9F] text-white flex items-center justify-center" style="backdrop-filter: blur(4px);">
                                    1
                                </div>
                            </div>
                            <div class="fade-in-left w-[275px] md:w-[333px] bg-white rounded-3xl p-[2.5rem] absolute z-1 top-0 bottom-0 mt-auto mb-auto left-[-2rem] md:left-[-12rem] h-fit" style="box-shadow: 0px 10px 10px 0px #0000000A; box-shadow: 0px 20px 25px -5px #0000001A;">
                                <h3 class="text-sm font-semibold leading-tight text-[#215558] mb-[0.5rem]">Pakketten voor elk budget</h3>
                                <p class="text-gray-700 text-sm font-normal mb-[2rem]">Bij Eazy weet je altijd precies waar je aan toe bent. Onze pakketten groeien mee met jouw bedrijf en beschikbare budget!</p>
                                <a href="/website#pakketten" class="px-5 py-3 text-base font-medium rounded-full bg-[#215558] hover:bg-gray-800 transition duration-300 text-white">Bekijk pakketten</a>
                                <div class="w-[24px] h-[24px] rounded-full absolute z-1 top-[-0.5rem] right-[-0.6rem] bg-[#0F9B9F] text-white flex items-center justify-center" style="backdrop-filter: blur(4px);">
                                    2
                                </div>
                            </div>
                            <div class="fade-in-right w-[275px] md:w-[333px] bg-white rounded-3xl p-[2.5rem] absolute z-1 bottom-[3rem] md:bottom-[4rem] right-[-2rem] md:right-[5rem] h-fit" style="box-shadow: 0px 10px 10px 0px #0000000A; box-shadow: 0px 20px 25px -5px #0000001A;">
                                <h3 class="text-sm font-semibold leading-tight text-[#215558] mb-[0.5rem]">Ons team staat voor je klaar</h3>
                                <p class="text-gray-700 text-sm font-normal mb-[2rem]">Ons team van professionele designers bouwen binnen enkele weken jouw website af op basis van jouw wensen.</p>
                                <a href="/website" class="px-5 py-3 text-base font-medium rounded-full bg-[#215558] hover:bg-gray-800 transition duration-300 text-white">Start gratis preview</a>
                                <div class="w-[24px] h-[24px] rounded-full absolute z-1 top-[-0.5rem] left-[-0.6rem] bg-[#0F9B9F] text-white flex items-center justify-center" style="backdrop-filter: blur(4px);">
                                    3
                                </div>
                            </div>
                        </div>
                        <div class="hidden md:block fade-in-right-delay absolute z-[100] right-[-3.5rem] top-[40%]">
                            <p class="text-[#0F9B9F] text-2xl rotate-[15deg] caveat-font pb-[1rem] -mr-[9rem] text-center">Slimme onboardingstool<br>ondersteund door AI!</p>
                            <svg class="rotate-[180deg] scale-x-[-1]" width="64" height="75" viewBox="0 0 64 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M55.7651 56.674C56.9303 55.675 57.9599 54.784 58.9625 53.92C59.8296 55 61.2115 55.945 61.4554 57.16C62.4851 61.9121 63.1896 66.7182 63.9212 71.4973C64.3277 74.2513 63.1625 75.3584 60.3986 74.8994C56.9845 74.3323 53.5161 73.8733 50.2103 72.9553C49.1535 72.6583 48.4761 71.0383 47.609 70.0123C48.8012 69.4183 49.9664 68.4192 51.2128 68.3382C52.7574 68.2302 54.3561 68.8512 56.6322 69.2832C45.1432 40.0417 25.8232 18.0093 -5.31099e-08 1.21501C0.216774 0.810004 0.433548 0.405005 0.677422 -2.76792e-06C1.89677 0.378004 3.22451 0.566994 4.28129 1.188C16.0142 8.12713 26.3109 16.8213 35.3883 26.9465C43.5716 36.0726 50.427 46.1168 55.7922 56.674L55.7651 56.674Z" fill="#215558"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full h-auto py-[4rem] px-[1rem] md:px-[7rem] bg-[#f4f5f7]">
                <div class="max-w-[1200px] mx-auto flex flex-col md:flex-row gap-16 relative">
                    <div class="fade-in-left w-full md:w-1/2 flex flex-col gap-8">
                        <div class="w-full aspect-[2/1] rounded-3xl overflow-hidden">
                            <video preload="none" class="w-full object-cover" autoplay loop muted playsinline src="{{ asset('assets/eazyonline/videos/broll-2.mp4') }}"></video>
                        </div>
                        <div class="w-full flex gap-8">
                            <div class="w-1/2 aspect-square bg-cover bg-center rounded-3xl" style='background-image: url("{{ asset("assets/eazyonline/new/3.webp") }}")'></div>
                            <div class="w-1/2 aspect-square bg-cover bg-center rounded-3xl" style='background-image: url("{{ asset("assets/eazyonline/new/4.webp") }}")'></div>
                        </div>
                    </div>
                    <div class="fade-in-right w-full md:w-1/2 bg-white rounded-3xl p-[2.5rem]" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
                        <h2 class="flex items-center gap-2 mb-[1rem]">
                            <span class="leading-tight text-xl font-semibold text-[#0F9B9F]">Jouw digitale voorsprong</span>
                        </h2>
                        <h3 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Online succes,<br>slim geregeld</h3>
                        <p class="text-lg leading-tight font-medium text-[#215558] mb-[2rem]">Met Eazyonline een website laten maken betekent: geen stress over techniek of design, maar één partner die met je meedenkt en laat groeien.</p>
                        <ul class="flex flex-col gap-4 mb-[3.5rem]">
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_19342_12296)">
                                    <path d="M31.7766 0.286085C31.5906 0.0998059 31.3357 -0.0051746 31.074 0.000196489C30.9595 0.00166134 28.2283 0.0458508 24.6279 0.937453C21.2852 1.76533 16.5376 3.5146 12.9053 7.14693C12.4831 7.56929 12.0874 8.00704 11.7143 8.45552C8.76903 8.05586 5.93846 8.88301 3.45799 10.8859C1.34349 12.5932 0.23314 14.5512 0.186998 14.6337C0.0229352 14.9267 0.0209821 15.2832 0.181383 15.5781C0.342027 15.8728 0.642808 16.0642 0.978014 16.0852L6.51854 16.4311L5.00048 17.9492C4.81737 18.1323 4.71459 18.3806 4.71459 18.6396C4.71459 18.8984 4.81737 19.1467 5.00048 19.3298L5.76781 20.0971C5.75731 20.1076 5.74608 20.1179 5.73558 20.1284C2.44506 23.4191 2.343 28.518 2.34032 28.7336C2.3369 28.9968 2.44017 29.2502 2.62621 29.4362C2.80931 29.6193 3.0576 29.7221 3.31639 29.7221H3.32884C3.54442 29.7194 8.6433 29.6174 11.9338 26.3266C11.9446 26.3161 11.9548 26.3051 11.9651 26.2944L12.7327 27.062C12.9158 27.2451 13.1641 27.3479 13.4228 27.3479C13.6819 27.3479 13.9302 27.2451 14.1133 27.062L15.6313 25.5437L15.9773 31.0844C15.998 31.4194 16.1897 31.7202 16.4846 31.8808C16.6301 31.9604 16.7908 32 16.9517 32C17.116 32 17.2805 31.9585 17.4287 31.8755C17.5112 31.8291 19.4692 30.719 21.1765 28.6045C23.1794 26.1237 24.0068 23.2934 23.6072 20.3481C24.0554 19.9753 24.4934 19.5793 24.9155 19.1572C28.5481 15.5249 30.2971 10.7775 31.125 7.43453C32.0166 3.83443 32.0608 1.10298 32.0623 0.988478C32.0657 0.725539 31.9626 0.472121 31.7766 0.286085ZM7.07128 18.6396L8.73876 16.9721L15.0903 23.3237L13.4228 24.9912L7.07128 18.6396ZM7.1162 21.5092C7.12694 21.4985 7.13744 21.488 7.14818 21.4775L10.585 24.9145C10.5745 24.925 10.564 24.9358 10.5535 24.9463C8.65575 26.844 5.8994 27.4677 4.39086 27.6718C4.59496 26.1626 5.21898 23.4067 7.1162 21.5092ZM16.7046 22.1767L9.88549 15.3576C10.8559 12.9843 12.2585 10.5549 14.2859 8.52754C16.9858 5.82759 20.4014 4.25874 23.2451 3.35542L28.707 8.81758C27.8037 11.6611 26.2351 15.0766 23.5349 17.7766C21.4831 19.8283 19.0178 21.2268 16.7046 22.1767ZM29.2925 6.70699L25.3555 2.76997C27.3318 2.29854 29.0237 2.0981 30.0491 2.01338C29.9644 3.03902 29.7639 4.73091 29.2925 6.70699ZM4.7329 12.3662C6.47338 10.9765 8.35253 10.2883 10.3418 10.3066C9.41234 11.7124 8.67748 13.1694 8.09813 14.5734L2.8452 14.2456C3.312 13.674 3.94481 12.9956 4.7329 12.3662ZM19.6965 27.3296C19.0669 28.1176 18.3884 28.7507 17.8169 29.2175L17.489 23.9643C18.8931 23.3847 20.3501 22.6501 21.7559 21.7207C21.7744 23.7102 21.0862 25.5893 19.6965 27.3296Z" fill="#215558"/>
                                    <path d="M23.3496 8.71289C22.6069 7.96997 21.6191 7.56104 20.5688 7.56104C19.5183 7.56104 18.5305 7.96997 17.7878 8.71289C16.2543 10.2463 16.2543 12.7412 17.7878 14.2747C18.5305 15.0176 19.5183 15.4268 20.5688 15.4268C21.6191 15.4268 22.6066 15.0176 23.3496 14.2747C24.0925 13.532 24.5014 12.5442 24.5014 11.4939C24.5014 10.4434 24.0925 9.45557 23.3496 8.71289ZM21.9689 12.894C21.5949 13.2683 21.0976 13.4741 20.5688 13.4741C20.0397 13.4741 19.5424 13.2683 19.1682 12.8943C18.3962 12.1221 18.3962 10.8657 19.1682 10.0935C19.5424 9.71948 20.0397 9.51343 20.5688 9.51343C21.0976 9.51343 21.5949 9.71948 21.9689 10.0935C22.343 10.4675 22.549 10.9648 22.549 11.4939C22.549 12.023 22.343 12.52 21.9689 12.894Z" fill="#215558"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_19342_12296">
                                    <rect width="32" height="32" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                Binnen no-time professioneel online
                            </li>
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M23.8001 12.2605L24.8735 10.5901C24.277 4.37967 18.2995 -0.0735738 12.5726 0.000920791C5.46469 0.0169196 -0.0140345 5.76513 2.7006e-05 12.5787C2.7006e-05 16.0939 1.48692 19.4733 4.07936 21.8502C4.85893 22.5649 5.21678 23.6235 5.03673 24.682L3.792 32H17.1792L17.6425 27.7282L19.1382 28.1246C21.0846 28.7061 23.3085 27.0658 23.2455 24.9641V20.4802L26.9247 19.1638L23.8001 12.2605ZM21.3706 19.1597V21.5947H19.1033V23.4695H21.3706V24.9641C21.3924 25.7877 20.5171 26.5669 19.6185 26.3123L13.8305 24.7783C13.4437 24.6758 13.1191 24.4117 12.9402 24.0539L11.9698 22.113L10.2928 22.9515L11.2633 24.8924C11.6828 25.7313 12.4434 26.3503 13.3501 26.5907L15.8093 27.2425L15.4967 30.1252H6.01272L6.88509 24.9965C7.17238 23.3078 6.59718 21.615 5.34645 20.4683C3.14024 18.4455 1.87489 15.5699 1.87489 12.5787C1.86745 6.52339 6.80572 1.85941 12.5732 1.87804C17.5088 1.79685 22.1619 5.59558 22.9248 10.1544L21.6716 12.1047L24.3778 18.0837L21.3706 19.1597Z" fill="#215558"/>
                                    <path d="M19.6048 8.91325C19.5172 8.07031 19.1006 7.27549 18.4286 6.70634C17.8139 6.18569 17.0694 5.91296 16.3037 5.91296C16.3001 5.91296 16.2965 5.91296 16.2929 5.91296C15.7612 4.92366 14.8296 4.25639 13.6855 4.07766C12.5476 3.89998 11.4061 4.26008 10.6011 5.01396C9.40492 4.65749 8.1212 4.88316 7.11346 5.65929C6.09278 6.44549 5.54026 7.63684 5.58276 8.88518C4.67526 9.5017 4.10205 10.5452 4.10205 11.6676C4.10205 13.6035 5.677 15.1784 7.61286 15.1784C7.83278 15.1784 8.01171 15.3573 8.01171 15.5773V17.6683H11.9332L12.5967 15.4623C12.6478 15.2925 12.8013 15.1784 12.9787 15.1784H17.7384C19.5838 15.1784 21.0543 13.4697 21.0429 11.6676C21.0428 10.5594 20.494 9.53401 19.6048 8.91325ZM17.6261 13.3036H12.9785C11.9674 13.3036 11.0924 13.9541 10.8013 14.9223L10.5393 15.7934H9.88651V15.5773C9.88651 14.3235 8.86652 13.3036 7.6128 13.3036C6.71074 13.3036 5.97685 12.5697 5.97685 11.6676C5.96473 10.633 6.92085 10.2401 7.67829 9.99404C7.2277 8.58196 7.57086 7.6664 8.2575 7.14469C9.2473 6.3633 10.3174 6.84096 11.1834 7.34298C11.6266 6.5511 12.3373 5.74029 13.3962 5.93009C14.5272 6.07608 14.8202 7.1415 15.0584 8.06956C15.7366 7.87719 16.5769 7.56603 17.2169 8.13705C17.7834 8.55352 17.7906 9.39321 17.6789 10.0394C18.3943 10.2855 19.1867 10.7869 19.168 11.6676C19.168 12.4718 18.4945 13.1819 17.6261 13.3036Z" fill="#215558"/>
                                </svg>
                                Een design dat écht past bij jouw merk
                            </li>
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_19342_12307)">
                                    <path d="M14.3992 15.7255C14.5822 15.9085 14.8221 16 15.062 16C15.302 16 15.5419 15.9085 15.7249 15.7254L19.4749 11.9754C19.841 11.6093 19.841 11.0157 19.4749 10.6496C19.1089 10.2835 18.5152 10.2835 18.1491 10.6496L15.062 13.7367L13.8499 12.5247C13.4839 12.1585 12.8902 12.1585 12.5241 12.5247C12.158 12.8908 12.158 13.4843 12.5241 13.8505L14.3992 15.7255Z" fill="#215558"/>
                                    <path d="M16 20.6875C20.1355 20.6875 23.5 17.323 23.5 13.1875C23.5 9.052 20.1355 5.6875 16 5.6875C11.8645 5.6875 8.5 9.052 8.5 13.1875C8.5 17.323 11.8645 20.6875 16 20.6875ZM16 7.5625C19.1016 7.5625 21.625 10.0859 21.625 13.1875C21.625 16.2891 19.1016 18.8125 16 18.8125C12.8984 18.8125 10.375 16.2891 10.375 13.1875C10.375 10.0859 12.8984 7.5625 16 7.5625Z" fill="#215558"/>
                                    <path d="M3.86772 17.7193L6.85585 19.831L7.83891 22.997L5.38035 28.1934C5.23247 28.5059 5.2686 28.8745 5.47441 29.1523C5.6801 29.4302 6.02178 29.5722 6.36422 29.5219L9.08378 29.1218L10.5042 31.5378C10.6731 31.825 10.981 32 11.3121 32C11.327 32 11.342 31.9997 11.3569 31.999C11.7048 31.9824 12.0148 31.7743 12.162 31.4587C12.2118 31.3523 11.7387 32.3632 14.86 25.6938L15.4389 26.1262C15.6052 26.2504 15.8025 26.3125 15.9998 26.3125C16.1972 26.3125 16.3944 26.2503 16.5608 26.1262L17.1311 25.7003C20.2523 32.3696 19.7755 31.3507 19.826 31.4587C19.98 31.7889 20.3113 32 20.6758 32C21.007 32 21.315 31.825 21.4839 31.5377L22.9043 29.1217L25.6239 29.5218C25.966 29.5721 26.308 29.4301 26.5137 29.1523C26.7195 28.8744 26.7557 28.5059 26.6078 28.1933L24.1562 23.0118L25.1439 19.8309L28.132 17.7192C28.4712 17.4796 28.6122 17.0454 28.4787 16.6522L27.3023 13.1875L28.4785 9.72272C28.612 9.32953 28.471 8.89541 28.1318 8.65572L25.1437 6.54403L24.0587 3.04959C23.9368 2.65697 23.5737 2.39009 23.1635 2.39009C23.1594 2.39009 19.499 2.43803 19.499 2.43803L16.57 0.193406C16.2336 -0.0644687 15.766 -0.0644687 15.4295 0.193406L12.5005 2.43809L8.84841 2.39022C8.43547 2.38459 8.06391 2.65309 7.94085 3.04966L6.85585 6.54409L3.86772 8.65578C3.5286 8.89541 3.38753 9.32959 3.52103 9.72278L4.69722 13.1875L3.52103 16.6523C3.38753 17.0455 3.5286 17.4797 3.86772 17.7193ZM11.2164 29.0497L10.38 27.6272C10.1866 27.2982 9.81303 27.1193 9.43541 27.1748L7.82435 27.4118L9.44947 23.977L12.5072 23.9369L13.3273 24.5493C10.7957 29.9467 11.2785 28.9173 11.2164 29.0497ZM22.5525 27.1748C22.1745 27.1193 21.8012 27.2982 21.6078 27.6272L20.7715 29.0497C20.71 28.9184 21.1903 29.9424 18.6637 24.5558L19.4925 23.9369L22.5383 23.9768L24.1636 27.4118L22.5525 27.1748ZM6.57503 12.8862L5.5216 9.78297L8.19785 7.89159C8.36635 7.77247 8.49085 7.60109 8.5521 7.40397L9.52391 4.27422L12.8008 4.31716C13.0122 4.32109 13.2163 4.25184 13.3833 4.12384L15.9998 2.11872L18.6163 4.12391C18.7833 4.25191 18.99 4.32103 19.1988 4.31722L22.4757 4.27428L23.4475 7.40403C23.5087 7.60116 23.6332 7.77253 23.8017 7.89166L26.478 9.78303L25.4245 12.8862C25.3582 13.0817 25.3582 13.2935 25.4245 13.489L26.478 16.5922L23.8017 18.4835C23.6332 18.6027 23.5087 18.774 23.4475 18.9712L22.4757 22.1009L19.1988 22.058C18.9932 22.0548 18.7909 22.1207 18.6256 22.2442C18.0925 22.6423 16.9602 23.4878 15.9997 24.2051C15.4388 23.7862 13.8617 22.6085 13.3738 22.2442C13.2118 22.1232 13.015 22.0579 12.8129 22.0579C12.8088 22.0579 9.52378 22.1009 9.52378 22.1009L8.55197 18.9712C8.49078 18.774 8.36622 18.6027 8.19772 18.4835L5.52147 16.5922L6.57491 13.489C6.64141 13.2935 6.64141 13.0816 6.57503 12.8862Z" fill="#215558"/>
                                    </g>
                                    <defs>
                                    <clipPath id="clip0_19342_12307">
                                    <rect width="32" height="32" fill="white"/>
                                    </clipPath>
                                    </defs>
                                </svg>
                                Techniek die gewoon werkt
                            </li>
                            <li class="flex items-center gap-4 text-lg leading-tight font-medium text-gray-700">
                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.9999 20.2208C14.5486 20.2209 13.0978 19.866 11.7749 19.1565L2.23539 14.0396C0.852087 13.2977 -0.00430544 11.8629 0.000189904 10.2952C0.00474518 8.72753 0.869469 7.29777 2.25703 6.56377L11.8205 1.5052C14.4375 0.12099 17.5622 0.12099 20.1792 1.5052L29.7428 6.56377C31.1303 7.29771 31.995 8.72747 31.9996 10.2952C32.0041 11.8629 31.1477 13.2977 29.7644 14.0396L20.2249 19.1565C18.9023 19.866 17.4509 20.2207 15.9999 20.2208ZM15.9999 2.96726C14.9659 2.96726 13.932 3.21654 12.9894 3.71511L3.42606 8.77374C2.84826 9.07937 2.50212 9.65093 2.50026 10.3025C2.4984 10.9541 2.84113 11.5276 3.41719 11.8366L12.9567 16.9535C14.8624 17.9756 17.1377 17.9756 19.0434 16.9535L28.583 11.8366C29.159 11.5276 29.5017 10.9542 29.4999 10.3025C29.498 9.65087 29.1519 9.07937 28.5741 8.77374L19.0103 3.71511C18.0678 3.2166 17.0339 2.96726 15.9999 2.96726ZM20.2249 30.4688L31.3405 24.5068C31.9489 24.1805 32.1775 23.4227 31.8512 22.8144C31.5249 22.2061 30.7673 21.9774 30.1589 22.3037L19.0432 28.2657C17.1377 29.2879 14.8624 29.2879 12.9518 28.2631L1.83621 22.3636C1.22628 22.04 0.469683 22.272 0.146139 22.8817C-0.177526 23.4916 0.0544337 24.2481 0.664242 24.5718L11.7749 30.4687C13.0979 31.1783 14.5487 31.5331 16 31.5331C17.4509 31.5331 18.9023 31.1783 20.2249 30.4688ZM20.2249 24.8439L31.3405 18.8819C31.9489 18.5556 32.1775 17.7979 31.8512 17.1896C31.5249 16.5811 30.7673 16.3526 30.1589 16.6788L19.0432 22.6409C17.1377 23.6631 14.8624 23.663 12.9518 22.6383L1.83621 16.7388C1.22628 16.4151 0.469683 16.6471 0.146139 17.2569C-0.177526 17.8667 0.0544337 18.6233 0.664242 18.9469L11.7749 24.8439C13.0979 25.5535 14.5487 25.9082 16 25.9082C17.4509 25.9082 18.9023 25.5534 20.2249 24.8439Z" fill="#215558"/>
                                </svg>
                                Altijd klaar om met je mee te groeien
                            </li>
                        </ul>
                        <a href="/website" class="bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-fit text-white text-base font-medium px-6 py-3 rounded-full">Maak gratis preview</a>
                    </div>
                </div>
            </div>
            <div class="w-full bg-[#f4f5f7] py-[4rem] px-[1rem] md:px-[7rem] hidden md:flex flex-col">
                <h2 class="fade-in-up text-[#215558] leading-tight text-4xl font-extrabold mb-[2rem] text-center">Jouw website, ons vakmanschap</h2>
                <div class="max-w-[1200px] mx-auto">
                    <div class="fade-in-up swiper" id="team-swiper">
                        <div class="swiper-wrapper">
                            <!-- 1 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/raphael.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Eigenaar</p>
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Sales</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Raphael Muskitta</h2>
                                        <p class="text-base text-white italic">"Relaties bouwen is de sleutel tot succes,<br>de rest volgt vanzelf."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 2 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/martijn.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Eigenaar</p>
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Lead Development</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Martijn Visser</h2>
                                        <p class="text-base text-white italic">"Mijn missie: technologie vertalen naar succes voor onze klanten."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 3 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/laurina.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Front-end Developer</p>
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Klantenservice</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Laurina Pesulima</h2>
                                        <p class="text-base text-white italic">"Design met een glimlach,<br>service met een plan."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 4 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/boyd.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Front-end Developer</p>
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Back-end Developer</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Boyd Halfman</h2>
                                        <p class="text-base text-white italic">"In code bestaat geen onmogelijk,<br>alleen uitdagingen."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 5 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/yael.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Front-end Developer</p>
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Back-end Developer</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Yael Scholten</h2>
                                        <p class="text-base text-white italic">"Ik bouw dingen die<br>wél werken"</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 6 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/joris.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Front-end Developer</p>
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Back-end Developer</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Joris Lindner</h2>
                                        <p class="text-base text-white italic">"De perfecte website draait op balans<br>tussen code en design."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 7 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/laurenzo.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Mediavormgever</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Laurenzo Soemopawiro</h2>
                                        <p class="text-base text-white italic">"Elke post is een kans om<br>jouw verhaal te vertellen."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 8 -->
                            <div class="swiper-slide select-none">
                                <div class="rounded-3xl aspect-[1.5/2] relative overflow-hidden border border-[#eeeeee] group">
                                    <div class="w-full h-full bg-cover bg-center absolute z-1" style='background-image: url("{{ asset("assets/eazyonline/team/johnny.webp") }}")'></div>
                                    <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                                    <div class="w-full h-full absolute z-3 p-[2rem] flex flex-col justify-between">
                                    <div class="flex gap-2">
                                        <p class="bg-[#ffffff]/30 border border-[#ffffff] text-xs px-2 py-1 rounded-full text-white font-semibold">Mediavormgever</p>
                                    </div>
                                    <div class="flex flex-col gap-[1.5rem]">
                                        <h2 class="text-2xl font-bold text-white">Johnny Muskitta</h2>
                                        <p class="text-base text-white italic">"Elke post is een kans om<br>jouw verhaal te vertellen."</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 9 vacature -->
                            <div class="swiper-slide select-none">
                                <div class="bg-white rounded-3xl aspect-[1.5/1] p-[2rem] relative">
                                    <h2 class="text-[#215558] text-4xl font-extrabold mb-[1.5rem]">Kom ons team versterken!</h2>
                                    <p class="text-lg font-medium text-[#215558] mb-[2rem]">Wij zijn altijd op zoek naar creatieve en gedreven talenten die samen met ons willen bouwen aan online succes.</p>
                                    <div class="grid gap-2">
                                    <div class="rounded-2xl border border-[#eeeeee] p-6">
                                        <h3 class="text-[#215558] text-xl font-bold mb-2">Stagiair Developer</h3>
                                        <a href="mailto:info@eazyonline.nl" class="text-base text-[#215558] underline">Solliciteren</a>
                                    </div>
                                    <div class="rounded-2xl border border-[#eeeeee] p-6">
                                        <h3 class="text-[#215558] text-xl font-bold mb-2">Stagiair Mediavormgever</h3>
                                        <a href="mailto:info@eazyonline.nl" class="text-base text-[#215558] underline">Solliciteren</a>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fade-in-up w-full flex items-center justify-center mt-[1.5rem] gap-4">
                        <a href="/website" class="bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-fit text-white text-base font-medium px-6 py-3 rounded-full">Maak gratis preview</a>
                        <a href="/over-ons/team" class="bg-[#215558] hover:bg-gray-800 transition duration-300 w-fit text-white text-base font-medium px-6 py-3 rounded-full">Ontmoet ons team</a>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const el = document.querySelector('#team-swiper');
                    if (!el || !window.Swiper) return;

                    new window.Swiper('#team-swiper', {
                        effect: 'slide',
                        speed: 650,
                        slidesPerView: 3,
                        slidesPerGroup: 1,
                        spaceBetween: 32,
                        centeredSlides: false,
                        loop: true,
                        loopAdditionalSlides: 3,
                        autoplay: { delay: 3000, disableOnInteraction: false },

                        breakpoints: {
                        0:     { slidesPerView: 1, spaceBetween: 16 },
                        640:   { slidesPerView: 2, spaceBetween: 24 },
                        1024:  { slidesPerView: 3, spaceBetween: 32 },
                        },

                        navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                        pagination: { el: '.swiper-pagination', clickable: true },

                        observer: true,
                        observeParents: true,
                        resizeObserver: true,
                    });
                });
            </script>
            <div class="w-full py-[4rem] px-[1rem] md:px-[7rem] bg-[#f4f5f7]">
                <div class="max-w-[1200px] mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 relative">
                    <div class="fade-in-up bg-white rounded-3xl aspect-[1/1.75] relative overflow-hidden">
                        <video preload="none" class="w-full h-full object-cover" src="{{ asset('assets/eazyonline/videos/reviews/barbarosdetailing.mp4') }}" autoplay playsinline loop muted controls></video>
                    </div>
                    <div class="fade-in-up-delay bg-white rounded-3xl aspect-[1/1.75] relative overflow-hidden">
                        <video preload="none" class="w-full h-full object-cover" src="{{ asset('assets/eazyonline/videos/reviews/thegrind.mp4') }}" autoplay playsinline loop muted controls></video>
                    </div>
                    <div class="fade-in-up-more-delay bg-white rounded-3xl aspect-[1/1.75] relative overflow-hidden">
                        <video preload="none" class="w-full h-full object-cover" src="{{ asset('assets/eazyonline/videos/reviews/kapotsterk.mp4') }}" autoplay playsinline loop muted controls></video>
                    </div>
                    <div class="fade-in-up-even-more-delay bg-white rounded-3xl aspect-[1/1.75] relative overflow-hidden">
                        <video preload="none" class="w-full h-full object-cover" src="{{ asset('assets/eazyonline/videos/reviews/2befit.mp4') }}" autoplay playsinline loop muted controls></video>
                    </div>
                    <div class="fade-in-left-delay absolute z-[1] left-[4rem] top-[-6rem] md:left-[-3rem] md:top-[8rem]">
                        <p class="text-[#0F9B9F] text-2xl rotate-[15deg] md:rotate-[-15deg] caveat-font pb-[1rem] -ml-[-5rem] -mb-[2rem] md:-mb-0 md:-ml-[7rem] text-center">"Super snel<br>en Eazy!"</p>
                        <svg class="md:rotate-[-180deg] rotate-[-90deg]" width="64" height="75" viewBox="0 0 64 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M55.7651 56.674C56.9303 55.675 57.9599 54.784 58.9625 53.92C59.8296 55 61.2115 55.945 61.4554 57.16C62.4851 61.9121 63.1896 66.7182 63.9212 71.4973C64.3277 74.2513 63.1625 75.3584 60.3986 74.8994C56.9845 74.3323 53.5161 73.8733 50.2103 72.9553C49.1535 72.6583 48.4761 71.0383 47.609 70.0123C48.8012 69.4183 49.9664 68.4192 51.2128 68.3382C52.7574 68.2302 54.3561 68.8512 56.6322 69.2832C45.1432 40.0417 25.8232 18.0093 -5.31099e-08 1.21501C0.216774 0.810004 0.433548 0.405005 0.677422 -2.76792e-06C1.89677 0.378004 3.22451 0.566994 4.28129 1.188C16.0142 8.12713 26.3109 16.8213 35.3883 26.9465C43.5716 36.0726 50.427 46.1168 55.7922 56.674L55.7651 56.674Z" fill="#215558"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="w-full bg-[#f4f5f7] py-[4rem] px-[1rem] md:px-[7rem] flex flex-col items-center">
                <div class="max-w-[1200px] w-full mb-8 mx-auto h-full grid grid-cols-3 gap-8 auto-rows-[400px] relative">
                    <a href="https://www.barbarosdetailing.com/" target="_blank" class="fade-in-left col-span-3 md:col-span-2 row-span-1 rounded-3xl relative overflow-hidden group">
                        <div class="w-full h-full bg-cover bg-center absolute z-1 transition duration-500 group-hover:scale-[1.1]" style='background-image: url("{{ asset("assets/eazyonline/projecten/mockups/barbarosdetailing.webp") }}")'></div>
                        <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                        <div class="w-full h-full absolute z-3 left-0 top-0 p-[2rem] flex flex-col justify-between">
                            <div class="w-full flex items-start justify-between">
                                <div class="flex flex-col gap-2">
                                    <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-2 py-1 rounded-full">Detailingsbedrijf</p>
                                </div>
                                <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-1 py-1 rounded-full opacity-0 group-hover:opacity-100 transition duration-500">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 7h10v10"></path>
                                        <path d="M7 17 17 7"></path>
                                    </svg>
                                </p>
                            </div>
                            <div class="flex flex-col gap-[1.5rem]">
                                <h2 class="leading-tight text-2xl md:text-4xl font-bold text-[#fff] group-hover:text-[#63e1e6] transition duration-500">Jouw specialist in<br>high-end detailing</h2>
                                <p class="text-base text-white font-medium">Voor Barbaros Detailing hebben wij een mooie,<br>conversiegerichte webshop mogen realiseren.</p>
                            </div>
                        </div>
                    </a>
                    <a href="https://www.kapotsterk.nl/" target="_blank" class="fade-in-right col-span-3 md:col-span-1 row-span-1 rounded-3xl relative overflow-hidden group">
                        <div class="w-full h-full bg-cover bg-center absolute z-1 transition duration-500 group-hover:scale-[1.1]" style='background-image: url("{{ asset("assets/eazyonline/projecten/mockups/kapotsterk.webp") }}")'></div>
                        <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                        <div class="w-full h-full absolute z-3 left-0 top-0 p-[2rem] flex flex-col justify-between">
                            <div class="w-full flex items-start justify-between">
                                <div class="flex flex-col gap-2">
                                    <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-2 py-1 rounded-full">Community / Boek</p>
                                </div>
                                <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-1 py-1 rounded-full opacity-0 group-hover:opacity-100 transition duration-500">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 7h10v10"></path>
                                        <path d="M7 17 17 7"></path>
                                    </svg>
                                </p>
                            </div>
                            <div class="flex flex-col gap-[1.5rem]">
                                <h2 class="leading-tight text-2xl font-bold text-[#fff] group-hover:text-[#63e1e6] transition duration-500">Waar kracht en kwaliteit samenkomen</h2>
                                <p class="text-base text-white font-medium">Voor Wouter (Ome Wutru) Smit hebben wij een communityplatform ontwikkeld en de promotie van zijn boek verzorgd.</p>
                            </div>
                        </div>
                    </a>
                    <h5 class="fade-in-right-delay hidden md:block absolute z-5 -top-[1.5rem] -right-[1.5rem] text-5xl rotate-[25deg]">💪</h5>
                    <a href="https://www.renovion.nl/" target="_blank" class="fade-in-left col-span-3 md:col-span-1 row-span-1 rounded-3xl relative overflow-hidden group">
                        <div class="w-full h-full bg-cover bg-center absolute z-1 transition duration-500 group-hover:scale-[1.1]" style='background-image: url("{{ asset("assets/eazyonline/projecten/mockups/renovion.webp") }}")'></div>
                        <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                        <div class="w-full h-full absolute z-3 left-0 top-0 p-[2rem] flex flex-col justify-between">
                            <div class="w-full flex items-start justify-between">
                                <div class="flex flex-col gap-2">
                                    <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-2 py-1 rounded-full">Renovatiebedrijf</p>
                                </div>
                                <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-1 py-1 rounded-full opacity-0 group-hover:opacity-100 transition duration-500">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 7h10v10"></path>
                                        <path d="M7 17 17 7"></path>
                                    </svg>
                                </p>
                            </div>
                            <div class="flex flex-col gap-[1.5rem]">
                                <h2 class="leading-tight text-2xl font-bold text-[#fff] group-hover:text-[#63e1e6] transition duration-500">Van oud naar<br>ongekend nieuw</h2>
                                <p class="text-base text-white font-medium">Voor Renovion een prachtige, moderne informatieve website mogen realiseren.</p>
                            </div>
                        </div>
                    </a>
                    <h5 class="fade-in-left-delay hidden md:block absolute z-5 top-[26rem] -left-[1.5rem] text-5xl rotate-[-25deg]">🛠️</h5>
                    <a href="https://www.thegrind.nl/" target="_blank" class="fade-in-right col-span-3 md:col-span-2 row-span-1 rounded-3xl relative overflow-hidden group">
                        <div class="w-full h-full bg-cover bg-center absolute z-1 transition duration-500 group-hover:scale-[1.1]" style='background-image: url("{{ asset("assets/eazyonline/projecten/mockups/thegrind.webp") }}")'></div>
                        <div class="w-full h-full absolute z-2 bg-gradient-to-tr from-[#215558]/50 via-[#215558]/40 to-transparent"></div>
                        <div class="w-full h-full absolute z-3 left-0 top-0 p-[2rem] flex flex-col justify-between">
                            <div class="w-full flex items-start justify-between">
                                <div class="flex flex-col gap-2">
                                    <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-2 py-1 rounded-full">Sportkleding</p>
                                </div>
                                <p class="bg-[#ffffff]/30 border border-[#ffffff] w-fit text-[#ffffff] font-semibold text-xs px-1 py-1 rounded-full opacity-0 group-hover:opacity-100 transition duration-500">
                                    <svg class="w-4 h-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M7 7h10v10"></path>
                                        <path d="M7 17 17 7"></path>
                                    </svg>
                                </p>
                            </div>
                            <div class="flex flex-col gap-[1.5rem]">
                                <h2 class="leading-tight text-2xl md:text-4xl font-bold text-[#fff] group-hover:text-[#63e1e6] transition duration-500">Sportkleding die met<br>je meebeweegt</h2>
                                <p class="text-base text-white font-medium">Voor The Grind een krachtige, moderne webshop mogen realiseren,<br>volledig gericht op sport en performance.</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="w-full h-auto py-[4rem] px-[1rem] md:px-[7rem] bg-[#F4F5F7]">
                <div class="max-w-[1200px] mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
                    <h2 class="fade-in-up col-span-1 md:col-span-3 text-[#215558] leading-tight text-4xl font-extrabold">Veelgestelde vragen</h2>
                    <div class="fade-in-up bg-white rounded-3xl p-[2.5rem] shadow-ez">
                        <h2 class="text-lg font-semibold text-[#215558] mb-4">Hoe werkt de preview tool?</h2>
                        <p class="text-md leading-tight font-medium text-[#215558]">Je vult 3 korte vragen in over je bedrijf. Ons team maakt op basis daarvan een persoonlijke website-preview voor jou.</p>
                    </div>
                    <div class="fade-in-up-delay bg-white rounded-3xl p-[2.5rem] shadow-ez">
                        <h2 class="text-lg font-semibold text-[#215558] mb-4">Hoe snel ontvang ik mijn preview?</h2>
                        <p class="text-md leading-tight font-medium text-[#215558]">Binnen 24 uur ontvang je van ons een eerste ontwerp in je mailbox. Zo zie je direct hoe jouw website eruit kan zien.</p>
                    </div>
                    <div class="fade-in-up-more-delay bg-white rounded-3xl p-[2.5rem] shadow-ez">
                        <h2 class="text-lg font-semibold text-[#215558] mb-4">Is de preview vrijblijvend?</h2>
                        <p class="text-md leading-tight font-medium text-[#215558]">Ja! De preview is gratis en vrijblijvend. Je beslist zelf of je daarna met ons verder wilt.</p>
                    </div>
                    <div class="fade-in-up bg-white rounded-3xl p-[2.5rem] shadow-ez">
                        <h2 class="text-lg font-semibold text-[#215558] mb-4">Kan ik de preview nog aanpassen?</h2>
                        <p class="text-md leading-tight font-medium text-[#215558]">Zeker! Logo, kleuren, teksten en foto’s kunnen wij allemaal aanpassen zodat de site echt jouw uitstraling krijgt.</p>
                    </div>
                    <div class="fade-in-up-delay bg-white rounded-3xl p-[2.5rem] shadow-ez">
                        <h2 class="text-lg font-semibold text-[#215558] mb-4">Voor wie is dit bedoeld?</h2>
                        <p class="text-md leading-tight font-medium text-[#215558]">Voor ondernemers en bedrijven die snel een professioneel voorbeeld van hun nieuwe website willen zien, zonder direct grote verplichtingen.</p>
                    </div>
                    <div class="fade-in-up-more-delay bg-white rounded-3xl p-[2.5rem] shadow-ez">
                        <h2 class="text-lg font-semibold text-[#215558] mb-4">Wanneer staat mijn website online?</h2>
                        <p class="text-md leading-tight font-medium text-[#215558]">Zodra jij akkoord geeft op de preview en een pakket kiest, zetten wij jouw website binnen enkele werkdagen online.</p>
                    </div>
                </div>
            </div>
            @include('website.layouts.footer')
        </div>
        
        <!-- Uitlegbox -->
        <div x-data="{ isHelpOpen: false }" class="fixed bottom-2 md:bottom-6 right-2 md:right-6 z-[9999] flex flex-col items-end">
            <!-- Uitlegbox -->
            <div
                x-show="isHelpOpen"
                x-transition
                @click.outside="isHelpOpen = false"
                x-data="{ typed: '', full: `Of je nu een nieuwe site wilt starten, je huidige wilt upgraden of gewoon snel een preview wilt zien, bij ons kan het allemaal. Wij maken het Eazy.`,
                    typing: null
                }"
                x-init="$watch('isHelpOpen', value => {
                    if (value) {
                        typed = '';
                        clearInterval(typing);
                        let i = 0;
                        typing = setInterval(() => {
                            if (i < full.length) {
                                typed += full.charAt(i);
                                i++;
                            } else {
                                clearInterval(typing);
                            }
                        }, 18); // snelheid
                    }
                })"
                class="mb-4 w-[300px] md:w-[400px] bg-white text-[#215558] flex flex-col text-sm p-[1.5rem] rounded-3xl shadow-xl border border-gray-200 relative overflow-hidden"
            >
                
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 h-6 bg-[#215558]/10 rounded-full">
                        <img src="{{ asset('assets/eazyonline/memojis/raphael.webp') }}" loading="lazy" alt="Raphael">
                    </div> 
                    <h4 class="leading-tight text-sm font-semibold text-[#343434]">Raphael, Team Eazy</h4>
                </div>
                <p class="text-sm font-normal text-[#343434]" x-text="typed"></p>
                <a href="https://tidycal.com/eazyonline" class="bg-[#0F9B9F] mt-4 w-fit text-white text-sm font-medium px-6 py-3 rounded-full">Plan discovery call</a>
            </div>

            <!-- Floating knop -->
            <div @click="isHelpOpen = !isHelpOpen"
                class="w-[60px] md:w-[86px] h-[60px] md:h-[86px] rounded-full bg-[#0F9B9F]/35 border-2 border-[#0F9B9F]/30 shadow-lg flex items-center justify-center text-white text-sm font-semibold relative cursor-pointer transition hover:scale-105">

                <div class="w-4 h-4 rounded-full bg-[#4CC5CC] absolute top-1 left-1 border-2 border-white"></div>
                
                <!-- Deze tekst alleen tonen als box NIET open is -->
                <div x-show="!isHelpOpen" 
                    x-transition 
                    class="w-auto h-auto absolute -top-[2rem] -left-[3rem]">
                    <!-- <p class="text-2xl text-[#0F9B9F] rotate-[10deg] md:rotate-[-15deg] caveat-font text-center transition-colors duration-300">
                    Uitleg nodig?
                    </p> -->
                </div>

                <img src="{{ asset('assets/eazyonline/memojis/raphael.webp') }}" loading="lazy" alt="Raphael">
            </div>
        </div>
        <script defer>
        window.addEventListener("load", () => {
            const hero = document.getElementById('hero');
            window.addEventListener('scroll', function () {
                if (window.scrollY > (hero.offsetHeight - 75)) {
                    // Actie uitvoeren wanneer je verder dan 100vh bent
                    document.querySelector('#header-desktop').classList.add('scrolled');
                    document.querySelector('#header-desktop').classList.remove('backdrop-blur');
                    document.querySelector('#header-desktop').classList.remove('bg-[#ffffff30]');
                    document.querySelector('#header-desktop').classList.add('bg-[#fff]');
                    document.querySelector('#login-button').classList.add('bg-[#21555850]');
                    document.querySelector('.fa-bars').style.color = '#215558';
                } else {
                    // Eventueel weer terugzetten
                    document.querySelector('#header-desktop').classList.remove('scrolled');
                    document.querySelector('#header-desktop').classList.add('backdrop-blur');
                    document.querySelector('#header-desktop').classList.add('bg-[#ffffff30]');
                    document.querySelector('#header-desktop').classList.remove('bg-[#fff]');
                    document.querySelector('#login-button').classList.remove('bg-[#21555850]');
                    document.querySelector('.fa-bars').style.color = '#fff';
                }
            });

            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuOpen = document.getElementById('mobile-menu-open');
            const mobileMenuClose = document.getElementById('mobile-menu-close');
            mobileMenuOpen.addEventListener("click", function() {
                mobileMenu.classList.add("active");
            })
            mobileMenuClose.addEventListener("click", function() {
                mobileMenu.classList.remove("active");
            })

            gsap.registerPlugin(ScrollTrigger);
            // Fade-in van onder (standaard)
            gsap.utils.toArray(".fade-in-up").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                y: 40,
                duration: 1,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 70%",
                    toggleActions: "play none none none",
                }
                });
            });
            gsap.utils.toArray(".fade-in-up-delay").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                y: 40,
                duration: 1,
                delay: 0.4,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 70%",
                    toggleActions: "play none none none",
                }
                });
            });
            gsap.utils.toArray(".fade-in-up-more-delay").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                y: 40,
                duration: 1,
                delay: 0.8,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 70%",
                    toggleActions: "play none none none",
                }
                });
            });
            gsap.utils.toArray(".fade-in-up-even-more-delay").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                y: 40,
                duration: 1,
                delay: 1.2,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 70%",
                    toggleActions: "play none none none",
                }
                });
            });
            // Fade-in van links
            gsap.utils.toArray(".fade-in-left").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                x: -40,
                duration: 1,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 70%",
                    toggleActions: "play none none none",
                }
                });
            });
            // Fade-in van rechts
            gsap.utils.toArray(".fade-in-right").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                x: 40,
                duration: 1,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 70%",
                    toggleActions: "play none none none",
                }
                });
            });
            // Fade-in left met delay
            gsap.utils.toArray(".fade-in-left-delay").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                x: -40,
                duration: 1,
                delay: 0.4,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 85%",
                    toggleActions: "play none none none",
                }
                });
            });
            // Fade-in right met delay
            gsap.utils.toArray(".fade-in-right-delay").forEach((el) => {
                gsap.from(el, {
                opacity: 0,
                x: 40,
                duration: 1,
                delay: 0.4,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: el,
                    start: "top 85%",
                    toggleActions: "play none none none",
                }
                });
            });
        });
        </script>
    </body>
</html>