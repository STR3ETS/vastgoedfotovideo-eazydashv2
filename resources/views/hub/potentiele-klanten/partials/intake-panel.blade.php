@php
  $__start = $aanvraag->intake_at instanceof \Carbon\Carbon
      ? $aanvraag->intake_at
      : \Carbon\Carbon::parse($aanvraag->intake_at);

  $__duration = (int)($aanvraag->intake_duration ?? 30);
  $__end      = (clone $__start)->addMinutes($__duration);

  $__timeRange = $__start->format('H:i') . '–' . $__end->format('H:i');
  $__dateLabel = $__start->isToday()
      ? __('potentiele_klanten.intake_panel.today')
      : ($__start->isTomorrow()
          ? __('potentiele_klanten.intake_panel.tomorrow')
          : $__start->format('d-m-Y'));
@endphp

<div class="mt-2 p-3 rounded-xl border border-gray-200 bg-white"
    data-intake-panel
    x-data="{
       busy:false,
       completed: {{ $aanvraag->intake_done ? 'true' : 'false' }},
       confirmOpen:false,
       confirmError:'',

        async completeIntake(){
          if (this.busy || this.completed) return;
          this.busy = true;
          this.confirmError = '';
          try{
            const res = await fetch('{{ route('support.intake.complete', $aanvraag) }}', {
              method:'PATCH',
              headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'X-Requested-With':'XMLHttpRequest',
                'Accept':'application/json'
              },
              body: JSON.stringify({ done:true })
            });
            const data = await res.json().catch(()=>null);
            if(!res.ok || !data?.success) throw new Error('Complete mislukt');

            this.completed = true;

            // 👇 DIT TOEVOEGEN: kaart vertellen dat intake_done nu 1 is
            const card = this.$root.closest('[data-card-id]');
            if (card) {
              card.dataset.intakeDone = '1';
            }
            // ☝️ zodat _isDroppableFor(card, 'lead') true teruggeeft
            //    en dus GEEN opacity-50 / cursor-not-allowed meer plakt

            showToast(potklTrans('toast.intake_completed'), 'success');
          }catch(e){
            console.error(e);
            showToast(potklTrans('toast.intake_complete_error'), 'error');
          }finally{
            this.busy = false;
          }
        },

       openConfirmRemove() {
         if (this.busy) return;
         this.confirmError = '';
         this.confirmOpen = true;
       },

       async removeIntake(){
         if (this.busy) return;
         this.busy = true;
         this.confirmError = '';

         try {
           const res = await fetch('{{ route('support.intake.clear', $aanvraag) }}', {
             method: 'PATCH',
             headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}',
               'X-Requested-With': 'XMLHttpRequest',
               'Accept': 'application/json'
             },
             body: JSON.stringify({ intake_at: null })
           });

           const data = await res.json().catch(() => null);

           if (!res.ok || !data?.success) {
             throw new Error('Verwijderen mislukt');
           }

           // ✅ Statusbadge en status op de card updaten naar 'contact'
           const card = this.$root.closest('[data-card-id]');
           if (card && data) {
             const badge = card.querySelector('[data-status-badge]');
             if (badge) {
               card.dataset.status = data.status || 'contact';
               badge.dataset.statusValue = data.status || 'contact';
               badge.textContent = data.label || 'Contact';

               const all = [
                 'bg-[#b3e6ff]','text-[#0f6199]',
                 'bg-[#C2F0D5]','text-[#20603a]',
                 'bg-[#ffdfb3]','text-[#a0570f]',
                 'bg-[#ffb3b3]','text-[#8a2a2d]',
                 'bg-[#e0d4ff]','text-[#4c2a9b]',
                 'bg-slate-100','text-slate-700',
               ];
               badge.classList.remove(...all);
               badge.classList.add('bg-[#C2F0D5]', 'text-[#20603a]');
             }

             // ✅ Intake-panel leegmaken
             const panel = card.querySelector('#intake-panel-' + data.id);
             if (panel) {
               panel.innerHTML = '';
             }

             // ✅ Logboekregel toevoegen
             if (data.log && data.log.html) {
               const list  = card.querySelector('[data-status-log-list]');
               const empty = card.querySelector('[data-status-log-empty]');
               if (list) {
                 if (empty) empty.style.display = 'none';

                 const wrapper = document.createElement('div');
                 wrapper.innerHTML = data.log.html.trim();
                 const li = wrapper.firstElementChild;
                 if (li) list.prepend(li);
               }
             }
           }

           this.completed = false;
           this.confirmOpen = false;
           showToast(potklTrans('toast.intake_removed'), 'success');
         } catch (e) {
           console.error(e);
           this.confirmError = 'Verwijderen is mislukt. Probeer het nog een keer.';
           showToast(potklTrans('toast.intake_remove_error'), 'error');
         } finally {
           this.busy = false;
         }
       }
    }"
>
  <div class="flex items-center justify-between gap-2">
    <div class="flex flex-col gap-2">
      <p class="text-xs font-semibold text-[#215558] truncate">
        {{ $__dateLabel }} {{ $__timeRange }}
      </p>
      <span class="text-xs font-semibold text-[#215558] truncate">
        {{ __('potentiele_klanten.intake_panel.duration_prefix') }}
        {{ $__duration }}
        {{ __('potentiele_klanten.intake_panel.duration_suffix') }}
      </span>
    </div>

    <div class="flex items-center gap-4">
      <!-- 🔒 Knoppen alleen tonen als NIET voltooid -->
      <template x-if="!completed">
        <div class="flex items-center gap-2" x-cloak>
          <button type="button"
                  class="px-2.5 py-0.5 flex items-center rounded-full text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition duration-300 cursor-pointer"
                  @click="completeIntake()"
                  :disabled="busy">
            <i class="fa-solid fa-check fa-xs mr-1"></i>
            {{ __('potentiele_klanten.intake_panel.mark_done') }}
          </button>

          <button type="button"
                  class="px-2.5 py-0.5 flex items-center rounded-full text-[11px] font-semibold bg-red-600 hover:bg-red-700 transition duration-300 text-white cursor-pointer"
                  @click="openConfirmRemove()"
                  :disabled="busy">
            <i class="fa-solid fa-xs fa-trash mr-1 mt-0.5"></i>
            {{ __('potentiele_klanten.intake_panel.delete') }}
          </button>
        </div>
      </template>

      <!-- Rechter badge -->
      <template x-if="!completed">
        <span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-[#ffdfb3] text-[#a0570f]" x-cloak>
          {{ __('potentiele_klanten.intake_panel.planned_badge') }}
        </span>
      </template>
      <template x-if="completed">
        <span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-[#C2F0D5] text-[#20603a]" x-cloak>
          {{ __('potentiele_klanten.intake_panel.completed_badge') }}
        </span>
      </template>
    </div>
  </div>

  {{-- ✅ Lokale confirm-overlay voor verwijderen --}}
  <div
    x-show="confirmOpen"
    x-transition.opacity
    class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
    style="display:none;"
  >
    <div class="absolute inset-0 bg-black/25" @click="!busy && (confirmOpen = false)"></div>

    <div
      class="relative z-10 w-[380px] max-w-[92vw] bg-white rounded-2xl shadow-xl border border-gray-200 p-4
            transform-gpu transition-all duration-200 ease-out"
      x-transition:enter="transition duration-200 ease-out"
      x-transition:enter-start="opacity-0 translate-y-2"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition duration-150 ease-in"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 translate-y-2"
    >
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
        </div>
        <div class="flex-1">
          <h2 class="text-base font-black text-[#215558]">
            {{ __('potentiele_klanten.intake_panel.delete_title') }}
          </h2>
          <p class="mt-1 text-sm text-[#215558]">
            {!! __('potentiele_klanten.intake_panel.delete_question', [
                'status' => '<strong>' . __('potentiele_klanten.statuses.contact') . '</strong>',
            ]) !!}
          </p>
        </div>
      </div>

      <div class="mt-3 text-sm text-red-600" x-show="confirmError" x-text="confirmError"></div>

      <div class="mt-4 flex items-center gap-2">
        <button type="button"
                class="bg-red-500 hover:bg-red-600 cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300 disabled:opacity-60"
                :disabled="busy"
                @click="removeIntake()">
          <span class="inline-flex items-center gap-2">
            <span x-show="busy"><i class="fa-solid fa-spinner fa-spin"></i></span>
            <span x-show="!busy">
              {{ __('potentiele_klanten.intake_panel.delete_yes') }}
            </span>
          </span>
        </button>
        <button type="button"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300"
                :disabled="busy"
                @click="confirmOpen = false">
          {{ __('potentiele_klanten.intake_panel.delete_cancel') }}
        </button>
      </div>
    </div>
  </div>
</div>