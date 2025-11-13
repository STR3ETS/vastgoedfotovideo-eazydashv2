@php
  /** @var \App\Models\Project $project */
  $statusValue = $project->status ?? 'preview';
  $statusLabel = ($statusByValue[$statusValue] ?? null) ?: ucfirst($statusValue);

  $badgeColors = [
    'preview' => [
        'bg'   => 'bg-[#e0d4ff]',
        'text' => 'text-[#4c2a9b]',
    ],
    'waiting_customer' => [
        'bg'   => 'bg-[#b3e6ff]',
        'text' => 'text-[#0f6199]',
    ],
    'offerte' => [
        'bg'   => 'bg-[#ffdfb3]',
        'text' => 'text-[#a0570f]',
    ],
  ];

  $badge = $badgeColors[$statusValue] ?? [
    'bg'   => 'bg-slate-100',
    'text' => 'text-slate-700',
  ];

  $aanvraag  = $project->aanvraag ?? null;
  $callTask  = null;
  $questions = collect();

  if ($aanvraag) {
      $callTask  = $aanvraag->tasks->firstWhere('type', 'call_customer');
      $questions = $callTask?->questions ?? collect();
  }

  /** @var \App\Models\ProjectTask|null $offerteTask */
  $offerteTask = $project->tasks->firstWhere('type', 'call_customer');
  $offerteNoteQuestion   = $offerteTask?->questions->first();
  $offerteTaskTitle       = $offerteTask->title ?? 'Bellen met de klant';
  $offerteTaskDescription = $offerteTask->description ?? 'Bel de klant t.a.v. feedback/goedkeuring preview';
  $offerteNotes           = $offerteNoteQuestion->answer ?? '';
  $offerteTaskCompleted   = (bool) ($offerteTask?->completed_at);
@endphp
@php
  $previewFeedback = $project->previewFeedbacks()->latest()->limit(10)->get();
  $hasFeedback = $previewFeedback->isNotEmpty();
  $callLogs = $project->callLogs ?? collect();
@endphp

<div class="p-4 bg-gray-50 border border-gray-200 rounded-xl hover:bg-gray-50 transition"
    data-card-id="{{ $project->id }}"
    data-status="{{ $statusValue }}"
    x-on:dragover="onCardDragOver"
    x-on:dragleave="onCardDragLeave"
    x-on:drop="onCardDrop"
    x-on:project-status-updated="
        statusValue = $event.detail.status;
        if ($event.detail.offerteTask) {
            hasOfferteTask         = true;
            offerteTaskTitle       = $event.detail.offerteTask.title || offerteTaskTitle;
            offerteTaskDescription = $event.detail.offerteTask.description || offerteTaskDescription;
            if ($event.detail.offerteTask.notes !== undefined) {
                offerteNotes = $event.detail.offerteTask.notes || '';
            }
            if ($event.detail.offerteTask.completed !== undefined) {
                offerteTaskCompleted = !!$event.detail.offerteTask.completed;
            }
        }
    "
    x-data="{
        statusValue: '{{ $statusValue }}',
        openDetails: false,

        openPreviewSection: false,
        openPreviewFeedbackSection: false,
        openOfferteSection: false,

        openPreviewForm: false,
        openRequestOverlay: false,
        savingPreview: false,

        // Offerte-state
        hasOfferteTask: {{ $offerteTask ? 'true' : 'false' }},
        openOfferteOverlay: false,
        savingOfferteNotes: false,
        markingOfferteTask: false,
        offerteTaskTitle: @js($offerteTaskTitle),
        offerteTaskDescription: @js($offerteTaskDescription),
        offerteNotes: @js($offerteNotes),
        offerteTaskCompleted: {{ $offerteTaskCompleted ? 'true' : 'false' }},

        previewUrl: @js($project->preview_url ?? null),
        previewUrlInput: @js($project->preview_url ?? ''),
        previewLink: @js(
          $project->preview_url && $project->preview_token
            ? route('preview.show', ['token' => $project->preview_token])
            : null
        ),

        savePreview() {
            const self = this;
            self.savingPreview = true;
            fetch('{{ route('support.projecten.preview.update', $project) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ preview_url: self.previewUrlInput || null })
            })
            .then(res => {
                if (!res.ok) throw new Error('Preview save failed');
                return res.json().catch(() => null);
            })
            .then(data => {
                if (!data) return;
                if (typeof data.preview_url !== 'undefined') {
                    self.previewUrl      = data.preview_url;
                    self.previewUrlInput = data.preview_url || '';
                } else {
                    self.previewUrl = self.previewUrlInput || null;
                }
                if (typeof data.preview_link !== 'undefined') {
                    self.previewLink = data.preview_link || null;
                }
                if (data.status) {
                    self.statusValue = data.status;
                    const badge = self.$root.querySelector('[data-status-badge]');
                    if (badge) {
                        badge.dataset.statusValue = data.status;
                        badge.textContent = data.label || data.status;
                        const allColorClasses = [
                            'bg-[#e0d4ff]','text-[#4c2a9b]',
                            'bg-[#b3e6ff]','text-[#0f6199]',
                            'bg-[#ffdfb3]','text-[#a0570f]',
                            'bg-slate-100','text-slate-700'
                        ];
                        badge.classList.remove.apply(badge.classList, allColorClasses);
                        const colorMap = {
                            preview: ['bg-[#e0d4ff]', 'text-[#4c2a9b]'],
                            waiting_customer: ['bg-[#b3e6ff]', 'text-[#0f6199]'],
                            offerte: ['bg-[#ffdfb3]', 'text-[#a0570f]'],
                        };
                        const classes = colorMap[data.status] || ['bg-slate-100','text-slate-700'];
                        badge.classList.add.apply(badge.classList, classes);
                    }
                }
                if (window.showToast) {
                    window.showToast('Preview succesvol opgeslagen.', 'success');
                }
            })
            .catch(() => {
                if (window.showToast) {
                    window.showToast('Opslaan van preview is mislukt.', 'error');
                } else {
                    alert('Opslaan van preview is mislukt.');
                }
            })
            .finally(() => {
                self.savingPreview = false;
                self.openPreviewForm = false;
            });
        },

        saveOfferteNotes() {
            const self = this;
            self.savingOfferteNotes = true;
            fetch('{{ route('support.projecten.offerte_notes.update', $project) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ notes: self.offerteNotes })
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed');
                return res.json().catch(() => null);
            })
            .then(data => {
                if (data && data.notes !== undefined) {
                    self.offerteNotes = data.notes || '';
                }
                if (window.showToast && data?.success) {
                    window.showToast('Notities opgeslagen.', 'success');
                }
            })
            .catch(() => {
                if (window.showToast) {
                    window.showToast('Opslaan van notities is mislukt.', 'error');
                } else {
                    alert('Opslaan van notities is mislukt.');
                }
            })
            .finally(() => {
                self.savingOfferteNotes = false;
                self.openOfferteOverlay = false;
            });
        },

        completeOfferteTask() {
            const self = this;
            if (self.markingOfferteTask) return;

            self.markingOfferteTask = true;

            fetch('{{ route('support.projecten.offerte.complete', $project) }}', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({})
            })
            .then(res => {
                if (!res.ok) throw new Error('Failed');
                return res.json().catch(() => null);
            })
            .then(data => {
                if (data && data.offerte_task) {
                    self.hasOfferteTask        = true;
                    self.offerteTaskTitle      = data.offerte_task.title || self.offerteTaskTitle;
                    self.offerteTaskDescription= data.offerte_task.description || self.offerteTaskDescription;
                    if (typeof data.offerte_task.completed !== 'undefined') {
                        self.offerteTaskCompleted = !!data.offerte_task.completed;
                    }
                }
                if (window.showToast && data?.success) {
                    window.showToast('Offertegesprek gemarkeerd als voltooid.', 'success');
                }
            })
            .catch(() => {
                if (window.showToast) {
                    window.showToast('Markeren als voltooid is mislukt.', 'error');
                } else {
                    alert('Markeren als voltooid is mislukt.');
                }
            })
            .finally(() => {
                self.markingOfferteTask = false;
            });
        }    
    }"
>

  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0 w-full">
      <div class="text-lg text-[#215558] font-black leading-tight mb-2 flex items-center justify-between">
        <span class="truncate">
          {{ $project->company ?: __('projecten.unknown_company') }}
        </span>

        <button type="button"
                class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer"
                @click="openDetails = !openDetails">
          <i class="fa-solid fa-chevron-down text-[#215558] fa-sm transform transition-transform duration-200"
             :class="openDetails ? 'rotate-180' : 'rotate-0'"></i>
        </button>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-block text-xs px-2.5 py-0.5 font-semibold rounded-full {{ $badge['bg'] }} {{ $badge['text'] }}"
              data-status-badge
              data-status-value="{{ $statusValue }}">
          {{ $statusLabel }}
        </span>
      </div>

      <div class="w-full flex items-center gap-2 mt-3">
        <i class="min-w-[15px] fa-solid fa-user text-[#215558] text-xs"></i>
        <p class="text-xs font-semibold text-[#215558] truncate">
          {{ $project->contact_name ?: '—' }}
        </p>
      </div>

      <div class="w-full flex items-center gap-2 mt-1">
        <i class="min-w-[15px] fa-solid fa-paper-plane text-[#215558] text-[11px]"></i>
        <p class="text-xs font-semibold text-[#215558] truncate">
          {{ $project->contact_email ?: '—' }}
        </p>
      </div>

      <div class="w-full flex items-center gap-2 mt-1">
        <i class="min-w-[15px] fa-solid fa-phone text-[#215558] text-xs"></i>
        <p class="text-xs font-semibold text-[#215558] truncate">
          {{ $project->contact_phone ?: '—' }}
        </p>
      </div>

      <div x-show="openDetails" x-transition>
        <div class="pt-3 border-t border-gray-200 mt-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
              <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                {{ __('projecten.preview.section_title') }}
              </h3>
              <button type="button"
                      class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                      @click="openPreviewSection = !openPreviewSection">
                <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                  :class="openPreviewSection ? 'rotate-180' : 'rotate-0'"></i>
              </button>
            </div>
  
            <div class="flex items-center gap-2">
              <div class="relative">
                <button type="button"
                        class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                        @click.stop="openPreviewForm = !openPreviewForm">
                  <i class="fa-solid fa-plus text-[#215558] text-xs"></i>
  
                  <div
                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                           opacity-0 invisible translate-y-1 pointer-events-none
                           group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                           transition-all duration-200 ease-out z-10">
                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                      {{ __('projecten.preview.add_button_tooltip') }}
                    </p>
                  </div>
                </button>
  
                <div x-show="openPreviewForm"
                     x-transition
                     @click.outside="openPreviewForm = false"
                     class="absolute right-full mr-3 top-0 w-[380px] p-3 rounded-xl bg-white border border-gray-200 shadow-lg z-30"
                     style="display:none;">
                  <div>
                    <p class="text-base text-[#215558] font-black mb-3">
                      {{ __('projecten.preview.section_title') }}
                    </p>
  
                    <div class="grid gap-3 mt-2">
                      <div>
                        <label class="block text-xs text-[#215558] opacity-70 mb-1">
                          {{ __('projecten.preview.url_label') }}
                        </label>
                        <input type="text"
                               x-model="previewUrlInput"
                               class="w-full py-2 px-3 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                               placeholder="{{ __('projecten.preview.url_placeholder') }}">
                      </div>
  
                      <div class="flex items-center justify-end mt-3 gap-2">
                        <button type="button"
                                class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                                :disabled="savingPreview"
                                @click="savePreview()">
                          <span x-show="!savingPreview">
                            {{ __('projecten.preview.save') }}
                          </span>
                          <span x-show="savingPreview">
                            {{ __('projecten.preview.saving') }}
                          </span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
  
              @if($aanvraag && $questions->isNotEmpty())
                <div class="relative">
                  <button type="button"
                          class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                          @click.stop="openRequestOverlay = true">
                    <i class="fa-solid fa-eyes text-[#215558] text-xs"></i>
  
                    <div
                      class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                             opacity-0 invisible translate-y-1 pointer-events-none
                             group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                             transition-all duration-200 ease-out z-10">
                      <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                        {{ __('projecten.request.section_title') }}
                      </p>
                    </div>
                  </button>
                </div>
              @endif
  
              @php
                $hasPreviewLink = !empty($project->preview_url) && !empty($project->preview_token);
              @endphp

              <a
                  x-show="previewUrl"
                  x-cloak
                  :href="previewLink || '#'"
                  target="_blank"
                  class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-[#215558] hover:bg-gray-300 transition cursor-pointer relative group"
              >
                <i class="fa-solid fa-up-right-from-square text-xs"></i>

                <div
                  class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                        opacity-0 invisible translate-y-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                        transition-all duration-200 ease-out z-10">
                  <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                    {{ __('projecten.preview.view_preview_button') }}
                  </p>
                </div>
              </a>
            </div>
          </div>
  
          <div x-show="openPreviewSection" x-transition>
            @if($hasPreviewLink)
              @php
                $previewLogs = $project->previewViews()->latest()->limit(10)->get();
              @endphp
  
              @if($previewLogs->isNotEmpty())
                <div class="space-y-1 mt-2 max-h-40 overflow-y-auto pr-1 pb-1">
                  @foreach($previewLogs as $view)
                    <div class="p-3 rounded-xl border border-gray-200 bg-white">
                      <div>
                        <ul class="flex items-center justify-between">
                          @php
                            $dt   = optional($view->created_at)->timezone('Europe/Amsterdam');
                            $when = $dt ? $dt->format('d-m-Y H:i') : '—';
                            $loc  = collect([$view->city, $view->region, $view->country])->filter()->implode(', ');
                          @endphp
                          <li class="text-xs font-semibold text-[#215558] truncate flex items-center gap-2">
                            <p>{{ $when }}</p>
                            <p>IP: {{ $view->ip ?: '—' }}</p>
                          </li>
                          <li class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-[#b3e6ff] text-[#0f6199]">Preview bekeken</li>
                        </ul>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif
            @endif
  
            <template x-if="!previewUrl">
              <p class="text-[#215558] text-xs font-semibold opacity-75">
                {{ __('projecten.preview.none') }}
              </p>
            </template>
          </div>
        </div>

        @if($previewFeedback->isNotEmpty())
          <div class="pt-3 mt-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 min-w-0">
                <p class="text-base text-[#215558] font-bold leading-tight truncate">
                  Preview feedback
                </p>

                <button type="button"
                        class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                        @click="openPreviewFeedbackSection = !openPreviewFeedbackSection">
                  <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                    :class="openPreviewFeedbackSection ? 'rotate-180' : 'rotate-0'"></i>
                </button>
              </div>
            </div>

            <div x-show="openPreviewFeedbackSection" x-transition
                class="space-y-1 max-h-40 overflow-y-auto pr-1 pb-1 mt-2">
              @foreach($previewFeedback as $fb)
                @php
                  $dtFb       = optional($fb->created_at)->timezone('Europe/Amsterdam');
                  $whenFb     = $dtFb ? $dtFb->format('d-m-Y H:i') : '—';
                  $createdIso = $dtFb ? $dtFb->toIso8601String() : null;
                @endphp

                <div class="p-3 rounded-xl border border-gray-200 bg-white"
                    data-feedback-id="{{ $fb->id }}"
                    @if($createdIso)
                      x-data="feedbackTimer('{{ $createdIso }}')"
                      x-init="start()"
                    @endif
                >
                  <div class="flex items-center justify-between">
                    <div class="flex flex-col w-fit max-w-[75%] ">
                      <p class="text-xs font-semibold text-[#215558] truncate">
                        {{ $whenFb }}
                      </p>
                      <p class="text-xs font-medium text-[#215558] italic mt-1">
                        {{ $fb->feedback }}
                      </p>
                    </div>
                    <span class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-[#b3e6ff] text-[#0f6199]">
                      Feedback achtergelaten
                    </span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif
                
        <div class="pt-3 mt-4 border-t border-gray-200"
            x-show="statusValue === 'offerte'"
            x-cloak>
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
              <p class="text-base text-[#215558] font-bold leading-tight truncate">
                Offerte
              </p>

              <button type="button"
                      class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                      @click="openOfferteSection = !openOfferteSection">
                <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                  :class="openOfferteSection ? 'rotate-180' : 'rotate-0'"></i>
              </button>
            </div>

            {{-- 👇 zelfde pattern als bij preview: relative wrapper + genereer-knop --}}
            <div class="flex items-center gap-2">
              {{-- OOG-ICOON + NOTITIES --}}
              <div class="relative">
                <button type="button"
                        class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                        @click.stop="openOfferteOverlay = !openOfferteOverlay">
                  <i class="fa-solid fa-eyes text-[#215558] text-xs"></i>

                  <div
                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                          opacity-0 invisible translate-y-1 pointer-events-none
                          group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                          transition-all duration-200 ease-out z-10">
                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                      Open offertegesprek
                    </p>
                  </div>
                </button>

                {{-- 🔽 de uitklappende card, nu gekoppeld aan de relative wrapper --}}
                <div x-show="openOfferteOverlay"
                    x-transition
                    @click.outside="openOfferteOverlay = false"
                    class="absolute right-full mr-3 top-1/2 -translate-y-1/2 w-[380px] p-3 rounded-xl bg-white border border-gray-200 shadow-lg z-30"
                    style="display:none;">
                  <div>
                    <p class="text-base text-[#215558] font-black mb-3">
                      Notities offertegesprek
                    </p>

                    <div class="grid gap-3 mt-2">
                      <div>
                        <label class="block text-xs text-[#215558] opacity-70 mb-1">
                          Notities
                        </label>
                        <textarea
                          x-model="offerteNotes"
                          class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                          rows="6"></textarea>
                      </div>

                      <div class="flex items-center justify-end mt-3 gap-2">
                        <button type="button"
                                class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                                :disabled="savingOfferteNotes"
                                @click="saveOfferteNotes()">
                          <span x-show="!savingOfferteNotes">Notities opslaan</span>
                          <span x-show="savingOfferteNotes">Bezig met opslaan…</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- 🔹 EXTRA KNOP: OFFERTE GENEREREN (alleen als taak voltooid is) --}}
              <a
                x-show="offerteTaskCompleted"
                x-cloak
                href="#"
                class="w-7 h-7 rounded-full bg-gray-200 hover:gray-300 flex items-center justify-center transition duration-200 relative group cursor-pointer"
              >
                <i class="fa-solid fa-file-lines text-[#215558] text-xs"></i>

                <div
                  class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                        opacity-0 invisible translate-y-1 pointer-events-none
                        group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                        transition-all duration-200 ease-out z-10">
                  <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                    Genereer offerte
                  </p>
                </div>
              </a>
            </div>
          </div>

          <div x-show="openOfferteSection" x-transition>
            <template x-if="hasOfferteTask">
              <div class="space-y-1 mt-2">
                <div class="p-3 rounded-xl border border-gray-200 bg-white">
                  <div class="flex items-center justify-between">
                    <div class="flex flex-col w-fit max-w-[75%]">
                      <p class="text-xs font-semibold text-[#215558] truncate"
                        x-text="offerteTaskTitle"></p>
                      <p class="text-xs font-medium text-[#215558] mt-1"
                        x-text="offerteTaskDescription"></p>
                    </div>

                    <div class="flex items-center gap-2">
                      {{-- knop alleen als NIET voltooid --}}
                      <button type="button"
                              x-show="!offerteTaskCompleted"
                              class="px-2.5 py-0.5 flex items-center rounded-full text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition duration-300 cursor-pointer whitespace-nowrap disabled:opacity-60"
                              :disabled="markingOfferteTask"
                              @click="completeOfferteTask()">
                        <span x-show="!markingOfferteTask">
                          <i class="fa-solid fa-check fa-xs mr-1"></i>
                          Offertegesprek markeren als voltooid
                        </span>
                        <span x-show="markingOfferteTask">
                          <i class="fa-solid fa-spinner fa-spin fa-xs mr-1"></i>
                          Bezig...
                        </span>
                      </button>

                      {{-- badge als WEL voltooid --}}
                      <span x-show="offerteTaskCompleted"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">
                        Voltooid
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        {{-- Belmomenten --}}
        <div class="pt-3 mt-4 border-t border-gray-200"
            x-data="(() => {
                const base = callLog({
                  csrf: '{{ csrf_token() }}',
                  storeUrl: '{{ route('support.projecten.calls.store', $project) }}',
                  initialCalls: @js(
                    ($project->callLogs ?? collect())->map(fn($log) => [
                        'id'        => $log->id,
                        'called_at' => optional($log->called_at)->format('d-m-Y H:i'),
                        'outcome'   => $log->outcome,
                        'note'      => $log->note,
                        'user_name' => optional($log->user)->name,
                    ])
                  ),
                });

                return {
                  openCallSection: false,
                  ...base,
                };
            })()">

          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 min-w-0">
              <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                Belmomenten
              </h3>

              <button type="button"
                      class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                      @click="openCallSection = !openCallSection">
                <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                  :class="openCallSection ? 'rotate-180' : 'rotate-0'"></i>
              </button>
            </div>

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

              {{-- 🔽 UITKLAPBARE CARD VOOR NIEUW BELMOMENT --}}
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
                      <label class="block text-xs text-[#215558] opacity-70 mb-1">
                        Resultaat
                      </label>
                      <select x-model="outcome"
                              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
                        <option value="">
                          Kies resultaat
                        </option>
                        <option value="geen_antwoord">
                          Geen antwoord
                        </option>
                        <option value="gesproken">
                          Gesproken
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-xs text-[#215558] opacity-70 mb-1">
                        Notitie
                      </label>
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
                        <span x-show="!loading">
                          Opslaan
                        </span>
                        <span x-show="loading">
                          Opslaan...
                        </span>
                      </button>
                    </div>
                  </form>
                </div>
              </div>
              {{-- 🔼 EINDE UITKLAPBARE CARD --}}
            </div>
          </div>

          <div x-show="openCallSection" x-transition>
            <template x-if="!calls.length">
              <p class="text-[#215558] text-xs font-semibold opacity-75">
                {{ __('projecten.calls.none') }}
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
  
        @if($aanvraag)
          <div
            x-show="openRequestOverlay"
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
            style="display:none;"
          >
            <div class="absolute inset-0 bg-black/40"
                 @click="openRequestOverlay = false"></div>
  
            <div
              class="relative w-[900px] max-w-[95vw] bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden"
              x-transition:enter="transition duration-200 ease-out"
              x-transition:enter-start="opacity-0 translate-y-2"
              x-transition:enter-end="opacity-100 translate-y-0"
              x-transition:leave="transition duration-150 ease-in"
              x-transition:leave-start="opacity-100 translate-y-0"
              x-transition:leave-end="opacity-0 translate-y-2"
            >
              <div class="w-full flex items-center justify-between px-5 py-4">
                <div>
                  <h2 class="text-lg font-black text-[#215558]">
                    {{ __('projecten.request.section_title') }}
                  </h2>
                </div>
  
                <button type="button"
                        class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center cursor-pointer"
                        @click="openRequestOverlay = false">
                  <i class="fa-solid fa-xmark text-[#215558]"></i>
                </button>
              </div>
  
              <hr class="border-gray-200">
  
              <div class="p-5 max-h-[420px] overflow-y-auto">
                @if($questions->isEmpty())
                  <p class="text-xs text-[#215558] opacity-75">
                    {{ __('projecten.request.none') }}
                  </p>
                @else
                  <div class="grid grid-cols-2 gap-3 text-[11px] text-[#215558]">
                    @foreach($questions as $q)
                      <div class="p-3 rounded-xl border border-gray-200 bg-gray-50 flex flex-col justify-between">
                        <p class="block text-xs text-[#215558] opacity-70 mb-1">
                          {{ $q->question }}
                        </p>
                        <p class="w-full bg-white px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-[#215558]">
                          {{ $q->answer ?: '—' }}
                        </p>
                      </div>
                    @endforeach
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>