@extends('other.layouts.guest')

@section('content')
@php
    /** @var \Carbon\Carbon|null $expiresAt */
    $expiresIso       = optional($expiresAt)->toIso8601String();
    $isExpired        = $expiresAt ? $expiresAt->isPast() : false;
    $expiresFormatted = $expiresAt ? $expiresAt->format('d-m-Y H:i') : null;
    $previewHost      = $previewUrl
        ? (parse_url($previewUrl, PHP_URL_HOST) ?: $previewUrl)
        : 'preview.eazyonline.nl';
@endphp

<div class="w-full fixed z-50 top-0 left-0 bg-white border-b border-b-gray-200 p-4">
    <div class="max-w-6xl mx-auto flex items-center justify-between gap-2">
        {{-- Fullscreen toggle --}}
        <div class="flex items-center gap-4 relative">
            <button id="preview-fullscreen-toggle"
                   type="button"
                    class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer">
                <i id="preview-fullscreen-icon"
                class="fa-solid fa-maximize text-[#215558] text-xs"></i>

                {{-- Tooltip rechts, gecentreerd, zelfde animatie-stijl --}}
                <div
                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md
                        absolute left-full top-1/2 ml-2 -translate-y-1/2
                        opacity-0 invisible translate-x-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                        transition-all duration-200 ease-out z-10">
                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                        Volledig scherm bekijken
                    </p>
                </div>
            </button>
            <p class="caveat-font text-[#215558] text-lg animate-pulse">Bekijk de preview op volledig scherm!</p>
            <svg class="absolute h-fit -left-10.5 rotate-[-45deg] -top-7 w-[30px]" width="177" height="265" viewBox="0 0 177 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M31.6609 252.913C26.9982 250.913 22.3461 248.492 17.4805 247.088C15.266 246.47 11.1171 246.988 10.217 248.494C8.08411 251.854 10.6409 254.327 14.0978 255.293C21.7134 257.419 29.3655 259.384 37.0227 261.4C38.6394 261.816 40.381 261.905 41.8624 262.544C48.8485 265.729 51.589 264.249 51.6232 256.693C51.6948 246.877 51.7664 237.061 51.1101 227.318C50.9342 225.028 48.1646 221.475 46.1268 221.048C41.838 220.165 40.7551 224.049 40.6913 227.622C40.6113 232.613 40.7392 237.584 40.8094 244.081C31.5548 232.362 25.3988 220.65 21.0472 208.075C3.36551 157.123 10.508 108.155 34.2655 60.992C41.1492 47.3499 51.8891 37.1 67.8978 34.4025C71.6673 33.7645 75.5975 34.2119 80.6821 34.1246C71.8231 46.9145 65.6677 59.4348 63.7071 73.4778C62.6592 80.8731 62.209 88.4712 62.7883 95.8617C63.5022 104.603 67.3931 112.03 76.5993 114.836C85.9718 117.731 94.073 114.72 100.476 107.84C110.145 97.3823 114.054 84.4564 114.19 70.543C114.389 53.5809 107.61 39.8323 93.0418 29.5878C116.93 6.38264 144.856 -0.0207063 176.447 6.11382C176.552 5.57892 176.651 4.99207 176.755 4.45717C174.852 3.8076 173.038 2.99186 171.052 2.5604C141.042 -4.04644 114.348 2.60123 91.2768 22.945C88.3332 25.5463 86.0505 26.3509 82.0166 25.3893C67.1178 21.8392 53.5625 25.9706 42.3548 35.7427C35.6875 41.5477 29.238 48.485 25.2888 56.2745C-0.071148 106.325 -7.95309 158.409 11.3911 212.342C16.4024 226.267 24.5595 239.092 31.5672 253.027L31.6609 252.913ZM86.514 37.3197C113.807 53.0095 106.726 91.0091 92.2112 103.419C82.6314 111.612 71.9181 107.383 70.3013 94.3259C67.7364 73.3384 74.1647 54.6013 86.5088 37.2678L86.514 37.3197Z" fill="#215558"></path>
            </svg>
        </div>

        <div class="flex items-center gap-2">
            {{-- Countdown --}}
            @php
                $initialCountdownText = 'Laden...';

                if (!empty($isApproved)) {
                    $initialCountdownText = 'Preview goedgekeurd';
                } elseif (!empty($expiresAt) && $expiresAt->isPast()) {
                    $initialCountdownText = 'Preview is verlopen';
                } elseif (!empty($remainingSeconds)) {
                    $d = intdiv($remainingSeconds, 86400);
                    $h = intdiv($remainingSeconds % 86400, 3600);
                    $m = intdiv($remainingSeconds % 3600, 60);
                    $s = $remainingSeconds % 60;
                    $initialCountdownText = sprintf('%02d:%02d:%02d:%02d over', $d, $h, $m, $s);
                }
            @endphp
            <p id="preview-countdown"
                data-expires-at="{{ $expiresIso }}"
                data-approved="{{ !empty($isApproved) ? 1 : 0 }}"
                class="px-2 py-0.5 text-xs bg-green-200 text-green-700 font-semibold rounded-full w-fit">
                {{ $initialCountdownText }}
            </p>

            {{-- Absolute eindtijd --}}
            @if($expiresFormatted)
                <p class="px-2 py-0.5 text-xs bg-gray-200 text-gray-700 font-semibold rounded-full w-fit">
                    Beschikbaar tot: {{ $expiresFormatted }}
                </p>
            @endif
        </div>
    </div>
</div>

<div class="w-full px-6 mt-[88px] mb-[88px]">
    <div class="max-w-6xl mx-auto grid grid-cols-3 gap-10">
        <div class="col-span-3 grid grid-cols-3 gap-6">
            <div class="col-span-2">
                <div class="bg-white rounded-2xl p-6 border border-gray-200 relative">
                    {{-- Preview lock --}}
                    <div id="preview-lock"
                        class="absolute z-[999] w-full h-full left-0 top-0 p-6 {{ $isExpired ? '' : 'hidden' }}">
                        <div class="backdrop-blur-2xl bg-black/40 w-full h-full rounded-xl flex flex-col items-center justify-center">
                            <i class="fa-solid fa-lock fa-2xl text-white"></i>
                            <p class="text-white text-base font-semibold opacity-80 text-center mt-6">
                                Deze preview is verlopen.<br>Neem contact op.
                            </p>
                        </div>
                    </div>

                    {{-- Wrapper die de uiteindelijke breedte bepaalt --}}
                    <div id="preview-frame-wrapper"
                        class="relative w-full overflow-hidden rounded-xl bg-gray-50">

                        {{-- Fake browser-chrome (alleen in fullscreen zichtbaar) --}}
                        <div id="preview-browser-chrome"
                            class="w-full border-b border-gray-200 bg-[#f5f5f7] px-4 py-2 text-xs text-gray-500 items-center gap-3 -mb-11.5"
                            style="display:none;">
                            <div class="flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                                <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                            </div>

                            <div class="flex-1 flex items-center justify-center">
                                <div class="flex items-center gap-2 w-full max-w-xl px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-sm">
                                    <i class="fa-solid fa-lock text-[10px] text-gray-400"></i>
                                    <span class="text-[11px] font-medium text-white truncate">
                                        .
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 text-gray-400">
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                <i class="fa-solid fa-share-nodes text-xs"></i>
                            </div>
                        </div>

                        {{-- Inner op “desktop” formaat, wordt geschaald --}}
                        <div id="preview-frame-inner"
                            class="origin-top-left"
                            style="width:1440px;height:900px;">
                            <iframe
                                id="preview-iframe"
                                src="{{ $previewUrl }}"
                                class="w-[1440px] h-[900px] border-0"
                                loading="lazy"
                            ></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-lg text-[#215558] font-black leading-tight truncate shrink-0">Feedback</p>
                    <p id="feedback-status"
                    class="text-xs font-semibold {{ session('status') === 'feedback_saved' ? 'text-green-700' : 'hidden' }}">
                        Bedankt! Je feedback is verstuurd.
                    </p>
                </div>
                <form id="preview-feedback-form"
                    method="POST"
                    action="{{ route('preview.feedback.store', $project->preview_token) }}">
                    @csrf
                    <textarea name="feedback"
                            placeholder="Typ hier jouw feedback..."
                            class="w-full min-h-[285px] max-h-[285px] py-2 px-3 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"></textarea>

                    <button type="submit"
                            class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300 mt-4">
                        Verstuur feedback
                    </button>
                </form>
            </div>
        </div>

        <!-- Reviews slider -->
        <div class="col-span-3 mt-6">
            <div 
                x-data="reviewsCarousel()" 
                x-init="start()" 
                class="relative"
                x-on:mouseenter="pause()" 
                x-on:mouseleave="start()"
            >
                <div class="overflow-hidden">
                    <!-- Track -->
                    <div 
                        x-ref="track"
                        class="flex -mx-3 transition-transform duration-700 ease-in-out"
                        :style="translateStyle()"
                    >
                        <!-- Slide 1 -->
                        <div class="shrink-0 basis-full md:basis-1/3 px-3">
                            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full bg-cover bg-center" style="background-image: url('/assets/eazyonline/projecten/profielfotos/2befit.webp')"></div>
                                    <div>
                                        <p class="text-sm text-[#215558] font-bold">Roy Koenders</p>
                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar 2BeFit Coaching</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#215558] font-medium">Van idee tot eindproduct: Eazy leverde een strak, modern en uniek design dat onze visie perfect weerspiegelt.</p>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="shrink-0 basis-full md:basis-1/3 px-3">
                            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full bg-cover bg-center" style="background-image: url('/assets/eazyonline/projecten/profielfotos/barbarosdetailing.webp')"></div>
                                    <div>
                                        <p class="text-sm text-[#215558] font-bold">Baris Yildirim</p>
                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar Babaros Detailing</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#215558] font-medium">Binnen no-time hadden we een op maat gemaakte website die precies laat zien waar ons bedrijf voor staat. Supertevreden.</p>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="shrink-0 basis-full md:basis-1/3 px-3">
                            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full bg-cover bg-center" style="background-image: url('/assets/eazyonline/projecten/profielfotos/thegrind.webp')"></div>
                                    <div>
                                        <p class="text-sm text-[#215558] font-bold">Donny Roelvink</p>
                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar The Grind</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#215558] font-medium">Eazy heeft elke versie van onze website naar een hoger niveau getild. Ze snappen exact wat je als ondernemer nodig hebt.</p>
                            </div>
                        </div>

                        <!-- Slide 4 -->
                        <div class="shrink-0 basis-full md:basis-1/3 px-3">
                            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full bg-cover bg-center" style="background-image: url('/assets/eazyonline/projecten/profielfotos/kapotsterk.webp')"></div>
                                    <div>
                                        <p class="text-sm text-[#215558] font-bold">Wouter Smith</p>
                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar KapotSterk</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#215558] font-medium">Samenwerken met Eazy voelt als een gedeeld avontuur. Ze denken altijd mee en bouwen écht mee aan ons merk.</p>
                            </div>
                        </div>

                        <!-- Slide 5 -->
                        <div class="shrink-0 basis-full md:basis-1/3 px-3">
                            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full bg-cover bg-center" style="background-image: url('/assets/eazyonline/projecten/profielfotos/huisjekaatsheuvel.webp')"></div>
                                    <div>
                                        <p class="text-sm text-[#215558] font-bold">Nienke Roseboom</p>
                                        <p class="text-xs text-[#215558] font-semibold">Eigenaresse Huisje Kaatsheuvel</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#215558] font-medium">Vanaf dag één goede communicatie, snelle updates en een team dat je écht meeneemt in het proces. Heel professioneel.</p>
                            </div>
                        </div>

                        <!-- Slide 6 -->
                        <div class="shrink-0 basis-full md:basis-1/3 px-3">
                            <div class="bg-white h-fit rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                <div class="flex items-center gap-1">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                    @endfor
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-8 h-8 rounded-full bg-cover bg-center" style="background-image: url('/assets/eazyonline/projecten/profielfotos/blowertechnic.webp')"></div>
                                    <div>
                                        <p class="text-sm text-[#215558] font-bold">Bas &amp; David</p>
                                        <p class="text-xs text-[#215558] font-semibold">Eigenaren BlowerTechnic</p>
                                    </div>
                                </div>
                                <p class="text-sm text-[#215558] font-medium">Onze oude websites voldeden niet meer aan onze visie. Eazy ontwikkelde een volledig nieuw concept dat onze verwachtingen overtrof.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('reviewsCarousel', () => ({
                index: 0,
                perView: 1,
                total: 0,
                timer: null,

                updatePerView() {
                    this.perView = window.matchMedia('(min-width: 768px)').matches ? 3 : 1;
                    // Zorg dat index binnen bereik blijft wanneer viewport verandert
                    const maxIndex = Math.max(0, this.total - this.perView);
                    if (this.index > maxIndex) this.index = 0;
                },

                translateStyle() {
                    // Elke stap is 100% / perView
                    const step = 100 / this.perView;
                    return `transform: translateX(-${this.index * step}%);`;
                },

                tick() {
                    const maxIndex = Math.max(0, this.total - this.perView); // bij 6 kaarten & 3 per view => 3
                    this.index = (this.index >= maxIndex) ? 0 : this.index + 1;
                },

                start() {
                    this.total = this.$refs.track.children.length;
                    this.updatePerView();
                    window.addEventListener('resize', () => this.updatePerView());

                    this.pause();
                    this.timer = setInterval(() => this.tick(), 5000);
                },

                pause() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                }
            }));
        });
        </script>
        <div class="col-span-3 grid grid-cols-4 gap-6">
            <div class="w-full aspect-[1/1.75] rounded-2xl relative overflow-hidden">
                <video class="w-full h-full object-cover absolute z-1" src="/assets/eazyonline/videos/reviews/thegrind.mp4" loop muted autoplay playsinline></video>
            </div>
            <div class="w-full aspect-[1/1.75] rounded-2xl relative overflow-hidden">
                <video class="w-full h-full object-cover absolute z-1" src="/assets/eazyonline/videos/reviews/barbarosdetailing.mp4" loop muted autoplay playsinline></video>
            </div>
            <div class="w-full aspect-[1/1.75] rounded-2xl relative overflow-hidden">
                <video class="w-full h-full object-cover absolute z-1" src="/assets/eazyonline/videos/reviews/kapotsterk.mp4" loop muted autoplay playsinline></video>
            </div>
            <div class="w-full aspect-[1/1.75] rounded-2xl relative overflow-hidden">
                <video class="w-full h-full object-cover absolute z-1" src="/assets/eazyonline/videos/reviews/2befit.mp4" loop muted autoplay playsinline></video>
            </div>
        </div>
    </div>
</div>

<div class="w-full fixed z-50 bottom-0 left-0 bg-white border-t border-gray-200 p-4">
    <div class="max-w-6xl mx-auto flex items-center justify-start gap-3">
        <button
            type="button"
            id="preview-approve-btn"
            data-approve-url="{{ route('preview.approve', $project->preview_token) }}"
            class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
            Preview goedkeuren
        </button>
        <button
            type="button"
            id="preview-call-btn"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300">
            Bellen met een medewerker
        </button>
        <p id="preview-approved-msg" class="text-sm font-semibold text-green-700 hidden">
            Je hebt de preview goedgekeurd. Bekijk je e-mail voor de vervolgstappen!
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Feedback via soft submit (AJAX) ---
    const feedbackForm = document.getElementById('preview-feedback-form');
    if (feedbackForm) {
        const textarea = feedbackForm.querySelector('textarea[name="feedback"]');
        const statusEl = document.getElementById('feedback-status');

        feedbackForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const value = textarea ? textarea.value.trim() : '';
            if (!value) {
                if (statusEl) {
                    statusEl.textContent = 'Typ eerst even je feedback 😊';
                    statusEl.classList.remove('hidden', 'text-green-700');
                    statusEl.classList.add('text-red-700');
                }
                return;
            }

            const formData = new FormData(feedbackForm);
            const action   = feedbackForm.getAttribute('action');

            fetch(action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Request failed');
                }
                return response.json().catch(function () {
                    return {};
                });
            })
            .then(function () {
                if (statusEl) {
                    statusEl.textContent = 'Bedankt! Je feedback is verstuurd.';
                    statusEl.classList.remove('hidden', 'text-red-700');
                    statusEl.classList.add('text-green-700');
                }
                if (textarea) {
                    textarea.value = '';
                }
            })
            .catch(function () {
                if (statusEl) {
                    statusEl.textContent = 'Er ging iets mis. Probeer het later opnieuw.';
                    statusEl.classList.remove('hidden', 'text-green-700');
                    statusEl.classList.add('text-red-700');
                }
            });
        });
    }

    // --- iframe + schaal ---
    const wrapper    = document.getElementById('preview-frame-wrapper');
    const inner      = document.getElementById('preview-frame-inner');
    const iframe     = document.getElementById('preview-iframe');
    const chromeBar  = document.getElementById('preview-browser-chrome');

    const DESIGN_WIDTH  = 1440; // desktopbreedte
    const DESIGN_HEIGHT = 900;  // hoogte
    const NORMAL_HEIGHT = DESIGN_HEIGHT - 100;

    function applyNormalMode() {
        if (!wrapper || !inner) return;

        if (chromeBar) {
            chromeBar.style.display = 'none';
        }

        const availableWidth = wrapper.clientWidth || DESIGN_WIDTH;
        const scale = Math.min(1, availableWidth / DESIGN_WIDTH); // nooit groter dan 1

        inner.style.transformOrigin = 'top left';
        inner.style.transform = 'scale(' + scale + ')';
        inner.style.width  = DESIGN_WIDTH + 'px';
        inner.style.height = NORMAL_HEIGHT + 'px';     // <- was DESIGN_HEIGHT
        inner.style.marginTop = '0px';

        if (iframe) {
            iframe.style.width  = DESIGN_WIDTH + 'px';
            iframe.style.height = NORMAL_HEIGHT + 'px'; // <- was DESIGN_HEIGHT
        }

        wrapper.style.height = (NORMAL_HEIGHT * scale) + 'px'; // <- was DESIGN_HEIGHT * scale
    }

    function applyFullscreenMode() {
        if (!wrapper || !inner) return;

        // chrome tonen
        let chromeHeight = 0;
        if (chromeBar) {
            chromeBar.style.display = 'flex';
            chromeHeight = chromeBar.offsetHeight || 0;
        }

        const availableHeight = window.innerHeight - chromeHeight;

        inner.style.transform = 'none';
        inner.style.marginTop = chromeHeight + 'px';
        inner.style.width  = window.innerWidth + 'px';
        inner.style.height = availableHeight + 'px';

        if (iframe) {
            iframe.style.width  = '100%';
            iframe.style.height = '100%';
        }

        wrapper.style.height = window.innerHeight + 'px';
    }

    function updateScale() {
        if (!wrapper || !inner) return;

        if (document.fullscreenElement === wrapper) {
            applyFullscreenMode();
        } else {
            applyNormalMode();
        }
    }

    updateScale();
    window.addEventListener('resize', updateScale);

    // --- fullscreen toggle ---
    const fsBtn  = document.getElementById('preview-fullscreen-toggle');
    const fsIcon = document.getElementById('preview-fullscreen-icon');

    if (fsBtn && wrapper) {
        fsBtn.addEventListener('click', function () {
            if (!document.fullscreenElement) {
                if (wrapper.requestFullscreen) {
                    wrapper.requestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        });

        document.addEventListener('fullscreenchange', function () {
            const isFs = (document.fullscreenElement === wrapper);

            if (fsIcon) {
                fsIcon.classList.toggle('fa-maximize', !isFs);
                fsIcon.classList.toggle('fa-minimize', isFs);
            }

            updateScale();
        });
    }

    // --- countdown + lock + approve (FIX: maar 1 timer) ---
    const countdownEl  = document.getElementById('preview-countdown');
    const lockEl       = document.getElementById('preview-lock');

    const approveBtn = document.getElementById('preview-approve-btn');
    const callBtn    = document.getElementById('preview-call-btn');
    const msgEl      = document.getElementById('preview-approved-msg');

    const expiresAtStr = countdownEl ? countdownEl.dataset.expiresAt : null;

    let approved = !!(countdownEl && countdownEl.dataset.approved === '1');
    let countdownTimerId = null;

    function lockButtons() {
        const lockClass = ['opacity-50','pointer-events-none'];
        if (approveBtn) approveBtn.classList.add(...lockClass);
        if (callBtn) callBtn.classList.add(...lockClass);
    }

    function stopCountdown() {
        if (countdownTimerId) {
            clearInterval(countdownTimerId);
            countdownTimerId = null;
        }
    }

    function setApprovedUI() {
        approved = true;
        if (countdownEl) countdownEl.dataset.approved = '1';

        stopCountdown();

        if (countdownEl) {
            countdownEl.textContent = 'Preview goedgekeurd';
            countdownEl.classList.remove('bg-red-100','text-red-700');
            countdownEl.classList.add('bg-green-200','text-green-700');
        }

        lockButtons();
        if (msgEl) msgEl.classList.remove('hidden');
    }

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatRemaining(diffMs) {
        const totalSeconds = Math.max(0, Math.floor(diffMs / 1000));
        const days    = Math.floor(totalSeconds / 86400);
        const hours   = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return `${pad(days)}:${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    }

    function setExpiredState() {
        if (!countdownEl) return;

        countdownEl.textContent = 'Preview is verlopen';
        countdownEl.classList.remove('bg-green-200','text-green-700');
        countdownEl.classList.add('bg-red-100','text-red-700');

        if (lockEl) lockEl.classList.remove('hidden');

        stopCountdown();
    }

    function setActiveState(text) {
        if (!countdownEl) return;

        countdownEl.textContent = text;
        countdownEl.classList.remove('bg-red-100','text-red-700');
        countdownEl.classList.add('bg-green-200','text-green-700');
    }

    function updateCountdown() {
        if (!countdownEl || approved || !expiresAtStr) return;

        const expiresAt = new Date(expiresAtStr);
        const diff = expiresAt - new Date();

        if (diff <= 0) {
            setExpiredState();
            return;
        }

        setActiveState(formatRemaining(diff) + ' over');
    }

    // init: als al approved, direct UI locken (en GEEN timer starten)
    if (approved) {
        setApprovedUI();
    } else if (expiresAtStr) {
        updateCountdown();
        countdownTimerId = setInterval(updateCountdown, 1000);
    }

    // click handler
    if (approveBtn) {
        approveBtn.addEventListener('click', async function () {
            // direct UI locken (snappy)
            setApprovedUI();

            const url  = approveBtn.dataset.approveUrl;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                if (!res.ok) throw new Error('Approve failed');
            } catch (e) {
                if (msgEl) {
                    msgEl.textContent = 'Er ging iets mis. Probeer het later opnieuw.';
                    msgEl.classList.remove('hidden','text-green-700');
                    msgEl.classList.add('text-red-700');
                }
            }
        });
    }
});
</script>
@endsection
