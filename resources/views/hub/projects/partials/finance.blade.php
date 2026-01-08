@php
  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $fmtCents = fn($cents) => '€' . number_format(((int)$cents) / 100, 2, ',', '.');
  $financeTotal = (int) ($project->financeItems?->sum('total_cents') ?? 0);

  $errorsBag = $financeErrors ?? null;

  // kolommen incl. actions
  $cols = "grid-cols-[1fr_0.25fr_0.35fr_0.35fr_0.22fr]";
@endphp

<div
  id="project-finance"
  class="col-span-2"
  x-data='{
    addOpen:false,
    newDescription:"",
    newQty: 1,
    newPrice:"",

    openAdd(){
      this.addOpen = true;
      this.$nextTick(() => this.$refs.descInput?.focus());
    },

    closeAdd(){
      this.addOpen = false;
      this.newDescription = "";
      this.newQty = 1;
      this.newPrice = "";
    }
  }'
>
  <div class="{{ $sectionWrap }} overflow-visible">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Financieel</p>

        <p class="ml-4 text-[#191D38] font-bold text-xs opacity-50">
          Totaal: <span class="text-[#009AC3] font-black">{{ $fmtCents($financeTotal) }}</span>
        </p>
      </div>

      <button
        type="button"
        x-on:click="openAdd()"
        class="h-8 px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
      >
        Nieuwe regel aanmaken
      </button>
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
        <p class="text-[#191D38] font-bold text-xs opacity-50">Omschrijving</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-start">Aantal</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Prijs</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Totaalprijs</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
      </div>
    </div>

    {{-- Body --}}
    <div class="{{ $sectionBody }} rounded-b-2xl">
      <div class="px-6 py-2 divide-y divide-[#191D38]/10">
        @php
          // ✅ nieuw: sorteren zodat newest altijd onderaan staat
          $financeItems = ($project->financeItems ?? collect())
            ->sortBy(fn($i) => [$i->created_at?->timestamp ?? 0, $i->id])
            ->values();
        @endphp

        @forelse($financeItems as $item)
          @php
            // ✅ belangrijk: geen duizend-separator, anders breekt eurToCents bij "1.234,56"
            $priceInit = number_format(((int) ($item->unit_price_cents ?? 0)) / 100, 2, ',', '');
          @endphp

          <div
            class="py-3 grid {{ $cols }} items-center gap-6"
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
          >
            {{-- Omschrijving --}}
            <div class="min-w-0">
              <p x-show="!edit" class="text-[#191D38] font-semibold text-sm">
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
            <div class="text-[#009AC3] text-sm text-right">
              <span x-show="!edit">{{ $fmtCents((int) ($item->total_cents ?? 0)) }}</span>
              <span x-cloak x-show="edit">—</span>
            </div>

            {{-- Acties --}}
            <div class="flex items-center justify-end gap-2">

              {{-- Read-only actions --}}
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

              {{-- Edit mode actions --}}
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

                {{-- ✅ hidden fields die meegaan in submit --}}
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

        {{-- ✅ Nieuwe regel rij (onderaan, net als taken) --}}
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
</div>
