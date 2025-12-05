@extends('hub.layouts.app')

@section('content')
  <div class="col-span-5 grid grid-cols-4 w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 overflow-hidden"
      x-data="statusDnD({
          csrf: '{{ csrf_token() }}',
          updateUrlTemplate: '{{ route('support.potentiele-klanten.status.update', ['aanvraag' => '__ID__']) }}',
          labelsByValue: @js($statusByValue ?? []),
          statusCounts: @js($statusCounts ?? []),
          statusCountTexts: @js([
              'singular' => __('potentiele_klanten.status_counts.singular'),
              'plural'   => __('potentiele_klanten.status_counts.plural'),
          ]),
      })"
      x-init="init()">

    <div class="col-span-4 w-full h-full flex flex-col min-h-0">
      @include('hub.potentiele-klanten.partials.statuses', [
        'statusMap'    => $statusMap ?? [],
        'statusCounts' => $statusCounts ?? [],
      ])
      <div class="mt-3 flex gap-4 items-center">
        <div class="w-fit flex items-center gap-2 p-2 rounded-full bg-[#f3f8f8] overflow-x-auto">
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/boyd.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Boyd Halfman</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/yael.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Yael Scholten</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/raphael.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Raphael Muskitta</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/johnny.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Johnny Muskitta</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/martijn.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Martijn Visser</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/laurenzo.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Laurenzo Soemopawiro</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/joris.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Joris Lindner</p>
          </div>
          <div class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab">
            <img src="/assets/eazyonline/memojis/laurina.webp" class="max-h-[80%]">
            <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">Laurina Pesulima</p>
          </div>
        </div>
      </div>
      {{-- Scroll alleen rechts (detail) --}}
      <div class="mt-3 flex-1 min-h-0 overflow-hidden flex flex-col">

          {{-- Status-filters --}}
          <div class="flex flex-wrap items-center gap-2 mt-6">
              {{-- Prospect --}}
              <button type="button"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                      :class="activeFilters.includes('prospect')
                        ? 'bg-[#b3e6ff] border-[#0f6199] text-[#0f6199]'
                        : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                      @click="toggleFilter('prospect')">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0f6199]"></span>
                <span>{{ __('potentiele_klanten.filters.prospect') }}</span>
              </button>

              {{-- Contact --}}
              <button type="button"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                      :class="activeFilters.includes('contact')
                        ? 'bg-[#C2F0D5] border-[#20603a] text-[#20603a]'
                        : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                      @click="toggleFilter('contact')">
                <span class="w-1.5 h-1.5 rounded-full bg-[#20603a]"></span>
                <span>{{ __('potentiele_klanten.filters.contact') }}</span>
              </button>

              {{-- Intake --}}
              <button type="button"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                      :class="activeFilters.includes('intake')
                        ? 'bg-[#ffdfb3] border-[#a0570f] text-[#a0570f]'
                        : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                      @click="toggleFilter('intake')">
                <span class="w-1.5 h-1.5 rounded-full bg-[#a0570f]"></span>
                <span>{{ __('potentiele_klanten.filters.intake') }}</span>
              </button>

              {{-- Lead --}}
              <button type="button"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                      :class="activeFilters.includes('lead')
                        ? 'bg-[#e0d4ff] border-[#4c2a9b] text-[#4c2a9b]'
                        : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                      @click="toggleFilter('lead')">
                <span class="w-1.5 h-1.5 rounded-full bg-[#4c2a9b]"></span>
                <span>{{ __('potentiele_klanten.filters.lead') }}</span>
              </button>

              {{-- Dead --}}
              <button type="button"
                      class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                      :class="activeFilters.includes('dead')
                        ? 'bg-[#ffb3b3] border-[#8a2a2d] text-[#8a2a2d]'
                        : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                      @click="toggleFilter('dead')">
                <span class="w-1.5 h-1.5 rounded-full bg-[#8a2a2d]"></span>
                <span>{{ __('potentiele_klanten.filters.dead') }}</span>
              </button>
          </div>

          {{-- ✅ MASTER / DETAIL --}}
          @php
              $aanvragenCollection = $aanvragen ?? collect();

              $firstAanvraagId = $aanvragenCollection->first()->id ?? null;

              $choiceMap = [
                  'new'   => __('potentiele_klanten.choices.new'),
                  'renew' => __('potentiele_klanten.choices.renew'),
              ];
          @endphp

          <div
              class="mt-6 grid grid-cols-3 gap-6 flex-1 min-h-0"
              x-data="{ activeId: @js($firstAanvraagId) }"
          >
              {{-- ===================== LEFT LIST ===================== --}}
              <div class="space-y-2 bg-[#f3f8f8] rounded-4xl p-8 h-full min-h-0">
                  @forelse($aanvragenCollection as $aanvraag)
                      @php
                          $rowStatus = $aanvraag->status ?? 'prospect';
                      @endphp

                      <button
                          type="button"
                          x-show="!activeFilters.length || activeFilters.includes(@js($rowStatus))"
                          x-cloak
                          class="w-full text-left pl-5 pr-2 py-4 transition duration-300
                                border-l-4 rounded-tr-4xl rounded-br-4xl cursor-pointer"
                          :class="activeId === {{ $aanvraag->id }}
                              ? 'bg-white border-l-[#0F9B9F]'
                              : 'bg-white border-l-[#215558]/20 hover:bg-gray-50'"
                          @click="activeId = {{ $aanvraag->id }}"
                      >
                          <div class="flex items-start justify-between gap-4">
                              <div class="w-full flex items-center justify-between">
                                  <div class="w-full">
                                      <p class="text-base font-bold text-[#215558] truncate mb-1">
                                          {{ $choiceMap[$aanvraag->choice] ?? __('potentiele_klanten.choices.default') }}
                                      </p>
                                      <p class="text-sm font-medium text-[#215558] leading-[20px] opacity-75">
                                          {{ $aanvraag->company
                                              ?? $aanvraag->bedrijf
                                              ?? $aanvraag->contact_person
                                              ?? $aanvraag->contactpersoon
                                              ?? '' }}
                                      </p>
                                  </div>

                                  <div class="flex items-center gap-2">
                                      @php
                                          $status = $aanvraag->status ?? 'prospect';

                                          $badgeMap = [
                                              'prospect' => ['label' => __('potentiele_klanten.filters.prospect'), 'class' => 'bg-[#b3e6ff] text-[#0f6199]'],
                                              'contact'  => ['label' => __('potentiele_klanten.filters.contact'),  'class' => 'bg-[#C2F0D5] text-[#20603a]'],
                                              'intake'   => ['label' => __('potentiele_klanten.filters.intake'),   'class' => 'bg-[#ffdfb3] text-[#a0570f]'],
                                              'lead'     => ['label' => __('potentiele_klanten.filters.lead'),     'class' => 'bg-[#e0d4ff] text-[#4c2a9b]'],
                                              'dead'     => ['label' => __('potentiele_klanten.filters.dead'),     'class' => 'bg-[#ffb3b3] text-[#8a2a2d]'],
                                          ];

                                          $badge = $badgeMap[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-600'];
                                      @endphp

                                      <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badge['class'] }}">
                                          {{ $badge['label'] }}
                                      </span>

                                      @if(!empty($aanvraag->budget_label))
                                          <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700">
                                              {{ $aanvraag->budget_label }}
                                          </span>
                                      @endif
                                  </div>
                              </div>

                              <div class="flex flex-col items-end gap-2 shrink-0">
                                  <span class="text-[11px] text-[#215558] opacity-50">
                                      {{ optional($aanvraag->updated_at)->format('H:i') }}
                                  </span>

                                  @if(!empty($aanvraag->owner_avatar_url))
                                      <img src="{{ $aanvraag->owner_avatar_url }}"
                                          class="w-6 h-6 rounded-full object-cover border border-gray-200">
                                  @endif
                              </div>
                          </div>
                      </button>
                  @empty
                      <div class="p-4">
                          <p class="text-sm font-semibold text-[#215558] opacity-70">
                              Geen potentiële klanten gevonden.
                          </p>
                      </div>
                  @endforelse
              </div>

              {{-- ===================== RIGHT DETAIL ===================== --}}
              <div class="col-span-2 min-h-0 bg-[#f3f8f8] rounded-4xl overflow-hidden flex flex-col">
                  <div class="p-8 flex-1 min-h-0 overflow-y-auto">
                      @forelse($aanvragenCollection as $aanvraag)
                          <div
                              x-show="activeId === {{ $aanvraag->id }}"
                              x-cloak
                          >
                              @include('hub.potentiele-klanten.partials.card', [
                                  'aanvraag'      => $aanvraag,
                                  'statusByValue' => $statusByValue ?? [],
                                  'showListRow'   => false,
                              ])
                          </div>
                      @empty
                          <div class="bg-white rounded-2xl border border-gray-200 p-4">
                              <p class="text-sm font-semibold text-[#215558] opacity-70">
                                  Selecteer een klant om details te zien.
                              </p>
                          </div>
                      @endforelse
                  </div>
              </div>
          </div>
      </div>

    </div>
    {{-- Lead → project overlay --}}
    <div
      x-show="leadConfirmOpen"
      x-transition.opacity
      class="fixed inset-0 z-[9998] flex items-center justify-center px-4"
      style="display:none;"
    >
      <div class="absolute inset-0 bg-black/25" @click="!leadConfirmLoading && cancelLead()"></div>

      <div
        class="relative z-10 w-[420px] max-w-[92vw] bg-white rounded-2xl shadow-xl border border-gray-200 p-4
              transform-gpu transition-all duration-200 ease-out"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
      >
        <div class="flex items-start gap-3">
          <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-diagram-project text-emerald-600"></i>
          </div>
          <div class="flex-1">
            <h2 class="text-base font-black text-[#215558]">
              {{ __('potentiele_klanten.lead_modal.title') }}
            </h2>
            <p class="mt-1 text-sm text-[#215558] opacity-80">
              {{ __('potentiele_klanten.lead_modal.text') }}
            </p>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
          <button type="button"
                  class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300 disabled:opacity-60"
                  :disabled="leadConfirmLoading"
                  @click="confirmLead()">
            <span class="inline-flex items-center gap-2">
              <span x-show="leadConfirmLoading"><i class="fa-solid fa-spinner fa-spin"></i></span>
              <span x-show="!leadConfirmLoading">{{ __('potentiele_klanten.lead_modal.confirm') }}</span>
            </span>
          </button>
          <button type="button"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300"
                  :disabled="leadConfirmLoading"
                  @click="cancelLead()">
            {{ __('potentiele_klanten.lead_modal.cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Toast container rechtsonder --}}
  <div id="toast-container"
       class="fixed bottom-6 right-6 z-[9999] space-y-2 pointer-events-none">
  </div>

  <!-- ✅ Intake planner overlay -->
  <div x-data="intakePlanner({
          csrf: '{{ csrf_token() }}',
          availabilityUrlTemplate: '{{ url('/app/support/intake/availability?date=__DATE__') }}',
        })"
      x-init="init()"
      x-show="open"
      x-transition.opacity
      class="fixed inset-0 z-[9999] flex items-center justify-center"
      style="display:none;">

    <!-- Achtergrond -->
    <div class="absolute inset-0 bg-black/40" @click="close()"></div>

    <!-- Modal -->
    <div class="relative w-[900px] max-w-[95vw] bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
      <div class="flex items-center justify-between px-5 py-4">
        <h3 class="text-lg font-black text-[#215558]">
          {{ __('potentiele_klanten.intake_modal.title') }}
        </h3>
        <button class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center cursor-pointer"
                @click="close()">
          <i class="fa-solid fa-xmark text-[#215558]"></i>
        </button>
      </div>

      <hr class="border-gray-200">

      <div class="p-5 grid grid-cols-3 gap-4">
        <!-- Linker kolom: datum -->
        <div class="grid h-fit gap-3">
          <div>
            <label class="block text-xs text-[#215558] opacity-70 mb-1">
              {{ __('potentiele_klanten.intake_modal.date_label') }}
            </label>
            <input type="date"
                x-model="date"
                @change="loadDay()"
                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-[#215558] outline-none focus:border-[#3b8b8f] transition" />
          </div>
          <div>
            <label class="block text-xs text-[#215558] opacity-70 mb-1">
              {{ __('potentiele_klanten.intake_modal.duration_label') }}
            </label>
            <select x-model.number="durationMinutes"
                    @change="rebuildSlots()"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-[#215558] outline-none focus:border-[#3b8b8f] transition">
              <option value="30">{{ __('potentiele_klanten.intake_modal.duration_30') }}</option>
              <option value="45">{{ __('potentiele_klanten.intake_modal.duration_45') }}</option>
              <option value="60">{{ __('potentiele_klanten.intake_modal.duration_60') }}</option>
            </select>
          </div>
        </div>

        <!-- Rechter kolommen: mini agenda -->
        <div class="col-span-2">
          <div class="flex items-center justify-between mb-1">
            <p class="block text-xs text-[#215558] opacity-70">
              <span x-text="prettyDate(date)"></span>
            </p>
          </div>

          <div class="grid grid-cols-3 gap-2 max-h-[340px] overflow-y-auto pr-1">
            <template x-for="slot in slots" :key="slot.startLocal">
              <button type="button"
                class="text-sm px-3 py-2 rounded-xl border transition cursor-pointer text-left"
                :class="{
                  // FREE (groen) alleen als niet-picked
                  'bg-emerald-50 border-emerald-200 text-[#215558]': slot.state === 'free' && (!picked || picked.startLocal !== slot.startLocal),
                  'hover:bg-emerald-100':                               slot.state === 'free' && (!picked || picked.startLocal !== slot.startLocal),

                  // BUSY (rood)
                  'bg-red-50 border-red-200 text-red-700 opacity-60 cursor-not-allowed': slot.state === 'busy',

                  // PICKED (cyan)
                  'bg-cyan-100 border-cyan-300 text-cyan-700': picked && picked.startLocal === slot.startLocal,
                }"
                :disabled="slot.state !== 'free'"
                @click="pickSlot(slot)">
                <div class="font-semibold" x-text="slot.label"></div>
                <div class="text-[11px] opacity-70" x-text="slotRange(slot)"></div>
              </button>
            </template>
          </div>

          <div class="mt-4 flex items-center justify-end gap-2">
            <button type="button"
                    class="px-4 py-2 rounded-full border border-gray-200 text-sm font-semibold text-[#215558] hover:bg-gray-50 cursor-pointer"
                    @click="close()">{{ __('potentiele_klanten.intake_modal.cancel') }}</button>

            <button type="button"
                    class="px-5 py-2 rounded-full text-sm font-semibold text-white bg-[#0F9B9F] hover:bg-[#215558] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                    :disabled="!picked || loading"
                    @click="confirm()">
              <span x-show="!loading">
                {{ __('potentiele_klanten.intake_modal.confirm') }}
              </span>
              <span x-show="loading">
                {{ __('potentiele_klanten.intake_modal.confirming') }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  window.POTKL_STRINGS = {!! json_encode([
      'toast' => [
          'status_intake_only_from_contact'         => __('potentiele_klanten.toast.status_intake_only_from_contact'),
          'status_dead_only_from_contact_or_intake' => __('potentiele_klanten.toast.status_dead_only_from_contact_or_intake'),
          'status_already'                          => __('potentiele_klanten.toast.status_already'),
          'status_update_success'                   => __('potentiele_klanten.toast.status_update_success'),
          'status_update_error'                     => __('potentiele_klanten.toast.status_update_error'),
          'intake_planned'                          => __('potentiele_klanten.toast.intake_planned'),
          'intake_plan_error'                       => __('potentiele_klanten.toast.intake_plan_error'),
          'intake_completed'                        => __('potentiele_klanten.toast.intake_completed'),
          'intake_complete_error'                   => __('potentiele_klanten.toast.intake_complete_error'),
          'intake_removed'                          => __('potentiele_klanten.toast.intake_removed'),
          'intake_remove_error'                     => __('potentiele_klanten.toast.intake_remove_error'),
          'answer_save_success'                     => __('potentiele_klanten.toast.answer_save_success'),
          'answer_save_error'                       => __('potentiele_klanten.toast.answer_save_error'),
          'call_save_success'                       => __('potentiele_klanten.toast.call_save_success'),
          'call_save_error'                         => __('potentiele_klanten.toast.call_save_error'),
          'file_upload_success'                     => __('potentiele_klanten.toast.file_upload_success'),
          'file_upload_error'                       => __('potentiele_klanten.toast.file_upload_error'),
          'file_delete_success'                     => __('potentiele_klanten.toast.file_delete_success'),
          'file_delete_error'                       => __('potentiele_klanten.toast.file_delete_error'),
          'status_lead_requires_intake'             => __('potentiele_klanten.toast.status_lead_requires_intake'),
      ],
      'calls' => [
          'badge_none'   => __('potentiele_klanten.calls.badge_none'),
          'badge_spoken' => __('potentiele_klanten.calls.badge_spoken'),
      ],
  ]) !!};
</script>
@verbatim
<script>
/**
 * Eenvoudige toast rechtsonder
 * type: 'success' | 'error'
 */
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const el = document.createElement('div');
  el.className = [
    'pointer-events-auto',
    'w-72',
    'rounded-2xl',
    'border',
    'border-gray-200',
    'px-3',
    'py-2.5',
    'bg-white',
    'shadow-lg',
    'flex',
    'items-start',
    'gap-2',
    'transform-gpu',
    'translate-y-3',
    'opacity-0',
    'transition-all',
    'duration-300'
  ].join(' ');

  const isError = type === 'error';

  const iconHtml = isError
    ? `
      <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
        <i class="fa-solid fa-xmark text-[11px] text-red-500 mb-0.5"></i>
      </div>
    `
    : `
      <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
        <i class="fa-solid fa-check text-[11px] text-emerald-600 mb-0.5"></i>
      </div>
    `;

  el.innerHTML = `
    ${iconHtml}
    <div class="mt-0.5 text-xs font-semibold text-[#215558] leading-snug">
      ${message}
    </div>
  `;

  container.appendChild(el);

  requestAnimationFrame(() => {
    el.classList.remove('translate-y-3', 'opacity-0');
  });

  setTimeout(() => {
    el.classList.add('opacity-0', 'translate-y-3');
    el.addEventListener('transitionend', () => {
      el.remove();
    }, { once: true });
  }, 3000);
}

function potklTrans(path, replacements) {
  const parts = path.split('.');
  let value = window.POTKL_STRINGS || {};
  for (const p of parts) {
    value = value && value[p];
  }
  if (typeof value !== 'string') return path;

  replacements = replacements || {};
  return Object.keys(replacements).reduce((str, key) => {
    const re = new RegExp(':' + key, 'g');
    return str.replace(re, replacements[key]);
  }, value);
}

// transparante drag image
let __transparentDragImg = null;
function getTransparentDragImage() {
  if (__transparentDragImg) return __transparentDragImg;

  const canvas = document.createElement('canvas');
  canvas.width = canvas.height = 1;
  const img = new Image();
  img.src = canvas.toDataURL();
  __transparentDragImg = img;
  return img;
}

function statusDnD({ csrf, updateUrlTemplate, labelsByValue, statusCounts, statusCountTexts }) {
  return {
    draggingValue: null,
    draggingLabel: null,
    statusCounts: statusCounts || {},
    statusCountTexts: statusCountTexts || {
      singular: ':count project',
      plural: ':count projecten',
    },

    // lead → project overlay
    leadConfirmOpen: false,
    leadConfirmLoading: false,
    leadConfirmCard: null,
    leadConfirmUrl: null,
    leadConfirmOldValue: null,
    leadConfirmNewValue: null,

    // filters
    activeFilters: [],
    hasVisibleCards: true,

    // drag ghost
    dragGhost: null,
    dragGhostOffsetX: 0,
    dragGhostOffsetY: 0,
    lastGhostX: null,

    // ✅ Drop-logic & visuele hints (intake/dead/lead)
    _isDroppableFor(card, dragging) {
      const badge  = card.querySelector('[data-status-badge]');
      const status = badge ? badge.dataset.statusValue : card.dataset.status;

      const intakeDoneAttr = card.dataset.intakeDone;
      const intakeDone = String(intakeDoneAttr) === '1' || intakeDoneAttr === 1 || intakeDoneAttr === true;

      if (dragging === 'intake') {
        // Intake alleen naar kaarten die nu 'contact' zijn
        return status === 'contact';
      }

      if (dragging === 'dead') {
        // Dead alleen vanuit contact of intake
        return status === 'contact' || status === 'intake';
      }

      if (dragging === 'lead') {
        // ⬅️ Alleen droppable als intake_done = 1
        return intakeDone === true;
      }

      return true; // overige statussen vrij
    },

    _applyDragHints() {
      const dragging = this.draggingValue;
      if (!dragging) return;
      document.querySelectorAll('[data-card-id]').forEach(card => {
        if (!this._isDroppableFor(card, dragging)) {
          card.classList.add('opacity-50', 'cursor-not-allowed');
        }
      });
    },

    _clearDragHints() {
      document.querySelectorAll('[data-card-id]').forEach(card => {
        card.classList.remove('opacity-50', 'cursor-not-allowed');
      });
    },

    init() {
      window.addEventListener('dragover', (e) => {
        if (!this.dragGhost) return;
        this._updateGhostPosition(e.clientX, e.clientY);
      });

      // direct bij load cards filteren (default: alles zichtbaar)
      this.filterCards();
    },

    humanize(value) {
      if (!value) return '';
      return value.toString()
        .replace(/_/g, ' ')
        .trim()
        .replace(/\s+/g, ' ')
        .replace(/\b\w/g, c => c.toUpperCase());
    },

    formatStatusCount(value) {
      const count = this.statusCounts[value] || 0;
      const tpl = count === 1
        ? this.statusCountTexts.singular
        : this.statusCountTexts.plural;

      return tpl.replace(':count', count);
    },

    // FILTERS
    toggleFilter(status) {
      if (this.activeFilters.includes(status)) {
        this.activeFilters = this.activeFilters.filter(s => s !== status);
      } else {
        this.activeFilters.push(status);
      }
      this.filterCards();
    },

    filterCards() {
      const cards = document.querySelectorAll('[data-card-id]');
      let visible = 0;

      if (!this.activeFilters.length) {
        cards.forEach(card => {
          card.classList.remove('hidden');
          visible++;
        });
        this.hasVisibleCards = visible > 0;
        return;
      }

      cards.forEach(card => {
        const status = card.dataset.status;
        const show = this.activeFilters.includes(status);
        card.classList.toggle('hidden', !show);
        if (show) visible++;
      });

      this.hasVisibleCards = visible > 0;
    },

    // DRAG
    onStatusDragStart(value, label, el, event) {
      this.draggingValue = value;
      this.draggingLabel = label;

      // 🎯 Toon hints
      if (['intake','dead','lead'].includes(value)) {
        this._applyDragHints();
      }

      if (event && event.dataTransfer) {
        event.dataTransfer.setDragImage(getTransparentDragImage(), 0, 0);
      }

      const rect = el.getBoundingClientRect();
      const ghost = el.cloneNode(true);
      ghost.id = '';
      ghost.classList.add(
        'pointer-events-none',
        'fixed',
        'z-[9998]',
        'shadow-xl',
        'rounded-xl'
      );
      ghost.style.width = rect.width + 'px';

      document.body.appendChild(ghost);
      this.dragGhost = ghost;

      this.dragGhostOffsetX = rect.width / 2;
      this.dragGhostOffsetY = rect.height / 2;
      this.lastGhostX = event?.clientX ?? null;

      this._updateGhostPosition(event.clientX, event.clientY, true);
    },

    onStatusDragEnd(el) {
      if (this.dragGhost) this.dragGhost.remove();
      this.dragGhost = null;
      this.lastGhostX = null;

      // 🧹 Hints altijd opruimen als drag eindigt
      this._clearDragHints();

      this.draggingValue = null;
      this.draggingLabel = null;
    },

    _updateGhostPosition(clientX, clientY, initial = false) {
      if (!this.dragGhost || clientX == null || clientY == null) return;

      if (initial || this.lastGhostX == null) {
        this.lastGhostX = clientX;
      }

      const x = clientX - this.dragGhostOffsetX;
      const y = clientY - this.dragGhostOffsetY;

      const dx = clientX - this.lastGhostX;
      const angle = Math.max(-10, Math.min(10, dx / 2));

      this.dragGhost.style.transform =
        `translate(${x}px, ${y}px) rotate(${angle}deg)`;

      this.lastGhostX = this.lastGhostX + dx * 0.2;
    },

    onCardDragOver(e) {
      e.preventDefault();
      const card = e.target.closest('[data-card-id]') || e.currentTarget;

      // Alleen highlight als kaart droppable is voor huidige drag
      if (this.draggingValue && !this._isDroppableFor(card, this.draggingValue)) {
        return;
      }

      card.classList.add('border-[#0F9B9F]');
      card.classList.remove('border-gray-200');
    },

    onCardDragLeave(e) {
      const card = e.target.closest('[data-card-id]') || e.currentTarget;
      card.classList.remove('border-[#0F9B9F]');
      card.classList.add('border-gray-200');
    },

    _getLabelFor(value) {
      if (labelsByValue && Object.prototype.hasOwnProperty.call(labelsByValue, value)) {
        return labelsByValue[value];
      }
      if (this.draggingValue === value && this.draggingLabel) {
        return this.draggingLabel;
      }
      return this.humanize(value);
    },

    _updateBadge(cardEl, newValue) {
      const badge = cardEl.querySelector('[data-status-badge]');
      if (!badge) return;

      // 👉 HIER: data-status van de card updaten
      cardEl.dataset.status = newValue;

      const label = this._getLabelFor(newValue);
      badge.dataset.statusValue = newValue;
      badge.textContent = label;

      const colorMap = {
        prospect: ['bg-[#b3e6ff]',   'text-[#0f6199]'],
        contact:  ['bg-[#C2F0D5]',   'text-[#20603a]'],
        intake:   ['bg-[#ffdfb3]',   'text-[#a0570f]'],
        dead:     ['bg-[#ffb3b3]',   'text-[#8a2a2d]'],
        lead:     ['bg-[#e0d4ff]',   'text-[#4c2a9b]'],
      };

      const allColorClasses = [
        'bg-[#b3e6ff]','text-[#0f6199]',
        'bg-[#C2F0D5]','text-[#20603a]',
        'bg-[#ffdfb3]','text-[#a0570f]',
        'bg-[#ffb3b3]','text-[#8a2a2d]',
        'bg-[#e0d4ff]','text-[#4c2a9b]',
      ];

      badge.classList.remove(...allColorClasses);

      const classes = colorMap[newValue] || ['bg-slate-200', 'text-slate-600'];
      badge.classList.add(...classes);

      badge.classList.add('bg-[#0F9B9F]/20');
      setTimeout(() => badge.classList.remove('bg-[#0F9B9F]/20'), 250);
    },

    _updateCounts(oldValue, newValue) {
      if (!oldValue || oldValue === newValue) return;
      if (this.statusCounts[oldValue] == null) this.statusCounts[oldValue] = 0;
      if (this.statusCounts[newValue] == null) this.statusCounts[newValue] = 0;

      this.statusCounts[oldValue] = Math.max(0, (this.statusCounts[oldValue] || 0) - 1);
      this.statusCounts[newValue] = (this.statusCounts[newValue] || 0) + 1;
    },

    _appendStatusLog(cardEl, log) {
      if (!log || !log.html) return;

      const list  = cardEl.querySelector('[data-status-log-list]');
      const empty = cardEl.querySelector('[data-status-log-empty]');
      if (!list) return;

      if (empty) {
        empty.style.display = 'none';
      }

      const wrapper = document.createElement('div');
      wrapper.innerHTML = log.html.trim();
      const li = wrapper.firstElementChild;
      if (!li) return;

      list.prepend(li);
    },

    async onCardDrop(e) {
      e.preventDefault();
      const card = e.target.closest('[data-card-id]') || e.currentTarget;
      card.classList.remove('border-[#0F9B9F]');
      card.classList.add('border-gray-200');

      if (!this.draggingValue) return;

      const id  = card.dataset.cardId;
      const url = updateUrlTemplate.replace('__ID__', id);

      const badge    = card.querySelector('[data-status-badge]');
      const oldValue = badge ? badge.dataset.statusValue : null;
      const newValue = this.draggingValue;

      // 🚫 Intake alleen toegestaan vanaf 'contact'
      if (newValue === 'intake' && oldValue !== 'contact') {
        const oldLabel = this._getLabelFor(oldValue || '');
        showToast(
          potklTrans('toast.status_intake_only_from_contact', { current: oldLabel || '—' }),
          'error'
        );

        if (this.dragGhost) this.dragGhost.remove();
        this.dragGhost = null;
        this.lastGhostX = null;
        this._clearDragHints();

        this.draggingValue = null;
        this.draggingLabel = null;
        return; // ⛔️ stop hier
      }

      // Als status niet verandert: meld en klaar
      if (oldValue === newValue) {
        const label = this._getLabelFor(newValue);
        showToast(
          potklTrans('toast.status_already', { status: label }),
          'error'
        );
        this.draggingValue = null;
        this.draggingLabel = null;
        this._clearDragHints();
        return;
      }

      // 🚫 Dead alleen vanaf 'contact' of 'intake'
      if (newValue === 'dead' && !['contact','intake'].includes(oldValue)) {
        const oldLabel = this._getLabelFor(oldValue || '');
        showToast(
          potklTrans('toast.status_dead_only_from_contact_or_intake', { current: oldLabel || '—' }),
          'error'
        );

        if (this.dragGhost) this.dragGhost.remove();
        this.dragGhost = null;
        this.lastGhostX = null;
        this._clearDragHints();

        this.draggingValue = null;
        this.draggingLabel = null;
        return;
      }

      // ✅ Intercept: intake -> open overlay en stop
      if (newValue === 'intake') {
        window.dispatchEvent(new CustomEvent('open-intake-planner', {
          detail: {
            aanvraagId: id,
            cardEl: card,
            oldValue,
            newValue,
            updateUrl: url,
          }
        }));

        if (this.dragGhost) this.dragGhost.remove();
        this.dragGhost = null;
        this.lastGhostX = null;
        this._clearDragHints();

        this.draggingValue = null;
        this.draggingLabel = null;
        return;
      }

      // ✅ Lead: alleen doorgaan als kaart droppable is, en dan eerst overlay openen
      if (newValue === 'lead') {
        // kaart mag deze status niet krijgen → melding + resetten en stoppen
        if (!this._isDroppableFor(card, newValue)) {
          showToast(
            potklTrans('toast.status_lead_requires_intake'),
            'error'
          );

          if (this.dragGhost) this.dragGhost.remove();
          this.dragGhost = null;
          this.lastGhostX = null;
          this._clearDragHints();
          this.draggingValue = null;
          this.draggingLabel = null;
          return;
        }

        // kaart is WÉL droppable → overlay openen, PATCH gebeurt pas in confirmLead()
        this.leadConfirmCard     = card;
        this.leadConfirmUrl      = url;
        this.leadConfirmOldValue = oldValue;
        this.leadConfirmNewValue = newValue;
        this.leadConfirmOpen     = true;

        if (this.dragGhost) this.dragGhost.remove();
        this.dragGhost = null;
        this.lastGhostX = null;
        this._clearDragHints();
        this.draggingValue = null;
        this.draggingLabel = null;
        return;
      }

      // 🔁 Overige statustransities via API
      try {
        const res = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ status: newValue })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok) {
          // ⬇️ Serverboodschap (bijv. lead_requires_intake) als die er is
          showToast(
            data?.message || potklTrans('toast.status_update_error'),
            'error'
          );
          return;
        }

        const finalValue = (data && data.status) || newValue;

        // ✅ dataset.intakeDone bijwerken als server het meestuurt
        if (data && typeof data.intake_done !== 'undefined') {
          card.dataset.intakeDone = data.intake_done ? '1' : '0';
        }

        // ✅ en als we naar LEAD gingen, is intake_done sowieso voldaan
        if (finalValue === 'lead') {
          card.dataset.intakeDone = '1';
        }

        this._updateCounts(oldValue, finalValue);
        this._updateBadge(card, finalValue);

        if (data && data.log) {
          this._appendStatusLog(card, data.log);
        }

        const label = this._getLabelFor(finalValue);
        showToast(
          potklTrans('toast.status_update_success', { status: label }),
          'success'
        );
      } catch (err) {
        console.error(err);
        showToast(potklTrans('toast.status_update_error'), 'error');
      } finally {
        if (this.dragGhost) this.dragGhost.remove();
        this.dragGhost = null;
        this.lastGhostX = null;

        this._clearDragHints();
        this.draggingValue = null;
        this.draggingLabel = null;
      }
    },

    async confirmLead() {
      if (!this.leadConfirmCard || !this.leadConfirmUrl) {
        this.leadConfirmOpen = false;
        return;
      }

      this.leadConfirmLoading = true;

      const card     = this.leadConfirmCard;
      const url      = this.leadConfirmUrl;
      const oldValue = this.leadConfirmOldValue;
      const newValue = this.leadConfirmNewValue;

      try {
        const res = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ status: newValue })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok) {
          showToast(
            data?.message || potklTrans('toast.status_update_error'),
            'error'
          );
          return;
        }

        const finalValue = (data && data.status) || newValue;

        if (data && typeof data.intake_done !== 'undefined') {
          card.dataset.intakeDone = data.intake_done ? '1' : '0';
        }

        if (finalValue === 'lead') {
          card.dataset.intakeDone = '1';
        }

        this._updateCounts(oldValue, finalValue);
        this._updateBadge(card, finalValue);

        if (data && data.log) {
          this._appendStatusLog(card, data.log);
        }

        const label = this._getLabelFor(finalValue);
        showToast(
          potklTrans('toast.status_update_success', { status: label }),
          'success'
        );
      } catch (err) {
        console.error(err);
        showToast(potklTrans('toast.status_update_error'), 'error');
      } finally {
        this.leadConfirmLoading = false;
        this.leadConfirmOpen    = false;
        this.leadConfirmCard     = null;
        this.leadConfirmUrl      = null;
        this.leadConfirmOldValue = null;
        this.leadConfirmNewValue = null;
      }
    },

    cancelLead() {
      if (this.leadConfirmLoading) return;
      this.leadConfirmOpen    = false;
      this.leadConfirmCard     = null;
      this.leadConfirmUrl      = null;
      this.leadConfirmOldValue = null;
      this.leadConfirmNewValue = null;
    },
  };
}

function taskAnswers({ csrf, updateUrlTemplate }) {
  return {
    async saveAnswer(id, value) {
      try {
        const url = updateUrlTemplate.replace('__ID__', id);

        const res = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ answer: value }),
        });

        if (!res.ok) {
          console.error('Answer save failed', res.status);
          showToast(potklTrans('toast.answer_save_error'), 'error');
        } else {
          showToast(potklTrans('toast.answer_save_success'), 'success');
        }
      } catch (e) {
        console.error(e);
        showToast(potklTrans('toast.answer_save_error'), 'error');
      }
    },
  };
}

function callLog({ csrf, storeUrl, initialCalls }) {
  return {
    openCallPanel: false,
    outcome: '',
    note: '',
    loading: false,
    calls: initialCalls || [],
    labels: {
      geen_antwoord: potklTrans('calls.badge_none'),
      gesproken: potklTrans('calls.badge_spoken'),
    },

    async submit() {
      if (!this.outcome) {
        alert('Kies eerst een resultaat.');
        return;
      }

      this.loading = true;
      try {
        const res = await fetch(storeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            outcome: this.outcome,
            note: this.note,
          }),
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.success) {
          throw new Error('Belmoment opslaan mislukt');
        }

        this.calls.unshift(data.call);
        this.outcome = '';
        this.note = '';
        this.openCallPanel = false;

        showToast(potklTrans('toast.call_save_success'), 'success');
      } catch (e) {
        console.error(e);
        showToast(potklTrans('toast.call_save_error'), 'error');
      } finally {
        this.loading = false;
      }
    },
  };
}

function intakePlanner({ csrf, availabilityUrlTemplate }) {
  return {
    open: false,
    loading: false,
    aanvraagId: null,
    updateUrl: null,
    cardEl: null,
    oldValue: null,
    newValue: 'intake',

    date: new Date().toISOString().slice(0,10),
    durationMinutes: 30,
    workDay: { start: '09:00', end: '16:30' },
    slots: [],
    picked: null,

    init() {
      window.addEventListener('open-intake-planner', (e) => {
        const { aanvraagId, updateUrl, cardEl, oldValue, newValue } = e.detail || {};
        this.aanvraagId = aanvraagId;
        this.updateUrl  = updateUrl;
        this.cardEl     = cardEl;
        this.oldValue   = oldValue;
        this.newValue   = newValue || 'intake';
        this.picked     = null;
        this.open       = true;
        this.loadDay();
      });
    },

    close() {
      this.open   = false;
      this.loading = false;
      this.picked = null;
    },

    prettyDate(yyyyMmDd) {
      if (!yyyyMmDd) return '';
      const d = new Date(yyyyMmDd + 'T00:00:00');
      return d.toLocaleDateString('nl-NL', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    },

    slotRange(slot) {
      return `${slot.startLabel} – ${slot.endLabel}`;
    },

    pickSlot(slot) {
      this.picked = slot;
    },

    rebuildSlots() {
      this.loadDay();
    },

    makeLocalDate(dateStr, timeStr) {
      const [y, m, d] = dateStr.split('-').map(Number);
      const [H, M, S = 0] = timeStr.split(':').map(Number);
      return new Date(y, m - 1, d, H, M, S, 0);
    },

    parseIsoLocal(isoLocal) {
      const [d, t] = isoLocal.split('T');
      return this.makeLocalDate(d, t);
    },

    async loadDay() {
      this.slots = [];
      this.picked = null;

      let busy = [];
      try {
        if (availabilityUrlTemplate) {
          const url = availabilityUrlTemplate.replace('__DATE__', this.date);
          const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
          if (res.ok) {
            const data = await res.json().catch(()=>null);
            busy = Array.isArray(data?.busy) ? data.busy : [];
          }
        }
      } catch (e) {
        console.warn('[intakePlanner] availability fetch failed, fallback op local-only slots');
      }

      const dayStart = this.makeLocalDate(this.date, `${this.workDay.start}:00`);
      const dayEnd   = this.makeLocalDate(this.date, `${this.workDay.end}:00`);

      const slots = [];
      for (let t = new Date(dayStart); t < dayEnd; ) {
        const start = new Date(t);
        const end   = new Date(t); end.setMinutes(end.getMinutes() + this.durationMinutes);
        if (end > dayEnd) break;

        const label    = start.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
        const endLabel = end.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });

        const overlapsBusy = busy.some(b => {
          const bStart = this.parseIsoLocal(b.start);
          const bEnd   = this.parseIsoLocal(b.end);
          return start < bEnd && end > bStart;
        });

        const pad = (n) => String(n).padStart(2, '0');

        slots.push({
          date: this.date,

          // Bewaar ook UTC (alleen nodig voor overlap-checks / debugging)
          startUtc: start.toISOString(),
          endUtc:   end.toISOString(),

          // 👇 LOKALE tijd (zonder Z). DIT sturen we later naar de server.
          startLocal: `${this.date}T${pad(start.getHours())}:${pad(start.getMinutes())}:00`,
          endLocal:   `${this.date}T${pad(end.getHours())}:${pad(end.getMinutes())}:00`,

          startLabel: label,
          endLabel,
          label,
          state: overlapsBusy ? 'busy' : 'free'
        });

        t.setMinutes(t.getMinutes() + 30);
      }

      this.slots = slots;
    },

    async confirm() {
      if (!this.picked) return;
      this.loading = true;
      try {
        const tz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Europe/Amsterdam';

        const res = await fetch(this.updateUrl, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            status: 'intake',
            intake_at_local: this.picked.startLocal,
            intake_duration: this.durationMinutes,
            tz
          })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok) throw new Error('Status+intake plannen mislukt');

        // 1) Badge updaten
        if (this.cardEl) {
          const badge = this.cardEl.querySelector('[data-status-badge]');
          if (badge) {
            this.cardEl.dataset.status = 'intake';
            badge.dataset.statusValue = 'intake';
            badge.textContent = (data && data.label) || 'Intake';

            const all = [
              'bg-[#b3e6ff]','text-[#0f6199]',
              'bg-[#C2F0D5]','text-[#20603a]',
              'bg-[#ffdfb3]','text-[#a0570f]',
              'bg-[#ffb3b3]','text-[#8a2a2d]',
              'bg-[#e0d4ff]','text-[#4c2a9b]',
            ];
            badge.classList.remove(...all);
            badge.classList.add('bg-[#ffdfb3]', 'text-[#a0570f]');
          }
        }

        // 2) Intake-panel direct updaten
        if (data && data.intake_html && this.cardEl) {
          const panel = this.cardEl.querySelector(`#intake-panel-${this.aanvraagId}`);
          if (panel) {
            panel.innerHTML = data.intake_html;

            if (window.Alpine && typeof Alpine.initTree === 'function') {
              Alpine.initTree(panel);
            }
          }
        }

        // 3) Logboek direct updaten (zelfde idee als _appendStatusLog)
        if (data && data.log && this.cardEl) {
          const list  = this.cardEl.querySelector('[data-status-log-list]');
          const empty = this.cardEl.querySelector('[data-status-log-empty]');
          if (list) {
            if (empty) empty.style.display = 'none';

            const wrapper = document.createElement('div');
            wrapper.innerHTML = data.log.html.trim();
            const li = wrapper.firstElementChild;
            if (li) list.prepend(li);
          }
        }

        showToast(potklTrans('toast.intake_planned'), 'success');
        this.close();
      } catch (e) {
        console.error(e);
        showToast(potklTrans('toast.intake_plan_error'), 'error');
      } finally {
        this.loading = false;
      }
    }
  }
}

function filesManager({ csrf, uploadUrl, deleteUrlTemplate, initialFiles }) {
  return {
    csrf,
    uploadUrl,
    deleteUrlTemplate,
    files: initialFiles || [],
    dragOver: false,
    uploading: false,
    removingId: null,

    // overlay state
    confirmOpen: false,
    confirmError: '',
    fileToDelete: null,

    iconFor(file) {
      const ext = (file.extension || '').toLowerCase();

      if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) {
        return 'fa-image text-[#215558]';
      }
      if (['pdf'].includes(ext)) {
        return 'fa-file-pdf text-red-500';
      }
      if (['doc','docx','odt','rtf'].includes(ext)) {
        return 'fa-file-word text-blue-500';
      }
      if (['xls','xlsx','ods','csv'].includes(ext)) {
        return 'fa-file-excel text-emerald-600';
      }
      if (['zip','rar','7z','tar','gz'].includes(ext)) {
        return 'fa-file-zipper text-amber-600';
      }
      if (['txt','md','log'].includes(ext)) {
        return 'fa-file-lines text-slate-600';
      }
      if (['ppt','pptx','key'].includes(ext)) {
        return 'fa-file-powerpoint text-orange-500';
      }

      return 'fa-file text-slate-500';
    },

    handleInput(fileList) {
      if (!fileList || !fileList.length) return;
      this.uploadFiles(Array.from(fileList));
    },

    handleDrop(e) {
      this.dragOver = false;
      const dt = e.dataTransfer;
      if (!dt || !dt.files || !dt.files.length) return;
      this.uploadFiles(Array.from(dt.files));
    },

    async uploadFiles(files) {
      if (!files.length || this.uploading) return;

      const form = new FormData();
      files.forEach(f => form.append('files[]', f));

      this.uploading = true;
      try {
        const res = await fetch(this.uploadUrl, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: form,
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !Array.isArray(data.files)) {
          throw new Error('Upload mislukt');
        }

        data.files.forEach(f => this.files.push(f));
        showToast(potklTrans('toast.file_upload_success'), 'success');
      } catch (e) {
        console.error(e);
        showToast(potklTrans('toast.file_upload_error'), 'error');
      } finally {
        this.uploading = false;
      }
    },

    openConfirm(file) {
      if (this.removingId) return;
      this.confirmError = '';
      this.fileToDelete = file;
      this.confirmOpen = true;
    },

    async confirmRemove() {
      if (!this.fileToDelete || this.removingId) return;

      const file = this.fileToDelete;
      this.removingId = file.id;
      this.confirmError = '';

      try {
        const url = this.deleteUrlTemplate.replace('__ID__', file.id);

        const res = await fetch(url, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': this.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data?.success) {
          throw new Error('Verwijderen mislukt');
        }

        this.files = this.files.filter(f => f.id !== file.id);
        this.fileToDelete = null;
        this.confirmOpen = false;

        showToast(potklTrans('toast.file_delete_success'), 'success');
      } catch (e) {
        console.error(e);
        this.confirmError = 'Verwijderen is mislukt. Probeer het nog een keer.';
        showToast(potklTrans('toast.file_delete_error'), 'error');
      } finally {
        this.removingId = null;
      }
    },
  };
}
</script>
@endverbatim

@endsection