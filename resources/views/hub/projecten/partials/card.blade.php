{{-- resources/views/hub/projecten/partials/card.blade.php --}}

@php
  /** @var \App\Models\Project $project */

  // ✅ Project status
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
    'preview_approved' => [
      'bg'   => 'bg-[#C2F0D5]',
      'text' => 'text-[#20603a]',
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

  // ✅ Aanvraag koppeling + vragen (altijd “mee”)
  $aanvraag  = $project->aanvraag ?? null;
  $callTask  = null;
  $questions = collect();

  if ($aanvraag) {
      $callTask  = $aanvraag->tasks->firstWhere('type', 'call_customer');
      $questions = $callTask?->questions ?? collect();
  }

  // ✅ Aanvraag meta (zoals aanvragen pagina)
  $aanvraagChoiceMap = [
    'new'   => __('potentiele_klanten.choices.new'),
    'renew' => __('potentiele_klanten.choices.renew'),
  ];

  $aanvraagChoiceTitle = $aanvraag
      ? ($aanvraagChoiceMap[$aanvraag->choice] ?? __('potentiele_klanten.choices.default'))
      : null;

  $aanvraagAllowedStatuses = ['prospect', 'contact', 'intake', 'dead', 'lead'];

  $aanvraagStatusValue = ($aanvraag && in_array($aanvraag->status, $aanvraagAllowedStatuses, true))
      ? $aanvraag->status
      : 'prospect';

  $aanvraagStatusLabel = ucfirst((string) $aanvraagStatusValue);

  $aanvraagBadgeColors = [
      'prospect' => ['bg' => 'bg-[#b3e6ff]', 'text' => 'text-[#0f6199]'],
      'contact'  => ['bg' => 'bg-[#C2F0D5]', 'text' => 'text-[#20603a]'],
      'intake'   => ['bg' => 'bg-[#ffdfb3]', 'text' => 'text-[#a0570f]'],
      'dead'     => ['bg' => 'bg-[#ffb3b3]', 'text' => 'text-[#8a2a2d]'],
      'lead'     => ['bg' => 'bg-[#e0d4ff]', 'text' => 'text-[#4c2a9b]'],
  ];

  $aanvraagBadge = $aanvraagBadgeColors[$aanvraagStatusValue] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-700'];

  // ✅ Offerte task (project)
  /** @var \App\Models\ProjectTask|null $offerteTask */
  $offerteTask = $project->tasks->firstWhere('type', 'call_customer');
  $offerteNoteQuestion    = $offerteTask?->questions->first();
  $offerteTaskTitle       = $offerteTask->title ?? 'Bellen met de klant';
  $offerteTaskDescription = $offerteTask->description ?? 'Bel de klant t.a.v. feedback/goedkeuring preview';
  $offerteNotes           = $offerteNoteQuestion->answer ?? '';
  $offerteTaskCompleted   = (bool) ($offerteTask?->completed_at);

  // ✅ Feedback + calls
  $previewFeedback = $project->previewFeedbacks()->latest()->limit(10)->get();
  $callLogs = $project->callLogs ?? collect();

  // ✅ Offerte link
  /** @var \App\Models\Offerte|null $existingOfferte */
  $existingOfferte = $project->offerte ?? null;

  $offerteBeheerderUrl = $existingOfferte
      ? route('offerte.beheerder.show', ['token' => $existingOfferte->public_uuid])
      : null;

  $hasPreviewLink = !empty($project->preview_url) && !empty($project->preview_token);

  // ✅ kijk-momenten (views) altijd klaarzetten
  $previewLogs = $hasPreviewLink
      ? $project->previewViews()->latest()->limit(10)->get()
      : collect();

  $hasViewMoments = $previewLogs->isNotEmpty();

  $choiceMap = [
    'new'   => __('potentiele_klanten.choices.new'),
    'renew' => __('potentiele_klanten.choices.renew'),
  ];

  $choiceTitle = $aanvraag
    ? ($choiceMap[$aanvraag->choice] ?? __('potentiele_klanten.choices.default'))
    : __('potentiele_klanten.choices.default');
@endphp

<div
  data-card-id="{{ $project->id }}"
  data-status="{{ $statusValue }}"
  x-on:dragover.prevent="onCardDragOver($event)"
  x-on:dragleave="onCardDragLeave($event)"
  x-on:drop="onCardDrop($event)"
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
      openDetails: true,

      openPreviewSection: true,
      openPreviewFeedbackSection: true,
      openOfferteSection: true,

      openPreviewForm: false,
      openRequestOverlay: false,
      savingPreview: false,

      generatingOfferte: false,

      // Offerte-state
      hasOfferteTask: {{ $offerteTask ? 'true' : 'false' }},
      openOfferteOverlay: false,
      savingOfferteNotes: false,
      markingOfferteTask: false,
      offerteTaskTitle: @js($offerteTaskTitle),
      offerteTaskDescription: @js($offerteTaskDescription),
      offerteNotes: @js($offerteNotes),
      offerteTaskCompleted: {{ $offerteTaskCompleted ? 'true' : 'false' }},

      offerteGenerated: {{ $existingOfferte ? 'true' : 'false' }},
      offerteBeheerderUrl: @js($offerteBeheerderUrl),

      hasViewMoments: {{ $hasViewMoments ? 'true' : 'false' }},

      previewUrl: @js($project->preview_url ?? null),
      previewUrlInput: @js($project->preview_url ?? ''),
      previewLink: @js(
        $project->preview_url && $project->preview_token
          ? route('preview.show', ['token' => $project->preview_token])
          : null
      ),

      savePreview() {
          const self = this;
          const hadPreviewBefore = !!self.previewUrl;
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

              // ✅ kijk-momenten state bijwerken (zonder extra fetch)
              if (!self.previewUrl) {
                  // preview_url weg -> ook geen kijk-momenten tonen
                  self.hasViewMoments = false;
              } else if (!hadPreviewBefore && self.previewUrl) {
                  // preview_url net toegevoegd -> default: nog geen kijk-momenten
                  self.hasViewMoments = false;
              }

              // (optioneel beter) als je backend dit meegeeft:
              if (data && typeof data.has_view_moments !== 'undefined') {
                  self.hasViewMoments = !!data.has_view_moments;
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
                        'bg-[#d1fae5]','text-[#065f46]',
                        'bg-[#ffdfb3]','text-[#a0570f]',
                        'bg-gray-100','text-gray-600',
                        'bg-slate-100','text-slate-700',
                      ];

                      badge.classList.remove.apply(badge.classList, allColorClasses);

                      const colorMap = {
                        preview:          ['bg-[#e0d4ff]', 'text-[#4c2a9b]'],
                        waiting_customer: ['bg-[#b3e6ff]', 'text-[#0f6199]'],
                        preview_approved: ['bg-[#d1fae5]', 'text-[#065f46]'],
                        offerte:          ['bg-[#ffdfb3]', 'text-[#a0570f]'],
                      };

                      const classes = colorMap[data.status] || ['bg-slate-100','text-slate-700'];
                      badge.classList.add.apply(badge.classList, classes);
                  }

                  const cardEl = self.$root.closest('[data-card-id]');
                  if (cardEl) {
                    window.dispatchEvent(new CustomEvent('project-card-status-changed', {
                      detail: {
                        id: cardEl.dataset.cardId,
                        newValue: data.status,
                        data
                      }
                    }));
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
                  self.hasOfferteTask         = true;
                  self.offerteTaskTitle       = data.offerte_task.title || self.offerteTaskTitle;
                  self.offerteTaskDescription = data.offerte_task.description || self.offerteTaskDescription;
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
      },

      generateOfferte() {
          const self = this;
          if (self.generatingOfferte) return;

          self.generatingOfferte = true;

          fetch('{{ route('support.projecten.offerte.generate', $project) }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}',
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json',
              },
              credentials: 'same-origin',
              body: JSON.stringify({}),
          })
          .then(res => {
              if (!res.ok) throw new Error('Failed');
              return res.json();
          })
          .then(data => {
              if (!data.success) {
                  throw new Error(data.message || 'Failed');
              }

              self.offerteGenerated = true;

              if (data.redirect_url) {
                  self.offerteBeheerderUrl = data.redirect_url;
              }

              if (window.showToast) {
                  window.showToast('Offerte succesvol gegenereerd.', 'success');
              }
          })
          .catch((err) => {
              console.error(err);
              if (window.showToast) {
                  window.showToast('Genereren van offerte is mislukt.', 'error');
              } else {
                  alert('Genereren van offerte is mislukt.');
              }
          })
          .finally(() => {
              self.generatingOfferte = false;
          });
      },
  }"
>
  {{-- ✅ KORTE HEADER (zoals je al had) --}}
  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0 w-full">
      <div>
        <div class="flex items-center justify-between mb-1">
          <h2 class="font-black text-[#215558] text-xl">
            {{ $choiceTitle }}
          </h2>

          <div class="flex items-center gap-2">
            @if($aanvraag)
              <a href="tel:{{ $aanvraag->contactPhone }}" class="w-7 h-7 rounded-full bg-[#215558]/20 flex items-center justify-center">
                <i class="fa-solid fa-phone text-[#215558] text-xs"></i>
              </a>
              <a href="mailto:{{ $aanvraag->contactEmail }}" class="w-7 h-7 rounded-full bg-[#215558]/20 flex items-center justify-center">
                <i class="fa-solid fa-envelope text-[#215558] text-xs"></i>
              </a>
            @else
              <span class="w-7 h-7 rounded-full bg-[#215558]/10 flex items-center justify-center opacity-50 cursor-not-allowed">
                <i class="fa-solid fa-phone text-[#215558] text-xs"></i>
              </span>
              <span class="w-7 h-7 rounded-full bg-[#215558]/10 flex items-center justify-center opacity-50 cursor-not-allowed">
                <i class="fa-solid fa-envelope text-[#215558] text-xs"></i>
              </span>
            @endif
            @if($aanvraag)
              @include('hub.potentiele-klanten.partials.owner-badge', ['aanvraag' => $aanvraag])
            @endif
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
          <span class="text-sm font-semibold text-[#215558]/75 flex items-center gap-2">
            <i class="fa-solid fa-building fa-xs"></i>
            {{ $project->company ?: __('projecten.unknown_company') }}
          </span>

          <span class="text-sm font-semibold text-[#215558]/75 flex items-center gap-2">
            <i class="fa-solid fa-user fa-xs"></i>
            {{ $project->contact_name ?: '—' }}
          </span>
        </div>

        @php
          $projectStepOrder = ['preview', 'waiting_customer', 'preview_approved', 'offerte'];
          $projectStepIndex = array_search($statusValue, $projectStepOrder, true);

          $projectProgressPercent = $projectStepIndex === false
              ? 0
              : (($projectStepIndex + 1) / count($projectStepOrder)) * 100;
        @endphp

        <div class="mt-4 mb-6">
          <div class="grid grid-cols-4 text-xs font-semibold text-[#215558] mb-2">
            <p class="opacity-50">Preview</p>
            <p class="opacity-50">Wachten klant</p>
            <p class="opacity-50">Goedgekeurd</p>
            <p class="opacity-50">Offerte</p>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
            <div class="h-full bg-[#0F9B9F] rounded-full transition-all duration-300"
                style="width: {{ $projectProgressPercent }}%;"></div>
          </div>
        </div>

        <hr class="border-gray-200 mb-6">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {{-- LINKS (2/3) --}}
          <div class="lg:col-span-2 flex flex-col gap-6">
 
          {{-- ✅ PROJECT DETAILS (split) --}}
          @php
            use Illuminate\Support\Str;

            $summaryRaw = (string) ($project?->intake_ai_summary ?? '');
            $summary    = trim($summaryRaw);

            // ✅ Normalize: maak van "Kopje:" óók "**Kopje**:" zodat je regex altijd matcht
            $knownHeadings = [
              'Korte intro',
              'Doel',
              'Must-haves',
              'Nice-to-haves',
              'Aandachtspunten',
              'Moet in de preview',
              'Moet in de preview (homepage)',
              'Huisstijl',
              'Home preview opbouw (voorstel)',
              'Home preview opbouw',
              'Preview opbouw',
              'Volgende stap',
              'Actiepunten',
              'Belangrijkste wensen',
            ];

            foreach ($knownHeadings as $h) {
                $summary = preg_replace(
                    '/^(?:\*\*)?' . preg_quote($h, '/') . '(?:\*\*)?\s*:\s*/m',
                    '**' . $h . '**: ',
                    $summary
                );
            }

            $getSection = function (string $text, array $names): ?string {
                foreach ($names as $name) {
                    $pattern = '/\*\*' . preg_quote($name, '/') . '\*\*\s*:?\s*(.*?)(?=\n\s*\n\*\*[^*]+\*\*|\z)/s';
                    if (preg_match($pattern, $text, $m)) {
                        $val = trim($m[1] ?? '');
                        if ($val !== '') return $val;
                    }
                }
                return null;
            };

            // ✅ Project stuk
            $intro = $getSection($summary, ['Korte intro']);
            $doel  = $getSection($summary, ['Doel']);
            $must  = $getSection($summary, ['Must-haves']);
            $nice  = $getSection($summary, ['Nice-to-haves']);
            $focus = $getSection($summary, ['Aandachtspunten', "Risico’s / aandachtspunten", "Risico's / aandachtspunten"]);
            $next  = $getSection($summary, ['Volgende stap', 'Actiepunten']);

            // ✅ Preview stuk
            $previewMust = $getSection($summary, ['Moet in de preview (homepage)', 'Moet in de preview', 'Belangrijkste wensen']);
            $huisstijl   = $getSection($summary, ['Huisstijl']);
            $home        = $getSection($summary, ['Home preview opbouw (voorstel)', 'Home preview opbouw', 'Preview opbouw']);

            $projectMdParts = [];
            if ($intro) $projectMdParts[] = "**Korte intro**:\n" . trim($intro);
            if ($doel)  $projectMdParts[] = "**Doel**:\n" . trim($doel);
            if ($must)  $projectMdParts[] = "**Must-haves**:\n" . trim($must);
            if ($nice)  $projectMdParts[] = "**Nice-to-haves**:\n" . trim($nice);
            if ($focus) $projectMdParts[] = "**Aandachtspunten**:\n" . trim($focus);
            if ($next)  $projectMdParts[] = "**Volgende stap**:\n" . trim($next);

            $projectMd = trim(implode("\n\n", $projectMdParts));

            $previewMdParts = [];
            if ($previewMust) $previewMdParts[] = "**Wat moet erin**:\n" . trim($previewMust);
            if ($huisstijl)   $previewMdParts[] = "**Huisstijl**:\n" . trim($huisstijl);
            if ($home)        $previewMdParts[] = "**Home opbouw**:\n" . trim($home);

            $previewMd = trim(implode("\n\n", $previewMdParts));
          @endphp

          {{-- ✅ PROJECT DETAILS --}}
          <div class="bg-[#fff] rounded-4xl p-8">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                <i class="fa-solid fa-file-pen fa-sm"></i>
                Project details
              </h3>

              <span class="px-2.5 py-0.5 font-semibold text-purple-700 bg-purple-200 text-[11px] flex items-center gap-2 rounded-full">
                <i class="fa-solid fa-sparkle fa-xs"></i>
                Samenvatting door AI
              </span>
            </div>

            @if($project && filled($summary) && filled($projectMd))
              <div class="text-sm font-medium text-[#215558] leading-[20px] opacity-75
                          max-h-[220px] overflow-y-auto pr-2 break-words whitespace-normal
                          [&_p]:mb-2 [&_p:last-child]:mb-0
                          [&_p+_ul]:mt-2 [&_ul]:mb-3 [&_ul+_p]:mt-3
                          [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1
                          [&_li_strong]:text-[#215558]">
                {!! Str::markdown($projectMd, [
                      'html_input' => 'strip',
                      'allow_unsafe_links' => false,
                ]) !!}
              </div>
            @elseif($project && filled($summary))
              {{-- fallback: toon volledige samenvatting als parsing faalt --}}
              <div class="text-sm font-medium text-[#215558] leading-[20px] opacity-75
                          max-h-[220px] overflow-y-auto pr-2 break-words whitespace-normal
                          [&_p]:mb-2 [&_p:last-child]:mb-0
                          [&_p+_ul]:mt-2 [&_ul]:mb-3 [&_ul+_p]:mt-3
                          [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1
                          [&_li_strong]:text-[#215558]">
                {!! Str::markdown($summary, [
                      'html_input' => 'strip',
                      'allow_unsafe_links' => false,
                ]) !!}
              </div>
            @else
              <p class="text-[#215558] text-xs font-semibold opacity-75">
                Nog geen intake-samenvatting beschikbaar.
              </p>
            @endif
          </div>

          {{-- ✅ PREVIEW DETAILS --}}
          <div class="bg-[#fff] rounded-4xl p-8">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles fa-sm"></i>
                Preview details
              </h3>

              <span class="px-2.5 py-0.5 font-semibold text-purple-700 bg-purple-200 text-[11px] flex items-center gap-2 rounded-full">
                <i class="fa-solid fa-sparkle fa-xs"></i>
                Voorgesteld door AI
              </span>
            </div>

            @if($project && filled($summary) && filled($previewMd))
              <div class="text-sm font-medium text-[#215558] leading-[20px] opacity-75
                          max-h-[220px] overflow-y-auto pr-2 break-words whitespace-normal
                          [&_p]:mb-2 [&_p:last-child]:mb-0
                          [&_p+_ul]:mt-2 [&_ul]:mb-3 [&_ul+_p]:mt-3
                          [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1
                          [&_li_strong]:text-[#215558]">
                {!! Str::markdown($previewMd, [
                      'html_input' => 'strip',
                      'allow_unsafe_links' => false,
                ]) !!}
              </div>
            @else
              <p class="text-[#215558] text-xs font-semibold opacity-75">
                Nog geen preview-instructies gevonden in de samenvatting.
              </p>
            @endif
          </div>

            {{-- ✅ PREVIEW --}}
            <div class="bg-[#fff] rounded-4xl p-8 overflow-visible">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-4 min-w-0">
                  <h3 class="text-base text-[#215558] font-black leading-tight truncate flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles fa-sm"></i>
                    Preview & kijk-momenten
                  </h3>
                </div>

                <div class="flex items-center gap-2">
                  <span
                    x-show="!!previewUrl"
                    x-cloak
                    class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#b3e6ff] text-[#0f6199]"
                  >
                    Wachten op goedkeuring klant
                  </span>
                  {{-- + preview toevoegen --}}
                  <div class="relative">
                    <button type="button"
                            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                            x-on:click.stop="openPreviewForm = !openPreviewForm">
                      <i class="fa-solid fa-plus text-[#215558] text-xs"></i>

                      <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                                  opacity-0 invisible translate-y-1 pointer-events-none
                                  group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                                  transition-all duration-200 ease-out z-10">
                        <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                          Preview-URL instellen
                        </p>
                      </div>
                    </button>

                    <div x-show="openPreviewForm"
                         x-transition
                         x-on:click.outside="openPreviewForm = false"
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
                                    x-on:click="savePreview()">
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

                  {{-- 👁 aanvraag vragen (blijft) --}}
                  @if($aanvraag && $questions->isNotEmpty())
                    <div class="relative">
                      <button type="button"
                              class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                              x-on:click.stop="openRequestOverlay = true">
                        <i class="fa-solid fa-eyes text-[#215558] text-xs"></i>

                        <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
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

                  {{-- 🔗 preview link --}}
                  <a
                    x-show="previewUrl"
                    x-cloak
                    :href="previewLink || '#'"
                    target="_blank"
                    class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-[#215558] hover:bg-gray-300 transition cursor-pointer relative group"
                  >
                    <i class="fa-solid fa-up-right-from-square text-xs"></i>

                    <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
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

              <div class="mt-2">
                @if($hasPreviewLink)
                  @if($previewLogs->isNotEmpty())
                    <div class="space-y-1 max-h-40 overflow-y-auto pr-1 pb-1">
                      @foreach($previewLogs as $view)
                        @php
                          $dt   = optional($view->created_at)->timezone('Europe/Amsterdam');
                          $when = $dt ? $dt->format('d-m-Y H:i') : '—';
                        @endphp
                        <div class="p-3 rounded-xl border border-gray-200 bg-white">
                          <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-[#215558] truncate">
                              {{ $when }} · IP: {{ $view->ip ?: '—' }}
                            </p>
                            <span class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-[#b3e6ff] text-[#0f6199]">
                              Preview bekeken
                            </span>
                          </div>
                        </div>
                      @endforeach
                    </div>
                  @endif
                @endif

                <template x-if="previewUrl && !hasViewMoments">
                  <p class="text-[#215558] text-xs font-semibold opacity-75">
                    Nog geen kijk-momenten.
                  </p>
                </template>

                <template x-if="!previewUrl">
                  <p class="text-[#215558] text-xs font-semibold opacity-75">
                    {{ __('projecten.preview.none') }}
                  </p>
                </template>
              </div>
            </div>

            {{-- ✅ PREVIEW FEEDBACK --}}
            @if($previewFeedback->isNotEmpty())
              <div class="bg-[#fff] rounded-4xl p-8">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2 min-w-0">
                    <p class="text-base text-[#215558] font-black leading-tight truncate flex items-center gap-2">
                      <i class="fa-solid fa-message fa-sm opacity-70"></i>
                      Preview feedback
                    </p>

                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            x-on:click="openPreviewFeedbackSection = !openPreviewFeedbackSection">
                      <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                         :class="openPreviewFeedbackSection ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                  </div>
                </div>

                <div class="space-y-1 max-h-40 overflow-y-auto pr-1 pb-1 mt-2">
                  @foreach($previewFeedback as $fb)
                    @php
                      $dtFb   = optional($fb->created_at)->timezone('Europe/Amsterdam');
                      $whenFb = $dtFb ? $dtFb->format('d-m-Y H:i') : '—';
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
                        <div class="flex flex-col w-fit max-w-[75%]">
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

            {{-- ✅ OFFERTE --}}
            <div class="bg-[#fff] rounded-4xl p-8 overflow-visible" x-cloak>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                  <p class="text-base text-[#215558] font-black leading-tight truncate flex items-center gap-2">
                    <i class="fa-solid fa-file-lines fa-sm opacity-70"></i>
                    Offerte
                  </p>

                  <button type="button"
                          class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                          x-on:click="openOfferteSection = !openOfferteSection">
                    <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                       :class="openOfferteSection ? 'rotate-180' : 'rotate-0'"></i>
                  </button>
                </div>

                <div class="flex items-center gap-2">
                  {{-- 👁 notities --}}
                  <div class="relative">
                    <button type="button"
                            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                            x-on:click.stop="openOfferteOverlay = !openOfferteOverlay">
                      <i class="fa-solid fa-eyes text-[#215558] text-xs"></i>

                      <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                                  opacity-0 invisible translate-y-1 pointer-events-none
                                  group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                                  transition-all duration-200 ease-out z-10">
                        <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                          Open offertegesprek
                        </p>
                      </div>
                    </button>

                    <div x-show="openOfferteOverlay"
                         x-transition
                         x-on:click.outside="openOfferteOverlay = false"
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
                                    x-on:click="saveOfferteNotes()">
                              <span x-show="!savingOfferteNotes">Notities opslaan</span>
                              <span x-show="savingOfferteNotes">Bezig met opslaan…</span>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Genereer / bekijk offerte --}}
                  <button
                    type="button"
                    x-show="offerteTaskCompleted"
                    x-cloak
                    class="w-7 h-7 rounded-full bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition duration-200 relative group cursor-pointer disabled:opacity-60"
                    :disabled="generatingOfferte"
                    x-on:click="
                      if (offerteGenerated && offerteBeheerderUrl) {
                        window.location.href = offerteBeheerderUrl;
                      } else {
                        generateOfferte();
                      }
                    "
                  >
                    <i x-show="!generatingOfferte && !offerteGenerated"
                       class="fa-solid fa-file-lines text-[#215558] text-xs"></i>

                    <i x-show="!generatingOfferte && offerteGenerated"
                       class="fa-solid fa-up-right-from-square text-[#215558] text-xs"></i>

                    <i x-show="generatingOfferte"
                       class="fa-solid fa-spinner fa-spin text-[#215558] text-xs"></i>

                    <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                                opacity-0 invisible translate-y-1 pointer-events-none
                                group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                                transition-all duration-200 ease-out z-10">
                      <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap"
                         x-text="offerteGenerated ? 'Bekijk offerte' : 'Genereer offerte'"></p>
                    </div>
                  </button>
                </div>
              </div>

              <div class="mt-2">
                <template x-if="hasOfferteTask">
                  <div class="space-y-1">
                    <div class="p-3 rounded-xl border border-gray-200 bg-white">
                      <div class="flex items-center justify-between">
                        <div class="flex flex-col w-fit max-w-[75%]">
                          <p class="text-xs font-semibold text-[#215558] truncate" x-text="offerteTaskTitle"></p>
                          <p class="text-xs font-medium text-[#215558] mt-1" x-text="offerteTaskDescription"></p>
                        </div>

                        <div class="flex items-center gap-2">
                          <button type="button"
                                  x-show="!offerteTaskCompleted"
                                  class="px-2.5 py-0.5 flex items-center rounded-full text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition duration-300 cursor-pointer whitespace-nowrap disabled:opacity-60"
                                  :disabled="markingOfferteTask"
                                  x-on:click="completeOfferteTask()">
                            <span x-show="!markingOfferteTask">
                              <i class="fa-solid fa-check fa-xs mr-1"></i>
                              Offertegesprek markeren als voltooid
                            </span>
                            <span x-show="markingOfferteTask">
                              <i class="fa-solid fa-spinner fa-spin fa-xs mr-1"></i>
                              Bezig...
                            </span>
                          </button>

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

            {{-- ✅ BELMOMENTEN (timeline style zoals aanvragen) --}}
            <div class="bg-[#fff] rounded-4xl p-8 overflow-visible"
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
                      openCallSection: true,
                      ...base,
                    };
                 })()">

              <div class="flex items-center justify-between mb-3">
                <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                  <i class="fa-solid fa-phone fa-sm"></i>
                  Belmomenten
                </h3>

                <div class="relative">
                  <button type="button"
                          class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                          x-on:click.stop="openCallPanel = !openCallPanel">
                    <i class="fa-solid fa-plus text-[#215558] text-xs"></i>

                    <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
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
                       x-on:click.outside="openCallPanel = false"
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
                            <option value="">Kies resultaat</option>
                            <option value="geen_antwoord">Geen antwoord</option>
                            <option value="gesproken">Gesproken</option>
                          </select>
                        </div>

                        <div>
                          <label class="block text-xs text-[#215558] opacity-70 mb-1">
                            Notitie
                          </label>
                          <textarea x-model="note"
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

              <template x-if="!calls.length">
                <p class="text-[#215558] text-xs font-semibold opacity-75">
                  {{ __('projecten.calls.none') }}
                </p>
              </template>

              <div class="overflow-x-visible" x-show="calls.length">
                <ul class="flex flex-col gap-2 divide-[#215558]/20 max-h-50 overflow-y-auto pr-1 mt-2">
                  <template x-for="call in calls" :key="call.id">
                    <li class="text-xs pl-8 py-2 relative">
                      <div class="absolute left-2.25 top-0 bottom-0 w-px bg-[#215558]/20"></div>
                      <div class="absolute left-1 top-2 w-3 h-3 rounded-full bg-[#f3f8f8] border-[2px] border-[#215558]/20 z-[1]"></div>

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

                        <span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                              :class="{
                                'bg-red-200 text-red-700': call.outcome === 'geen_antwoord',
                                'bg-green-200 text-green-700': call.outcome === 'gesproken',
                              }"
                              x-text="labels[call.outcome] || call.outcome"></span>
                      </div>
                    </li>
                  </template>
                </ul>
              </div>
            </div>
          </div>

          {{-- RECHTS (1/3) - ✅ altijd aanvraag details + data --}}
          <div class="flex flex-col gap-6">
            {{-- ✅ AANVRAAG DATA --}}
            <div class="bg-[#fff] rounded-4xl p-8">
              <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                <i class="fa-solid fa-database fa-sm"></i>
                Project data
              </h3>

              <div class="flex flex-col gap-2 mt-3">
                <div class="flex flex-col">
                  <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Datum aanvraag</p>
                  <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag?->created_at ?: '—' }}</p>
                </div>

                <div class="flex flex-col">
                  <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Type aanvraag</p>
                  <p class="text-sm font-semibold text-[#215558]">{{ $aanvraagChoiceTitle ?: '—' }}</p>
                </div>

                <div class="flex flex-col">
                  <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Lead doorgevoerd door</p>
                  <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag?->owner?->name ?: '—' }}</p>
                </div>
              </div>

              <hr class="border-t-[#215558]/20 my-5">

              <h3 class="text-[#215558] font-black text-sm shrink-0">
                Contactpersoon
              </h3>

              <div class="flex flex-col gap-2 mt-3">
                <div class="flex flex-col">
                  <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Naam</p>
                  <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag?->contactName ?: ($project->contact_name ?: '—') }}</p>
                </div>
                <div class="flex flex-col">
                  <p class="text-[11px] font-semibold opacity-50 text-[#215558]">E-mailadres</p>
                  <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag?->contactEmail ?: ($project->contact_email ?: '—') }}</p>
                </div>
                <div class="flex flex-col">
                  <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Telefoonnummer</p>
                  <p class="text-sm font-semibold text-[#215558]">{{ $aanvraag?->contactPhone ?: ($project->contact_phone ?: '—') }}</p>
                </div>
              </div>
            </div>
            @php
              // ✅ Project taken (project_tasks)
              $projectCurrentStatus = strtolower((string) ($statusValue ?? ($project->status ?? 'preview')));

              $projectTasks = $project->tasks ?? collect();
              $projectTasks = $projectTasks->sortBy(fn($t) => $t->order ?? 999)->values();
              $projectTasksByType = $projectTasks->keyBy('type');

              /**
              * ✅ Funnel UX net als aanvragen:
              * - UI-only status acties (niet in DB)
              * - echte taken komen uit project_tasks (en kunnen ook aangemaakt worden via checkbox)
              */
              $taskMeta = [
                  // UI-only status acties
                  'status_to_waiting_customer' => [
                      'category'      => 'Status',
                      'title'         => "Preview-URL is opgegeven en is automatisch verstuurd naar de klant",
                      'ui_only'       => true,
                      'target_status' => 'waiting_customer',
                      'order'         => 1,
                  ],

                  // Echte taken (DB)
                  'create_preview' => [
                      'category'  => 'Preview',
                      'title'     => 'Preview klaarzetten',
                      'order'     => 10,
                  ],
                  'process_feedback' => [
                      'category'  => 'Wachten klant',
                      'title'     => 'Feedback verwerken',
                      'order'     => 40,
                  ],
              ];

              $uiOnlyTypes = collect($taskMeta)
                  ->filter(fn ($m) => !empty($m['ui_only']))
                  ->keys()
                  ->all();

              $allTypes = array_values(array_unique(array_merge(
                  $uiOnlyTypes,
                  $projectTasks->pluck('type')->filter()->all()
              )));

              $tasksToShow = collect($allTypes)->map(function ($type) use ($projectTasksByType, $taskMeta) {
                  $meta = $taskMeta[$type] ?? [];

                  return $projectTasksByType->get($type) ?? (object) [
                      'id'          => null,
                      'type'        => $type,
                      'title'       => $meta['title'] ?? ucfirst(str_replace('_', ' ', $type)),
                      'status'      => 'open',
                      'category'    => $meta['category'] ?? 'Taak',
                      'order'       => $meta['order'] ?? 999,
                      'completed_at'=> null,
                  ];
              })->sortBy(fn($t) => $t->order ?? 999)->values();

              $initialTaskStates = $tasksToShow->mapWithKeys(function ($task) use ($taskMeta) {
                  $type = (string) $task->type;
                  $meta = $taskMeta[$type] ?? [];

                  if (!empty($meta['ui_only'])) {
                      return [$type => false];
                  }

                  $statusStr = strtolower((string) ($task->status ?? 'open'));
                  $isDone = !empty($task->completed_at) || in_array($statusStr, ['done', 'completed', 'closed'], true);

                  return [$type => $isDone];
              })->all();

              $projectTasksPayload = [
                  'projectId'        => (string) $project->id, // ✅ toevoegen
                  'csrf'             => csrf_token(),
                  'updateUrl'        => route('support.projecten.tasks.status.update', ['project' => $project->id]),
                  'statusUpdateUrl'  => route('support.projecten.status.update', ['project' => $project->id]),
                  'initial'          => $initialTaskStates,
                  'initialStatus' => $projectCurrentStatus,
              ];
            @endphp
            <div
              class="bg-[#fff] rounded-4xl p-8 h-fit"
              x-data='projectTasks(@json($projectTasksPayload))'
              x-init="init()"
            >
              <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2 mb-3">
                <i class="fa-solid fa-list fa-sm"></i>
                Taken
              </h3>
              <p
                x-show="currentStatus === 'waiting_customer'"
                x-cloak
                class="text-[#215558] text-xs font-semibold opacity-75"
              >
                Wachten op feedback op de preview...
              </p>
              <div class="flex flex-col gap-2 divide-[#215558]/20">
                @foreach($tasksToShow as $task)
                  @php
                    $meta = $taskMeta[$task->type] ?? [];

                    $category = $task->category ?? ($meta['category'] ?? 'Taak');
                    $title    = $task->title ?? ($meta['title'] ?? ucfirst(str_replace('_', ' ', $task->type)));

                    $isUiOnly     = (bool) ($meta['ui_only'] ?? false);
                    $targetStatus = $meta['target_status'] ?? null;
                  @endphp
                  <div x-show="isVisible('{{ $task->type }}')" x-cloak>
                    <p class="text-[11px] font-semibold opacity-50 text-[#215558]">
                      {{ $category }}
                    </p>
                    @if($isUiOnly)
                      <p class="text-sm font-semibold text-[#215558]">
                        {{ $title }}
                      </p>
                      <button
                        type="button"
                        class="bg-gray-200 hover:bg-gray-300 whitespace-nowrap text-[#215558] text-xs font-semibold px-3 py-1.5 rounded-full transition cursor-pointer mt-1.5"
                        @click="setStatus('{{ $targetStatus }}')"
                      >
                        Status aanpassen
                      </button>
                    @else
                      <div class="flex items-center justify-between">
                        <p
                          class="text-sm font-semibold text-[#215558] transition max-w-[80%]"
                          :class="tasks['{{ $task->type }}'] ? 'line-through opacity-60' : ''"
                        >
                          {{ $title }}
                        </p>
                        <input
                          type="checkbox"
                          :checked="tasks['{{ $task->type }}']"
                          :disabled="!canCheck('{{ $task->type }}')"
                          :class="!canCheck('{{ $task->type }}')
                            ? 'opacity-50 cursor-not-allowed'
                            : 'cursor-pointer'"
                          @change="toggle('{{ $task->type }}', $event.target.checked)"
                        >
                      </div>
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        {{-- ✅ Aanvraag-vragen overlay (zoals je al had) --}}
        @if($aanvraag)
          <div
            x-show="openRequestOverlay"
            x-transition.opacity
            class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
            style="display:none;"
          >
            <div class="absolute inset-0 bg-black/40"
                 x-on:click="openRequestOverlay = false"></div>

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
                        x-on:click="openRequestOverlay = false">
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