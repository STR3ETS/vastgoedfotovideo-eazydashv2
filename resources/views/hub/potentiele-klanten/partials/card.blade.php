@php
  $valueToLabel = $statusByValue ?? [];

  $allowedStatuses = ['prospect', 'contact', 'intake', 'dead', 'lead'];
  $rawStatus = $aanvraag->status;

  $currentValue = in_array($rawStatus, $allowedStatuses, true)
      ? $rawStatus
      : 'prospect';

  $currentLabel = $valueToLabel[$currentValue] ?? 'Prospect';

  $choiceMap = [
      'new'   => __('potentiele_klanten.choices.new'),
      'renew' => __('potentiele_klanten.choices.renew'),
  ];

  $choiceTitle = $choiceMap[$aanvraag->choice]
      ?? __('potentiele_klanten.choices.default');

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

  $showListRow = $showListRow ?? true;
@endphp

<div class="transition"
     data-card-id="{{ $aanvraag->id }}"
     data-status="{{ $currentValue }}"
     data-intake-done="{{ $aanvraag->intake_done ? 1 : 0 }}"
     x-data="{ openDetails: {{ $showListRow ? 'false' : 'true' }} }"
     x-on:dragover="onCardDragOver"
     x-on:dragleave="onCardDragLeave"
     x-on:drop="onCardDrop">

  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0 w-full">
      @if($showListRow)
        <div class="grid grid-cols-8 items-center gap-2">
          <p class="text-sm font-medium text-[#215558] truncate">{{ $aanvraag->company }}</p>
          <span class="truncate text-sm font-bold text-[#215558]">{{ $choiceTitle }}</span>
          <p class="text-sm font-medium text-[#215558] truncate">{{ $aanvraag->contactName }}</p>
          <p class="text-sm font-medium text-[#215558] truncate">{{ $aanvraag->contactPhone }}</p>
          <p class="text-sm font-medium text-[#215558] truncate">{{ $aanvraag->contactEmail }}</p>
          <p class="text-sm font-medium text-[#215558] truncate">{{ $aanvraag->created_at }}</p>
          <div class="flex items-center">
            <span class="inline-block text-xs transition duration-300 px-2.5 py-0.5 font-semibold rounded-full {{ $badge['bg'] }} {{ $badge['text'] }}"
                  data-status-badge
                  data-status-value="{{ $currentValue }}">
              {{ $currentLabel }}
            </span>
          </div>
          <div class="flex items-center justify-end">
            <button type="button"
                    class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer"
                    @click="openDetails = !openDetails">
              <i class="fa-solid fa-chevron-down text-[#215558] fa-sm transform transition-transform duration-200"
                :class="openDetails ? 'rotate-180' : 'rotate-0'"></i>
            </button>
          </div>
        </div>
      @endif

      {{-- DETAILS --}}
      <div
          @if($showListRow)
            x-show="openDetails"
            x-transition
          @endif>

          <div>
            <div class="flex items-center justify-between mb-1">
              <h2 class="font-black text-[#215558] text-xl">{{ $choiceTitle }}</h2>
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                  <i class="fa-solid fa-phone text-[#215558] text-xs"></i>
                </div>
                <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center">
                  <i class="fa-solid fa-envelope text-[#215558] text-xs"></i>
                </div>
              </div>
            </div>
            <div class="flex items-center gap-4">
              <span class="text-sm font-semibold text-[#215558]/75 flex items-center gap-2">
                <i class="fa-solid fa-building fa-xs"></i>
                {{ $aanvraag->company }}
              </span>
              <span class="text-sm font-semibold text-[#215558]/75 flex items-center gap-2">
                <i class="fa-solid fa-user fa-xs"></i>
                {{ $aanvraag->contactName }}
              </span>
            </div>
            <div class="mt-4 mb-6">
              <div class="grid grid-cols-4 text-xs font-semibold text-[#215558] mb-2">
                <p class="text-[#0F9B9F]">Prospect</p>
                <p class="opacity-50">Contact</p>
                <p class="opacity-50">Intake</p>
                <p class="opacity-50">Lead</p>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="w-1/4 h-full bg-[#0F9B9F] rounded-full"></div>
              </div>
            </div>
            <hr class="border-gray-200 mb-6">
            <div class="grid grid-cols-3 gap-6">
              <div class="col-span-2 flex flex-col gap-6">
                <div class="col-span-2 h-fit bg-[#fff] rounded-4xl p-8">
                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                      <i class="fa-solid fa-file-pen fa-sm"></i>
                      Aanvraag details
                    </h3>
                    <span class="px-2.5 py-0.5 font-semibold text-purple-700 bg-purple-200 text-[11px] flex items-center gap-2 rounded-full">
                      <i class="fa-solid fa-sparkle fa-xs"></i>
                      Samenvatting door AI
                    </span>
                  </div>
                  <p class="text-sm font-medium text-[#215558] leading-[20px] opacity-75">
                      {{ $aanvraag->ai_summary }}
                  </p>
                </div>
                {{-- Belmomenten --}}
                <div class="bg-[#fff] rounded-4xl p-8 overflow-visible"
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

                  <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                      <i class="fa-solid fa-phone fa-sm"></i>
                      Belmomenten
                    </h3>

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
                            {{ __('potentiele_klanten.calls.new_call_tooltip') }}
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
                            {{ __('potentiele_klanten.calls.new_call_title') }}
                          </p>

                          <form class="grid gap-3 mt-2" x-on:submit.prevent="submit">
                            <div>
                              <label class="block text-xs text-[#215558] opacity-70 mb-1">
                                {{ __('potentiele_klanten.calls.result_label') }}
                              </label>
                              <select x-model="outcome"
                                      class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
                                <option value="">
                                  {{ __('potentiele_klanten.calls.result_choose') }}
                                </option>
                                <option value="geen_antwoord">
                                  {{ __('potentiele_klanten.calls.result_none') }}
                                </option>
                                <option value="gesproken">
                                  {{ __('potentiele_klanten.calls.result_spoken') }}
                                </option>
                              </select>
                            </div>

                            <div>
                              <label class="block text-xs text-[#215558] opacity-70 mb-1">
                                {{ __('potentiele_klanten.calls.note_label') }}
                              </label>
                              <textarea
                                x-model="note"
                                class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                                rows="2"
                                placeholder="{{ __('potentiele_klanten.calls.note_placeholder') }}"></textarea>
                            </div>

                            <div class="flex items-center justify-end mt-1">
                              <button type="submit"
                                      class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                                      x-bind:disabled="loading">
                                <span x-show="!loading">
                                  {{ __('potentiele_klanten.calls.save') }}
                                </span>
                                <span x-show="loading">
                                  {{ __('potentiele_klanten.calls.saving') }}
                                </span>
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="overflow-x-visible">
                    <template x-if="!calls.length">
                      <p class="text-[#215558] text-xs font-semibold opacity-75">
                        {{ __('potentiele_klanten.calls.none') }}
                      </p>
                    </template>
                    <ul class="flex flex-col gap-2 divide-[#215558]/20 max-h-50 overflow-y-auto pr-1 mt-2" x-show="calls.length">
                      <template x-for="call in calls" :key="call.id">
                        <li class="text-xs pl-8 py-2 relative">
                          {{-- verticale lijn --}}
                          <div class="absolute left-2.25 top-0 bottom-0 w-px bg-[#215558]/20"></div>

                          {{-- bolletje dat links over de lijn uitsteekt --}}
                          <div
                            class="absolute left-1 top-2 w-3 h-3 rounded-full bg-[#f3f8f8] border-[2px] border-[#215558]/20 z-[1]">
                          </div>

                          <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-4">
                              <div class="flex flex-col">
                                <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Gebeld door</p>
                                <p class="text-sm font-semibold text-[#215558]" x-text="(call.user_name || 'Onbekend')"></p>
                              </div>
                              <div class="flex flex-col">
                                <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Gebeld op</p>
                                <p class="text-sm font-semibold text-[#215558]" x-text="call.called_at"></p>
                              </div>
                              <div class="flex flex-col">
                                <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Opmerking</p>
                                <p class="text-sm font-semibold text-[#215558]" x-show="call.note" x-text="call.note"></p>
                              </div>
                            </div>

                            <span
                              class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                              :class="{
                                'bg-red-200 text-red-700': call.outcome === 'geen_antwoord',
                                'bg-green-200 text-green-700': call.outcome === 'gesproken',
                              }"
                              x-text="labels[call.outcome] || call.outcome"
                            ></span>
                          </div>
                        </li>
                      </template>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="bg-[#fff] rounded-4xl p-8 h-fit">
                <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                  <i class="fa-solid fa-database fa-sm"></i>
                  Aanvraag data
                </h3>
                <div class="flex flex-col gap-2 mt-3">
                  <div class="flex flex-col">
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Geschatte budget</p>
                    <p class="text-sm font-semibold text-[#215558]">€15.000 - €25.000</p>
                  </div>
                  <div class="flex flex-col">
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Datum aanvraag</p>
                    <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag->created_at }}</p>
                  </div>
                  <div class="flex flex-col">
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558] -mb-0.5">Lead bron</p>
                    <p class="text-sm font-semibold text-[#215558] flex items-center">
                      <span class="w-2 h-2 rounded-full bg-orange-500 mr-2"></span>
                      Formulier zelf ingevuld
                    </p>
                  </div>
                </div>
                <hr class="border-t-[#215558]/20 my-5">
                <h3 class="text-[#215558] font-black text-sm shrink-0">
                  Contactpersoon
                </h3>
                <div class="flex flex-col gap-2 mt-3">
                  <div class="flex flex-col">
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Naam</p>
                    <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag->contactName }}</p>
                  </div>
                  <div class="flex flex-col">
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558]">E-mailadres</p>
                    <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag->contactEmail }}</p>
                  </div>
                  <div class="flex flex-col">
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Telefoonnummer</p>
                    <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag->contactPhone }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>


           <div class="grid grid-cols-2 gap-4">

            {{-- Intakegesprek --}}
            @if($callTask && $callTask->questions->isNotEmpty())
              <div class="bg-[#f3f8f8] rounded-4xl p-8"
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
                    <h3 class="text-[#215558] font-black text-base shrink-0">
                      {{ __('potentiele_klanten.intake_questions.section_title') }}
                    </h3>
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
                          {{ __('potentiele_klanten.intake_questions.open_panel_tooltip') }}
                        </p>
                      </div>
                    </button>

                    <div x-show="openIntakePanel"
                        x-transition
                        @click.outside="openIntakePanel = false"
                        class="absolute right-full mr-3 top-1/2 -translate-y-1/2 z-30"
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
                          <p class="text-base text-[#215558] font-black mb-3">
                            {{ __('potentiele_klanten.intake_questions.notes_title') }}
                          </p>

                          @if($notesQ)
                            <textarea
                              x-ref="answer_{{ $notesQ->id }}"
                              data-answer-id="{{ $notesQ->id }}"
                              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300 max-h-58"
                              rows="10">{{ $notesQ->answer }}</textarea>
                              <p class="text-[11px] text-[#215558] opacity-70 mt-2">
                                {{ __('potentiele_klanten.intake_questions.notes_help') }}
                              </p>
                          @else
                            <p class="text-xs text-[#215558] opacity-75">
                              {{ __('potentiele_klanten.intake_questions.notes_missing') }}
                            </p>
                          @endif
                        </div>

                        {{-- RECHTS: Intakevragen (zonder notities) --}}
                        <div class="w-[480px] p-4 bg-white rounded-xl border border-gray-200 shadow-lg">
                          <div class="flex items-start justify-between">
                            <p class="text-base text-[#215558] font-black mb-1">
                              {{ __('potentiele_klanten.intake_questions.section_title') }}
                            </p>
                          </div>

                          <div class="grid gap-3 max-h-50 overflow-y-auto pr-1 mt-2">
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
                              <span x-show="!savingAll">
                                {{ __('potentiele_klanten.intake_questions.save') }}
                              </span>
                              <span x-show="savingAll">
                                {{ __('potentiele_klanten.intake_questions.saving') }}
                              </span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="intake-panel-{{ $aanvraag->id }}" data-intake-panel>
                  @if($aanvraag->intake_at)
                    @include('hub.potentiele-klanten.partials.intake-panel', ['aanvraag' => $aanvraag, 'valueToLabel' => $valueToLabel ?? []])
                  @endif
                </div>
              </div>
            @endif
           </div>

        {{-- ✅ Bestanden --}}
        <div class="bg-[#f3f8f8] rounded-4xl p-8"
            x-data="filesManager({
              csrf: '{{ csrf_token() }}',
              uploadUrl: '{{ route('support.potentiele-klanten.files.store', $aanvraag) }}',
              deleteUrlTemplate: '{{ route('support.potentiele-klanten.files.destroy', ['file' => '__ID__']) }}',
              initialFiles: @js(
                ($aanvraag->files ?? collect())->map(fn($file) => [
                  'id'         => $file->id,
                  'name'       => $file->original_name ?? $file->name,
                  'url'        => route('support.potentiele-klanten.files.download', $file),
                  'extension'  => strtolower(pathinfo($file->original_name ?? $file->name, PATHINFO_EXTENSION)),
                  'size_human' => $file->size_human ?? null,
                  'uploaded_at'=> optional($file->created_at)->format('d-m-Y H:i'),
                ])
              ),
            })">

          <div class="flex items-center justify-between gap-2">
            <h3 class="text-[#215558] font-black text-base shrink-0">
              {{ __('potentiele_klanten.files.section_title') }}
            </h3>
          </div>

          {{-- Dropzone --}}
          <div class="mt-2">
            <div
              class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-300 bg-white px-4 py-6 text-center cursor-pointer transition duration-200"
              :class="dragOver ? 'border-[#0F9B9F] bg-emerald-50/40' : 'hover:border-[#0F9B9F]'"
              @dragover.prevent="dragOver = true"
              @dragleave.prevent="dragOver = false"
              @drop.prevent="handleDrop($event)"
              @click="$refs.fileInput.click()"
            >
              <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fa-solid fa-file-arrow-up text-[#215558]"></i>
              </div>
              <div class="text-xs text-[#215558] font-semibold">
                {!! __('potentiele_klanten.files.drop_text', [
                    'click' => '<span class="underline">' . __('potentiele_klanten.files.drop_click') . '</span>',
                ]) !!}
              </div>
              <p class="text-[11px] text-[#215558] opacity-70">
                {{ __('potentiele_klanten.files.drop_help') }}
              </p>

              <input type="file"
                    multiple
                    class="hidden"
                    x-ref="fileInput"
                    @change="handleInput($event.target.files)">
            </div>
          </div>

          {{-- Lijst met bestanden --}}
          <div class="mt-3">
            <template x-if="!files.length && !uploading">
              <p class="text-[#215558] text-xs font-semibold opacity-75">
                {{ __('potentiele_klanten.files.none') }}
              </p>
            </template>

            <template x-if="uploading">
              <p class="text-[#215558] text-xs font-semibold opacity-75 flex items-center gap-2">
                <i class="fa-solid fa-spinner fa-spin"></i>
                {{ __('potentiele_klanten.files.uploading') }}
              </p>
            </template>

            <ul class="mt-2 space-y-1 max-h-40 overflow-y-auto pr-1" x-show="files.length">
              <template x-for="file in files" :key="file.id">
                <li class="flex items-center justify-between gap-2 bg-white border border-gray-200 rounded-xl p-3">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <i class="fa-solid text-sm" :class="iconFor(file)"></i>
                    </div>
                    <div class="min-w-0">
                      <p class="text-xs font-semibold text-[#215558] truncate" x-text="file.name"></p>
                      <p class="text-[10px] font-medium text-[#215558] truncate opacity-80">
                        <span x-text="file.extension?.toUpperCase() || ''"></span>
                        <span x-show="file.size_human"> · <span x-text="file.size_human"></span></span>
                        <span x-show="file.uploaded_at">
                          · {{ __('potentiele_klanten.files.uploaded_on', ['date' => '']) }}
                          <span x-text="file.uploaded_at"></span>
                        </span>
                      </p>
                    </div>
                  </div>

                  <div class="flex items-center gap-2 flex-shrink-0">
                    <a :href="file.url"
                      target="_blank"
                      class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-200 text-[#215558] hover:bg-gray-300 transition duration-300 cursor-pointer">
                      <i class="fa-solid fa-arrow-up-right-from-square fa-xs mr-1 mt-2"></i>
                      {{ __('potentiele_klanten.files.open') }}
                    </a>
                    <button type="button"
                            class="px-2.5 py-0.5 flex items-center rounded-full text-[11px] font-semibold bg-red-600 hover:bg-red-700 transition duration-300 text-white cursor-pointer"
                            @click="openConfirm(file)"
                            :disabled="removingId === file.id">
                      <span x-show="removingId !== file.id">
                        <i class="fa-solid fa-xs fa-trash mr-0.5 mt-2"></i>
                        {{ __('potentiele_klanten.files.delete') }}
                      </span>
                      <span x-show="removingId === file.id">
                        <i class="fa-solid fa-spinner fa-spin mr-1"></i>
                      </span>
                    </button>
                  </div>
                </li>
              </template>
            </ul>
          </div>

          {{-- ✅ Lokale confirm-overlay voor bestand verwijderen --}}
          <div
            x-show="confirmOpen"
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
            style="display:none;"
          >
            <div class="absolute inset-0 bg-black/25" @click="!removingId && (confirmOpen = false)"></div>

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
                    {{ __('potentiele_klanten.files.delete_title') }}
                  </h2>
                  <p class="mt-1 text-sm text-[#215558]">
                    {{ __('potentiele_klanten.files.delete_question') }}
                  </p>
                </div>
              </div>

              <div class="mt-3 text-sm text-red-600" x-show="confirmError" x-text="confirmError"></div>

              <div class="mt-4 flex items-center gap-2">
                <button type="button"
                        class="bg-red-500 hover:bg-red-600 cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300 disabled:opacity-60"
                        :disabled="removingId"
                        @click="confirmRemove()">
                  <span class="inline-flex items-center gap-2">
                    <span x-show="removingId"><i class="fa-solid fa-spinner fa-spin"></i></span>
                    <span x-show="!removingId">
                      {{ __('potentiele_klanten.files.delete_yes') }}
                    </span>
                  </span>
                </button>
                <button type="button"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300"
                        :disabled="removingId"
                        @click="confirmOpen = false; fileToDelete = null;">
                  {{ __('potentiele_klanten.files.delete_cancel') }}
                </button>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-[#f3f8f8] rounded-4xl p-8">
          <div class="flex items-center justify-between">
            <h3 class="text-[#215558] font-black text-base shrink-0">
              {{ __('potentiele_klanten.logbook.title') }}
            </h3>
          </div>

          <p class="text-[#215558] text-xs font-semibold opacity-75 mt-1"
             data-status-log-empty
             @if($statusLogs->isNotEmpty()) style="display:none;" @endif>
            {{ __('potentiele_klanten.logbook.empty') }}
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