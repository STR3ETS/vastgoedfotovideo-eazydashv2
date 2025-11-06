@php
  $valueToLabel = $statusByValue ?? [];

  $allowedStatuses = ['prospect', 'contact', 'intake', 'dead', 'lead'];
  $rawStatus = $aanvraag->status;

  $currentValue = in_array($rawStatus, $allowedStatuses, true)
      ? $rawStatus
      : 'prospect';

  $currentLabel = $valueToLabel[$currentValue] ?? 'Prospect';

  $choiceMap = [
      'new'   => 'Nieuwe website',
      'renew' => 'Website vernieuwen',
  ];
  $choiceTitle = $choiceMap[$aanvraag->choice] ?? 'Website-aanvraag';

  $badgeColors = [
      'prospect' => [
          'bg'   => 'bg-[#b3e6ff]',
          'text' => 'text-[#0f6199]',
      ],
      'contact' => [
          'bg'   => 'bg-[#C2F0D5]',
          'text' => 'text-[#20603a]',
      ],
      'intake' => [
          'bg'   => 'bg-[#ffdfb3]',
          'text' => 'text-[#a0570f]',
      ],
      'dead' => [
          'bg'   => 'bg-[#ffb3b3]',
          'text' => 'text-[#8a2a2d]',
      ],
      'lead' => [
          'bg'   => 'bg-[#e0d4ff]',
          'text' => 'text-[#4c2a9b]',
      ],
  ];

  $badge = $badgeColors[$currentValue] ?? [
      'bg'   => 'bg-slate-100',
      'text' => 'text-slate-700',
  ];

  /** @var \App\Models\AanvraagTask|null $callTask */
  $callTask   = $aanvraag->tasks->firstWhere('type', 'call_customer') ?? null;
  $callLogs   = $aanvraag->callLogs ?? collect();
  $statusLogs = $aanvraag->statusLogs ?? collect();
@endphp

<div class="p-4 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-50 transition"
     data-card-id="{{ $aanvraag->id }}"
     data-status="{{ $currentValue }}"
     x-data="{ openDetails: false }"
     x-on:dragover="onCardDragOver"
     x-on:dragleave="onCardDragLeave"
     x-on:drop="onCardDrop">

  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0 w-full">
      {{-- Titel + chevron --}}
      <div class="text-lg text-[#215558] font-black leading-tight mb-1 flex items-center justify-between">
        <span class="truncate">{{ $choiceTitle }}</span>

        <button type="button"
                class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer"
                @click="openDetails = !openDetails">
          <i class="fa-solid fa-chevron-down text-[#215558] fa-sm transform transition-transform duration-200"
             :class="openDetails ? 'rotate-180' : 'rotate-0'"></i>
        </button>
      </div>

      <p class="text-sm font-bold text-[#215558] truncate mb-2">{{ $aanvraag->company }}</p>

      <span class="inline-block text-xs transition duration-300 px-2.5 py-0.5 font-semibold rounded-full {{ $badge['bg'] }} {{ $badge['text'] }}"
            data-status-badge
            data-status-value="{{ $currentValue }}">
        {{ $currentLabel }}
      </span>

      <div class="w-full flex items-center gap-2 mt-3">
        <i class="min-w-[15px] fa-solid fa-user text-[#215558] text-xs"></i>
        <p class="text-xs font-semibold text-[#215558] truncate">{{ $aanvraag->contactName }}</p>
      </div>
      <div class="w-full flex items-center gap-2 mt-1">
        <i class="min-w-[15px] fa-solid fa-paper-plane text-[#215558] text-[11px]"></i>
        <p class="text-xs font-semibold text-[#215558] truncate">{{ $aanvraag->contactEmail }}</p>
      </div>
      <div class="w-full flex items-center gap-2 mt-1">
        <i class="min-w-[15px] fa-solid fa-phone text-[#215558] text-xs"></i>
        <p class="text-xs font-semibold text-[#215558] truncate">{{ $aanvraag->contactPhone }}</p>
      </div>

      {{-- DETAILS --}}
      <div class="mt-4"
           x-show="openDetails"
           x-transition>

        {{-- Intakegesprek --}}
        @if($callTask && $callTask->questions->isNotEmpty())
          <div class="pt-3 border-t border-gray-200 mt-4"
            x-data="{
              csrf: '{{ csrf_token() }}',
              updateUrlTemplate: '{{ route('support.tasks.questions.update', ['question' => '__ID__']) }}',
              openIntakePanel: false,
              savingAll: false,

              async saveAll () {
                console.log('[Intake] saveAll start');
                try {
                  this.savingAll = true;

                  // Pak ALLE textareas binnen deze component die een data-answer-id hebben
                  const fields = Array.from(this.$root.querySelectorAll('textarea[data-answer-id]'));
                  console.log('[Intake] gevonden velden:', fields.length);

                  if (!fields.length) {
                    console.warn('[Intake] Geen textareas met data-answer-id gevonden');
                  }

                  for (const el of fields) {
                    const id  = el.dataset.answerId; // <-- uit data-answer-id
                    const url = this.updateUrlTemplate.replace('__ID__', id);

                    const form = new FormData();
                    form.append('_method', 'PATCH'); // Laravel-friendly
                    form.append('answer', el.value ?? '');

                    const res = await fetch(url, {
                      method: 'POST',
                      credentials: 'same-origin', // sessie/cookies meegeven
                      headers: {
                        'X-CSRF-TOKEN': this.csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                      },
                      body: form
                    });

                    if (res.redirected && res.url.includes('/login')) {
                      console.error('[Intake] Redirect naar login (sessie verlopen?)');
                      alert('Je sessie is verlopen. Log opnieuw in.');
                      return;
                    }
                    if (!res.ok) {
                      const text = await res.text().catch(()=> '');
                      console.error('[Intake] Update failed', res.status, text);
                      throw new Error(`Update failed (${res.status})`);
                    }
                  }
                } catch (err) {
                  console.error(err);
                  alert('Opslaan mislukt. Check de console voor details.');
                } finally {
                  this.savingAll = false;
                  this.openIntakePanel = false;
                }
              }
            }">

            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <h3 class="text-base text-[#215558] font-bold leading-tight truncate">Intakegesprek</h3>
              </div>

              <div class="relative">
                <button type="button"
                        class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                        @click.stop="openIntakePanel = !openIntakePanel">
                  <i class="fa-solid fa-eyes text-[#215558] text-xs"></i>

                  <div
                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                           opacity-0 invisible translate-y-1 pointer-events-none
                           group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                           transition-all duration-200 ease-out z-10">
                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                      Open intakegesprek
                    </p>
                  </div>
                </button>

                <div x-show="openIntakePanel"
                    x-transition
                    @click.outside="openIntakePanel = false"
                    class="absolute right-full mr-3 top-0 z-30"
                    style="display:none;">

                  @php
                    // Pak de (optionele) notities-vraag op tekst (case-insensitive bevat 'notitie')
                    $notesQ = $callTask->questions->first(function ($q) {
                        return str_contains(mb_strtolower($q->question), 'notitie');
                    });
                    // De overige vragen (zonder notities)
                    $otherQs = $callTask->questions->reject(function ($q) {
                        return str_contains(mb_strtolower($q->question), 'notitie');
                    });
                  @endphp

                  <div class="flex items-start gap-3">
                    {{-- LINKS: Vrije notities, eigen card --}}
                    <div class="w-[360px] p-4 bg-white rounded-xl border border-gray-200 shadow-lg">
                      <p class="text-base text-[#215558] font-black mb-3">Notities</p>

                      @if($notesQ)
                        <textarea
                          x-ref="answer_{{ $notesQ->id }}"
                          data-answer-id="{{ $notesQ->id }}"
                          class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                          rows="10">{{ $notesQ->answer }}</textarea>
                        <p class="text-[11px] text-[#215558] opacity-70 mt-2">
                          Wordt opgeslagen met de knop <strong>Opslaan</strong>.
                        </p>
                      @else
                        <p class="text-xs text-[#215558] opacity-75">
                          Geen notities-veld gevonden in deze intake.
                        </p>
                      @endif
                    </div>

                    {{-- RECHTS: Intakevragen (zonder notities) --}}
                    <div class="w-[480px] p-4 bg-white rounded-xl border border-gray-200 shadow-lg">
                      <div class="flex items-start justify-between">
                        <p class="text-base text-[#215558] font-black mb-1">Intakegesprek</p>
                      </div>

                      <div class="grid gap-3 max-h-60 overflow-y-auto pr-1 mt-2">
                        @foreach($otherQs as $q)
                          @php $refName = 'answer_'.$q->id; @endphp
                          <div>
                            <p class="block text-xs text-[#215558] opacity-70 mb-1">
                              {{ $q->order }}. {{ $q->question }}
                            </p>
                            <textarea
                              x-ref="{{ $refName }}"
                              data-answer-id="{{ $q->id }}"
                              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                              rows="2">{{ $q->answer }}</textarea>
                          </div>
                        @endforeach
                      </div>

                      <div class="mt-5 flex justify-end">
                        <button type="button"
                                class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                                @click="saveAll()"
                                x-bind:disabled="savingAll">
                          <span x-show="!savingAll">Opslaan</span>
                          <span x-show="savingAll">Opslaan...</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif

        {{-- Belmomenten --}}
        <div class="pt-3 mt-4 border-t border-gray-200"
             x-data="callLog({
                csrf: '{{ csrf_token() }}',
                storeUrl: '{{ route('support.potentiele-klanten.calls.store', $aanvraag) }}',
                initialCalls: @js(
                  $callLogs->map(fn($log) => [
                      'id'        => $log->id,
                      'called_at' => optional($log->called_at)->format('d-m-Y H:i'),
                      'outcome'   => $log->outcome,
                      'note'      => $log->note,
                      'user_name' => optional($log->user)->name,
                  ])
                ),
             })">

          <div class="flex items-center justify-between">
            <h3 class="text-base text-[#215558] font-bold leading-tight truncate">Belmomenten</h3>

            <div class="relative">
              <button type="button"
                      class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                      @click.stop="openCallPanel = !openCallPanel">
                <i class="fa-solid fa-plus text-[#215558] text-xs"></i>

                <div
                  class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                         opacity-0 invisible translate-y-1 pointer-events-none
                         group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                         transition-all duration-200 ease-out z-10">
                  <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                    Nieuw belmoment
                  </p>
                </div>
              </button>

              <div x-show="openCallPanel"
                   x-transition
                   @click.outside="openCallPanel = false"
                   class="absolute right-full mr-3 top-0 w-[380px] p-3 rounded-xl bg-white border border-gray-200 shadow-lg z-30"
                   style="display:none;">
                <div>
                  <p class="text-base text-[#215558] font-black mb-3">
                    Nieuw belmoment
                  </p>

                  <form class="grid gap-3 mt-2" x-on:submit.prevent="submit">
                    <div>
                      <label class="block text-xs text-[#215558] opacity-70 mb-1">Resultaat</label>
                      <select x-model="outcome"
                              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
                        <option value="">Kies resultaat</option>
                        <option value="geen_antwoord">Geen antwoord</option>
                        <option value="gesproken">Gesproken</option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs text-[#215558] opacity-70 mb-1">Notitie</label>
                      <textarea
                        x-model="note"
                        class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                        rows="2"
                        placeholder="Schrijf een notitie..."></textarea>
                    </div>

                    <div class="flex items-center justify-end mt-1">
                      <button type="submit"
                              class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                              x-bind:disabled="loading">
                        <span x-show="!loading">Opslaan</span>
                        <span x-show="loading">Opslaan...</span>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div>
            <template x-if="!calls.length">
              <p class="text-[#215558] text-xs font-semibold opacity-75">
                Nog geen belmomenten geregistreerd.
              </p>
            </template>

            <ul class="space-y-1 max-h-40 overflow-y-auto pr-1 mt-2" x-show="calls.length">
              <template x-for="call in calls" :key="call.id">
                <li class="text-xs bg-white border border-gray-200 rounded-xl p-3">
                  <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                      <span class="text-xs font-semibold text-[#215558] truncate" x-text="call.called_at"></span>
                      <span class="text-xs font-semibold text-[#215558] truncate"
                            x-text="'Gebeld door: ' + (call.user_name || 'Onbekend')"></span>
                    </div>

                    <span
                      class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                      :class="{
                        'bg-[#ffb3b3] text-[#8a2a2d]': call.outcome === 'geen_antwoord',
                        'bg-[#C2F0D5] text-[#20603a]': call.outcome === 'gesproken',
                      }"
                      x-text="labels[call.outcome] || call.outcome"
                    ></span>
                  </div>
                  <p class="text-xs font-medium text-[#215558] mt-1 italic"
                     x-show="call.note"
                     x-text="call.note"></p>
                </li>
              </template>
            </ul>
          </div>
        </div>

        {{-- ✅ Statuslogboek: altijd zichtbaar --}}
        <div class="pt-3 border-t border-gray-200 mt-4">
          <div class="flex items-center justify-between">
            <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
              Logboek
            </h3>
          </div>

          <p class="text-[#215558] text-xs font-semibold opacity-75 mt-1"
             data-status-log-empty
             @if($statusLogs->isNotEmpty()) style="display:none;" @endif>
            Nog geen activiteit.
          </p>

          <ul class="mt-2 space-y-1 max-h-40 overflow-y-auto pr-1"
              data-status-log-list>
            @foreach($statusLogs as $log)
              @include('hub.potentiele-klanten.partials.status-log-item', [
                'log'          => $log,
                'valueToLabel' => $valueToLabel,
              ])
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>