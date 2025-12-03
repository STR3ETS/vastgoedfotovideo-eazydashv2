@if(!$activeTemplate)
    <div class="flex items-center gap-4">
        <span class="text-4xl">👈</span>
        <p class="text-base font-bold text-[#215558]/80 mt-1">
            Maak of selecteer een nieuwsbrief-template om te beginnen.
        </p>
    </div>
@else
    <div class="w-full flex-1 flex flex-col min-h-0">
        <div class="shrink-0 mb-6 flex items-center justify-between gap-3">
            <h2 class="text-[#215558] font-black text-xl">
                {{ $activeTemplate->name }}
            </h2>

            <form id="email-builder-save-form"
                    action="{{ route('support.marketing.mailing.nieuwsbriefTemplates.update', $activeTemplate) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')
                <input type="hidden" name="html" id="email-builder-html-input">

                <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#0F9B9F] text-white text-[11px] font-semibold hover:bg-[#0d8589] transition">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    <span>Template opslaan</span>
                </button>
            </form>

            {{-- Template settings --}}
            <div class="relative">
                <button type="button"
                        id="email-builder-settings-toggle"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-[#21555820] bg-white text-[11px] font-semibold text-[#215558] hover:bg-[#21555805] transition">
                    <i class="fa-solid fa-sliders text-xs"></i>
                    <span>Settings</span>
                </button>

                <div id="email-builder-settings-panel"
                    class="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 z-20 hidden">
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558] mb-3">
                        Canvas instellingen
                    </p>

                    {{-- Achtergrondkleur linker canvasvlak --}}
                    <label class="grid gap-1 mb-3">
                        <span class="text-[11px] font-semibold text-[#215558]">
                            Achtergrond linker vlak
                        </span>
                        <div class="flex items-center gap-2">
                            <input type="color"
                                id="email-builder-bg-color"
                                value="#ffffff"
                                class="w-9 h-7 p-0 border-none bg-transparent cursor-pointer">
                            <input type="text"
                                id="email-builder-bg-color-hex"
                                value="#ffffff"
                                class="flex-1 border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                placeholder="#ffffff">
                        </div>
                        <p class="text-[10px] text-[#21555880]">
                            De kleur van het witte blok links waar de canvas in zit.
                        </p>
                    </label>

                    {{-- Snelle presets --}}
                    <div class="flex flex-wrap gap-2">
                        <button type="button"
                                class="w-6 h-6 rounded-full border border-[#21555820]"
                                style="background-color:#ffffff"
                                data-bg-preset="#ffffff"
                                title="Wit"></button>
                        <button type="button"
                                class="w-6 h-6 rounded-full border border-[#21555820]"
                                style="background-color:#f3f8f8"
                                data-bg-preset="#f3f8f8"
                                title="Licht grijs/groen"></button>
                        <button type="button"
                                class="w-6 h-6 rounded-full border border-[#21555820]"
                                style="background-color:#0F9B9F10"
                                data-bg-preset="#0F9B9F10"
                                title="Cyaan tint"></button>
                        <button type="button"
                                class="w-6 h-6 rounded-full border border-[#21555820]"
                                style="background-color:#21555810"
                                data-bg-preset="#21555810"
                                title="Donkere tint"></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full flex-1 grid grid-cols-3 gap-8 min-h-0 overflow-visible">
            {{-- CANVAS LINKS --}}
            <div class="col-span-2 flex flex-col min-h-0 overflow-visible">
                <div id="email-builder-canvas"
                    data-email-bg-wrapper
                    class="w-full flex-1 border-2 border-dashed border-[#21555820] flex flex-col min-h-0 overflow-y-auto"
                    style="background-color:#ffffff;">

                    {{-- Placeholder als er nog geen blokken zijn --}}
                    <div id="email-builder-placeholder"
                         class="flex-1 flex items-center justify-center px-6 text-center">
                        <p class="text-base font-bold text-[#215558]/20">
                            Sleep hier je elementen in.
                        </p>
                    </div>

                    {{-- Container voor blokken in de mail --}}
                    <div id="email-builder-blocks"
                        class="flex-1 flex flex-col overflow-visible min-h-0">
                        @if($activeTemplate && $activeTemplate->html)
                            {!! $activeTemplate->html !!}
                        @endif
                    </div>
                </div>
            </div>

            {{-- PALETTE RECHTS --}}
            <div class="p-8 bg-white rounded-4xl min-h-0 flex flex-col overflow-y-auto">
                <div id="email-builder-palette"
                    class="grid grid-cols-2 gap-2 w-full h-fit">
                    <!-- Logo -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="logo">
                        <i class="fa-solid fa-image text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Logo</p>
                    </div>
                    <!-- Titel -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="title">
                        <i class="fa-solid fa-h1 text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Titel</p>
                    </div>
                    <!-- Subtitel -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="subtitle">
                        <i class="fa-solid fa-h2 text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Subtitel</p>
                    </div>
                    <!-- Paragraaf -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="paragraph">
                        <i class="fa-solid fa-align-left text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Paragraaf</p>
                    </div>
                    <!-- Button -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="button">
                        <i class="fa-solid fa-link text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Button</p>
                    </div>
                    <!-- Document -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="document">
                        <i class="fa-solid fa-paperclip text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Document</p>
                    </div>
                    <!-- Afbeelding / Banner -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="image">
                        <i class="fa-solid fa-image text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Afbeelding</p>
                    </div>
                    <!-- Verdeler -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="divider">
                        <i class="fa-solid fa-grip-lines text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Verdeler</p>
                    </div>
                    <!-- Spacer / Witruimte -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="spacer">
                        <i class="fa-solid fa-arrows-up-down text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Witruimte</p>
                    </div>
                    <!-- Afmeld-link -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="unsubscribe">
                        <i class="fa-solid fa-user-slash text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">Afmeld-link</p>
                    </div>
                    <!-- Code blok -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="html">
                        <i class="fa-solid fa-code text-[#215558] text-2xl"></i>
                        <p class="text-sm font-semibold text-[#215558] text-center">HTML</p>
                    </div>
                    <!-- Sectie 1 kolom -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="one-column">
                        <div class="h-[24px] flex items-center gap-0.5">
                            <div class="w-[18px] h-full bg-[#215558] rounded-xl"></div>
                        </div>
                        <p class="text-sm font-semibold text-[#215558] text-center">1 Kolom</p>
                    </div>
                    <!-- Sectie 2 kolommen -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="two-columns">
                        <div class="h-[24px] flex items-center gap-0.5">
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                        </div>
                        <p class="text-sm font-semibold text-[#215558] text-center">2 Kolommen</p>
                    </div>
                    <!-- Sectie 3 kolommen -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="three-columns">
                        <div class="h-[24px] flex items-center gap-0.5">
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                        </div>
                        <p class="text-sm font-semibold text-[#215558] text-center">3 Kolommen</p>
                    </div>
                    <!-- Sectie 4 kolommen -->
                    <div class="builder-palette-item p-2 aspect-[1.5/1] rounded-3xl border border-[#21555820] bg-[#21555810] flex flex-col justify-center gap-2 items-center cursor-move"
                         draggable="true"
                         data-block-type="four-columns">
                        <div class="h-[24px] flex items-center gap-0.5">
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                            <div class="w-[10px] h-full bg-[#215558] rounded-xl"></div>
                        </div>
                        <p class="text-sm font-semibold text-[#215558] text-center">4 Kolommen</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
