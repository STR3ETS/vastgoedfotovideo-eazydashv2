@extends('hub.layouts.app')

@section('content')
  @php
      use App\Models\User;

      $authUser = auth()->user();

      // ✅ Medewerkers = users binnen dezelfde company_id
      $assigneesQuery = User::query();

      if (!empty($authUser?->company_id)) {
          $assigneesQuery->where('company_id', $authUser->company_id);
      } else {
          // fallback (intern)
          $assigneesQuery->whereNull('company_id');
      }

      $assignees = $assigneesQuery
          ->orderBy('name')
          ->get();

      // ✅ Voor JS: map op ID -> naam + avatar_url (met fallback)
      $assigneesById = $assignees->mapWithKeys(function ($u) {
          $avatar = trim((string) $u->avatar_url);

          if ($avatar === '') {
              $avatar = '/assets/eazyonline/memojis/default.webp';
          } else {
              // relatief pad -> leading slash
              if (!preg_match('~^https?://~i', $avatar) && ($avatar[0] ?? '') !== '/') {
                  $avatar = '/' . $avatar;
              }

              // lokaal pad bestaat niet -> fallback
              if (!preg_match('~^https?://~i', $avatar)) {
                  $rel = ltrim($avatar, '/');
                  if (!file_exists(public_path($rel))) {
                      $avatar = '/assets/eazyonline/memojis/default.webp';
                  }
              }
          }

          return [
              $u->id => [
                  'name'   => $u->name,
                  'avatar' => $avatar,
              ],
          ];
      })->all();
  @endphp
  <div
      data-potkl-page
      class="col-span-5 grid grid-cols-4 w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 overflow-hidden"
      x-data="statusDnD({
          csrf: '{{ csrf_token() }}',
          updateUrlTemplate: '{{ route('support.potentiele-klanten.status.update', ['aanvraag' => '__ID__']) }}',
          ownerUpdateUrlTemplate: '{{ route('support.potentiele-klanten.owner.update', ['aanvraag' => '__ID__']) }}',
          assigneesById: @js($assigneesById),
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
          @foreach($assignees as $user)
            @php
                $avatar = $assigneesById[$user->id]['avatar'] ?? '/assets/eazyonline/memojis/default.webp';
            @endphp

            <div
              class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab"
              data-assignee-pill
              draggable="true"
              @dragstart="onOwnerDragStart({{ $user->id }}, @js($user->name), @js($avatar), $event)"
              @dragend="onOwnerDragEnd()"
            >
              <img src="{{ $avatar }}" class="max-h-[80%]">
              <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">{{ $user->name }}</p>
            </div>
          @endforeach
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

              // ✅ ID uit de route: /potentiele-klanten/{aanvraag}
              $routeAanvraagId = null;

              // Meestal heb je $aanvraag vanuit de controller (Route Model Binding)
              if (isset($aanvraag) && is_object($aanvraag) && isset($aanvraag->id)) {
                  $routeAanvraagId = (int) $aanvraag->id;
              } else {
                  // Fallback (voor de zekerheid)
                  $routeParam = request()->route('aanvraag');
                  $routeAanvraagId = is_object($routeParam)
                      ? (int) $routeParam->id
                      : (is_numeric($routeParam) ? (int) $routeParam : null);
              }

              // ✅ Alleen actief als je echt op /{aanvraag} zit
              $initialActiveId = ($routeAanvraagId && $aanvragenCollection->contains('id', $routeAanvraagId))
                  ? $routeAanvraagId
                  : null;

              $choiceMap = [
                  'new'   => __('potentiele_klanten.choices.new'),
                  'renew' => __('potentiele_klanten.choices.renew'),
              ];
          @endphp

          <div
            class="mt-6 grid grid-cols-4 gap-6 flex-1 min-h-0"
            x-data='{
              activeId: @js($initialActiveId),
              selectAanvraag(id, href) {
                this.activeId = Number(id);

                if (href) {
                  history.pushState({ aanvraagId: this.activeId }, "", href);
                }

                const row = this.$el.querySelector(`[data-card-row-id="${this.activeId}"]`);
                if (row) row.scrollIntoView({ block: "nearest", behavior: "smooth" });
              }
            }'
            x-on:potkl-open-aanvraag.window='selectAanvraag($event.detail.id, $event.detail.href)'
            x-init='
              window.addEventListener("popstate", () => {
                const m = window.location.pathname.match(/potentiele-klanten\/(\d+)/);
                activeId = (m && m[1]) ? Number(m[1]) : null;
              });
            '
          >
              {{-- ===================== LEFT LIST ===================== --}}
              <div class="space-y-2 bg-[#f3f8f8] rounded-4xl p-8 h-full min-h-0 overflow-y-auto">
                  @forelse($aanvragenCollection as $aanvraag)
                      @php
                          $rowStatus = $aanvraag->status ?? 'prospect';
                      @endphp

                      <button
                          type="button"
                          data-card-id="{{ $aanvraag->id }}"
                          data-card-row-id="{{ $aanvraag->id }}"
                          data-href="{{ route('support.potentiele-klanten.show', ['aanvraag' => $aanvraag->id]) }}"
                          data-status="{{ $rowStatus }}"
                          @dragover.prevent="onCardDragOver($event)"
                          @dragleave="onCardDragLeave($event)"
                          @drop="onCardDrop($event)"
                          class="w-full text-left pl-5 pr-2 py-4 transition duration-300
                                border-l-4 rounded-tr-4xl rounded-br-4xl cursor-pointer"
                          :class="activeId === {{ $aanvraag->id }}
                              ? 'bg-white border-l-[#0F9B9F]'
                              : 'bg-white border-l-[#215558]/20 hover:bg-gray-50'"
                          @click='selectAanvraag({{ $aanvraag->id }}, $el.dataset.href)'
                      >
                          <div class="flex items-start justify-between gap-4">
                              <div class="w-full flex items-center justify-between">
                                  <div class="w-full">
                                      <p class="text-base font-bold text-[#215558] truncate">
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

                                      <span
                                          class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $badge['class'] }}"
                                          data-status-badge
                                          data-status-value="{{ $status }}"
                                      >
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

                                  <img
                                    data-owner-list-avatar
                                    data-owner-id="{{ $aanvraag->owner_id ?? '' }}"
                                    src="{{ $aanvraag->owner_avatar_url ?? '' }}"
                                    class="w-6 h-6 rounded-full object-cover border border-gray-200"
                                    @if(empty($aanvraag->owner_avatar_url)) style="display:none;" @endif
                                    alt=""
                                  >
                              </div>
                          </div>
                      </button>
                  @empty
                      <div>
                          <p class="text-xs font-semibold text-[#215558]/50">
                              Nog geen potentiële klanten gevonden.
                          </p>
                      </div>
                  @endforelse
              </div>

              {{-- ===================== RIGHT DETAIL ===================== --}}
              <div class="col-span-3 min-h-0 bg-[#f3f8f8] rounded-4xl overflow-hidden flex flex-col">
                  <div class="p-8 flex-1 min-h-0 overflow-y-auto">
                      <div x-show="!activeId" x-cloak class="flex items-center gap-4">
                        <span class="text-4xl">👈</span>
                        <p class="text-base font-bold text-[#215558]/80 mt-1">
                          Selecteer een potentiële klant om te beginnen.
                        </p>
                      </div>
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
                      <div class="flex items-center gap-4">
                        <span class="text-4xl">👈</span>
                        <p class="text-base font-bold text-[#215558]/80 mt-1">
                            Selecteer een potentiële klant om te beginnen.
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
    assigneesById: @js($assigneesById),
  })"
      x-init="init()"
      x-show="open"
      x-transition.opacity
      class="fixed inset-0 z-[9999] flex items-center justify-center"
      style="display:none;">

    <!-- Achtergrond -->
    <div class="absolute inset-0 bg-black/40" @click="close()"></div>

    <!-- Modal -->
    <div class="relative w-[900px] max-w-[95vw] bg-white rounded-4xl border border-[#215558]/20 overflow-hidden">
      <div class="flex items-center justify-between p-8">
        <h3 class="text-lg font-black text-[#215558]">
          Plan een intakegesprek in
        </h3>
        <button class="w-8 h-8 rounded-full bg-[#215558]/10 hover:bg-[#215558]/20 transition duration-300 flex items-center justify-center cursor-pointer"
                @click="close()">
          <i class="fa-solid fa-xmark text-[#215558]"></i>
        </button>
      </div>

      <hr class="border-gray-200">

      <div class="p-8 grid grid-cols-3 gap-8">
        <div class="col-span-3 p-4 rounded-3xl bg-[#f3f8f8]">
          <span class="w-fit px-2.5 py-0.5 mb-2 font-semibold text-purple-700 bg-purple-200 text-[11px] flex items-center gap-2 rounded-full">
            <i class="fa-solid fa-sparkle fa-xs"></i>
            Samenvatting door AI
          </span>
          <div class="mt-3" x-show="summaryLoading">
            <p class="text-xs font-semibold text-[#215558]/70">
              <i class="fa-solid fa-spinner fa-spin mr-1"></i> Beschikbaarheid ophalen…
            </p>
          </div>
          <div class="space-y-2" x-show="!summaryLoading">
            <p class="text-xs font-semibold text-[#215558]/80 leading-relaxed" x-text="summaryText"></p>
            <div class="mt-2" x-show="dayTimes.length">
              <div class="grid grid-cols-8 gap-1">
                <template x-for="t in dayTimes" :key="t">
                  <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-white text-center border border-[#215558]/15 text-[#215558]"
                        x-text="t"></span>
                </template>
              </div>
            </div>
          </div>
        </div>
        <!-- Linker kolom: datum -->
        <div class="grid h-fit">
          <div>
            <label class="text-[11px] font-semibold opacity-50 text-[#215558]">
              Wanneer
            </label>
            <input type="date"
                x-model="date"
                :min="today"
                @change="loadDay()"
                class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-[#215558] outline-none focus:border-[#3b8b8f] transition" />
          </div>
          <div>
            <label class="text-[11px] font-semibold opacity-50 text-[#215558]">
              Hoelang
            </label>
            <select x-model.number="durationMinutes"
                    @change="rebuildSlots()"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-[#215558] outline-none focus:border-[#3b8b8f] transition">
              <option value="60">{{ __('potentiele_klanten.intake_modal.duration_60') }}</option>
            </select>
          </div>
        </div>

        <!-- Rechter kolommen: mini agenda -->
        <div class="col-span-2">
          <div class="flex items-center justify-between mb-1">
            <p class="text-[11px] font-semibold opacity-50 text-[#215558]">
              <span x-text="prettyDate(date)"></span>
            </p>
          </div>

          <div class="grid grid-cols-3 gap-2 max-h-[340px] overflow-y-auto pr-1">
            <template x-for="slot in slots" :key="slot.startLocal">
              <button type="button"
                class="text-sm p-4 rounded-xl transition cursor-pointer text-left"
                :class="{
                  // FREE (groen) alleen als niet-picked
                  'bg-emerald-100 text-[#215558]': slot.state === 'free' && (!picked || picked.startLocal !== slot.startLocal),
                  'hover:bg-emerald-200':                               slot.state === 'free' && (!picked || picked.startLocal !== slot.startLocal),

                  // BUSY (rood)
                  'bg-red-100 text-red-700 opacity-60 cursor-not-allowed': slot.state === 'busy',

                  // PICKED (cyan)
                  'bg-cyan-100 text-cyan-700': picked && picked.startLocal === slot.startLocal,
                }"
                :disabled="slot.state !== 'free'"
                @click="pickSlot(slot)">
                <div class="font-semibold" x-text="slot.label"></div>
                <div class="text-[11px] opacity-70" x-text="slotRange(slot)"></div>
              </button>
            </template>
          </div>

          <div class="mt-6 flex items-center justify-end gap-2">
            <button type="button"
                    class="w-fit px-3 py-2 cursor-pointer text-gray-700 font-semibold text-sm bg-gray-100 hover:bg-gray-200 transition duration-300 w-full rounded-full text-center"
                    @click="close()">{{ __('potentiele_klanten.intake_modal.cancel') }}</button>

            <button type="button"
                    class="w-fit px-3 py-2 cursor-pointer text-white font-semibold text-sm bg-[#0F9B9F] hover:bg-[#215558] transition duration-300 w-full rounded-full text-center disabled:opacity-50 disabled:cursor-not-allowed"
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

function statusDnD({ csrf, updateUrlTemplate, labelsByValue, statusCounts, statusCountTexts, ownerUpdateUrlTemplate = null, assigneesById = {}, }) {
  return {
    draggingValue: null,
    draggingLabel: null,
    statusCounts: statusCounts || {},
    statusCountTexts: statusCountTexts || {
      singular: ':count project',
      plural: ':count projecten',
    },
    assigneesById: assigneesById || {},

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

    draggingOwnerId: null,
    draggingOwnerName: null,
    draggingOwnerAvatar: null,

    onOwnerDragStart(id, name, avatar, event) {
      this.draggingOwnerId = id;
      this.draggingOwnerName = name;
      this.draggingOwnerAvatar = avatar;

      if (event && event.dataTransfer) {
        event.dataTransfer.setDragImage(getTransparentDragImage(), 0, 0);
      }
    },

    onOwnerDragEnd() {
      this.draggingOwnerId = null;
      this.draggingOwnerName = null;
      this.draggingOwnerAvatar = null;
    },

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

      if (dragging === 'contact') {
        const ownerId = (card?.dataset?.ownerId || '').trim();

        // Alleen blokkeren voor Prospect -> Contact zonder owner
        if (String(status).toLowerCase() === 'prospect' && !ownerId) {
          return false;
        }

        return true;
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

    _updateAllInstances(id, finalValue, data) {
      const els = document.querySelectorAll(`[data-card-id="${id}"]`);
      if (!els.length) return;

      els.forEach(el => {
        this._updateBadge(el, finalValue);
        this._syncStatusProgress(el, finalValue);

        if (data && data.log) {
          this._appendStatusLog(el, data.log);
        }

        // ✅ intakeDone DOM sync als server dit terugstuurt
        if (data && typeof data.intake_done !== 'undefined') {
          el.dataset.intakeDone = data.intake_done ? '1' : '0';
        }

        if (finalValue === 'lead') {
          el.dataset.intakeDone = '1';
        }
      });

      // ✅ Filters opnieuw toepassen
      this.filterCards();

      // ✅ NIEUW: laat de taken-component mee-updaten
      window.dispatchEvent(new CustomEvent('potkl-card-status-changed', {
        detail: {
          id: String(id),
          newValue: finalValue,
          data: data || {}
        }
      }));
    },

    _getOwnerData(ownerId) {
      if (!ownerId) return null;

      const map = this.assigneesById || {};
      const key = String(ownerId);
      const numericKey = parseInt(key, 10);

      const base =
        map[key] ||
        (Number.isFinite(numericKey) ? map[numericKey] : null) ||
        null;

      if (!base) return null;

      return {
        id: ownerId,
        name: base.name || 'Onbekend',
        avatar: base.avatar || '/assets/eazyonline/memojis/default.webp',
      };
    },

    _updateOwnerUi(id, ownerId) {
      const owner = this._getOwnerData(ownerId);

      // 🔍 Pak alleen de detail-kaart (rechts): die heeft data-intake-done
      const cards = document.querySelectorAll(
        `[data-card-id="${id}"][data-intake-done]`
      );
      if (!cards.length) return;

      cards.forEach(card => {
        card.dataset.ownerId = ownerId || '';

        // Alleen de badge rechts, NIET de lijst links
        const badge = card.querySelector('[data-owner-badge]');
        if (!badge) return;

        const avatarImg   = badge.querySelector('[data-owner-badge-avatar]');
        const nameEl      = badge.querySelector('[data-owner-badge-name]');
        const placeholder = badge.querySelector('[data-owner-badge-placeholder]');

        if (owner) {
          // ✅ Owner ingevuld
          if (avatarImg) {
            avatarImg.src = owner.avatar;
            avatarImg.classList.remove('hidden');
          }

          if (nameEl) {
            nameEl.textContent = owner.name;
            nameEl.classList.remove('text-[#215558]/60');
            nameEl.classList.add('text-[#215558]');
          }

          if (placeholder) {
            placeholder.style.display = 'none';
          }

          badge.classList.remove('bg-[#f3f8f8]', 'border-dashed', 'border-[#215558]/30');
          badge.classList.add('bg-white', 'border-[#215558]/25');
        } else {
          // ❌ Geen owner (meer)
          if (avatarImg) {
            avatarImg.classList.add('hidden');
          }

          if (nameEl) {
            nameEl.textContent = ownerId
              ? 'Nog niet toegewezen'
              : 'Niet toegewezen';
            nameEl.classList.remove('text-[#215558]');
            nameEl.classList.add('text-[#215558]/60');
          }

          if (placeholder) {
            placeholder.style.display = '';
          }

          badge.classList.remove('bg-white', 'border-[#215558]/25');
          badge.classList.add('bg-[#f3f8f8]', 'border-dashed', 'border-[#215558]/30');
        }
      });
    },

    init() {
      window.addEventListener('dragover', (e) => {
        if (!this.dragGhost) return;
        this._updateGhostPosition(e.clientX, e.clientY);
      });

      // ✅ NIEUW: externe soft updates (bijv. vanuit intakePlanner)
      window.addEventListener('potkl-status-soft-update', (e) => {
        const { id, oldValue, newValue, data } = e.detail || {};
        if (!id || !newValue) return;

        if (oldValue && oldValue !== newValue) {
          this._updateCounts(oldValue, newValue);
        }

        this._updateAllInstances(String(id), newValue, data || {});
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
      if (['intake','dead','lead','contact'].includes(value)) {
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

    // ✅ Sync progress-balk + labels in card (Prospect/Contact/Intake/Lead)
    _syncStatusProgress(cardEl, statusValue) {
      const wrapper = cardEl.querySelector('[data-status-progress-wrapper]');
      if (!wrapper) return;

      const order = ['prospect', 'contact', 'intake', 'lead'];
      const idx = order.indexOf(statusValue);
      const pct = idx === -1 ? 0 : ((idx + 1) / order.length) * 100;

      const bar = wrapper.querySelector('[data-status-progress-bar]');
      if (bar) {
        bar.style.width = `${pct}%`;
      }

      wrapper.querySelectorAll('[data-status-label]').forEach(labelEl => {
        const v = labelEl.dataset.statusLabel;
        const isActive = v === statusValue;

        labelEl.classList.toggle('opacity-50', !isActive);

        // optioneel accent (past bij je style)
        if (isActive) {
          labelEl.classList.add('text-[#0F9B9F]');
        } else {
          labelEl.classList.remove('text-[#0F9B9F]');
        }
      });
    },

    _updateBadge(cardEl, newValue) {
      // ✅ altijd data-status updaten (ook als er geen badge is)
      cardEl.dataset.status = newValue;

      // ✅ altijd progress syncen
      this._syncStatusProgress(cardEl, newValue);

      const badge = cardEl.querySelector('[data-status-badge]');
      if (!badge) return;

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

      // ✅ NIEUW: progress-balk + labels ook updaten
      this._syncStatusProgress(cardEl, newValue);
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
      if (!card) return;

      // reset hover border
      card.classList.remove('border-[#0F9B9F]');
      card.classList.add('border-gray-200');

      // ✅ OWNER DROP heeft voorrang op status
      if (this.draggingOwnerId && ownerUpdateUrlTemplate) {
        const id  = card.dataset.cardId;
        const url = ownerUpdateUrlTemplate.replace('__ID__', id);

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
            body: JSON.stringify({ owner_id: this.draggingOwnerId })
          });

          const data = await res.json().catch(() => null);

          if (!res.ok || !data?.success) {
            showToast(data?.message || 'Owner kon niet worden bijgewerkt.', 'error');
            return;
          }

          const newOwnerId = data?.owner_id ?? this.draggingOwnerId ?? null;

          // 🔥 lijst + rechter badge direct in sync
          this._updateOwnerUi(id, newOwnerId);

          window.dispatchEvent(new CustomEvent('potkl-owner-updated', {
            detail: { id: String(id), ownerId: newOwnerId }
          }));

          showToast('Gelukt! De medewerker is gekoppeld aan de aanvraag.', 'success');
        } catch (err) {
          console.error(err);
          showToast('Owner kon niet worden bijgewerkt.', 'error');
        } finally {
          this.onOwnerDragEnd();
        }

        return; // ⛔️ stop status-logica
      }

      // ===========================
      // ✅ STATUS DROP (jouw logica)
      // ===========================

      if (!this.draggingValue) return;

      const id  = card.dataset.cardId;
      const url = updateUrlTemplate.replace('__ID__', id);

      const badge = card.querySelector('[data-status-badge]');

      // ✅ fallback naar data-status als badge ontbreekt (detail view)
      const oldValue = (badge && badge.dataset.statusValue)
        ? badge.dataset.statusValue
        : (card.dataset.status || null);

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
        return;
      }

      // Als status niet verandert
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
        // Owner achterhalen (eerst van deze card, anders van detail-card)
        let ownerId = card.dataset.ownerId || null;
        if (!ownerId) {
          const detailCard = document.querySelector(
            `[data-card-id="${id}"][data-owner-id]`
          );
          if (detailCard) {
            ownerId = detailCard.dataset.ownerId || null;
          }
        }

        window.dispatchEvent(new CustomEvent('open-intake-planner', {
          detail: {
            aanvraagId: id,
            ownerId,
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

        this._updateAllInstances(id, finalValue, data);

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

        // ✅ update alle instanties (list + detail)
        const id = card.dataset.cardId;
        this._updateAllInstances(id, finalValue, data);

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

function aanvraagComments({ csrf, storeUrl, fetchUrl, initialComments, mentionables = [] }) {
  return {
    csrf, storeUrl, fetchUrl,

    comments: Array.isArray(initialComments) ? initialComments : [],
    threads: [],

    indentPx: 18,
    loading: false,

    reply: {
      parentId: null,
      parentUserName: '',
    },

    sortMode: 'recent',

    _index: {},

    // editor state (contenteditable -> plain text)
    body: '',
    mentionables: mentionables || [],

    mention: {
      open: false,
      query: '',
      index: 0,
      range: null,
    },

    pollMs: 4000,
    _timer: null,

    init() {
      this._reindexAndRebuild();
      this.poll(true);
      this._timer = setInterval(() => this.poll(false), this.pollMs);

      document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') this.poll(true);
      });

      this.$nextTick(() => {
        if (this.$refs.editor) this.$refs.editor.innerHTML = '';
        this._syncBodyFromEditor();
      });
    },

    destroy() {
      if (this._timer) clearInterval(this._timer);
      this._timer = null;
    },

    _reindexAndRebuild() {
      const idx = {};
      for (const c of (this.comments || [])) idx[String(c.id)] = c;
      this._index = idx;
      this._buildThreads();
    },

    _buildThreads() {
      const list = Array.isArray(this.comments) ? this.comments.slice() : [];
      if (!list.length) {
        this.threads = [];
        return;
      }

      const byId = {};
      for (const c of list) byId[String(c.id)] = c;

      const children = {};
      const roots = [];

      for (const c of list) {
        const pid = c.parent_id ? String(c.parent_id) : null;

        if (!pid || !byId[pid]) {
          roots.push(c);
        } else {
          (children[pid] ||= []).push(c);
        }
      }

      // sort roots
      if (this.sortMode === 'oldest') {
        roots.sort((a, b) => Number(a.id) - Number(b.id));
      } else {
        roots.sort((a, b) => Number(b.id) - Number(a.id));
      }

      // children altijd oldest-first (leest lekker)
      for (const k of Object.keys(children)) {
        children[k].sort((a, b) => Number(a.id) - Number(b.id));
      }

      const threads = [];
      const walk = (parentId, depth, out) => {
        const kids = children[String(parentId)] || [];
        for (const child of kids) {
          out.push({ comment: child, depth });
          walk(child.id, depth + 1, out);
        }
      };

      for (const r of roots) {
        const nodes = [];
        walk(r.id, 1, nodes); // ✅ direct replies hebben depth 1
        threads.push({ root: r, nodes });
      }

      this.threads = threads;
    },

    parentOf(c) {
      if (!c || !c.parent_id) return null;
      return this._index[String(c.parent_id)] || null;
    },

    parentName(c) {
      const p = this.parentOf(c);
      return (p && p.user_name) ? p.user_name : 'Onbekend';
    },

    startReply(c) {
      this.reply.parentId = Number(c.id);
      this.reply.parentUserName = c.user_name || 'Onbekend';
      this.$nextTick(() => this.$refs.editor?.focus());
    },

    cancelReply() {
      this.reply.parentId = null;
      this.reply.parentUserName = '';
    },

    _escapeHtml(s) {
      return String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
    },

    _escapeRegExp(s) {
      return String(s ?? '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    },

    renderBody(text) {
      // plain text -> safe HTML + linebreaks + mention highlight
      let html = this._escapeHtml(text ?? '').replace(/\n/g, '<br>');

      // highlight @Name (boundary-safe)
      for (const u of (this.mentionables || [])) {
        if (!u?.name) continue;
        const name = String(u.name);
        const re = new RegExp(`@${this._escapeRegExp(name)}(?![\\p{L}\\p{N}_])`, 'giu');
        html = html.replace(re, `<span class="text-blue-500 bg-blue-100 rounded-full py-0.5 px-1 text-[12px] font-bold">@${this._escapeHtml(name)}</span>`);
      }
      return html;
    },

    _syncBodyFromEditor() {
      const editor = this.$refs.editor;
      this.body = (editor?.innerText ?? '').replace(/\u00A0/g, ' ');
    },

    onEditorInput() {
      this._syncBodyFromEditor();
      this._maybeOpenMention();
    },

    onEditorCaretChange() {
      this._maybeOpenMention();
    },

    _textBeforeCaret() {
      const sel = window.getSelection();
      if (!sel || !sel.rangeCount) return '';
      const r = sel.getRangeAt(0);
      if (!this.$refs.editor || !this.$refs.editor.contains(r.endContainer)) return '';

      const pre = r.cloneRange();
      pre.selectNodeContents(this.$refs.editor);
      pre.setEnd(r.endContainer, r.endOffset);
      return pre.toString();
    },

    _maybeOpenMention() {
      const before = this._textBeforeCaret();
      const m = before.match(/@([^\s@]{0,40})$/);
      if (!m) {
        this.mention.open = false;
        this.mention.query = '';
        this.mention.index = 0;
        return;
      }

      this.mention.open = true;
      this.mention.query = m[1] || '';
      this.mention.index = 0;
    },

    mentionResults() {
      if (!this.mention.open) return [];
      const q = (this.mention.query || '').toLowerCase().trim();
      const list = this.mentionables || [];
      if (!q) return list.slice(0, 8);
      return list.filter(u => (u.name || '').toLowerCase().includes(q)).slice(0, 8);
    },

    pickMention(u) {
      // simpele insert: voeg spatie toe na naam
      const editor = this.$refs.editor;
      if (!editor) return;

      const text = editor.innerText.replace(/\u00A0/g, ' ');
      const newText = text.replace(/@([^\s@]{0,40})$/, `@${u.name} `);

      editor.innerText = newText;
      this._syncBodyFromEditor();

      this.mention.open = false;
      this.mention.query = '';
      this.mention.index = 0;

      // caret naar einde
      this.$nextTick(() => {
        const range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
      });
    },

    onEditorKeydown(e) {
      // mention navigation
      if (this.mention.open) {
        if (e.key === 'ArrowDown') { e.preventDefault(); this.mention.index++; return; }
        if (e.key === 'ArrowUp') { e.preventDefault(); this.mention.index = Math.max(0, this.mention.index - 1); return; }
        if (e.key === 'Escape') { this.mention.open = false; return; }
        if (e.key === 'Enter') {
          e.preventDefault();
          const res = this.mentionResults();
          const pick = res[this.mention.index] || res[0];
          if (pick) this.pickMention(pick);
          return;
        }
      }

      // submit on Enter (no shift)
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.submit();
      }
    },

    _afterId() {
      const ids = (this.comments || []).map(c => Number(c.id) || 0);
      return ids.length ? Math.max(...ids) : 0;
    },

    async poll(force) {
      if (document.visibilityState !== 'visible') return;

      try {
        const afterId = this._afterId();
        const url = afterId ? `${this.fetchUrl}?after_id=${afterId}` : this.fetchUrl;

        const res = await fetch(url, {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.success) return;

        const incoming = data.comments || [];
        if (!incoming.length) return;

        for (const c of incoming) {
          if (!this.comments.some(x => Number(x.id) === Number(c.id))) {
            this.comments.unshift(c);
          }
        }

        this._reindexAndRebuild();
      } catch (e) {
        console.error(e);
      }
    },

    async submit() {
      const text = (this.body || '').trim();
      if (!text) return;

      this.loading = true;
      try {
        const res = await fetch(this.storeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({
            body: text,
            parent_id: this.reply.parentId || null,
          }),
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.success) throw new Error('Comment opslaan mislukt');

        this.comments.unshift(data.comment);
        this._reindexAndRebuild();
        this.cancelReply();

        // reset editor
        this.body = '';
        if (this.$refs.editor) this.$refs.editor.innerHTML = '';
        this.mention.open = false;

      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
  };
}

function intakePlanner({ csrf, availabilityUrlTemplate, assigneesById = {} }) {
  return {
    open: false,
    loading: false,
    aanvraagId: null,
    updateUrl: null,
    cardEl: null,
    oldValue: null,
    newValue: 'intake',
    ownerId: null,
    assigneesById: assigneesById || {},
    summaryLoading: false,
    summaryText: '',
    dayTimes: [],
    dayFreeCount: 0,

    today: new Date().toISOString().slice(0, 10),
    date:  new Date().toISOString().slice(0, 10),

    // ✅ Altijd hele uren
    durationMinutes: 60,

    // ✅ 09:00 t/m 17:00
    workDay: { start: '09:00', end: '17:00' },

    slots: [],
    picked: null,

    init() {
      window.addEventListener('open-intake-planner', (e) => {
        const { aanvraagId, updateUrl, cardEl, oldValue, newValue, ownerId } = e.detail || {};

        this.aanvraagId = aanvraagId;
        this.updateUrl  = updateUrl;
        this.cardEl     = cardEl;
        this.oldValue   = oldValue;
        this.newValue   = newValue || 'intake';
        this.ownerId    = ownerId || null;

        // Altijd vanaf vandaag
        this.date   = this.today;
        this.picked = null;
        this.open   = true;
        this.loadDay();
      });
    },

    close() {
      this.open    = false;
      this.loading = false;
      this.picked  = null;
    },

    prettyDate(yyyyMmDd) {
      if (!yyyyMmDd) return '';
      const d = new Date(yyyyMmDd + 'T00:00:00');
      return d.toLocaleDateString('nl-NL', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      });
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

    ownerMeta() {
      if (!this.ownerId) return null;

      const map = this.assigneesById || {};
      const key = String(this.ownerId);
      const numericKey = parseInt(key, 10);

      const base =
        map[key] ||
        (Number.isFinite(numericKey) ? map[numericKey] : null) ||
        null;

      if (!base) {
        return { name: 'Onbekend', avatar: '/assets/eazyonline/memojis/default.webp' };
      }

      return {
        name: base.name || 'Onbekend',
        avatar: base.avatar || '/assets/eazyonline/memojis/default.webp',
      };
    },

    _getWeekDates() {
      // vanaf vandaag t/m zondag
      const start = new Date(this.today + 'T00:00:00');
      const dow = start.getDay(); // 0=zo ... 6=za
      const daysToSunday = (7 - dow) % 7;

      const dates = [];
      for (let i = 0; i <= daysToSunday; i++) {
        const d = new Date(start);
        d.setDate(d.getDate() + i);
        dates.push(d.toISOString().slice(0, 10));
      }
      return dates;
    },

    _prettyShort(yyyyMmDd) {
      const d = new Date(yyyyMmDd + 'T00:00:00');
      return d.toLocaleDateString('nl-NL', { weekday: 'long', day: 'numeric', month: 'short' });
    },

    _computeFreeLabelsForDate(dateStr, busy) {
      const dayStart = this.makeLocalDate(dateStr, `${this.workDay.start}:00`);
      const dayEnd   = this.makeLocalDate(dateStr, `${this.workDay.end}:00`);

      const now = new Date();
      const isToday = dateStr === this.today;

      const labels = [];

      for (let t = new Date(dayStart); t < dayEnd; ) {
        const start = new Date(t);
        const end   = new Date(t);
        end.setMinutes(end.getMinutes() + this.durationMinutes);
        if (end > dayEnd) break;

        const isPast = isToday && end <= now;

        const overlapsBusy = (busy || []).some(b => {
          const bStart = this.parseIsoLocal(b.start);
          const bEnd   = this.parseIsoLocal(b.end);
          return start < bEnd && end > bStart;
        });

        if (!isPast && !overlapsBusy) {
          labels.push(start.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' }));
        }

        t.setMinutes(t.getMinutes() + this.durationMinutes);
      }

      return labels;
    },

    async loadWeekSummary() {
      this.weekRows = [];

      if (!this.ownerId) {
        this.summaryText = 'Koppel een medewerker om direct beschikbaarheid te zien.';
        return;
      }

      this.summaryLoading = true;
      this.summaryText = '';
      this.weekRows = [];

      try {
        const dates = this._getWeekDates();
        let totalFree = 0;

        for (const d of dates) {
          let busy = [];

          try {
            const base = availabilityUrlTemplate.replace('__DATE__', d);
            const url  = new URL(base, window.location.origin);

            // owner filter (jij gebruikt dit al in loadDay)
            url.searchParams.set('owner_id', this.ownerId);

            const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            if (res.ok) {
              const data = await res.json().catch(() => null);
              busy = Array.isArray(data?.busy) ? data.busy : [];
            }
          } catch (e) {
            busy = [];
          }

          const freeLabels = this._computeFreeLabelsForDate(d, busy);
          totalFree += freeLabels.length;

          const show = freeLabels.slice(0, 4).join(', ');
          const extra = freeLabels.length > 4 ? ` +${freeLabels.length - 4}` : '';

          this.weekRows.push({
            date: d,
            label: this._prettyShort(d) + ': ',
            text: freeLabels.length
              ? `${freeLabels.length} plek(ken) (${show}${extra})`
              : 'geen plekken',
          });
        }

        const o = this.ownerMeta();
        const firstName = o?.name ? o.name.split(' ')[0] : 'De medewerker';

        this.summaryText =
          totalFree > 0
            ? `${firstName} is gekoppeld aan de aanvraag. Deze week zijn er nog ${totalFree} beschikbare uurblokken.`
            : `${firstName} is gekoppeld aan de aanvraag, maar deze week lijkt volgepland.`;
      } finally {
        this.summaryLoading = false;
      }
    },

    updateDaySummaryFromSlots() {
      this.dayTimes = [];
      this.dayFreeCount = 0;

      if (!this.ownerId) {
        this.summaryText = 'Geen medewerker gekoppeld. Koppel eerst iemand om beschikbaarheid te zien.';
        this.summaryLoading = false;
        return;
      }

      const free = (this.slots || []).filter(s => s.state === 'free');
      this.dayFreeCount = free.length;

      // Welke tijden (als ranges)
      this.dayTimes = free.map(s => this.slotRange(s));

      const o = this.ownerMeta();
      const firstName = o?.name ? o.name.split(' ')[0] : 'De medewerker';

      if (!free.length) {
        this.summaryText = `${firstName} is gekoppeld aan de aanvraag, maar deze datum zit vol.`;
      } else {
        this.summaryText = `${firstName} is gekoppeld aan de aanvraag. Er zijn nog ${free.length} beschikbare tijdsblokken op ${this.prettyDate(this.date)}.`;
      }

      this.summaryLoading = false;
    },

    async loadDay() {
      // 🔒 Nooit terug in de tijd
      if (this.date < this.today) {
        this.date = this.today;
      }

      this.summaryLoading = true;

      this.slots  = [];
      this.picked = null;

      let busy = [];
      try {
        if (availabilityUrlTemplate) {
          const base = availabilityUrlTemplate.replace('__DATE__', this.date);
          const url  = new URL(base, window.location.origin);

          // 👤 Owner-specifieke beschikbaarheid
          if (this.ownerId) {
            url.searchParams.set('owner_id', this.ownerId);
          }

          const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
          });

          if (res.ok) {
            const data = await res.json().catch(() => null);
            busy = Array.isArray(data?.busy) ? data.busy : [];
          }
        }
      } catch (e) {
        console.warn('[intakePlanner] availability fetch failed, fallback op local-only slots');
      }

      const dayStart = this.makeLocalDate(this.date, `${this.workDay.start}:00`);
      const dayEnd   = this.makeLocalDate(this.date, `${this.workDay.end}:00`);

      const todayStr = this.today;
      const now      = new Date();

      const slots = [];

      // ✅ Alleen hele uren: 09–10, 10–11, …, 16–17
      for (let t = new Date(dayStart); t < dayEnd; ) {
        const start = new Date(t);
        const end   = new Date(t);
        end.setMinutes(end.getMinutes() + this.durationMinutes);
        if (end > dayEnd) break;

        const label    = start.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });
        const endLabel = end.toLocaleTimeString('nl-NL', { hour: '2-digit', minute: '2-digit' });

        const overlapsBusy = busy.some(b => {
          const bStart = this.parseIsoLocal(b.start);
          const bEnd   = this.parseIsoLocal(b.end);
          return start < bEnd && end > bStart;
        });

        // 🔒 Slots in het verleden blokkeren
        let isPast = false;
        if (this.date < todayStr) {
          isPast = true;
        } else if (this.date === todayStr && end <= now) {
          isPast = true;
        }

        const pad = (n) => String(n).padStart(2, '0');

        slots.push({
          date: this.date,

          startUtc: start.toISOString(),
          endUtc:   end.toISOString(),

          startLocal: `${this.date}T${pad(start.getHours())}:${pad(start.getMinutes())}:00`,
          endLocal:   `${this.date}T${pad(end.getHours())}:${pad(end.getMinutes())}:00`,

          startLabel: label,
          endLabel,
          label,
          state: (overlapsBusy || isPast) ? 'busy' : 'free',
        });

        // ⏩ per uur opschuiven
        t.setMinutes(t.getMinutes() + this.durationMinutes);
      }

      this.slots = slots;
      this.updateDaySummaryFromSlots();
    },

    async confirm() {
      if (!this.picked) return;
      this.loading = true;

      const keepStatus = String(
        this.oldValue || this.cardEl?.dataset?.status || 'contact'
      ).toLowerCase();

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
            status: keepStatus,
            intake_at_local: this.picked.startLocal,
            intake_duration: this.durationMinutes,
            tz,
          }),
        });

        const data = await res.json().catch(() => null);
        if (!res.ok) throw new Error('Intake plannen mislukt');

        if (data && data.intake_html && this.cardEl) {
          const panel = this.cardEl.querySelector(`#intake-panel-${this.aanvraagId}`);
          if (panel) {
            panel.innerHTML = data.intake_html;

            if (window.Alpine && typeof Alpine.initTree === 'function') {
              Alpine.initTree(panel);
            }
          }
        }

        window.dispatchEvent(new CustomEvent('potkl-status-soft-update', {
          detail: {
            id: this.aanvraagId,
            oldValue: keepStatus,
            newValue: keepStatus,
            data,
          },
        }));

        showToast(potklTrans('toast.intake_planned'), 'success');
        this.close();
      } catch (e) {
        console.error(e);
        showToast(potklTrans('toast.intake_plan_error'), 'error');
      } finally {
        this.loading = false;
      }
    },
  };
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

function aanvraagTasks(config) {
  const {
    csrf,
    updateUrl,
    statusUpdateUrl = null,

    initial = {},
    cardId,
    initialStatus = 'prospect',
    initialIntakeDone = false,

    initialOwnerId = '',
  } = config || {};

  return {
    tasks: { ...initial },
    loadingTypes: {},

    cardId: String(cardId || ''),
    currentStatus: String(initialStatus || 'prospect').toLowerCase(),
    intakeDone: !!initialIntakeDone,

    ownerId: String(initialOwnerId || '').trim(),

    init() {
      // ✅ Sync vanuit drag/drop of andere status-updates
      window.addEventListener('potkl-card-status-changed', (e) => {
        const { id, newValue, data } = e.detail || {};
        if (!id || String(id) !== this.cardId) return;

        if (newValue) {
          this.currentStatus = String(newValue).toLowerCase();
        }

        if (data && typeof data.intake_done !== 'undefined') {
          this.intakeDone = !!data.intake_done;
        }
      });

      // ✅ NIEUW: owner live sync
      window.addEventListener('potkl-owner-updated', (e) => {
        const { id, ownerId } = e.detail || {};
        if (!id || String(id) !== this.cardId) return;
        this.ownerId = ownerId ? String(ownerId) : null;
      });
    },

    getOwnerId() {
      if (this.ownerId) return this.ownerId;

      const detailCard = document.querySelector(
        `[data-card-id="${this.cardId}"][data-owner-id]`
      );
      const domOwner = detailCard?.dataset?.ownerId || null;
      this.ownerId = domOwner || null;
      return this.ownerId;
    },

    hasOwner() {
      return !!this.getOwnerId();
    },

    async setUiStatus(type, newStatus) {
      const ns = String(newStatus || '').toLowerCase();

      if (type === 'status_to_contact' && this.currentStatus === 'prospect' && ns === 'contact' && !this.hasOwner()) {
        showToast('Koppel eerst een medewerker (owner) voordat je naar Contact gaat.', 'error');
        return;
      }

      return this.setStatus(ns);
    },

    /**
     * ✅ Zichtbaarheid per status
     * - Prospect: GEEN echte taken tonen (lost jouw issue op)
     * - Contact: bespreken + intake plannen
     * - Intake: intake voeren
     * - Lead/Dead: niets in deze module
     *
     * UI-only status taken (als je ze render't in Blade):
     * - status_to_contact: zichtbaar bij prospect
     * - status_to_intake: zichtbaar bij contact als call + schedule done
     * - status_to_lead: zichtbaar bij intake als conduct done
     */
    isVisible(type) {
      // UI-only status actions
      if (type === 'status_to_contact') {
        return this.currentStatus === 'prospect';
      }

      if (type === 'status_to_intake') {
        return this.currentStatus === 'contact'
          && !!this.tasks.call_customer
          && !!this.tasks.schedule_intake;
      }

      if (type === 'status_to_lead') {
        return this.currentStatus === 'intake'
          && !!this.tasks.conduct_intake;
      }

      // Echte taken
      if (type === 'call_customer' || type === 'schedule_intake') {
        return this.currentStatus === 'contact';
      }

      if (type === 'conduct_intake') {
        return this.currentStatus === 'intake';
      }

      // ❌ Niet tonen in deze module
      if (type === 'convert_to_project') {
        return false;
      }

      return false;
    },

    /**
     * ✅ Afvink-regels
     */
    canCheck(type) {
      // UI-only types hebben geen checkbox-logica
      if (['status_to_contact','status_to_intake','status_to_lead'].includes(type)) {
        return false;
      }

      switch (type) {
        case 'call_customer':
        case 'schedule_intake':
          return this.currentStatus === 'contact';

        case 'conduct_intake':
          return this.currentStatus === 'intake';

        // ❌ buiten scope
        case 'convert_to_project':
          return false;

        default:
          return false;
      }
    },

    isStatusToContactDisabled() {
      return this.currentStatus === 'prospect' && !this.ownerId;
    },

    /**
     * ✅ Toggle echte taak → server
     * (houdt jouw huidige PATCH style aan)
     */
    async toggle(type, checked, evt) {
      if (this.loadingTypes[type]) return;

      // Guard
      if (!this.canCheck(type)) {
        if (evt?.target) evt.target.checked = !!this.tasks[type];
        return;
      }

      const prev = !!this.tasks[type];
      this.tasks[type] = checked;
      this.loadingTypes[type] = true;

      const newStatus = checked ? 'done' : 'open';

      try {
        const res = await fetch(updateUrl, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ type, status: newStatus }),
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data?.message || 'Taak update mislukt');

        const serverStatus = String(data?.status || newStatus).toLowerCase();
        this.tasks[type] = serverStatus === 'done';

        showToast(
          this.tasks[type]
            ? 'Gelukt! De taak is gemarkeerd als voltooid'
            : 'Gelukt! De taak is opnieuw geopend',
          'success'
        );
      } catch (e) {
        this.tasks[type] = prev;
        if (evt?.target) evt.target.checked = prev;

        showToast('Oeps! De taak kon niet worden bijgewerkt.', 'error');
      } finally {
        this.loadingTypes[type] = false;
      }
    },

    /**
     * ✅ UI-only status knop support
     * Werkt samen met jouw statusDnD soft-update systeem.
     *
     * Vereist dat je statusUpdateUrl meegeeft vanuit Blade tasksPayload.
     */
    async setStatus(newStatus) {
      if (!statusUpdateUrl) {
        showToast('Status URL ontbreekt in tasksPayload.', 'error');
        return;
      }

      const oldValue = this.currentStatus;

      try {
        const res = await fetch(statusUpdateUrl, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ status: newStatus }),
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          showToast(data?.message || potklTrans('toast.status_update_error'), 'error');
          return;
        }

        const finalValue = String(data?.status || newStatus).toLowerCase();

        this.currentStatus = finalValue;

        if (data && typeof data.intake_done !== 'undefined') {
          this.intakeDone = !!data.intake_done;
        }

        // ✅ Laat statusDnD list+detail synch updaten
        window.dispatchEvent(new CustomEvent('potkl-status-soft-update', {
          detail: {
            id: this.cardId,
            oldValue,
            newValue: finalValue,
            data
          }
        }));

        showToast(
          potklTrans('toast.status_update_success', { status: finalValue }),
          'success'
        );
      } catch (e) {
        console.error(e);
        showToast(potklTrans('toast.status_update_error'), 'error');
      }
    },
  };
}
</script>
@endverbatim

@endsection