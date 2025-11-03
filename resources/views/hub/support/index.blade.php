@extends('layouts.app')

@section('content')
  {{-- LINKER KOLOM: hoofdkeuze --}}
  <div class="col-span-1 p-4 h-full bg-white rounded-xl flex flex-col gap-4" id="support-menu">
    <h1 class="text-xl text-[#215558] font-black">Support</h1>

    <div class="flex flex-col gap-1">
      {{-- Actieve keuze: Inzien --}}
      <a id="tab-inzien"
         role="menuitem"
         class="cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 bg-gray-200 transition duration-300"
         aria-current="page">
        <i class="min-w-[16px] fa-solid fa-eye text-[#215558] fa-sm"></i>
        <p class="px-1 text-sm text-[#215558] font-semibold">Support-vraag inzien</p>
      </a>

      {{-- UITGESCHAKELD: Indienen (niet onderdeel van deze scope) --}}
      <a role="menuitem"
         aria-disabled="true"
         class="cursor-not-allowed opacity-50 w-full p-3 rounded-xl flex items-center gap-1 bg-gray-100 transition duration-300">
        <i class="min-w-[16px] fa-solid fa-question text-[#215558] fa-sm"></i>
        <p class="px-1 text-sm text-[#215558] font-semibold">Support-vraag indienen</p>
      </a>
    </div>
  </div>

  {{-- MIDDEN KOLOM: statuskeuze (verschijnt bij "Inzien") --}}
  <div id="status-panel"
       class="col-span-1 p-4 h-fit bg-white rounded-xl flex flex-col gap-1">
    <a id="tab-openstaand"
        role="menuitem"
        class="cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
        data-url="{{ route('support.tickets.openstaand') }}"
        hx-get="{{ route('support.tickets.openstaand') }}"
        hx-target="#tickets-list"
        hx-swap="innerHTML transition:true">
      <div class="relative w-[16px] h-[16px] flex items-center justify-center">
        <i class="w-[12px] h-[12px] bg-green-500 rounded-full animate-ping"></i>
        <i class="w-[16px] h-[16px] bg-green-500 rounded-full absolute z-1"></i>
      </div>
      <p class="px-1 text-sm text-[#215558] font-semibold">Openstaand</p>
    </a>

    <a id="tab-behandeling"
        role="menuitem"
        class="cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
        data-url="{{ route('support.tickets.in_behandeling') }}"
        hx-get="{{ route('support.tickets.in_behandeling') }}"
        hx-target="#tickets-list"
        hx-swap="innerHTML transition:true">
      <div class="relative w-[16px] h-[16px] flex items-center justify-center">
        <i class="w-[12px] h-[12px] bg-orange-500 rounded-full animate-ping"></i>
        <i class="w-[16px] h-[16px] bg-orange-500 rounded-full absolute z-1"></i>
      </div>
      <p class="px-1 text-sm text-[#215558] font-semibold">In behandeling</p>
    </a>

    <a id="tab-gesloten"
        role="menuitem"
        class="cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
        data-url="{{ route('support.tickets.gesloten') }}"
        hx-get="{{ route('support.tickets.gesloten') }}"
        hx-target="#tickets-list"
        hx-swap="innerHTML transition:true">
      <div class="relative w-[16px] h-[16px] flex items-center justify-center">
        <i class="w-[12px] h-[12px] bg-red-500 rounded-full animate-ping"></i>
        <i class="w-[16px] h-[16px] bg-red-500 rounded-full absolute z-1"></i>
      </div>
      <p class="px-1 text-sm text-[#215558] font-semibold">Gesloten</p>
    </a>
  </div>

  {{-- RECHTER KOLOM: ticketslijst (verschijnt nadat een status is gekozen) --}}
  <div id="tickets-panel" class="col-span-1 p-4 h-full bg-white rounded-xl flex flex-col gap-4 hidden">
    <div class="flex items-center justify-between">
      <h2 id="tickets-title" class="text-lg text-[#215558] font-black">Tickets</h2>
    </div>

    {{-- Skeleton / content target --}}
    <div id="tickets-list">
      <div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div>
    </div>
  </div>

  {{-- UI-script (verbatim om Blade/JS quotes te isoleren) --}}
    @verbatim
    <script>
    (function(){
    // Shortcuts
    const $  = (s, r=document)=>r.querySelector(s);
    const $$ = (s, r=document)=>Array.from(r.querySelectorAll(s));

    // Elements
    const tabInzien    = $('#tab-inzien');
    const statusPanel  = $('#status-panel');
    const ticketsPanel = $('#tickets-panel');
    const ticketsTitle = $('#tickets-title');
    const ticketsList  = $('#tickets-list');

    // Status tabs
    const tabOpen   = $('#tab-openstaand');
    const tabBehand = $('#tab-behandeling');
    const tabGeslot = $('#tab-gesloten');

    // ===== Active state helpers (zoals bij Gebruikers) =====
    function setActiveLeft(link) {
        $$('#support-menu a[role="menuitem"]').forEach(a=>{
        a.classList.remove('bg-gray-200'); a.classList.add('hover:bg-gray-200'); a.removeAttribute('aria-current');
        });
        link.classList.add('bg-gray-200'); link.classList.remove('hover:bg-gray-200'); link.setAttribute('aria-current','page');
    }
    function setActiveStatus(link) {
        $$('#status-panel a[role="menuitem"]').forEach(a=>{
        a.classList.remove('bg-gray-200'); a.classList.add('hover:bg-gray-200'); a.removeAttribute('aria-current');
        });
        link.classList.add('bg-gray-200'); link.classList.remove('hover:bg-gray-200'); link.setAttribute('aria-current','page');
    }

    // ===== Anim helpers (zelfde aanpak als je delete overlay) =====
    const enterStart = ['opacity-0','-translate-x-2'];
    const enterEnd   = ['opacity-100','translate-x-0'];
    const baseTrans  = ['transform-gpu','transition-all','duration-200','ease-out'];

    function animateIn(el){
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add(...baseTrans, ...enterStart);
        // volgende frame: anim naar eindstaat
        requestAnimationFrame(()=> {
        el.classList.remove(...enterStart);
        el.classList.add(...enterEnd);
        // na anim: opruimen van trans classes (optioneel)
        setTimeout(()=> {
            el.classList.remove(...baseTrans, ...enterEnd);
        }, 210);
        });
    }

    function showSkeleton(){
        if (!ticketsList) return;
        ticketsList.innerHTML = '<div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div>';
    }

    // Staggered list reveal (rijen moeten .ticket-item hebben)
    function animateListItems(root){
        const items = $$('.ticket-item', root) ;
        if (!items.length) return;
        items.forEach((el, i)=>{
        el.classList.add('opacity-0','translate-y-1','transform-gpu','transition-all','duration-200','ease-out');
        el.style.transitionDelay = (i * 25)+'ms'; // subtiele stagger
        requestAnimationFrame(()=> {
            el.classList.remove('opacity-0','translate-y-1');
            el.classList.add('opacity-100','translate-y-0');
            setTimeout(()=> {
            el.classList.remove('transform-gpu','transition-all','duration-200','ease-out','opacity-100','translate-y-0');
            el.style.transitionDelay = '';
            }, 250 + i*25);
        });
        });
    }

    // ===== UI toggles =====
    function showStatusPanel(){
        statusPanel?.classList.remove('hidden');
        ticketsPanel?.classList.add('hidden'); // alleen na klik status tonen
    }
    function showTicketsPanelAnimated(){
        // reset + skeleton + anim in
        showSkeleton();
        animateIn(ticketsPanel);
    }

    // ===== Titel sync =====
    function syncTitle(id){
        const map = {
        'tab-openstaand'  : 'Openstaande vragen',
        'tab-behandeling' : 'Vragen in behandeling',
        'tab-gesloten'    : 'Gesloten vragen'
        };
        ticketsTitle.textContent = map[id] || 'Tickets';
    }

    // ===== Click delegation =====
    document.addEventListener('click', (e)=>{
        // Linker keuze (alleen Inzien actief)
        const left = e.target.closest('#support-menu a[role="menuitem"]');
        if (left && left.id === 'tab-inzien'){
        e.preventDefault();
        setActiveLeft(left);
        showStatusPanel();
        return;
        }

        // Status klik
        const statusLink = e.target.closest('#status-panel a[role="menuitem"]');
        if (statusLink){
        e.preventDefault();
        setActiveStatus(statusLink);
        syncTitle(statusLink.id);
        showTicketsPanelAnimated();

        // Veilig HTMX-aanroepen zonder recursion
        const url = statusLink.getAttribute('data-url') || statusLink.getAttribute('hx-get');
        if (url){
            htmx.ajax('GET', url, {
            target: '#tickets-list',
            swap: 'innerHTML transition:true',
            headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }
            });
        }
        }
    });

    // ===== HTMX lifecycles voor smoothness =====
    document.body.addEventListener('htmx:beforeRequest', (evt)=>{
        const path = evt.detail?.path || '';
        const hits = /\/support\/tickets\/(openstaand|in-behandeling|gesloten)$/i.test(path);
        if (hits){
        // Zorg dat panel zichtbaar is en skeleton toont vóór de swap
        if (ticketsPanel?.classList.contains('hidden')) animateIn(ticketsPanel);
        showSkeleton();
        }
    });

    document.body.addEventListener('htmx:afterSwap', (evt)=>{
        if (evt.target && evt.target.id === 'tickets-list'){
        // lijst is ge-swapt -> animatie van items
        animateListItems(evt.target);
        }
    });

    // ===== Init =====
    setActiveLeft(tabInzien);
    showStatusPanel();
    })();
    </script>
    @endverbatim
@endsection
