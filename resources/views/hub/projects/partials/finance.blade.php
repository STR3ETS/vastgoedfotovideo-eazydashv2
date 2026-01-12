@php
  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $fmtCents = fn($cents) => '€' . number_format(((int)$cents) / 100, 2, ',', '.');
  $financeTotal = (int) ($project->financeItems?->sum('total_cents') ?? 0);

  $errorsBag = $financeErrors ?? null;

  // ✅ kolommen incl. checkbox + actions
  $cols = "grid-cols-[40px_minmax(0,1fr)_minmax(0,0.25fr)_minmax(0,0.35fr)_minmax(0,0.35fr)_minmax(0,0.22fr)]";

  // ✅ 1x sorteren (ook voor modal)
  $financeItems = ($project->financeItems ?? collect())
    ->sortBy(fn($i) => [$i->created_at?->timestamp ?? 0, $i->id])
    ->values();

  // ✅ seed voor offerte modal (prefill)
  $financeSeed = $financeItems->map(fn($i) => [
    'id' => (int) $i->id,
    'description' => (string) ($i->description ?? ''),
    'quantity' => (int) ($i->quantity ?? 1),
    'unit_price_cents' => (int) ($i->unit_price_cents ?? 0),
  ])->values();
@endphp

<div
  id="project-finance"
  class="col-span-2"
  x-data='{
    projectId: @json($project->id),
    storageKey: null,

    // ✅ selectie
    selected: [],
    selectedQuotes: [],
    quotesStorageKey: null,

    financeSeed: @json($financeSeed),

    // ✅ add-rij
    addOpen:false,
    newDescription:"",
    newQty: 1,
    newPrice:"",

    // ✅ offerte modal
    quoteOpen: false,
    quoteDate: "",
    quoteExpireDate: "",
    quoteStatus: "draft",   // draft | sent | accepted | rejected
    quoteNotes: "",
    vatRate: 21,
    quoteItems: [],

    init(){
      this.storageKey = "project_finance_selected_" + this.projectId;

      this.quotesStorageKey = "project_quotes_selected_" + this.projectId;

      // restore quotes selectie na HTMX swap / page reload
      this.restoreQuotesSelection();

      // drop quote ids die niet meer bestaan in de huidige DOM
      this.$nextTick(() => this.normalizeQuotesSelection());

      // persist quotes selectie bij elke wijziging
      this.$watch("selectedQuotes", (v) => {
        try { sessionStorage.setItem(this.quotesStorageKey, JSON.stringify((v || []).map(String))); } catch(e) {}
      });

      // restore selectie na HTMX swap / page reload
      this.restoreSelection();

      // drop ids die niet meer bestaan in de huidige DOM
      this.$nextTick(() => this.normalizeSelection());

      // persist bij elke wijziging
      this.$watch("selected", (v) => {
        try { sessionStorage.setItem(this.storageKey, JSON.stringify((v || []).map(String))); } catch(e) {}
      });
    },

    restoreSelection(){
      try{
        const raw = sessionStorage.getItem(this.storageKey);
        if(!raw) return;
        const arr = JSON.parse(raw);
        if(Array.isArray(arr)){
          this.selected = arr.map(String);
        }
      } catch(e) {}
    },

    normalizeSelection(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-finance-checkbox]"))
        .map(cb => String(cb.value));

      this.selected = (this.selected || []).map(String).filter(id => ids.includes(id));
    },

    isSelected(id){
      return this.selected.includes(String(id));
    },

    toggleAll(ev){
      const on = ev.target.checked;
      const ids = Array.from(this.$root.querySelectorAll("input[data-finance-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));

      this.selected = on ? ids : [];
    },

    isAllSelected(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-finance-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));

      return ids.length > 0 && ids.every(id => this.selected.includes(id));
    },

    clearSelection(){
      this.selected = [];
      const master = this.$root.querySelector("input[data-master-checkbox]");
      if(master) master.checked = false;
    },

    restoreQuotesSelection(){
      try{
        const raw = sessionStorage.getItem(this.quotesStorageKey);
        if(!raw) return;
        const arr = JSON.parse(raw);
        if(Array.isArray(arr)){
          this.selectedQuotes = arr.map(String);
        }
      } catch(e) {}
    },

    normalizeQuotesSelection(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-quote-checkbox]"))
        .map(cb => String(cb.value));

      this.selectedQuotes = (this.selectedQuotes || []).map(String).filter(id => ids.includes(id));
    },

    toggleAllQuotes(ev){
      const on = ev.target.checked;
      const ids = Array.from(this.$root.querySelectorAll("input[data-quote-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));

      this.selectedQuotes = on ? ids : [];
    },

    isAllQuotesSelected(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-quote-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));

      return ids.length > 0 && ids.every(id => (this.selectedQuotes || []).includes(id));
    },

    clearQuotesSelection(){
      this.selectedQuotes = [];
      const master = this.$root.querySelector("input[data-master-quote-checkbox]");
      if(master) master.checked = false;
    },

    // ✅ add-rij helpers
    openAdd(){
      this.addOpen = true;
      this.$nextTick(() => this.$refs.descInput?.focus());
    },

    closeAdd(){
      this.addOpen = false;
      this.newDescription = "";
      this.newQty = 1;
      this.newPrice = "";
    },

    // =========================
    // ✅ Offerte modal helpers
    // =========================
    pad(n){ return String(n).padStart(2,"0"); },

    dateKey(d){
      return d.getFullYear()+"-"+this.pad(d.getMonth()+1)+"-"+this.pad(d.getDate());
    },

    formatCents(c){
      const v = Number(c) || 0;
      try{
        return "€ " + new Intl.NumberFormat("nl-NL", { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v / 100);
      } catch(e){
        return "€ " + ((v/100).toFixed(2)).replace(".", ",");
      }
    },

    centsToEurInput(c){
      const v = Number(c) || 0;
      // voor inputveld: 12,50
      return ((v/100).toFixed(2)).replace(".", ",");
    },

    eurToCents(value){
      let v = String(value ?? "").trim();
      v = v.replace("€", "").replace(/\s/g, "");
      // duizendtallen eruit, komma -> punt
      v = v.replace(/\./g, "").replace(",", ".");
      const f = parseFloat(v);
      if(!isFinite(f)) return 0;
      return Math.round(f * 100);
    },

    lineTotalCents(it){
      const qty = Math.max(1, Number(it.qty) || 1);
      const unit = this.eurToCents(it.unit_price_eur);
      return qty * Math.max(0, unit);
    },

    subTotalCents(){
      return (this.quoteItems || []).reduce((s, it) => s + this.lineTotalCents(it), 0);
    },

    vatCents(){
      const rate = Math.max(0, Number(this.vatRate) || 0);
      return Math.round(this.subTotalCents() * rate / 100);
    },

    grandTotalCents(){
      return this.subTotalCents() + this.vatCents();
    },

    seedQuote(){
      // defaults
      const now = new Date();
      const exp = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 30);

      this.quoteDate = this.dateKey(now);
      this.quoteExpireDate = this.dateKey(exp);
      this.quoteStatus = "draft";
      this.quoteNotes = "";
      this.vatRate = 21;

      // items vanuit finance
      this.quoteItems = (this.financeSeed || []).map(i => ({
        key: String(i.id),
        description: i.description || "",
        qty: Math.max(1, Number(i.quantity) || 1),
        unit_price_eur: this.centsToEurInput(i.unit_price_cents || 0)
      }));
    },

    openQuote(){
      this.seedQuote();
      this.quoteOpen = true;
      this.$nextTick(() => { document.documentElement.classList.add("overflow-hidden"); });
    },

    closeQuote(){
      this.quoteOpen = false;
      this.$nextTick(() => { document.documentElement.classList.remove("overflow-hidden"); });
    },

    removeQuoteItem(idx){
      this.quoteItems.splice(idx, 1);
    },

    addQuoteItem(){
      this.quoteItems.push({
        key: "new-" + Date.now(),
        description: "",
        qty: 1,
        unit_price_eur: "0,00"
      });
    }
  }'
  x-init="init()"
>
  <div class="{{ $sectionWrap }} overflow-visible">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Financieel</p>

        <p class="ml-4 text-[#191D38] font-bold text-xs opacity-50">
          Totaal: <span class="text-[#009AC3] font-black">{{ $fmtCents($financeTotal) }}</span>
        </p>
      </div>

      <div class="flex items-center gap-2">
        <div
          x-cloak
          x-show="selected.length > 0"
          class="flex items-center gap-2 mr-4"
        >
          <span class="text-[#191D38] font-bold text-xs opacity-50">
            Geselecteerd: <span x-text="selected.length"></span>
          </span>
        </div>

        <button
          type="button"
          x-on:click="openAdd()"
          class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
        >
          Nieuwe regel aanmaken
        </button>

        <button
          type="button"
          x-on:click="openQuote()"
          class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#191D38] text-white text-xs font-semibold hover:bg-[#191D38]/80 transition duration-200"
        >
          Offerte maken
        </button>

        {{-- ✅ Bulk delete button --}}
        <form
          x-cloak
          x-show="selected.length > 0"
          method="POST"
          action="{{ route('support.projecten.finance.bulk_destroy', ['project' => $project]) }}"

          hx-post="{{ route('support.projecten.finance.bulk_destroy', ['project' => $project]) }}"
          hx-target="#project-finance"
          hx-swap="outerHTML"
          hx-confirm="Weet je zeker dat je de geselecteerde financiële regels wilt verwijderen?"
        >
          @csrf
          @method('DELETE')

          <template x-for="id in selected" :key="'bulk-fin-del-'+id">
            <input type="hidden" name="finance_item_ids[]" :value="id">
          </template>

          <button
            type="submit"
            class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#DF2935] text-white text-xs font-semibold hover:bg-[#DF2935]/80 transition duration-200"
          >
            Verwijder geselecteerde
          </button>
        </form>
      </div>
    </div>

    {{-- Errors (optioneel) --}}
    @if($errorsBag && method_exists($errorsBag, 'any') && $errorsBag->any())
      <div class="px-6 pt-4">
        <div class="rounded-2xl bg-[#DF2935]/10 text-[#DF2935] text-xs font-semibold px-4 py-3">
          {{ $errorsBag->first() }}
        </div>
      </div>
    @endif

    {{-- Header row --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
      <div class="grid {{ $cols }} items-center gap-6">
        <div class="flex items-center">
          <input
            type="checkbox"
            data-master-checkbox
            class="h-4 w-4 rounded border-[#191D38]/20"
            x-on:change="toggleAll($event)"
            :checked="isAllSelected()"
          >
        </div>

        <p class="text-[#191D38] font-bold text-xs opacity-50">Omschrijving</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-start">Aantal</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Prijs per eenheid</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Totaalprijs</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
      </div>
    </div>

    {{-- Body --}}
    <div class="{{ $sectionBody }} rounded-b-2xl">
      <div class="px-6 py-2 divide-y divide-[#191D38]/10">
        @php
          $financeItems = ($project->financeItems ?? collect())
            ->sortBy(fn($i) => [$i->created_at?->timestamp ?? 0, $i->id])
            ->values();
        @endphp

        @forelse($financeItems as $item)
          @php
            $priceInit = number_format(((int) ($item->unit_price_cents ?? 0)) / 100, 2, ',', '');
          @endphp

          <div
            class="py-3 grid {{ $cols }} items-center gap-6 transition-opacity duration-200"
            x-data='{
              edit:false,
              desc: @json($item->description),
              qty:  @json((int)($item->quantity ?? 1)),
              price:@json($priceInit),

              oDesc:@json($item->description),
              oQty: @json((int)($item->quantity ?? 1)),
              oPrice:@json($priceInit),

              openEdit(){
                this.edit = true;
                this.$nextTick(() => this.$refs.descInput?.focus());
              },
              cancelEdit(){
                this.desc = this.oDesc;
                this.qty = this.oQty;
                this.price = this.oPrice;
                this.edit = false;
              }
            }'
            x-bind:class="(selected.length > 0 && !selected.includes(String({{ $item->id }}))) ? 'opacity-50' : 'opacity-100'"
          >
            {{-- ✅ Checkbox --}}
            <div class="flex items-center">
              <input
                type="checkbox"
                value="{{ $item->id }}"
                data-finance-checkbox
                class="h-4 w-4 rounded border-[#191D38]/20"
                x-model="selected"
              >
            </div>

            {{-- Omschrijving --}}
            <div class="min-w-0">
              <p x-show="!edit" class="text-[#191D38] font-semibold text-sm truncate">
                {{ $item->description }}
              </p>

              <input
                x-cloak
                x-show="edit"
                x-ref="descInput"
                type="text"
                x-model="desc"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                x-on:keydown.enter.prevent="$refs.editForm.requestSubmit()"
                x-on:keydown.escape.prevent="cancelEdit()"
              >
            </div>

            {{-- Aantal --}}
            <div class="text-[#191D38] text-sm">
              <span x-show="!edit">{{ (int) ($item->quantity ?? 1) }}</span>

              <input
                x-cloak
                x-show="edit"
                type="number"
                min="1"
                x-model.number="qty"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                x-on:keydown.enter.prevent="$refs.editForm.requestSubmit()"
                x-on:keydown.escape.prevent="cancelEdit()"
              >
            </div>

            {{-- Prijs --}}
            <div class="text-[#191D38] text-sm">
              <span x-show="!edit">{{ $fmtCents((int) ($item->unit_price_cents ?? 0)) }}</span>

              <input
                x-cloak
                x-show="edit"
                type="text"
                x-model="price"
                placeholder="Bijv. 49,95"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                x-on:keydown.enter.prevent="$refs.editForm.requestSubmit()"
                x-on:keydown.escape.prevent="cancelEdit()"
              >
            </div>

            {{-- Totaalprijs --}}
            <div class="text-[#009AC3] text-sm">
              <span x-show="!edit">{{ $fmtCents((int) ($item->total_cents ?? 0)) }}</span>
              <span x-cloak x-show="edit">—</span>
            </div>

            {{-- Acties --}}
            <div class="flex items-center justify-end gap-2">
              <button
                type="button"
                x-show="!edit"
                x-on:click="openEdit()"
                class="cursor-pointer"
                title="Bewerken"
              >
                <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
              </button>

              <form
                x-show="!edit"
                method="POST"
                action="{{ route('support.projecten.finance.destroy', ['project' => $project, 'financeItem' => $item]) }}"

                hx-post="{{ route('support.projecten.finance.destroy', ['project' => $project, 'financeItem' => $item]) }}"
                hx-target="#project-finance"
                hx-swap="outerHTML"
                hx-confirm="Weet je zeker dat je deze financiële regel wilt verwijderen?"
              >
                @csrf
                @method('DELETE')

                <button class="cursor-pointer" type="submit" title="Verwijder regel">
                  <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                </button>
              </form>

              <form
                x-cloak
                x-show="edit"
                x-ref="editForm"
                method="POST"
                action="{{ route('support.projecten.finance.update', ['project' => $project, 'financeItem' => $item]) }}"

                hx-patch="{{ route('support.projecten.finance.update', ['project' => $project, 'financeItem' => $item]) }}"
                hx-target="#project-finance"
                hx-swap="outerHTML"
              >
                @csrf
                @method('PATCH')

                <input type="hidden" name="description" :value="desc">
                <input type="hidden" name="quantity" :value="qty">
                <input type="hidden" name="unit_price_eur" :value="price">
              </form>

              <button
                x-cloak
                x-show="edit"
                type="button"
                x-on:click="$refs.editForm.requestSubmit()"
                class="cursor-pointer"
                title="Opslaan (Enter)"
              >
                <i class="fa-solid fa-check hover:text-[#009AC3] transition duration-200"></i>
              </button>

              <button
                x-cloak
                x-show="edit"
                type="button"
                x-on:click="cancelEdit()"
                class="cursor-pointer"
                title="Annuleren (Esc)"
              >
                <i class="fa-solid fa-xmark hover:text-[#009AC3] transition duration-200"></i>
              </button>
            </div>
          </div>
        @empty
          <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
            Nog geen financiële regels.
          </div>
        @endforelse

        {{-- ✅ Nieuwe regel rij (onderaan) --}}
        <form
          x-cloak
          x-show="addOpen"
          class="py-3 grid {{ $cols }} items-center gap-6"
          method="POST"
          action="{{ route('support.projecten.finance.store', $project) }}"

          hx-post="{{ route('support.projecten.finance.store', $project) }}"
          hx-target="#project-finance"
          hx-swap="outerHTML"
        >
          @csrf

          {{-- checkbox kolom leeg --}}
          <div></div>

          <div class="min-w-0">
            <input
              x-ref="descInput"
              type="text"
              name="description"
              x-model="newDescription"
              placeholder="Omschrijving..."
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              x-on:keydown.escape.prevent="closeAdd()"
              required
            >
          </div>

          <div>
            <input
              type="number"
              name="quantity"
              min="1"
              x-model.number="newQty"
              placeholder="Aantal..."
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              required
            >
          </div>

          <div>
            <input
              type="text"
              name="unit_price_eur"
              x-model="newPrice"
              placeholder="Prijs..."
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              required
            >
          </div>

          <div class="text-[#009AC3] text-sm text-right">
            —
          </div>

          <div class="flex items-center justify-end gap-2">
            <button
              type="button"
              x-on:click="closeAdd()"
              class="h-9 px-4 inline-flex items-center rounded-full bg-[#2A324B]/20 text-[#2A324B]/60 text-xs font-semibold hover:bg-[#2A324B]/10 transition"
            >
              Annuleren
            </button>

            <button
              type="submit"
              class="h-9 px-4 inline-flex items-center rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition"
              x-bind:disabled="!newDescription || !newDescription.trim() || !newPrice || !newPrice.trim() || (Number(newQty) || 0) < 1"
            >
              Opslaan
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <hr class="border-[#191D38]/10 col-span-2 my-8">

  @php
    $quoteStatusMap = [
      'draft'     => ['label' => 'Concept',             'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
      'sent'      => ['label' => 'Verstuurd naar klant','class' => 'text-[#009AC3] bg-[#009AC3]/20'],
      'accepted'  => ['label' => 'Getekend',            'class' => 'text-[#87A878] bg-[#87A878]/20'],
      'cancelled' => ['label' => 'Geannuleerd',         'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
    ];

    $quotes = ($project->quotes ?? collect())->values();
    $qCols  = "grid-cols-[40px_160px_140px_1fr_140px_140px_140px_90px]";
  @endphp

  <div>
    <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Offertes</p>
      </div>

      <div class="flex items-center gap-3">
        <div x-cloak x-show="selectedQuotes.length > 0" class="flex items-center gap-2 mr-4">
          <span class="text-[#191D38] font-bold text-xs opacity-50">
            Geselecteerd: <span x-text="selectedQuotes.length"></span>
          </span>
        </div>

        <form
          x-cloak
          x-show="selectedQuotes.length > 0"
          method="POST"
          action="{{ route('support.projecten.finance.offertes.bulk_destroy', ['project' => $project]) }}"

          hx-delete="{{ route('support.projecten.finance.offertes.bulk_destroy', ['project' => $project]) }}"
          hx-target="#project-finance"
          hx-swap="outerHTML"
          hx-confirm="Weet je zeker dat je de geselecteerde offertes wilt verwijderen?"
        >
          @csrf
          @method('DELETE')

          <template x-for="id in selectedQuotes" :key="'bulk-quote-del-'+id">
            <input type="hidden" name="quote_ids[]" :value="id">
          </template>

          <button
            type="submit"
            class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#DF2935] text-white text-xs font-semibold hover:bg-[#DF2935]/80 transition duration-200"
          >
            Verwijder geselecteerde
          </button>
        </form>
      </div>
    </div>

    {{-- Header row blijft altijd zichtbaar --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
      <div class="grid {{ $qCols }} gap-4 items-center">
        <div class="flex items-center">
          <input
            type="checkbox"
            data-master-quote-checkbox
            class="h-4 w-4 rounded border-[#191D38]/20"
            x-on:change="toggleAllQuotes($event)"
            :checked="isAllQuotesSelected()"
            {{ $quotes->count() ? '' : 'disabled' }}
          >
        </div>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Nummer</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Datum</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Ex. BTW</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">BTW</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Incl. BTW</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
      </div>
    </div>

    <div class="bg-[#191D38]/5 rounded-b-2xl divide-y divide-[#191D38]/10 px-6 py-2">
      @forelse($quotes as $q)
        @php
          $key  = strtolower((string)($q->status ?? 'draft'));
          $pill = $quoteStatusMap[$key] ?? ['label' => ucfirst($key), 'class' => 'text-[#191D38] bg-[#191D38]/10'];

          $sub = (int) ($q->sub_total_cents ?? 0);
          $vat = (int) ($q->vat_cents ?? 0);
          $tot = (int) ($q->total_cents ?? ($sub + $vat));
        @endphp

        <div
          class="grid {{ $qCols }} gap-4 items-center py-3 transition-opacity duration-200"
          x-bind:class="(selectedQuotes.length > 0 && !selectedQuotes.includes(String({{ $q->id }}))) ? 'opacity-50' : 'opacity-100'"
        >
          <div class="flex items-center">
            <input
              type="checkbox"
              value="{{ $q->id }}"
              data-quote-checkbox
              class="h-4 w-4 rounded border-[#191D38]/20"
              x-model="selectedQuotes"
            >
          </div>

          <div class="text-[#191D38] font-semibold text-sm">
            {{ $q->quote_number ?? '—' }}
          </div>

          <div class="text-[#191D38] text-sm">
            {{ \Carbon\Carbon::parse($q->quote_date)->format('d-m-Y') }}
          </div>

          <div
            class="relative"
            x-data="{ open:false }"
            x-on:click.outside="open=false"
            x-on:keydown.escape.window="open=false"
          >
            <button
              type="button"
              x-on:click="open = !open"
              class="cursor-pointer w-full text-xs font-semibold rounded-full py-1.5 inline-flex items-center justify-center gap-2 px-4 text-left {{ $pill['class'] }}"
            >
              <span>{{ $pill['label'] }}</span>
            </button>

            <div
              x-cloak
              x-show="open"
              x-transition.origin.top
              class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-64"
            >
              <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-2 shadow-lg">
                <form
                  method="POST"
                  action="{{ route('support.projecten.finance.offertes.status', ['project' => $project, 'quote' => $q]) }}"
                  class="max-h-64 overflow-y-auto custom-scroll flex flex-col gap-2"

                  hx-patch="{{ route('support.projecten.finance.offertes.status', ['project' => $project, 'quote' => $q]) }}"
                  hx-target="#project-finance"
                  hx-swap="outerHTML"
                >
                  @csrf
                  @method('PATCH')

                  <template x-if="selectedQuotes.length > 0 && selectedQuotes.includes(String({{ $q->id }}))">
                    <div>
                      <template x-for="id in selectedQuotes" :key="'bulk-quote-status-'+id">
                        <input type="hidden" name="quote_ids[]" :value="id">
                      </template>
                    </div>
                  </template>

                  <button type="submit" name="status" value="draft" class="text-[#DF9A57] cursor-pointer bg-[#DF9A57]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                    Concept
                  </button>
                  <button type="submit" name="status" value="sent" class="text-[#009AC3] cursor-pointer bg-[#009AC3]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                    Verstuurd naar klant
                  </button>
                  <button type="submit" name="status" value="accepted" class="text-[#87A878] cursor-pointer bg-[#87A878]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                    Getekend
                  </button>
                  <button type="submit" name="status" value="cancelled" class="text-[#DF2935] cursor-pointer bg-[#DF2935]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                    Geannuleerd
                  </button>
                </form>
              </div>
            </div>
          </div>

          <div class="text-[#191D38] text-sm">{{ $fmtCents($sub) }}</div>
          <div class="text-[#191D38] text-sm">{{ $fmtCents($vat) }}</div>
          <div class="text-[#009AC3] text-sm">{{ $fmtCents($tot) }}</div>

          <div class="flex justify-end items-center gap-2">
            <a
              href="{{ route('support.projecten.finance.offertes.pdf', ['project' => $project, 'quote' => $q]) }}"
              class="inline-flex items-center justify-center"
              title="Download PDF"
            >
              <i class="fa-solid fa-download hover:text-[#009AC3] transition duration-200"></i>
            </a>

            <form
              method="POST"
              action="{{ route('support.projecten.finance.offertes.destroy', ['project' => $project, 'quote' => $q]) }}"

              hx-delete="{{ route('support.projecten.finance.offertes.destroy', ['project' => $project, 'quote' => $q]) }}"
              hx-target="#project-finance"
              hx-swap="outerHTML"
              hx-confirm="Weet je zeker dat je deze offerte wilt verwijderen?"
            >
              @csrf
              @method('DELETE')

              <button type="submit" class="inline-flex items-center justify-center" title="Verwijder offerte">
                <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
          Nog geen offertes.
        </div>
      @endforelse
    </div>
  </div>

  {{-- ✅ Offerte modal (styling aligned with page) --}}
  <div
    x-cloak
    x-show="quoteOpen"
    x-on:keydown.escape.window="closeQuote()"
    class="fixed inset-0 z-[999] flex items-center justify-center p-6"
  >
    {{-- backdrop --}}
    <div class="absolute inset-0 bg-black/40" x-on:click="closeQuote()"></div>

    {{-- modal --}}
    <div class="relative w-[min(1100px,calc(100%-2rem))] max-h-[85vh] bg-white rounded-2xl shadow-xl ring-1 ring-[#191D38]/10 overflow-hidden">

      {{-- header (zelfde vibe als section header) --}}
      <div class="px-6 py-4 bg-[#191D38]/5 border-b border-[#191D38]/10 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <i class="fa-regular fa-file-lines text-[#009AC3]"></i>
          <div>
            <p class="text-[#191D38] font-black text-sm leading-tight">Offerte maken</p>
            <p class="text-[#191D38]/50 text-xs font-semibold">
              Project: {{ $project->title ?? '—' }}
            </p>
          </div>
        </div>

        <button
          type="button"
          x-on:click="closeQuote()"
          class="h-9 w-9 inline-flex items-center justify-center rounded-full bg-white ring-1 ring-[#191D38]/10 hover:bg-[#191D38]/5 transition"
          aria-label="Sluiten"
        >
          <i class="fa-solid fa-xmark text-[#2A324B]/70"></i>
        </button>
      </div>

      {{-- body (scroll) --}}
      <div class="p-6 overflow-y-auto custom-scroll max-h-[calc(85vh-64px)]">

        {{-- top fields --}}
        <div class="grid grid-cols-2 gap-6">
          <div>
            <div>
              <label class="text-[#191D38] font-bold text-xs opacity-50">Offertedatum *</label>
              <input
                type="date"
                x-model="quoteDate"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              >
            </div>
          </div>

          <div>
            <div>
              <label class="text-[#191D38] font-bold text-xs opacity-50">Vervaldatum *</label>
              <input
                type="date"
                x-model="quoteExpireDate"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              >
            </div>
          </div>
        </div>

        <div class="my-6 border-t border-[#191D38]/10"></div>

        {{-- header row (zelfde als tabel header op page) --}}
        <div class="px-6 py-4 bg-[#191D38]/10 rounded-tr-xl rounded-tl-xl">
          <div class="grid grid-cols-[minmax(260px,1fr)_120px_170px_170px_44px] gap-4 items-center">
            <p class="text-[#191D38] font-bold text-xs opacity-50">Omschrijving</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Aantal</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Prijs per eenheid</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Totaalprijs</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
          </div>
        </div>

        {{-- table body (zelfde tint + divide) --}}
        <div class="bg-[#191D38]/5 rounded-bl-xl rounded-br-xl px-6 py-2 divide-y divide-[#191D38]/10 px-4">
          <template x-for="(it, idx) in quoteItems" :key="it.key">
            <div class="grid grid-cols-[minmax(260px,1fr)_120px_170px_170px_44px] gap-4 py-3 items-center">
              <input
                type="text"
                x-model="it.description"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              >

              <input
                type="number"
                min="1"
                x-model.number="it.qty"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              >

              <input
                type="text"
                x-model="it.unit_price_eur"
                placeholder="Bijv. 49,95"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              >

              <span
                class="w-full inline-flex items-center justify-start text-xs text-[#009AC3]"
                x-text="formatCents(lineTotalCents(it))"
              ></span>

              <button
                type="button"
                x-on:click="removeQuoteItem(idx)"
                class="flex items-center justify-end cursor-pointer"
              >
                <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
              </button>
            </div>
          </template>

          <div x-show="quoteItems.length === 0" class="py-8 text-center text-sm font-semibold text-[#191D38]/50">
            Nog geen offertregels.
          </div>
        </div>

        {{-- totals + buttons --}}
        <div class="mt-6 pt-6">
          <div class="grid grid-cols-2 gap-6 items-start">
            <div></div>

            <div class="space-y-1">
              <div class="flex items-center justify-between text-xs">
                <span class="text-[#191D38] font-bold opacity-50">Subtotaal:</span>
                <span class="text-[#191D38] font-semibold" x-text="formatCents(subTotalCents())"></span>
              </div>

              <div class="flex items-center justify-between text-xs pb-3">
                <span class="text-[#191D38] font-bold opacity-50">BTW:</span>
                <div class="flex items-center gap-3">
                  <input
                    type="number"
                    min="0"
                    max="100"
                    x-model.number="vatRate"
                    class="h-9 w-30 rounded-full px-3 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                  >
                  <span class="text-[#191D38] font-bold opacity-50">%</span>
                  <span class="text-[#191D38] font-semibold w-28 text-right" x-text="formatCents(vatCents())"></span>
                </div>
              </div>

              <div class="pt-3 border-t border-[#191D38]/10 flex items-center justify-between">
                <span class="text-[#009AC3] font-black text-sm">Totaal incl. BTW:</span>
                <span class="text-[#009AC3] font-black text-lg" x-text="formatCents(grandTotalCents())"></span>
              </div>

              <form
                method="POST"
                action="{{ route('support.projecten.finance.offertes.store', ['project' => $project]) }}"

                hx-post="{{ route('support.projecten.finance.offertes.store', ['project' => $project]) }}"
                hx-target="#project-finance"
                hx-swap="outerHTML"
              >
                @csrf
                <input type="hidden" name="quote_date" :value="quoteDate">
                <input type="hidden" name="expire_date" :value="quoteExpireDate">
                <input type="hidden" name="status" :value="quoteStatus">
                <input type="hidden" name="vat_rate" :value="vatRate">
                <input type="hidden" name="notes" :value="quoteNotes">
                <template x-for="(it, idx) in quoteItems" :key="'q-h-'+it.key">
                  <div>
                    <input type="hidden" :name="`items[${idx}][description]`" :value="it.description">
                    <input type="hidden" :name="`items[${idx}][quantity]`" :value="it.qty">
                    <input type="hidden" :name="`items[${idx}][unit_price_eur]`" :value="it.unit_price_eur">
                  </div>
                </template>
                <div class="pt-4 flex items-center justify-end gap-3">
                  <button
                    type="button"
                    x-on:click="closeQuote()"
                    class="h-9 px-4 inline-flex items-center rounded-full bg-[#2A324B]/20 text-[#2A324B]/60 text-xs font-semibold hover:bg-[#2A324B]/10 transition"
                  >
                    Annuleren
                  </button>
                  <button
                    type="submit"
                    class="h-9 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
                  >
                    Offerte maken
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>