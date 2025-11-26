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

        // ---- STATE & HELPERS ----
        let draggingFromPalette = false;
        let dragSourceEl        = null;
        let currentDropTarget   = null;
        let dropBefore          = false;
        let currentContainer    = null;
        let isCopyDrag          = false;
        let selectedBlock       = null;

        function setSelectedBlock(blockEl) {
            // oude selectie resetten
            if (selectedBlock && selectedBlock !== blockEl) {
                selectedBlock.classList.remove(
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
                    'ring-1',
                    'ring-[#0F9B9F]',
                    'ring-offset-0',
                    'ring-offset-transparent',
                    'rounded'
                );
            }
        }

        function updatePlaceholder() {
            // Alleen echte blokken tellen, niet de dropIndicator
            const hasBlocks = !!blocksWrap.querySelector('.builder-canvas-item');

            if (placeholder) {
                placeholder.style.display = hasBlocks ? 'none' : 'flex';
            }

            if (canvas) {
                if (hasBlocks) {
                    // Als er blokken zijn: geen dashed rand, gewoon wit vlak
                    canvas.classList.remove('border-2', 'border-dashed', 'border-[#21555820]', 'bg-[#21555810]', 'p-4');
                    canvas.classList.add('bg-white');
                } else {
                    // Geen blokken: dashed rand + groene achtergrond
                    canvas.classList.add('border-2', 'border-dashed', 'border-[#21555820]', 'bg-[#21555810]', 'p-4');
                    canvas.classList.remove('bg-white');
                }
            }
        }

        function attachBlockDragEvents(blockEl) {
            // ========== DRAG ==========
            blockEl.addEventListener('dragstart', function (e) {
                // drag vanaf canvas
                draggingFromPalette = false;
                dragSourceEl = blockEl;

                // CTRL (Windows) / CMD (Mac) = kopieer-drag
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
            // klik ergens op het blok = selecteren
            blockEl.addEventListener('click', function (e) {
                setSelectedBlock(blockEl);
            });

            // ========== HOVER LABELS ==========
            const blockLabel = blockEl.querySelector('.builder-block-label');

            // Label moet óók selecteren en niet bubbelen
            if (blockLabel) {
                blockLabel.addEventListener('click', function (e) {
                    e.stopPropagation(); // voorkom rare bubbling
                    setSelectedBlock(blockEl);
                });
            }

            // Alleen het blok waar je overheen gaat, krijgt een label
            blockEl.addEventListener('mouseenter', function () {
                if (blockLabel) {
                    showLabel(blockLabel); // verbergt eerst alle andere labels
                }
            });

            // Als je het blok verlaat:
            blockEl.addEventListener('mouseleave', function () {
                // Check of we in een kolom zitten
                const col = blockEl.closest('.builder-column-blocks');
                if (col) {
                    const colLabel = col.querySelector('.column-label');
                    if (colLabel) {
                        showLabel(colLabel); // weer terug naar kolom-label
                        return;
                    }
                }
                // Anders: helemaal niets tonen
                hideAllLabels();
            });
        }

        function attachColumnDropzones(rootEl) {
            const columnBlocks = rootEl.querySelectorAll('.builder-column-blocks');

            columnBlocks.forEach(function (col) {
                // HOVER: alleen kolom-label tonen
                const colLabel = col.querySelector('.column-label');
                if (colLabel) {
                    col.addEventListener('mouseenter', function () {
                        showLabel(colLabel); // verbergt eerst alle andere labels
                    });
                    // 'mouseleave' niet nodig: andere elementen overschrijven dit
                }

                // Highlight kolom tijdens drag
                col.addEventListener('dragenter', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    col.style.borderColor = '#0F9B9F';
                    col.style.borderWidth = '2px';
                    col.style.borderStyle = 'dashed';
                });

                col.addEventListener('dragleave', function (e) {
                    // Alleen resetten als we écht de kolom verlaten
                    if (e.relatedTarget && col.contains(e.relatedTarget)) return;
                    col.style.borderColor = '';
                    col.style.borderWidth = '';
                    col.style.borderStyle = '';
                    clearDropIndicator();
                });

                // DRAG LOGICA
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

                    // Drop-indicator in kolom laten zien
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

                    // Kolom highlight + indicator resetten
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
            // wrapper zelf = blok dat je versleept
            wrapper.className = 'builder-canvas-item w-full relative group';
            wrapper.setAttribute('draggable', 'true');
            wrapper.dataset.blockType = type;

            let content = '';

            switch (type) {
                case 'logo':
                    content = `
                        <div class="w-full flex justify-center">
                            <div class="h-12 w-32 bg-[#21555820] rounded flex items-center justify-center text-[10px] font-semibold text-[#21555880] uppercase tracking-[0.15em]">
                                LOGO
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
                        <div class="w-full">
                            <div class="w-full aspect-[16/9] bg-[#21555810] border border-dashed border-[#21555840] rounded flex items-center justify-center text-[11px] text-[#21555880]">
                                Afbeelding placeholder
                            </div>
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
                    // Dit is de enige die écht witruimte mag maken
                    content = `
                        <div class="w-full h-6"></div>
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
                                            border border-[#21555820] rounded">
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
                            <div class="flex gap-4">
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

            // Voor layout-blokken (2/3/4 kolommen) GEEN buitenste label tonen
            if (type === 'two-columns' || type === 'three-columns' || type === 'four-columns') {
                wrapper.innerHTML = `
                    <div class="w-full rounded border border-transparent bg-white
                                group-hover:border-dashed group-hover:border-[#21555866]">
                        ${content}
                    </div>
                `;
            } else {
                // Normale blokken: eigen label dat we via JS tonen (niet meer met group-hover)
                wrapper.innerHTML = `
                    <span class="builder-block-label pointer-events-none absolute -top-5 left-0 px-2.5 py-0.5 rounded-full bg-[#0F9B9F]
                                text-[11px] font-semibold text-[#fff] opacity-0 transition-opacity duration-150
                                leading-none">
                        ${label}
                    </span>
                    <div class="w-full rounded border border-transparent bg-white
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

        // ---- CANVAS DRAGOVER / DROP (hoofdniveau, buiten kolommen) ----
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

            // Drop-indicator tussen hoofdniveau blokken tonen
            showDropIndicator(currentContainer, currentDropTarget, dropBefore);
        });

        canvas.addEventListener('drop', function (e) {
            e.preventDefault();

            const type = e.dataTransfer.getData('text/plain') || null;
            const container = currentContainer || blocksWrap;

            if ((draggingFromPalette || isCopyDrag) && type) {
                // Vanuit palette OF ctrl+drag vanaf canvas => NIEUW blok
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
                // Normale move binnen canvas / naar canvas
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

        // (Re)bind globale keyboard handler voor selected block delete
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

            // Laat backspace/delete gewoon werken als je in een invoerveld of contenteditable zit
            if (isEditableTarget(e.target)) return;

            if (!selectedBlock || !selectedBlock.parentElement) return;

            e.preventDefault();

            // Blok verwijderen
            const parent    = selectedBlock.parentElement;
            const colParent = parent.closest('.builder-column-blocks');

            parent.removeChild(selectedBlock);
            selectedBlock = null;

            if (colParent) {
                updateColumnBorder(colParent);
            }

            updatePlaceholder();
        };

        document.addEventListener('keydown', emailBuilderKeyHandler);

        // === Klik buiten geselecteerd blok = selectie weg ===
        emailBuilderClickHandler = function (e) {
            if (!selectedBlock) return;

            // Als je in het geselecteerde blok klikt: niets doen
            if (selectedBlock.contains(e.target)) return;

            // Overal anders: selectie resetten
            setSelectedBlock(null);
        };

        document.addEventListener('click', emailBuilderClickHandler);

        // Init borders voor alle bestaande kolommen (server-side + nieuwe)
        blocksWrap.querySelectorAll('.builder-column-blocks').forEach(updateColumnBorder);

        updatePlaceholder();
    }
    // ===== EINDE MAILBUILDER INIT =====

    function renderTemplateDetail(html) {
        if (!detail) return;
        detail.innerHTML = html;
        // Na elke soft reload opnieuw de builder aanzetten
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
                form.submit(); // fallback
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
            window.location.href = link.href; // fallback
        });
    });

    // Initial load: als er al een actieve template is, direct builder activeren
    initEmailBuilder();
});
</script>
@endsection