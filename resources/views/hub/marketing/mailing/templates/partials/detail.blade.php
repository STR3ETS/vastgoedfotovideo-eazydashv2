@if(!$activeTemplate)
    <div class="flex items-center gap-4">
        <span class="text-4xl">👈</span>
        <p class="text-base font-bold text-[#215558]/80 mt-1">
            Maak of selecteer een nieuwsbrief-template om te beginnen.
        </p>
    </div>
@else
    <div class="w-full flex-1 flex flex-col min-h-0">
        <div class="shrink-0 mb-6">
            <h2 class="text-[#215558] font-black text-xl">
                {{ $activeTemplate->name }}
            </h2>
        </div>

        <div class="w-full flex-1 grid grid-cols-3 gap-8 min-h-0">
            {{-- CANVAS LINKS --}}
            <div class="p-8 col-span-2 bg-white rounded-4xl min-h-0">
                <div id="email-builder-canvas"
                     class="w-full h-full rounded-3xl border-2 border-dashed border-[#21555820] bg-[#21555810] flex flex-col min-h-0">

                    {{-- Placeholder als er nog geen blokken zijn --}}
                    <div id="email-builder-placeholder"
                         class="flex-1 flex items-center justify-center px-6 text-center">
                        <p class="text-base font-bold text-[#215558]/20">
                            Sleep hier je elementen in.
                        </p>
                    </div>

                    {{-- Container voor blokken in de mail --}}
                    <div id="email-builder-blocks"
                         class="flex-1 flex flex-col gap-0.5 p-4 overflow-visisble min-h-0">
                        {{-- Hier worden blokken via JS toegevoegd --}}
                    </div>
                </div>
            </div>

            {{-- PALETTE RECHTS --}}
            <div class="p-8 bg-white rounded-4xl min-h-0">
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
