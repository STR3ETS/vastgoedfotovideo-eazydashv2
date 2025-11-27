@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 flex flex-col">

            {{-- PLUSKNOP: quick create --}}
            <form id="nieuwsbrief-quick-create-form"
                  method="POST"
                  action="{{ route('support.marketing.mailing.nieuwsbriefTemplates.quickCreate') }}">
                @csrf
                <button type="submit"
                    class="cursor-pointer w-6 h-6 rounded-full bg-[#0F9B9F] flex items-center justify-center mb-4 shrink-0 relative group">
                    <i class="fa-solid fa-plus text-xs text-white"></i>
                    <div
                        class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] left-0
                            opacity-0 invisible translate-y-1 pointer-events-none
                            group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                            transition-all duration-300 ease-out z-10">
                        <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">Nieuwe template maken</p>
                    </div>
                </button>
            </form>

            <div class="grid grid-cols-4 gap-8 flex-1">
                {{-- LIJST LINKS --}}
                <div class="bg-[#f3f8f8] rounded-4xl p-8">
                    @if($templates->isEmpty())
                        <p id="nieuwsbrief-template-empty"
                           class="text-xs font-semibold text-[#215558]/50">
                            Nog geen nieuwsbrief-templates aangemaakt.
                        </p>
                    @else
                        <div id="nieuwsbrief-template-list" class="grid gap-2">
                            @foreach($templates as $template)
                                @php
                                    $isActive = $activeTemplate && $activeTemplate->id === $template->id;
                                @endphp

                                <a href="{{ route('support.marketing.mailing.nieuwsbriefTemplates', ['template' => $template->id]) }}"
                                   data-template-id="{{ $template->id }}"
                                   class="block py-2 px-3 rounded-2xl border transition duration-300
                                        {{ $isActive
                                            ? 'text-[#0F9B9F] bg-[#0F9B9F]/10 border-[#0F9B9F]/25'
                                            : 'text-[#215558] bg-white border-gray-200' }}">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="truncate text-sm font-bold">{{ $template->name }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- DETAIL RECHTS --}}
                <div class="bg-[#f3f8f8] rounded-4xl p-8 col-span-3 flex flex-col min-h-0">
                    <div id="nieuwsbrief-template-detail" class="flex-1 flex flex-col min-h-0">
                        @include('hub.marketing.mailing.templates.partials.detail', [
                            'activeTemplate' => $activeTemplate
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    /* Alleen kolommen met inhoud krijgen bij hover een dashed turquoise rand */
    .builder-column-blocks.has-content:hover {
        border-style: dashed;
        border-color: #0F9B9F;
    }

    /* === Resize handles === */
    .builder-resize-handle {
        position: absolute;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        border: 2px solid #0F9B9F;
        background: #fff;
        box-shadow: 0 0 0 1px rgba(15, 155, 159, 0.35);
        opacity: 0;
        pointer-events: auto;
        transition: opacity 0.15s ease;
        z-index: 5;
    }

    /* Toon handles als blok geselecteerd of bij hover */
    .builder-canvas-item.builder-selected .builder-resize-handle,
    .builder-canvas-item:hover .builder-resize-handle {
        opacity: 1;
    }

    .builder-resize-handle[data-resize-corner="nw"] {
        top: -6px;
        left: -6px;
        cursor: nwse-resize;
    }
    .builder-resize-handle[data-resize-corner="ne"] {
        top: -6px;
        right: -6px;
        cursor: nesw-resize;
    }
    .builder-resize-handle[data-resize-corner="sw"] {
        bottom: -6px;
        left: -6px;
        cursor: nesw-resize;
    }
    .builder-resize-handle[data-resize-corner="se"] {
        bottom: -6px;
        right: -6px;
        cursor: nwse-resize;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form  = document.getElementById('nieuwsbrief-quick-create-form');
    let   list  = document.getElementById('nieuwsbrief-template-list');
    const empty = document.getElementById('nieuwsbrief-template-empty');
    const detail = document.getElementById('nieuwsbrief-template-detail');
    let emailBuilderKeyHandler = null;
    let emailBuilderClickHandler = null;

    function setActiveTemplateInList(id) {
        if (!list) return;

        list.querySelectorAll('a[data-template-id]').forEach(function (a) {
            a.classList.remove('text-[#0F9B9F]', 'bg-[#0F9B9F]/10', 'border-[#0F9B9F]/25');
            a.classList.add('text-[#215558]', 'bg-white', 'border-gray-200');
        });

        const active = list.querySelector('a[data-template-id="' + id + '"]');
        if (active) {
            active.classList.remove('text-[#215558]', 'bg-white', 'border-gray-200');
            active.classList.add('text-[#0F9B9F]', 'bg-[#0F9B9F]/10', 'border-[#0F9B9F]/25');
        }
    }

    // ============================
    // MAILBUILDER INIT FUNCTIE
    // ============================
    function initEmailBuilder() {
        const canvas      = document.getElementById('email-builder-canvas');
        const blocksWrap  = document.getElementById('email-builder-blocks');
        const placeholder = document.getElementById('email-builder-placeholder');
        const palette     = document.getElementById('email-builder-palette');

        // Root voor labels (binnen de canvas)
        const builderRoot = canvas;

        // Selector voor alle mogelijke labels
        const LABEL_SELECTOR = '.builder-block-label, .column-label';

        // === DROP INDICATOR (blauwe lijn tussen blokken) ===
        const dropIndicator = document.createElement('div');
        dropIndicator.className = 'builder-drop-indicator';
        dropIndicator.style.height = '2px';
        dropIndicator.style.backgroundColor = '#0F9B9F';
        dropIndicator.style.borderRadius = '999px';
        dropIndicator.style.margin = '2px 0';
        dropIndicator.style.pointerEvents = 'none';

        function clearDropIndicator() {
            if (dropIndicator.parentNode) {
                dropIndicator.parentNode.removeChild(dropIndicator);
            }
        }

        function showDropIndicator(container, targetBlock, before) {
            if (!container) return;
            clearDropIndicator();

            if (targetBlock && targetBlock.parentElement === container) {
                if (before) {
                    container.insertBefore(dropIndicator, targetBlock);
                } else {
                    container.insertBefore(dropIndicator, targetBlock.nextSibling);
                }
            } else {
                container.appendChild(dropIndicator);
            }
        }

        // Alles verbergen
        function hideAllLabels() {
            if (!builderRoot) return;
            builderRoot.querySelectorAll(LABEL_SELECTOR).forEach(function (el) {
                el.style.opacity = '0';
            });
        }

        // Eén specifiek label tonen
        function showLabel(el) {
            if (!el) return;
            hideAllLabels();
            el.style.opacity = '1';
        }

        function updateColumnBorder(col) {
            if (!col) return;
            const hasItems = !!col.querySelector('.builder-canvas-item');

            if (hasItems) {
                // Kolom heeft inhoud: geen dashed + geen min-h
                col.classList.remove('border-dashed', 'min-h-[48px]');
                col.classList.add('has-content');
            } else {
                // Lege kolom: wel dashed + wel min-h als dropzone
                col.classList.add('border-dashed', 'min-h-[48px]');
                col.classList.remove('has-content');
            }
        }

        // Geen actieve template => geen canvas
        if (!canvas || !blocksWrap || !palette) return;

        // ---- INSPECTOR STATE ----
        let inspector = document.getElementById('email-builder-inspector');
        let activeInspectorBlock = null;

        if (!inspector) {
            inspector = document.createElement('div');
            inspector.id = 'email-builder-inspector';
            inspector.style.position = 'absolute';
            inspector.style.display = 'none';
            inspector.style.zIndex = '9999';
            inspector.style.pointerEvents = 'auto';
            document.body.appendChild(inspector);
        }

        function closeInspector() {
            if (!inspector) return;
            inspector.style.display = 'none';
            inspector.innerHTML = '';
            activeInspectorBlock = null;
        }

        function openInspectorForColumn(colEl) {
            if (!inspector || !colEl) return;

            const rect = colEl.getBoundingClientRect();
            const top  = rect.top + window.scrollY;
            const left = rect.right + 16 + window.scrollX;

            inspector.style.top  = top + 'px';
            inspector.style.left = left + 'px';

            const index = colEl.getAttribute('data-column-index') || '';
            const styles = window.getComputedStyle(colEl);

            const pt = parseInt(styles.paddingTop, 10)    || 0;
            const pr = parseInt(styles.paddingRight, 10)  || 0;
            const pb = parseInt(styles.paddingBottom, 10) || 0;
            const pl = parseInt(styles.paddingLeft, 10)   || 0;

            inspector.innerHTML = `
                <div class="bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 w-72">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558]">
                            Kolom ${index} instellingen
                        </p>
                        <button type="button"
                                class="text-xs text-[#21555880] hover:text-[#215558]"
                                data-inspector-close>&times;</button>
                    </div>

                    <div class="space-y-3">
                        <label class="grid gap-1">
                            <span class="text-[11px] font-semibold text-[#215558]">Padding (px)</span>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number"
                                    class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                    value="${pt}" data-col-pad="top" placeholder="Top">
                                <input type="number"
                                    class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                    value="${pb}" data-col-pad="bottom" placeholder="Bottom">
                                <input type="number"
                                    class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                    value="${pl}" data-col-pad="left" placeholder="Links">
                                <input type="number"
                                    class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                    value="${pr}" data-col-pad="right" placeholder="Rechts">
                            </div>
                        </label>
                    </div>
                </div>
            `;

            const closeBtn = inspector.querySelector('[data-inspector-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    closeInspector();
                });
            }

            const padInputs = inspector.querySelectorAll('[data-col-pad]');
            padInputs.forEach(function (input) {
                const side = input.dataset.colPad;
                input.addEventListener('input', function () {
                    const v = parseInt(this.value, 10) || 0;
                    if (side === 'top')    colEl.style.paddingTop    = v + 'px';
                    if (side === 'right')  colEl.style.paddingRight  = v + 'px';
                    if (side === 'bottom') colEl.style.paddingBottom = v + 'px';
                    if (side === 'left')   colEl.style.paddingLeft   = v + 'px';
                });
            });

            inspector.style.display = 'block';
        }

        function openInspectorForBlock(blockEl) {
            if (!inspector || !blockEl) return;

            const type = blockEl.dataset.blockType;
            activeInspectorBlock = blockEl;

            const rect = blockEl.getBoundingClientRect();
            const top  = rect.top + window.scrollY;
            const left = rect.right + 16 + window.scrollX;

            inspector.style.top  = top + 'px';
            inspector.style.left = left + 'px';

            // ==== LOGO BLOK ====
            if (type === 'logo') {
                const container    = blockEl.querySelector('[data-logo-container]');
                const resizeTarget = blockEl.querySelector('[data-resize-target]');
                const blockStyles  = window.getComputedStyle(blockEl);
                const inner        = blockEl.querySelector('.builder-block-inner') || blockEl;
                const innerStyles  = window.getComputedStyle(inner);
                const logoStyles   = resizeTarget ? window.getComputedStyle(resizeTarget) : null;

                // Margin (4 kanten) – blijft op de outer wrapper
                const mt = parseFloat(blockStyles.marginTop)    || 0;
                const mr = parseFloat(blockStyles.marginRight)  || 0;
                const mb = parseFloat(blockStyles.marginBottom) || 0;
                const ml = parseFloat(blockStyles.marginLeft)   || 0;
                const marginAll = mt;

                // Padding (4 kanten) – nu van inner wrapper
                const pt = parseFloat(innerStyles.paddingTop)    || 0;
                const pr = parseFloat(innerStyles.paddingRight)  || 0;
                const pb = parseFloat(innerStyles.paddingBottom) || 0;
                const pl = parseFloat(innerStyles.paddingLeft)   || 0;
                const padAll = pt;

                // Breedte / hoogte van logo-element
                const w = logoStyles ? (parseFloat(logoStyles.width)  || 128) : 128;
                const h = logoStyles ? (parseFloat(logoStyles.height) || 48)  : 48;

                // Huidige uitlijning
                let align = 'center';
                if (container) {
                    if (container.classList.contains('justify-start')) align = 'left';
                    else if (container.classList.contains('justify-end')) align = 'right';
                }

                inspector.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 w-72">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558]">
                                Logo instellingen
                            </p>
                            <button type="button"
                                    class="text-xs text-[#21555880] hover:text-[#215558]"
                                    data-inspector-close>&times;</button>
                        </div>

                        <div class="space-y-4">
                            <!-- MARGIN -->
                            <label class="grid gap-1">
                                <span class="text-[11px] font-semibold text-[#215558]">Margin</span>

                                <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-2 items-center">
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${marginAll}"
                                        data-logo-margin-all
                                        placeholder="Alle kanten">
                                    <select
                                        class="border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558] bg-white"
                                        data-logo-margin-unit>
                                        <option value="px" selected>px</option>
                                        <option value="rem">rem</option>
                                        <option value="em">em</option>
                                    </select>
                                </div>
                                <p class="text-[10px] text-[#21555880]">
                                    Pas alle kanten tegelijk aan of verfijn per kant.
                                </p>

                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${mt}" placeholder="Boven"
                                        data-logo-margin-top>
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${mr}" placeholder="Rechts"
                                        data-logo-margin-right>
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${mb}" placeholder="Onder"
                                        data-logo-margin-bottom>
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${ml}" placeholder="Links"
                                        data-logo-margin-left>
                                </div>
                            </label>

                            <!-- PADDING -->
                            <label class="grid gap-1">
                                <span class="text-[11px] font-semibold text-[#215558]">Padding</span>

                                <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-2 items-center">
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${padAll}"
                                        data-logo-pad-all
                                        placeholder="Alle kanten">
                                    <select
                                        class="border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558] bg-white"
                                        data-logo-pad-unit>
                                        <option value="px" selected>px</option>
                                        <option value="rem">rem</option>
                                        <option value="em">em</option>
                                    </select>
                                </div>
                                <p class="text-[10px] text-[#21555880]">
                                    Padding rondom het blok, of per zijde.
                                </p>

                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${pt}" placeholder="Boven"
                                        data-logo-pad-top>
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${pr}" placeholder="Rechts"
                                        data-logo-pad-right>
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${pb}" placeholder="Onder"
                                        data-logo-pad-bottom>
                                    <input type="number"
                                        class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558]"
                                        value="${pl}" placeholder="Links"
                                        data-logo-pad-left>
                                </div>
                            </label>

                            <!-- LOGO BESTAND -->
                            <label class="grid gap-1">
                                <span class="text-[11px] font-semibold text-[#215558]">Logo bestand</span>
                                <div class="flex gap-2">
                                    <button type="button"
                                            class="flex-1 px-3 py-1.5 rounded-full border border-[#21555820] text-[11px] font-semibold text-[#215558] hover:bg-[#21555805]"
                                            data-logo-upload>
                                        Afbeelding / video uploaden
                                    </button>
                                    <button type="button"
                                            class="px-3 py-1.5 rounded-full border border-[#21555810] text-[11px] text-[#21555880] hover:bg-[#21555805]"
                                            data-logo-reset>
                                        Reset
                                    </button>
                                </div>
                                <input type="file"
                                    class="hidden"
                                    accept="image/*,video/*"
                                    data-logo-file-input>
                            </label>

                            <!-- UITLIJNING -->
                            <label class="grid gap-1">
                                <span class="text-[11px] font-semibold text-[#215558]">Uitlijning</span>
                                <div class="flex items-center gap-1 bg-[#f3f8f8] rounded-full p-0.5" data-logo-align-group>
                                    <button type="button"
                                        class="flex-1 px-2.5 py-1.5 rounded-full text-[11px] font-semibold flex items-center justify-center gap-1 text-[#21555880]"
                                        data-logo-align="left">
                                        <i class="fa-solid fa-align-left text-[10px]"></i>
                                        <span>Links</span>
                                    </button>
                                    <button type="button"
                                        class="flex-1 px-2.5 py-1.5 rounded-full text-[11px] font-semibold flex items-center justify-center gap-1 text-[#21555880]"
                                        data-logo-align="center">
                                        <i class="fa-solid fa-align-center text-[10px]"></i>
                                        <span>Midden</span>
                                    </button>
                                    <button type="button"
                                        class="flex-1 px-2.5 py-1.5 rounded-full text-[11px] font-semibold flex items-center justify-center gap-1 text-[#21555880]"
                                        data-logo-align="right">
                                        <i class="fa-solid fa-align-right text-[10px]"></i>
                                        <span>Rechts</span>
                                    </button>
                                </div>
                            </label>
                        </div>
                    </div>
                `;

                const closeBtn = inspector.querySelector('[data-inspector-close]');
                if (closeBtn) closeBtn.addEventListener('click', closeInspector);

                // === MARGIN CONTROLS ===
                const marginAllInput   = inspector.querySelector('[data-logo-margin-all]');
                const marginUnitSelect = inspector.querySelector('[data-logo-margin-unit]');
                const mTopInput        = inspector.querySelector('[data-logo-margin-top]');
                const mRightInput      = inspector.querySelector('[data-logo-margin-right]');
                const mBottomInput     = inspector.querySelector('[data-logo-margin-bottom]');
                const mLeftInput       = inspector.querySelector('[data-logo-margin-left]');

                function getMarginUnit() {
                    return (marginUnitSelect && marginUnitSelect.value) || 'px';
                }
                function applyMargin(side, value) {
                    const unit = getMarginUnit();
                    const v = isNaN(value) ? 0 : value;

                    if (side === 'all') {
                        blockEl.style.marginTop    = v + unit;
                        blockEl.style.marginRight  = v + unit;
                        blockEl.style.marginBottom = v + unit;
                        blockEl.style.marginLeft   = v + unit;

                        if (mTopInput)    mTopInput.value    = v;
                        if (mRightInput)  mRightInput.value  = v;
                        if (mBottomInput) mBottomInput.value = v;
                        if (mLeftInput)   mLeftInput.value   = v;
                    } else {
                        if (side === 'top')    blockEl.style.marginTop    = v + unit;
                        if (side === 'right')  blockEl.style.marginRight  = v + unit;
                        if (side === 'bottom') blockEl.style.marginBottom = v + unit;
                        if (side === 'left')   blockEl.style.marginLeft   = v + unit;
                    }
                }

                if (marginAllInput) {
                    marginAllInput.addEventListener('input', function () {
                        applyMargin('all', parseFloat(this.value));
                    });
                }
                if (mTopInput) mTopInput.addEventListener('input', function () {
                    applyMargin('top', parseFloat(this.value));
                });
                if (mRightInput) mRightInput.addEventListener('input', function () {
                    applyMargin('right', parseFloat(this.value));
                });
                if (mBottomInput) mBottomInput.addEventListener('input', function () {
                    applyMargin('bottom', parseFloat(this.value));
                });
                if (mLeftInput) mLeftInput.addEventListener('input', function () {
                    applyMargin('left', parseFloat(this.value));
                });

                if (marginUnitSelect) {
                    marginUnitSelect.addEventListener('change', function () {
                        const unit = this.value || 'px';
                        const topVal    = mTopInput    ? parseFloat(mTopInput.value)    : mt;
                        const rightVal  = mRightInput  ? parseFloat(mRightInput.value)  : mr;
                        const bottomVal = mBottomInput ? parseFloat(mBottomInput.value) : mb;
                        const leftVal   = mLeftInput   ? parseFloat(mLeftInput.value)   : ml;

                        blockEl.style.marginTop    = (isNaN(topVal)    ? 0 : topVal)    + unit;
                        blockEl.style.marginRight  = (isNaN(rightVal)  ? 0 : rightVal)  + unit;
                        blockEl.style.marginBottom = (isNaN(bottomVal) ? 0 : bottomVal) + unit;
                        blockEl.style.marginLeft   = (isNaN(leftVal)   ? 0 : leftVal)   + unit;
                    });
                }

                // === PADDING CONTROLS ===
                const padAllInput   = inspector.querySelector('[data-logo-pad-all]');
                const padUnitSelect = inspector.querySelector('[data-logo-pad-unit]');
                const pTopInput     = inspector.querySelector('[data-logo-pad-top]');
                const pRightInput   = inspector.querySelector('[data-logo-pad-right]');
                const pBottomInput  = inspector.querySelector('[data-logo-pad-bottom]');
                const pLeftInput    = inspector.querySelector('[data-logo-pad-left]');

                function getPadUnit() {
                    return (padUnitSelect && padUnitSelect.value) || 'px';
                }
                function applyPadding(side, value) {
                    const unit = getPadUnit();
                    const v = isNaN(value) ? 0 : value;

                    if (side === 'all') {
                        inner.style.paddingTop    = v + unit;
                        inner.style.paddingRight  = v + unit;
                        inner.style.paddingBottom = v + unit;
                        inner.style.paddingLeft   = v + unit;

                        if (pTopInput)    pTopInput.value    = v;
                        if (pRightInput)  pRightInput.value  = v;
                        if (pBottomInput) pBottomInput.value = v;
                        if (pLeftInput)   pLeftInput.value   = v;
                    } else {
                        if (side === 'top')    inner.style.paddingTop    = v + unit;
                        if (side === 'right')  inner.style.paddingRight  = v + unit;
                        if (side === 'bottom') inner.style.paddingBottom = v + unit;
                        if (side === 'left')   inner.style.paddingLeft   = v + unit;
                    }
                }

                if (padAllInput) {
                    padAllInput.addEventListener('input', function () {
                        applyPadding('all', parseFloat(this.value));
                    });
                }
                if (pTopInput) pTopInput.addEventListener('input', function () {
                    applyPadding('top', parseFloat(this.value));
                });
                if (pRightInput) pRightInput.addEventListener('input', function () {
                    applyPadding('right', parseFloat(this.value));
                });
                if (pBottomInput) pBottomInput.addEventListener('input', function () {
                    applyPadding('bottom', parseFloat(this.value));
                });
                if (pLeftInput) pLeftInput.addEventListener('input', function () {
                    applyPadding('left', parseFloat(this.value));
                });

                if (padUnitSelect) {
                    padUnitSelect.addEventListener('change', function () {
                        const unit = this.value || 'px';
                        const topVal    = pTopInput    ? parseFloat(pTopInput.value)    : pt;
                        const rightVal  = pRightInput  ? parseFloat(pRightInput.value)  : pr;
                        const bottomVal = pBottomInput ? parseFloat(pBottomInput.value) : pb;
                        const leftVal   = pLeftInput   ? parseFloat(pLeftInput.value)   : pl;

                        inner.style.paddingTop    = (isNaN(topVal)    ? 0 : topVal)    + unit;
                        inner.style.paddingRight  = (isNaN(rightVal)  ? 0 : rightVal)  + unit;
                        inner.style.paddingBottom = (isNaN(bottomVal) ? 0 : bottomVal) + unit;
                        inner.style.paddingLeft   = (isNaN(leftVal)   ? 0 : leftVal)   + unit;
                    });
                }

                // === LOGO GROOTTE ===
                const wInput = inspector.querySelector('[data-logo-size-width]');
                const hInput = inspector.querySelector('[data-logo-size-height]');

                if (wInput && resizeTarget) {
                    wInput.addEventListener('input', function () {
                        const v = parseFloat(this.value);
                        resizeTarget.style.width = (isNaN(v) ? 0 : v) + 'px';
                    });
                }
                if (hInput && resizeTarget) {
                    hInput.addEventListener('input', function () {
                        const v = parseFloat(this.value);
                        resizeTarget.style.height = (isNaN(v) ? 0 : v) + 'px';
                    });
                }

                // === LOGO UPLOAD / RESET ===
                const uploadBtn = inspector.querySelector('[data-logo-upload]');
                const resetBtn  = inspector.querySelector('[data-logo-reset]');
                const fileInput = inspector.querySelector('[data-logo-file-input]');

                function setLogoMedia(el) {
                    if (!container) return;
                    const wrapper   = blockEl.querySelector('[data-resize-container]') || container;
                    const oldTarget = wrapper.querySelector('[data-resize-target]');

                    el.setAttribute('data-resize-target', '1');
                    el.style.display = 'block';
                    el.style.height  = 'auto';
                    el.style.width   = 'auto';

                    if (oldTarget) {
                        wrapper.replaceChild(el, oldTarget);
                    } else {
                        wrapper.insertBefore(el, wrapper.firstChild);
                    }
                }

                if (uploadBtn && fileInput && container) {
                    uploadBtn.addEventListener('click', function () {
                        fileInput.click();
                    });

                    fileInput.addEventListener('change', function () {
                        const file = this.files && this.files[0];
                        if (!file) return;

                        const url = URL.createObjectURL(file);

                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = url;
                            img.alt = 'Logo';
                            setLogoMedia(img);
                        } else if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = url;
                            video.controls = true;
                            setLogoMedia(video);
                        }
                    });
                }

                if (resetBtn && container) {
                    resetBtn.addEventListener('click', function () {
                        const wrapper   = blockEl.querySelector('[data-resize-container]') || container;
                        const oldTarget = wrapper.querySelector('[data-resize-target]');

                        const placeholder = document.createElement('div');
                        placeholder.className = 'h-12 w-32 bg-[#21555820] rounded flex items-center justify-center text-[10px] font-semibold text-[#21555880] uppercase tracking-[0.15em]';
                        placeholder.setAttribute('data-logo-placeholder', '1');
                        placeholder.setAttribute('data-resize-target', '1');
                        placeholder.textContent = 'LOGO';

                        if (oldTarget) {
                            wrapper.replaceChild(placeholder, oldTarget);
                        } else {
                            wrapper.insertBefore(placeholder, wrapper.firstChild);
                        }
                    });
                }

                // === UITLIJNING ICON-TABS ===
                const alignButtons = inspector.querySelectorAll('[data-logo-align]');

                function setAlignButtons(active) {
                    alignButtons.forEach(function (btn) {
                        const isActive = btn.dataset.logoAlign === active;
                        btn.classList.toggle('bg-[#0F9B9F]', isActive);
                        btn.classList.toggle('text-white', isActive);
                        btn.classList.toggle('shadow-sm', isActive);
                        btn.classList.toggle('text-[#21555880]', !isActive);
                    });
                }

                setAlignButtons(align);

                alignButtons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const val = this.dataset.logoAlign || 'center';
                        setAlignButtons(val);

                        if (container) {
                            container.classList.remove('justify-start', 'justify-center', 'justify-end');
                            if (val === 'left')   container.classList.add('justify-start');
                            if (val === 'center') container.classList.add('justify-center');
                            if (val === 'right')  container.classList.add('justify-end');
                        }
                    });
                });

            // ==== SPACER / WITRUIMTE ====
            } else if (type === 'spacer') {
                const spacerInner = blockEl.querySelector('[data-spacer-inner]');
                if (!spacerInner) {
                    inspector.innerHTML = '';
                    inspector.style.display = 'none';
                    return;
                }

                const spacerStyles = window.getComputedStyle(spacerInner);
                const currentH     = parseInt(spacerStyles.height, 10) || 24;

                inspector.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 w-72">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558]">
                                Witruimte
                            </p>
                            <button type="button"
                                    class="text-xs text-[#21555880] hover:text-[#215558]"
                                    data-inspector-close>&times;</button>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-semibold text-[#215558]">Hoogte</span>
                                <span class="text-[11px] font-semibold text-[#215558]" data-spacer-value>${currentH} px</span>
                            </div>
                            <input type="range"
                                min="0"
                                max="200"
                                step="2"
                                value="${currentH}"
                                class="w-full accent-[#0F9B9F]"
                                data-spacer-slider>
                        </div>
                    </div>
                `;

                const closeBtn = inspector.querySelector('[data-inspector-close]');
                if (closeBtn) closeBtn.addEventListener('click', closeInspector);

                const slider = inspector.querySelector('[data-spacer-slider]');
                const valueLabel = inspector.querySelector('[data-spacer-value]');

                if (slider) {
                    slider.addEventListener('input', function () {
                        const v = parseInt(this.value, 10) || 0;
                        spacerInner.style.height = v + 'px';
                        if (valueLabel) valueLabel.textContent = v + ' px';
                    });
                }

            } else if (type === 'image') {
                const mediaContainer = blockEl.querySelector('[data-image-container]');
                if (!mediaContainer) {
                    inspector.innerHTML = '';
                    inspector.style.display = 'none';
                    return;
                }

                inspector.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 w-72">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558]">
                                Afbeelding / video
                            </p>
                            <button type="button"
                                    class="text-xs text-[#21555880] hover:text-[#215558]"
                                    data-inspector-close>&times;</button>
                        </div>

                        <div class="space-y-3">
                            <p class="text-[11px] text-[#21555880]">
                                Upload een afbeelding of video om de placeholder te vervangen.
                                Video wordt alleen als voorbeeld in de builder getoond.
                            </p>

                            <div class="flex gap-2">
                                <button type="button"
                                        class="flex-1 px-3 py-1.5 rounded-full border border-[#21555820] text-[11px] font-semibold text-[#215558] hover:bg-[#21555805]"
                                        data-image-upload>
                                    Bestand kiezen
                                </button>
                                <button type="button"
                                        class="px-3 py-1.5 rounded-full border border-[#21555810] text-[11px] text-[#21555880] hover:bg-[#21555805]"
                                        data-image-reset>
                                    Reset
                                </button>
                            </div>

                            <input type="file"
                                class="hidden"
                                accept="image/*,video/*"
                                data-image-file-input>
                        </div>
                    </div>
                `;

                const closeBtn   = inspector.querySelector('[data-inspector-close]');
                const uploadBtn  = inspector.querySelector('[data-image-upload]');
                const resetBtn   = inspector.querySelector('[data-image-reset]');
                const fileInput  = inspector.querySelector('[data-image-file-input]');

                if (closeBtn) {
                    closeBtn.addEventListener('click', closeInspector);
                }

                function setMediaElement(el) {
                    const wrapper = mediaContainer; // data-resize-container
                    const oldTarget = wrapper.querySelector('[data-resize-target]');

                    el.setAttribute('data-resize-target', '1');
                    el.style.display = 'block';
                    el.style.width = '100%';
                    el.style.height = 'auto';
                    el.style.borderRadius = '0.75rem';

                    if (oldTarget) {
                        wrapper.replaceChild(el, oldTarget);
                    } else {
                        wrapper.insertBefore(el, wrapper.firstChild);
                    }
                }

                if (uploadBtn && fileInput) {
                    uploadBtn.addEventListener('click', function () {
                        fileInput.click();
                    });

                    fileInput.addEventListener('change', function () {
                        const file = this.files && this.files[0];
                        if (!file) return;

                        const url = URL.createObjectURL(file);

                        if (file.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = url;
                            img.alt = 'Afbeelding';
                            setMediaElement(img);
                        } else if (file.type.startsWith('video/')) {
                            const video = document.createElement('video');
                            video.src = url;
                            video.controls = true;
                            setMediaElement(video);
                        }
                    });
                }

                if (resetBtn) {
                    resetBtn.addEventListener('click', function () {
                        const wrapper   = mediaContainer;
                        const oldTarget = wrapper.querySelector('[data-resize-target]');

                        const placeholder = document.createElement('div');
                        placeholder.className = 'w-full aspect-[16/9] bg-[#21555810] border border-dashed border-[#21555840] rounded flex items-center justify-center text-[11px] text-[#21555880]';
                        placeholder.setAttribute('data-image-placeholder', '1');
                        placeholder.setAttribute('data-resize-target', '1');
                        placeholder.textContent = 'Afbeelding placeholder';

                        if (oldTarget) {
                            wrapper.replaceChild(placeholder, oldTarget);
                        } else {
                            wrapper.insertBefore(placeholder, wrapper.firstChild);
                        }
                    });
                }

            // ==== 2/3/4 KOLUMNEN BLOK ALS GEHEEL ====
            } else if (type === 'two-columns' || type === 'three-columns' || type === 'four-columns') {
                const row = blockEl.querySelector('[data-columns-row]');
                if (!row) {
                    inspector.innerHTML = '';
                    inspector.style.display = 'none';
                    return;
                }

                const rowStyles = window.getComputedStyle(row);
                const currentGap = parseInt(rowStyles.columnGap || rowStyles.gap, 10) || 16;
                const alignCss   = rowStyles.alignItems || 'stretch';

                let align = 'top';
                if (alignCss === 'center')      align = 'middle';
                else if (alignCss === 'flex-end') align = 'bottom';

                inspector.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 w-72">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558]">
                                Kolom lay-out
                            </p>
                            <button type="button"
                                    class="text-xs text-[#21555880] hover:text-[#215558]"
                                    data-inspector-close>&times;</button>
                        </div>

                        <div class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-semibold text-[#215558]">Afstand tussen kolommen</span>
                                    <span class="text-[11px] font-semibold text-[#215558]" data-cols-gap-value>${currentGap} px</span>
                                </div>
                                <input type="range"
                                    min="0"
                                    max="96"
                                    step="2"
                                    value="${currentGap}"
                                    class="w-full accent-[#0F9B9F]"
                                    data-cols-gap-slider>
                            </div>

                            <label class="grid gap-1">
                                <span class="text-[11px] font-semibold text-[#215558]">Verticale uitlijning</span>
                                <select class="w-full border border-[#21555820] rounded-lg px-2 py-1 text-[11px] text-[#215558] bg-white"
                                        data-cols-align>
                                    <option value="top"${align === 'top' ? ' selected' : ''}>Boven</option>
                                    <option value="middle"${align === 'middle' ? ' selected' : ''}>Midden</option>
                                    <option value="bottom"${align === 'bottom' ? ' selected' : ''}>Onder</option>
                                </select>
                            </label>
                        </div>
                    </div>
                `;

                const closeBtn = inspector.querySelector('[data-inspector-close]');
                if (closeBtn) closeBtn.addEventListener('click', closeInspector);

                const gapSlider = inspector.querySelector('[data-cols-gap-slider]');
                const gapLabel  = inspector.querySelector('[data-cols-gap-value]');
                const alignSelect = inspector.querySelector('[data-cols-align]');

                if (gapSlider) {
                    gapSlider.addEventListener('input', function () {
                        const v = parseInt(this.value, 10) || 0;
                        row.style.columnGap = v + 'px';
                        row.style.gap       = v + 'px';
                        if (gapLabel) gapLabel.textContent = v + ' px';
                    });
                }

                if (alignSelect) {
                    alignSelect.addEventListener('change', function () {
                        if (this.value === 'top')    row.style.alignItems = 'flex-start';
                        if (this.value === 'middle') row.style.alignItems = 'center';
                        if (this.value === 'bottom') row.style.alignItems = 'flex-end';
                    });
                }

            // ==== DEFAULT ====
            } else {
                inspector.innerHTML = `
                    <div class="bg-white rounded-2xl shadow-lg border border-[#21555820] p-4 w-64">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#215558]">
                                Blok instellingen
                            </p>
                            <button type="button"
                                    class="text-xs text-[#21555880] hover:text-[#215558]"
                                    data-inspector-close>&times;</button>
                        </div>
                        <p class="text-[12px] text-[#215558]">
                            Voor dit bloktype zijn nog geen instellingen beschikbaar.
                        </p>
                    </div>
                `;

                const closeBtn = inspector.querySelector('[data-inspector-close]');
                if (closeBtn) closeBtn.addEventListener('click', closeInspector);
            }

            inspector.style.display = 'block';
        }

        // ---- STATE & HELPERS ----
        let draggingFromPalette = false;
        let dragSourceEl        = null;
        let currentDropTarget   = null;
        let dropBefore          = false;
        let currentContainer    = null;
        let isCopyDrag          = false;
        let selectedBlock       = null;

        function setSelectedBlock(blockEl) {
            if (selectedBlock && selectedBlock !== blockEl) {
                selectedBlock.classList.remove(
                    'builder-selected',
                    'ring-1',
                    'ring-[#0F9B9F]',
                    'ring-offset-0',
                    'ring-offset-transparent',
                    'rounded'
                );
            }

            selectedBlock = blockEl || null;

            if (selectedBlock) {
                selectedBlock.classList.add(
                    'builder-selected',
                    'ring-1',
                    'ring-[#0F9B9F]',
                    'ring-offset-0',
                    'ring-offset-transparent',
                    'rounded'
                );
            }
        }

        function updatePlaceholder() {
            const hasBlocks = !!blocksWrap.querySelector('.builder-canvas-item');

            if (placeholder) {
                placeholder.style.display = hasBlocks ? 'none' : 'flex';
            }

            if (canvas) {
                if (hasBlocks) {
                    canvas.classList.remove('border-2', 'border-dashed', 'border-[#21555820]', 'bg-[#21555810]', 'p-4');
                    canvas.classList.add('bg-white');
                } else {
                    canvas.classList.add('border-2', 'border-dashed', 'border-[#21555820]', 'bg-[#21555810]', 'p-4');
                    canvas.classList.remove('bg-white');
                }
            }
        }

        // ---- RESIZE STATE ----
        let resizeState = null;

        function onResizeMouseMove(e) {
            if (!resizeState) return;
            e.preventDefault();

            const dx = e.clientX - resizeState.startX;
            const dy = e.clientY - resizeState.startY;

            const corner = resizeState.corner;
            const aspect = resizeState.startAspect || 1;

            let newWidth;
            let newHeight;

            // Hoek rechts/links → base op horizontale beweging
            if (corner.includes('e') || corner.includes('w')) {
                const deltaX = corner.includes('e')
                    ? dx
                    : -dx;

                newWidth  = resizeState.startWidth + deltaX;
                newWidth  = Math.max(24, newWidth);
                newHeight = newWidth / aspect;

            // Hoek boven/onder (voor het geval je alleen verticaal wilt slepen)
            } else {
                const deltaY = corner.includes('s')
                    ? dy
                    : -dy;

                newHeight = resizeState.startHeight + deltaY;
                newHeight = Math.max(24, newHeight);
                newWidth  = newHeight * aspect;
            }

            resizeState.target.style.width  = newWidth + 'px';
            resizeState.target.style.height = newHeight + 'px';
        }

        function onResizeMouseUp() {
            if (!resizeState) return;
            document.removeEventListener('mousemove', onResizeMouseMove);
            document.removeEventListener('mouseup', onResizeMouseUp);
            resizeState = null;
        }

        function attachBlockDragEvents(blockEl) {
            // ========== DRAG ==========
            blockEl.addEventListener('dragstart', function (e) {
                draggingFromPalette = false;
                dragSourceEl = blockEl;

                isCopyDrag = e.ctrlKey || e.metaKey;

                e.dataTransfer.effectAllowed = isCopyDrag ? 'copyMove' : 'move';
                e.dataTransfer.setData('text/plain', blockEl.dataset.blockType || '');
            });

            blockEl.addEventListener('dragend', function () {
                dragSourceEl = null;
                isCopyDrag = false;
                clearDropIndicator();
            });

            // ========== SELECTIE OP KLIK ==========
            blockEl.addEventListener('click', function () {
                setSelectedBlock(blockEl);
            });

            // ========== DUBBELKLIK: PROPERTIES ==========
            blockEl.addEventListener('dblclick', function (e) {
                e.stopPropagation();
                setSelectedBlock(blockEl);
                openInspectorForBlock(blockEl);
            });

            // ========== HOVER LABELS ==========
            const blockLabel = blockEl.querySelector('.builder-block-label');

            if (blockLabel) {
                blockLabel.addEventListener('click', function (e) {
                    e.stopPropagation();
                    setSelectedBlock(blockEl);
                });
            }

            blockEl.addEventListener('mouseenter', function () {
                if (blockLabel) {
                    showLabel(blockLabel);
                }
            });

            blockEl.addEventListener('mouseleave', function () {
                const col = blockEl.closest('.builder-column-blocks');
                if (col) {
                    const colLabel = col.querySelector('.column-label');
                    if (colLabel) {
                        showLabel(colLabel);
                        return;
                    }
                }
                hideAllLabels();
            });

            // ========== RESIZE HANDLES ==========
            blockEl.querySelectorAll('.builder-resize-handle').forEach(function (handle) {
                handle.addEventListener('mousedown', function (e) {
                    e.stopPropagation();
                    e.preventDefault();

                    const corner = handle.dataset.resizeCorner || 'se';
                    const container = handle.closest('[data-resize-container]');
                    if (!container) return;

                    // Kies het fysieke element dat we willen schalen
                    const target = container.querySelector('[data-resize-target]') || container;
                    const rect   = target.getBoundingClientRect();

                    resizeState = {
                        target: target,
                        corner: corner,
                        startX: e.clientX,
                        startY: e.clientY,
                        startWidth: rect.width,
                        startHeight: rect.height,
                        startAspect: rect.width / rect.height || 1,
                    };

                    document.addEventListener('mousemove', onResizeMouseMove);
                    document.addEventListener('mouseup', onResizeMouseUp);
                });
            });
        }

        function attachColumnDropzones(rootEl) {
            const columnBlocks = rootEl.querySelectorAll('.builder-column-blocks');

            columnBlocks.forEach(function (col) {
                const colLabel = col.querySelector('.column-label');
                if (colLabel) {
                    col.addEventListener('mouseenter', function () {
                        showLabel(colLabel);
                    });
                }

                // Dubbelklik op kolom = kolom-instellingen
                col.addEventListener('dblclick', function (e) {
                    e.stopPropagation(); // voorkom dat de parent builder-canvas-item dblclick ook vuurt
                    openInspectorForColumn(col);
                });

                col.addEventListener('dragenter', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    col.style.borderColor = '#0F9B9F';
                    col.style.borderWidth = '2px';
                    col.style.borderStyle = 'dashed';
                });

                col.addEventListener('dragleave', function (e) {
                    if (e.relatedTarget && col.contains(e.relatedTarget)) return;
                    col.style.borderColor = '';
                    col.style.borderWidth = '';
                    col.style.borderStyle = '';
                    clearDropIndicator();
                });

                col.addEventListener('dragover', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    currentContainer = col;

                    const targetBlock = e.target.closest('.builder-canvas-item');
                    currentDropTarget = targetBlock || null;

                    if (targetBlock) {
                        const rect = targetBlock.getBoundingClientRect();
                        dropBefore = e.clientY < rect.top + rect.height / 2;
                    } else {
                        dropBefore = false;
                    }

                    showDropIndicator(currentContainer, currentDropTarget, dropBefore);
                });

                col.addEventListener('drop', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const type = e.dataTransfer.getData('text/plain') || null;
                    const container = currentContainer || col;

                    if ((draggingFromPalette || isCopyDrag) && type) {
                        const newBlock = createCanvasBlock(type);

                        if (currentDropTarget && currentDropTarget.parentElement === container) {
                            if (dropBefore) {
                                container.insertBefore(newBlock, currentDropTarget);
                            } else {
                                container.insertBefore(newBlock, currentDropTarget.nextSibling);
                            }
                        } else {
                            container.appendChild(newBlock);
                        }
                    } else if (!draggingFromPalette && dragSourceEl) {
                        if (currentDropTarget && currentDropTarget !== dragSourceEl && currentDropTarget.parentElement === container) {
                            if (dropBefore) {
                                container.insertBefore(dragSourceEl, currentDropTarget);
                            } else {
                                container.insertBefore(dragSourceEl, currentDropTarget.nextSibling);
                            }
                        } else if (!currentDropTarget) {
                            container.appendChild(dragSourceEl);
                        }
                    }

                    draggingFromPalette = false;
                    dragSourceEl = null;
                    currentDropTarget = null;
                    currentContainer = null;
                    dropBefore = false;

                    col.style.borderColor = '';
                    col.style.borderWidth = '';
                    col.style.borderStyle = '';
                    clearDropIndicator();

                    updatePlaceholder();
                    updateColumnBorder(col);
                });
            });
        }

        // ---- BLOK DEFINITIES + RENDERING ----
        const BLOCK_DEFS = {
            logo:            { label: 'Logo' },
            title:           { label: 'Titel' },
            subtitle:        { label: 'Subtitel' },
            paragraph:       { label: 'Paragraaf' },
            button:          { label: 'Button' },
            document:        { label: 'Document' },
            image:           { label: 'Afbeelding' },
            divider:         { label: 'Verdeler' },
            spacer:          { label: 'Witruimte' },
            unsubscribe:     { label: 'Afmeld-link' },
            html:            { label: 'HTML' },
            'two-columns':   { label: '2 kolommen' },
            'three-columns': { label: '3 kolommen' },
            'four-columns':  { label: '4 kolommen' },
        };

        function createCanvasBlock(type) {
            const wrapper = document.createElement('div');
            wrapper.className = 'builder-canvas-item w-full relative group';
            wrapper.setAttribute('draggable', 'true');
            wrapper.dataset.blockType = type;

            let content = '';

            switch (type) {
                case 'logo':
                    content = `
                        <div class="w-full flex justify-center"
                            data-logo-container>
                            <div class="relative inline-flex items-center justify-center"
                                data-resize-container>
                                <div
                                    class="h-12 w-32 bg-[#21555820] rounded flex items-center justify-center text-[10px] font-semibold text-[#21555880] uppercase tracking-[0.15em]"
                                    data-logo-placeholder
                                    data-resize-target
                                >
                                    LOGO
                                </div>

                                <span class="builder-resize-handle" data-resize-corner="nw"></span>
                                <span class="builder-resize-handle" data-resize-corner="ne"></span>
                                <span class="builder-resize-handle" data-resize-corner="sw"></span>
                                <span class="builder-resize-handle" data-resize-corner="se"></span>
                            </div>
                        </div>
                    `;
                    break;

                case 'title':
                    content = `
                        <h1 contenteditable="true" class="outline-none text-[22px] leading-snug font-bold text-[#215558]">
                            Lorem ipsum dolor sit amet.
                        </h1>
                    `;
                    break;

                case 'subtitle':
                    content = `
                        <h2 contenteditable="true" class="outline-none  text-[16px] leading-snug font-semibold text-[#215558]">
                            Schrijf hier je subtitel
                        </h2>
                    `;
                    break;

                case 'paragraph':
                    content = `
                        <p contenteditable="true" class="outline-none  text-[13px] leading-relaxed text-[#215558]">
                            Dit is voorbeeldtekst voor je nieuwsbrief. Klik om deze tekst aan te passen met jouw eigen verhaal.
                        </p>
                    `;
                    break;

                case 'button':
                    content = `
                        <div class="w-full flex justify-start">
                            <a href="#" contenteditable="true"
                            class="outline-none inline-flex px-5 py-2 rounded-full bg-[#0F9B9F] text-white text-[13px] font-semibold">
                                Call to action
                            </a>
                        </div>
                    `;
                    break;

                case 'image':
                    content = `
                        <div class="w-full relative"
                            data-image-container
                            data-resize-container>
                            <div class="w-full aspect-[16/9] bg-[#21555810] border border-dashed border-[#21555840] rounded flex items-center justify-center text-[11px] text-[#21555880]"
                                data-image-placeholder
                                data-resize-target>
                                Afbeelding placeholder
                            </div>

                            <span class="builder-resize-handle" data-resize-corner="nw"></span>
                            <span class="builder-resize-handle" data-resize-corner="ne"></span>
                            <span class="builder-resize-handle" data-resize-corner="sw"></span>
                            <span class="builder-resize-handle" data-resize-corner="se"></span>
                        </div>
                    `;
                    break;

                case 'divider':
                    content = `
                        <div class="w-full">
                            <div class="h-px w-full bg-[#21555820]"></div>
                        </div>
                    `;
                    break;

                case 'spacer':
                    content = `
                        <div class="w-full h-6" data-spacer-inner></div>
                    `;
                    break;

                case 'unsubscribe':
                    content = `
                        <p contenteditable="true" class="outline-none text-[11px] leading-snug text-[#21555880]">
                            Je ontvangt deze mail omdat je klant bent bij [bedrijfsnaam].
                            <u>Afmelden voor deze mails</u>.
                        </p>
                    `;
                    break;

                case 'document':
                    content = `
                        <div class="w-full flex items-center gap-3">
                            <div class="w-9 h-9 rounded bg-[#21555810] flex items-center justify-center">
                                <i class="fa-solid fa-file-pdf text-[#215558] text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p contenteditable="true" class="outline-none text-[13px] font-semibold text-[#215558] leading-snug">
                                    Documentnaam.pdf
                                </p>
                                <p class="text-[11px] text-[#21555880] leading-snug">
                                    Klik om te openen / downloaden
                                </p>
                            </div>
                        </div>
                    `;
                    break;

                case 'html':
                    content = `
                        <div class="w-full bg-[#0F9B9F]/5 border border-dashed border-[#0F9B9F40] rounded p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.18em] text-[#0F9B9F]">
                                Custom HTML
                            </p>
                            <pre contenteditable="true" class="outline-none text-[11px] leading-snug text-[#215558] font-mono whitespace-pre-wrap">&lt;!-- Plak hier je eigen HTML voor geavanceerde layouts --&gt;
                            </pre>
                        </div>
                    `;
                    break;

                case 'two-columns':
                case 'three-columns':
                case 'four-columns': {
                    const cols = type === 'two-columns' ? 2 : (type === 'three-columns' ? 3 : 4);
                    let colsHtml = '';

                    for (let i = 0; i < cols; i++) {
                        colsHtml += `
                            <div class="flex-1">
                                <div class="builder-column-blocks relative flex flex-col gap-1 min-h-[48px]
                                            border border-[#21555820] rounded"
                                    data-column-index="${i + 1}">
                                    <span class="column-label pointer-events-none absolute -top-5 left-1 px-2 py-0.5 rounded-full bg-[#0F9B9F]
                                                text-[10px] font-semibold text-[#fff] opacity-0 transition-opacity duration-150 leading-none">
                                        Kolom ${i + 1}
                                    </span>
                                    <!-- Sleep hier elementen in deze kolom -->
                                </div>
                            </div>
                        `;
                    }

                    content = `
                        <div class="w-full">
                            <div class="flex gap-4" data-columns-row data-columns-count="${cols}">
                                ${colsHtml}
                            </div>
                        </div>
                    `;
                    break;
                }

                default:
                    content = `
                        <p class="text-[13px] text-[#215558]">
                            ${BLOCK_DEFS[type]?.label || type}
                        </p>
                    `;
                    break;
            }

            const label = BLOCK_DEFS[type]?.label || type;

            if (type === 'two-columns' || type === 'three-columns' || type === 'four-columns') {
                wrapper.innerHTML = `
                    <div class="builder-block-inner w-full rounded border border-transparent bg-white
                                group-hover:border-dashed group-hover:border-[#21555866]">
                        ${content}
                    </div>
                `;
            } else {
                wrapper.innerHTML = `
                    <span class="builder-block-label pointer-events-none absolute -top-5 left-0 px-2.5 py-0.5 rounded-full bg-[#0F9B9F]
                                text-[11px] font-semibold text-[#fff] opacity-0 transition-opacity duration-150
                                leading-none">
                        ${label}
                    </span>
                    <div class="builder-block-inner w-full rounded border border-transparent bg-white
                                group-hover:border-dashed group-hover:border-[#21555866]">
                        ${content}
                    </div>
                `;
            }

            attachBlockDragEvents(wrapper);
            attachColumnDropzones(wrapper);

            return wrapper;
        }

        // ---- PALETTE → DRAGSTART ----
        palette.querySelectorAll('.builder-palette-item[data-block-type]').forEach(function (item) {
            item.addEventListener('dragstart', function (e) {
                const type = item.dataset.blockType;
                if (!type) return;

                draggingFromPalette = true;
                dragSourceEl = null;
                e.dataTransfer.setData('text/plain', type);
                e.dataTransfer.effectAllowed = 'copyMove';
            });
        });

        // ---- CANVAS DRAGOVER / DROP (hoofdniveau) ----
        canvas.addEventListener('dragover', function (e) {
            e.preventDefault();

            currentContainer = blocksWrap;

            const targetBlock = e.target.closest('.builder-canvas-item');
            currentDropTarget = targetBlock || null;

            if (targetBlock) {
                const rect = targetBlock.getBoundingClientRect();
                dropBefore = e.clientY < rect.top + rect.height / 2;
            } else {
                dropBefore = false;
            }

            showDropIndicator(currentContainer, currentDropTarget, dropBefore);
        });

        canvas.addEventListener('drop', function (e) {
            e.preventDefault();

            const type = e.dataTransfer.getData('text/plain') || null;
            const container = currentContainer || blocksWrap;

            if ((draggingFromPalette || isCopyDrag) && type) {
                const newBlock = createCanvasBlock(type);

                if (currentDropTarget && currentDropTarget.parentElement === container) {
                    if (dropBefore) {
                        container.insertBefore(newBlock, currentDropTarget);
                    } else {
                        container.insertBefore(newBlock, currentDropTarget.nextSibling);
                    }
                } else {
                    container.appendChild(newBlock);
                }

                updatePlaceholder();
            } else if (!draggingFromPalette && dragSourceEl) {
                if (currentDropTarget && currentDropTarget !== dragSourceEl && currentDropTarget.parentElement === container) {
                    if (dropBefore) {
                        container.insertBefore(dragSourceEl, currentDropTarget);
                    } else {
                        container.insertBefore(dragSourceEl, currentDropTarget.nextSibling);
                    }
                } else if (!currentDropTarget) {
                    container.appendChild(dragSourceEl);
                }

                updatePlaceholder();
            }

            draggingFromPalette = false;
            dragSourceEl = null;
            currentDropTarget = null;
            currentContainer = null;
            dropBefore = false;

            clearDropIndicator();
        });

        // Als er ooit server-side blokken staan:
        blocksWrap.querySelectorAll('.builder-canvas-item').forEach(attachBlockDragEvents);

        // (Re)bind globale keyboard + click handlers
        if (emailBuilderKeyHandler) {
            document.removeEventListener('keydown', emailBuilderKeyHandler);
        }
        if (emailBuilderClickHandler) {
            document.removeEventListener('click', emailBuilderClickHandler);
        }

        function isEditableTarget(target) {
            if (!target) return false;
            const tag = target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
            if (target.isContentEditable) return true;
            return false;
        }

        emailBuilderKeyHandler = function (e) {
            if (e.key !== 'Backspace' && e.key !== 'Delete') return;

            if (isEditableTarget(e.target)) return;
            if (!selectedBlock || !selectedBlock.parentElement) return;

            e.preventDefault();

            const blockToRemove = selectedBlock;
            const parent        = blockToRemove.parentElement;
            const colParent     = parent.closest('.builder-column-blocks');

            parent.removeChild(blockToRemove);

            if (activeInspectorBlock === blockToRemove) {
                closeInspector();
            }

            selectedBlock = null;

            if (colParent) {
                updateColumnBorder(colParent);
            }

            updatePlaceholder();
        };

        document.addEventListener('keydown', emailBuilderKeyHandler);

        emailBuilderClickHandler = function (e) {
            if (!selectedBlock) return;

            if (selectedBlock.contains(e.target)) return;

            if (inspector && inspector.contains(e.target)) return;

            setSelectedBlock(null);
        };

        document.addEventListener('click', emailBuilderClickHandler);

        blocksWrap.querySelectorAll('.builder-column-blocks').forEach(updateColumnBorder);

        updatePlaceholder();
    }
    // ===== EINDE MAILBUILDER INIT =====

    function renderTemplateDetail(html) {
        if (!detail) return;
        detail.innerHTML = html;
        initEmailBuilder();
    }

    // SOFT QUICK-CREATE
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60', 'cursor-wait');
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
            })
            .then(function (res) {
                if (!res.ok) throw new Error('Response not ok');
                return res.json();
            })
            .then(function (data) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-wait');
                }

                if (!list) {
                    const wrapper = empty ? empty.parentElement : null;
                    if (wrapper) {
                        if (empty) empty.remove();
                        list = document.createElement('div');
                        list.id = 'nieuwsbrief-template-list';
                        list.className = 'grid gap-2';
                        wrapper.appendChild(list);
                    }
                }

                if (!list) return;

                list.querySelectorAll('a[data-template-id]').forEach(function (a) {
                    a.classList.remove('text-[#0F9B9F]', 'bg-[#0F9B9F]/10', 'border-[#0F9B9F]/25');
                    a.classList.add('text-[#215558]', 'bg-white', 'border-gray-200');
                });

                const a = document.createElement('a');
                a.href = data.url;
                a.dataset.templateId = data.id;
                a.className = 'block py-2 px-3 rounded-2xl border transition duration-300 text-[#0F9B9F] bg-[#0F9B9F]/10 border-[#0F9B9F]/25';
                a.innerHTML = `
                    <div class="flex items-center justify-between gap-2">
                        <span class="truncate text-sm font-bold">${data.name || 'Nieuwe template'}</span>
                    </div>
                `;

                list.prepend(a);

                renderTemplateDetail(data.detail_html);

                if (data.url) {
                    history.pushState(null, '', data.url);
                }
            })
            .catch(function (err) {
                console.error(err);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-wait');
                }
                form.submit();
            });
        });
    }

    // SOFT SELECT
    document.addEventListener('click', function (e) {
        const link = e.target.closest('#nieuwsbrief-template-list a[data-template-id]');
        if (!link) return;

        e.preventDefault();

        const templateId = link.dataset.templateId;
        if (!templateId) return;

        setActiveTemplateInList(templateId);

        fetch(link.href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(function (res) {
            if (!res.ok) throw new Error('Response not ok');
            return res.json();
        })
        .then(function (data) {
            renderTemplateDetail(data.detail_html);

            if (data.url) {
                history.pushState(null, '', data.url);
            }
        })
        .catch(function (err) {
            console.error(err);
            window.location.href = link.href;
        });
    });

    initEmailBuilder();
});
</script>
@endsection