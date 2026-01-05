@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto pr-2 pl-1">
      <div class="max-w-2xl w-full mx-auto">

        <form
          method="POST"
          action="{{ route('support.onboarding.step5.store') }}"
          class="w-full basis-full pt-2"
          x-data='{
            monthNames: ["januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december"],
            dayNames: ["Su","Mo","Tu","We","Th","Fr","Sa"],

            minDate: @json(now()->format("Y-m-d")),
            viewYear: (new Date()).getFullYear(),
            viewMonth: (new Date()).getMonth(),

            selectedDate: @json(old("shoot_date", session("onboarding.shoot_date"))),
            selectedSlot: @json(old("shoot_slot", session("onboarding.shoot_slot"))),

            // hardcode tijdsloten (later agenda check)
            slots: ["09:00 - 11:00","11:00 - 13:00","13:00 - 15:00","15:00 - 17:00","17:00 - 19:00"],

            init(){
              // als er al een datum in session/old staat: open direct die maand
              if(this.selectedDate){
                const d = this.parseKey(this.selectedDate);
                if(d){
                  this.viewYear = d.getFullYear();
                  this.viewMonth = d.getMonth();
                }
              }
            },

            pad(n){ return String(n).padStart(2,"0"); },
            toKey(d){ return d.getFullYear()+"-"+this.pad(d.getMonth()+1)+"-"+this.pad(d.getDate()); },
            parseKey(k){
              if(!k) return null;
              const [y,m,dd] = k.split("-").map(Number);
              return new Date(y, m-1, dd);
            },
            isWeekend(d){ const w = d.getDay(); return (w === 0 || w === 6); },
            isBeforeMin(d){
              const key = this.toKey(d);
              return key < this.minDate;
            },
            inViewMonth(d){ return d.getMonth() === this.viewMonth && d.getFullYear() === this.viewYear; },

            monthLabel(){
              return this.monthNames[this.viewMonth] + " " + this.viewYear;
            },

            prevMonth(){
              const d = new Date(this.viewYear, this.viewMonth - 1, 1);
              this.viewYear = d.getFullYear();
              this.viewMonth = d.getMonth();
            },
            nextMonth(){
              const d = new Date(this.viewYear, this.viewMonth + 1, 1);
              this.viewYear = d.getFullYear();
              this.viewMonth = d.getMonth();
            },

            calendarCells(){
              const first = new Date(this.viewYear, this.viewMonth, 1);
              const offset = first.getDay();
              const start = new Date(this.viewYear, this.viewMonth, 1 - offset);

              const cells = [];
              for(let i=0;i<42;i++){
                const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                const key = this.toKey(d);

                const disabled =
                  !this.inViewMonth(d) ||
                  this.isWeekend(d) ||
                  this.isBeforeMin(d);

                cells.push({
                  date: d,
                  key,
                  day: d.getDate(),
                  disabled,
                  selected: this.selectedDate === key,
                });
              }
              return cells;
            },

            pickDate(cell){
              if(cell.disabled) return;

              // ✅ bij andere datum: tijdslot resetten
              if(this.selectedDate !== cell.key){
                this.selectedSlot = null;
              }

              this.selectedDate = cell.key;
            },

            pickSlot(slot){
              this.selectedSlot = slot;
            }
          }'
          x-init="init()"
        >
          @csrf

          <h1 class="text-[#191D38] text-3xl font-black tracking-tight text-center mb-4">Selecteer de datum en tijd.</h1>

          <div class="w-fit text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-8 px-4 rounded-full py-1.5 mx-auto">
            Wanneer kunnen we foto’s komen maken?
          </div>

          {{-- Hidden values (naar controller/store) --}}
          <input type="hidden" name="shoot_date" x-model="selectedDate">
          <input type="hidden" name="shoot_slot" x-model="selectedSlot">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Left: Date --}}
            <div>
              <label class="block text-xs font-bold text-[#191D38] mb-2">
                Kies een datum <span class="text-red-500">*</span>
              </label>

              <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
                <div class="flex items-center gap-2 mb-3">
                  <i class="fa-regular fa-calendar text-[#009AC3] text-sm"></i>
                  <p class="text-sm font-black text-[#191D38]">Selecteer een datum</p>
                </div>

                <p class="text-xs font-semibold text-[#191D38]/60 mb-4">
                  Kies een werkdag voor uw afspraak. Weekenden zijn niet beschikbaar.
                </p>

                {{-- Month header --}}
                <div class="flex items-center justify-between mb-3">
                  <button
                    type="button"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-full font-semibold transition cursor-pointer
                          bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40"
                    @click="prevMonth()"
                    aria-label="Vorige maand"
                  >
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                  </button>

                  <p class="text-sm font-black text-[#191D38]" x-text="monthLabel()"></p>

                  <button
                    type="button"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-full font-semibold transition cursor-pointer
                          bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40"
                    @click="nextMonth()"
                    aria-label="Volgende maand"
                  >
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                  </button>
                </div>

                {{-- Weekdays --}}
                <div class="grid grid-cols-7 gap-2 mb-2">
                  <template x-for="(dn, i) in dayNames" :key="i">
                    <div class="text-[11px] font-black text-[#191D38]/40 text-center" x-text="dn"></div>
                  </template>
                </div>

                {{-- Days --}}
                <div class="grid grid-cols-7 gap-2">
                  <template x-for="(cell, i) in calendarCells()" :key="cell.key + '-' + i">
                    <button
                      type="button"
                      class="h-8 w-8 inline-flex items-center justify-center rounded-full font-black text-xs transition"
                      :class="cell.disabled
                        ? 'bg-[#2A324B]/5 text-[#2A324B]/15 cursor-not-allowed'
                        : (cell.selected
                          ? 'bg-[#009AC3] hover:bg-[#009AC3]/70 text-white cursor-pointer'
                          : 'bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40 cursor-pointer'
                        )"
                      @click="pickDate(cell)"
                      :disabled="cell.disabled"
                    >
                      <span x-text="cell.day"></span>
                    </button>
                  </template>
                </div>

                @error('shoot_date')
                  <p class="text-xs font-semibold text-red-500 mt-3">{{ $message }}</p>
                @enderror
              </div>
            </div>

            {{-- Right: Slot --}}
            <div>
              <label class="block text-xs font-bold text-[#191D38] mb-2">
                Selecteer een tijdslot <span class="text-red-500">*</span>
              </label>

              <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
                <div class="flex items-center gap-2 mb-3">
                  <i class="fa-regular fa-clock text-[#009AC3] text-sm"></i>
                  <p class="text-sm font-black text-[#191D38]">Selecteer een tijdslot</p>
                </div>

                <p class="text-xs font-semibold text-[#191D38]/60 mb-4">
                  Kies het tijdslot dat het beste voor u uitkomt.
                </p>

                {{-- Eerst datum kiezen --}}
                <div x-cloak x-show="!selectedDate" class="rounded-xl ring-1 ring-[#191D38]/10 bg-white p-4">
                  <p class="text-xs font-semibold text-[#191D38]/60">
                    Selecteer eerst een datum om de beschikbare tijdsloten te tonen.
                  </p>
                </div>

                {{-- Dan pas tijdsloten tonen --}}
                <div x-cloak x-show="selectedDate" x-transition.opacity>
                  <div class="grid grid-cols-2 gap-3">
                    <template x-for="(slot, idx) in slots" :key="slot">
                      <button
                        type="button"
                        class="h-11 inline-flex items-center justify-center rounded-full font-black text-xs transition cursor-pointer"
                        :class="[
                          (selectedSlot === slot
                            ? 'bg-[#009AC3] hover:bg-[#009AC3]/70 text-white'
                            : 'bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40'
                          ),
                          (idx === 4 ? 'col-span-2' : '')
                        ].join(' ')"
                        @click="pickSlot(slot)"
                        x-text="slot"
                      ></button>
                    </template>
                  </div>

                  @error('shoot_slot')
                    <p class="text-xs font-semibold text-red-500 mt-3">{{ $message }}</p>
                  @enderror
                </div>

              </div>
            </div>
          </div>

          {{-- Footer navigatie --}}
          @php
            $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
            $navNextBtn = "h-11 inline-flex items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";
          @endphp

          <div class="mt-10 pt-2">
            <div class="grid grid-cols-3 items-center">
              <div class="justify-self-start">
                <a href="{{ route('support.onboarding.step4') }}" class="{{ $navPrevBtn }}">
                  Vorige stap
                </a>
              </div>

              <div class="justify-self-center">
                <p class="text-xs font-bold text-[#191D38]/30">
                  Stap 5 van 6
                </p>
              </div>

              <div class="justify-self-end">
                <button type="submit" class="{{ $navNextBtn }}">
                  Volgende stap
                </button>
              </div>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection
