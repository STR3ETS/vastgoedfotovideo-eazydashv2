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
     data-owner-id="{{ $aanvraag->owner_id ?? '' }}"
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
              <a href="tel:{{ $aanvraag->contactPhone }}" class="w-7 h-7 rounded-full bg-[#215558]/20 flex items-center justify-center">
                <i class="fa-solid fa-phone text-[#215558] text-xs"></i>
              </a>
              <a href="mailto:{{ $aanvraag->contactEmail }}" class="w-7 h-7 rounded-full bg-[#215558]/20 flex items-center justify-center">
                <i class="fa-solid fa-envelope text-[#215558] text-xs"></i>
              </a>
              @include('hub.potentiele-klanten.partials.owner-badge', ['aanvraag' => $aanvraag])
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
          @php
            $stepOrder = ['prospect', 'contact', 'intake', 'lead'];
            $stepIndex = array_search($currentValue, $stepOrder, true);

            // fallback 0% voor onbekend (of zet 25 als je dat mooier vindt)
            $progressPercent = $stepIndex === false
                ? 0
                : (($stepIndex + 1) / count($stepOrder)) * 100;
          @endphp
          <div class="mt-4 mb-6" data-status-progress-wrapper>
            <div class="grid grid-cols-4 text-xs font-semibold text-[#215558] mb-2">
              <p data-status-label="prospect" class="opacity-50">Prospect</p>
              <p data-status-label="contact"  class="opacity-50">Contact</p>
              <p data-status-label="intake"   class="opacity-50">Intake</p>
              <p data-status-label="lead"     class="opacity-50">Lead</p>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <div
                class="h-full bg-[#0F9B9F] rounded-full transition-all duration-300"
                data-status-progress-bar
                style="width: {{ $progressPercent }}%;"
              ></div>
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
                @php use Illuminate\Support\Str; @endphp
                <div class="text-sm font-medium text-[#215558] leading-[20px] opacity-75
                            [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1
                            [&_p+ul]:mt-3
                            [&_p]:mb-0
                            [&_li_strong]:text-[#215558]">
                  {!! Str::markdown($aanvraag->ai_summary ?? '', [
                        'html_input' => 'strip',
                        'allow_unsafe_links' => false,
                  ]) !!}
                </div>
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
                  <ul class="flex flex-col gap-2 divide-[#215558]/20 overflow-y-auto pr-1 mt-2" x-show="calls.length">
                    <template x-for="call in calls" :key="call.id">
                      <li class="text-xs pl-8 py-2 relative">
                        {{-- verticale lijn --}}
                        <div class="absolute left-2.25 top-0 bottom-0 w-px bg-[#215558]/20"></div>

                        {{-- bolletje dat links over de lijn uitsteekt --}}
                        <div
                          class="absolute left-1 top-2 w-3 h-3 rounded-full bg-[#f3f8f8] border-[2px] border-[#215558]/20 z-[1]">
                        </div>

                        <div class="flex items-center justify-between gap-2">
                          <div class="w-full flex flex-col gap-2">
                            <div class="flex items-center gap-4">
                              <div class="flex flex-col">
                                <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Gebeld door</p>
                                <p class="text-sm font-semibold text-[#215558]" x-text="(call.user_name || 'Onbekend')"></p>
                              </div>
                              <div class="flex flex-col">
                                <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Gebeld op</p>
                                <p class="text-sm font-semibold text-[#215558]" x-text="call.called_at"></p>
                              </div>
                            </div>
                            <div class="flex flex-col w-[80%]">
                              <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Opmerking</p>
                              <p class="text-sm font-semibold text-[#215558]" x-show="call.note" x-text="call.note"></p>
                            </div>
                          </div>

                          <span
                            class="inline-flex whitespace-nowrap items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
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
              <div class="bg-[#fff] rounded-4xl p-8 overflow-visible">
                {{-- ✅ Bestanden --}}
                <div
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
                    <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                      <i class="fa-solid fa-file fa-sm"></i>
                      Bestanden
                    </h3>
                  </div>

                  {{-- Dropzone --}}
                  <div class="mt-3">
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

                    <ul class="mt-3 space-y-3 max-h-40 overflow-y-auto pr-1" x-show="files.length">
                      <template x-for="file in files" :key="file.id">
                        <li class="flex items-center justify-between gap-2 bg-white">
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
              </div>
@php
  $authUser = auth()->user();

  $mentionables = \App\Models\User::query()
    ->when(!empty($authUser?->company_id), fn ($q) => $q->where('company_id', $authUser->company_id))
    ->when(empty($authUser?->company_id), fn ($q) => $q->whereNull('company_id'))
    ->orderBy('name')
    ->get(['id','name']);

  $initialComments = $aanvraag->comments()
    ->with('user')
    ->latest('id')
    ->take(200)
    ->get()
    ->map(fn($c) => [
      'id'         => $c->id,
      'parent_id'  => $c->parent_id,
      'created_at' => optional($c->created_at)->format('d-m-Y H:i'),
      'user_name'  => optional($c->user)->name ?? 'Onbekend',
      'body'       => $c->body,
    ])->values();
@endphp

<div class="bg-[#fff] rounded-4xl p-8 overflow-visible">
  <div
    x-data='aanvraagComments({
      csrf: @json(csrf_token()),
      storeUrl: @json(route("support.potentiele-klanten.comments.store", ["aanvraag" => $aanvraag->id])),
      fetchUrl: @json(route("support.potentiele-klanten.comments.index", ["aanvraag" => $aanvraag->id])),
      initialComments: @json($initialComments),
      mentionables: @json($mentionables),
    })'
    x-init="init()"
  >
    <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2 mb-3">
      <i class="fa-solid fa-comments fa-sm"></i>
      Activiteiten
    </h3>

    <div class="relative">
      {{-- Mention dropdown --}}
      <div
        x-show="mention.open"
        x-cloak
        class="absolute left-0 right-0 mt-12 bg-white border border-[#215558]/15 rounded-3xl shadow-lg overflow-hidden z-50"
      >
        <template x-for="(u, idx) in mentionResults()" :key="u.id">
          <button
            type="button"
            class="w-full text-left px-4 py-3 hover:bg-[#f3f8f8]"
            :class="idx === mention.index ? 'bg-[#f3f8f8]' : ''"
            @mousedown.prevent="pickMention(u)"
          >
            <span class="text-sm font-semibold text-[#215558]" x-text="u.name"></span>
          </button>
        </template>
      </div>
    </div>

    {{-- ✅ COMMENTS UI (zoals screenshot) --}}
    <div>
      {{-- Add comment box --}}
      <div class="bg-[#f6f7f7] rounded-2xl p-4 flex items-start gap-3 border border-[#215558]/10">
        <div class="flex-1">
          {{-- reply indicator --}}
          <div x-show="reply.parentId" x-cloak class="mb-2 flex items-center gap-2">
            <span class="text-xs font-bold text-[#215558]/60">Replying to</span>
            <span class="text-xs font-black text-[#215558]" x-text="reply.parentUserName"></span>
            <button
              type="button"
              class="text-xs font-bold text-[#215558]/60 hover:text-[#215558] underline"
              @click="cancelReply()"
            >
              Annuleren
            </button>
          </div>
          {{-- editor --}}
          <div
            x-ref="editor"
            contenteditable="true"
            class="w-full min-h-[44px] bg-none outline-none text-sm font-semibold text-[#215558]"
            :data-placeholder="reply.parentId ? 'Schrijf een antwoord…' : 'Schrijf een update…'"
            @input="onEditorInput()"
            @keydown="onEditorKeydown($event)"
          ></div>

          <style>
            [contenteditable][data-placeholder]:empty:before { content: attr(data-placeholder); opacity: .55; }
          </style>
        </div>
        <button
          type="button"
          class="bg-[#215558] hover:bg-[#1a4648] text-white text-xs font-black px-4 py-2 rounded-full transition disabled:opacity-60"
          :disabled="loading"
          @click="submit()"
        >
          <span x-show="!loading">Plaatsen</span>
          <span x-show="loading"><i class="fa-solid fa-spinner fa-spin"></i></span>
        </button>
      </div>
      {{-- Threads --}}
      <div class="mt-3 space-y-2">
        <template x-for="t in threads" :key="t.root.id">
          <div class="relative pl-8 py-4">
            <div class="absolute left-2.25 top-0 bottom-0 w-px bg-[#215558]/20"></div>
            <div class="absolute left-1 top-2 w-3 h-3 rounded-full bg-[#f3f8f8] border-[2px] border-[#215558]/20 z-[1]"></div>
            {{-- Root comment --}}
            <div class="flex items-start gap-3">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <div class="text-[11px] font-semibold opacity-50 text-[#215558]" x-text="t.root.user_name"></div>
                  <div class="text-[11px] font-semibold opacity-50 text-[#215558]" x-text="t.root.created_at"></div>
                </div>
                <div class="text-sm font-semibold text-[#215558]" x-html="renderBody(t.root.body)"></div>
                <div class="mt-2 flex items-center gap-4 text-xs font-bold text-[#215558]/60">
                  <button class="hover:text-[#215558] flex items-center gap-2" type="button" @click="startReply(t.root)">
                    <i class="fa-regular fa-comment-dots"></i> Antwoorden
                  </button>
                </div>
              </div>
            </div>
            {{-- Replies block (vertical line left) --}}
            <template x-if="t.nodes.length">
              <div class="mt-4">
                <div class="relative">
                  {{-- 1 verticale lijn voor alle replies --}}
                  <div class="space-y-3">
                    <template x-for="n in t.nodes" :key="n.comment.id">
                      <div class="relative" :style="`margin-left:${(n.depth - 1) * 30}px`">
                        <div class="flex items-start gap-3">
                          <div class="w-5 h-5 rounded-bl border-l border-b border-l-[#215558]/20 border-b-[#215558]/20 shrink-0"></div>
                          <div class="flex-1">
                            <div class="flex items-center gap-2">
                              <div class="text-[11px] font-semibold opacity-50 text-[#215558]" x-text="n.comment.user_name"></div>
                              <div class="text-[11px] font-semibold opacity-50 text-[#215558]" x-text="n.comment.created_at"></div>
                            </div>
                            <div class="text-sm font-semibold text-[#215558]" x-html="renderBody(n.comment.body)"></div>
                            <div class="mt-2 flex items-center gap-4 text-xs font-bold text-[#215558]/60">
                              <button class="hover:text-[#215558] flex items-center gap-2" type="button" @click="startReply(n.comment)">
                                <i class="fa-regular fa-comment-dots"></i> Antwoorden
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </template>
        <div x-show="!threads.length" class="text-[#215558] text-xs font-semibold opacity-75">
          Nog geen activiteiten.
        </div>
      </div>
    </div>
  </div>
</div>
            </div>
            <div class="flex flex-col gap-6">
              <div class="bg-[#fff] rounded-4xl p-8 h-fit">
                <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                  <i class="fa-solid fa-database fa-sm"></i>
                  Aanvraag data
                </h3>
                <div class="flex flex-col gap-2 mt-3">
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
              @php
                  $currentStatus = strtolower((string) ($currentValue ?? ($aanvraag->status ?? 'prospect')));
                  $intakeDone    = (bool) ($aanvraag->intake_done ?? false);

                  $tasks = $aanvraag->tasks ?? collect();
                  $tasks = $tasks->sortBy(fn($t) => $t->order ?? 999)->values();
                  $tasksByType = $tasks->keyBy('type');

                  // ✅ Vragen horen bij conduct_intake
                  $intakeQuestionsTask = $tasksByType->get('conduct_intake');

                  /**
                  * ✅ Funnel + sales UX:
                  * - UI-only status acties leggen uit wat eerst moet
                  * - echte taken alleen zichtbaar in de juiste status
                  */
                  $taskMeta = [
                      // UI-only status acties (niet in DB)
                      'status_to_contact' => [
                          'category'      => 'Status',
                          'title'         => "Aanvraag oppakken en status aanpassen naar 'Contact'",
                          'ui_only'       => true,
                          'target_status' => 'contact',
                          'order'         => 1,
                      ],
                      'status_to_intake' => [
                          'category'      => 'Status',
                          'title'         => "Intake is ingepland en status veranderen naar 'Intake'",
                          'ui_only'       => true,
                          'target_status' => 'intake',
                          'order'         => 99,
                      ],
                      'status_to_lead' => [
                          'category'      => 'Status',
                          'title'         => "Intake is afgerond en lead omzetten naar project",
                          'ui_only'       => true,
                          'target_status' => 'lead',
                          'order'         => 199,
                      ],

                      // Echte taken
                      'call_customer' => [
                          'category'  => 'Contactmoment',
                          'title'     => 'Aanvraag bespreken',
                          'order'     => 10,
                      ],
                      'schedule_intake' => [
                          'category'  => 'Planning',
                          'title'     => 'Intakegesprek inplannen',
                          'order'     => 20,
                      ],
                      'conduct_intake' => [
                          'category'  => 'Contactmoment',
                          'title'     => 'Intakegesprek voeren',
                          'order'     => 30,
                      ],
                  ];

                  $allTypes = array_keys($taskMeta);

                  $tasksToShow = collect($allTypes)->map(function ($type) use ($tasksByType, $taskMeta) {
                      $meta = $taskMeta[$type] ?? [];

                      return $tasksByType->get($type) ?? (object) [
                          'id'       => null,
                          'type'     => $type,
                          'title'    => $meta['title'] ?? ucfirst(str_replace('_', ' ', $type)),
                          'status'   => 'open',
                          'category' => $meta['category'] ?? 'Taak',
                          'order'    => $meta['order'] ?? 999,
                      ];
                  })->sortBy(fn($t) => $t->order ?? 999)->values();

                  $initialTaskStates = $tasksToShow->mapWithKeys(function ($task) use ($taskMeta) {
                      $type = (string) $task->type;
                      $meta = $taskMeta[$type] ?? [];

                      // UI-only taken hebben geen stored status
                      if (!empty($meta['ui_only'])) {
                          return [$type => false];
                      }

                      $statusStr = strtolower((string) ($task->status ?? 'open'));
                      $isDone = in_array($statusStr, ['done', 'completed', 'closed'], true);

                      return [$type => $isDone];
                  })->all();

                  $initialTasks = array_merge([
                      'call_customer'      => false,
                      'schedule_intake'    => false,
                      'conduct_intake'     => false,
                      'status_to_contact'  => false,
                      'status_to_intake'   => false,
                      'status_to_lead'     => false,
                  ], $initialTaskStates);

                  $tasksPayload = [
                      'csrf' => csrf_token(),
                      'updateUrl' => route('support.potentiele-klanten.tasks.status.update', ['aanvraag' => $aanvraag->id]),
                      'statusUpdateUrl' => route('support.potentiele-klanten.status.update', ['aanvraag' => $aanvraag->id]),

                      'initial' => $initialTasks,
                      'cardId' => (string) $aanvraag->id,
                      'initialStatus' => $currentStatus,
                      'initialIntakeDone' => $intakeDone,

                      'initialOwnerId' => (string) ($aanvraag->owner_id ?? ''),
                  ];
              @endphp

              <div
                class="bg-[#fff] rounded-4xl p-8 h-fit"
                x-data='aanvraagTasks(@json($tasksPayload))'
                x-init="init()"
              >
                <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                    <i class="fa-solid fa-list fa-sm"></i>
                    Taken
                </h3>

                <div class="mt-3 flex flex-col gap-2 divide-[#215558]/20">
                  @foreach($tasksToShow as $task)
                    @php
                      $meta = $taskMeta[$task->type] ?? [];

                      $category = $task->category
                          ?? ($meta['category'] ?? 'Taak');

                      $title = $task->title
                          ?? ($meta['title'] ?? ucfirst(str_replace('_', ' ', $task->type)));

                      $isUiOnly = (bool) ($meta['ui_only'] ?? false);
                      $targetStatus = $meta['target_status'] ?? null;
                    @endphp
                    <p
                      x-show="'{{ $task->type }}' === 'status_to_contact' && !hasOwner()"
                      class="mb-2 w-fit text-[11px] font-semibold text-red-500 px-2.5 py-0.5 rounded-full bg-red-100"
                    >
                      <strong>Om te beginnen:</strong> Koppel een medewerker
                    </p>
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
                        class="bg-gray-200 hover:bg-gray-300 whitespace-nowrap text-[#215558] text-xs font-semibold px-3 py-1.5 rounded-full transition cursor-pointer mt-1.5
                              disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-gray-200"
                        :disabled="'{{ $task->type }}' === 'status_to_contact' && !hasOwner()"
                        @click="setUiStatus('{{ $task->type }}', '{{ $targetStatus }}')"
                      >
                        Status aanpassen
                      </button>

                      @elseif($task->type === 'conduct_intake')
                        {{-- ✅ SPECIAL CASE: Intakegesprek voeren met oogje + vragen --}}
                        <div class="flex items-center justify-between gap-2">
                          <p
                            class="text-sm font-semibold text-[#215558] transition"
                            :class="tasks['conduct_intake'] ? 'line-through opacity-60' : ''"
                          >
                            {{ $title }}
                          </p>

                          <div class="flex items-center gap-2">
                            @if($intakeQuestionsTask)
                              <div
                                class="relative"
                                x-data="{
                                  csrf: '{{ csrf_token() }}',
                                  updateUrlTemplate: '{{ route('support.tasks.questions.update', ['question' => '__ID__']) }}',
                                  openIntakePanel: false,
                                  savingAll: false,

                                  async saveAll () {
                                    try {
                                      this.savingAll = true;
                                      const fields = Array.from(this.$root.querySelectorAll('textarea[data-answer-id]'));

                                      for (const el of fields) {
                                        const id  = el.dataset.answerId;
                                        const url = this.updateUrlTemplate.replace('__ID__', id);

                                        const form = new FormData();
                                        form.append('_method', 'PATCH');
                                        form.append('answer', el.value ?? '');

                                        const res = await fetch(url, {
                                          method: 'POST',
                                          credentials: 'same-origin',
                                          headers: {
                                            'X-CSRF-TOKEN': this.csrf,
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                          },
                                          body: form
                                        });

                                        if (res.redirected && res.url.includes('/login')) {
                                          alert('Je sessie is verlopen. Log opnieuw in.');
                                          return;
                                        }

                                        if (!res.ok) {
                                          const text = await res.text().catch(()=> '');
                                          throw new Error(`Update failed (${res.status}) ${text}`);
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
                                }"
                              >
                                {{-- ✅ OOGJE --}}
                                <button
                                  type="button"
                                  class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer"
                                  x-on:click.stop="openIntakePanel = !openIntakePanel"
                                  title="{{ __('potentiele_klanten.intake_questions.open_panel_tooltip') }}"
                                >
                                  <i class="fa-solid fa-eyes text-[#215558] text-xs"></i>
                                </button>

                                {{-- ✅ PANEL MET VRAGEN --}}
                                <div
                                  x-show="openIntakePanel"
                                  x-transition
                                  x-on:click.outside="openIntakePanel = false"
                                  class="absolute right-full mr-3 top-1/2 -translate-y-1/2 z-50"
                                  style="display:none;"
                                >
                                  @php
                                    $notesQ = $intakeQuestionsTask->questions->first(fn($q) =>
                                      str_contains(mb_strtolower($q->question), 'notitie')
                                    );

                                    $otherQs = $intakeQuestionsTask->questions->reject(fn($q) =>
                                      str_contains(mb_strtolower($q->question), 'notitie')
                                    );
                                  @endphp

                                  <div class="flex items-start gap-3">
                                    {{-- LINKS: Vrije notities --}}
                                    <div class="w-[360px] p-4 bg-white rounded-xl border border-gray-200 shadow-lg">
                                      <p class="text-base text-[#215558] font-black mb-3">
                                        {{ __('potentiele_klanten.intake_questions.notes_title') }}
                                      </p>

                                      @if($notesQ)
                                        <textarea
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

                                    {{-- RECHTS: Overige intakevragen --}}
                                    <div class="w-[480px] p-4 bg-white rounded-xl border border-gray-200 shadow-lg">
                                      <p class="text-base text-[#215558] font-black mb-1">
                                        {{ __('potentiele_klanten.intake_questions.section_title') }}
                                      </p>

                                      <div class="grid gap-3 max-h-50 overflow-y-auto pr-1 mt-2">
                                        @foreach($otherQs as $q)
                                          <div>
                                            <p class="block text-xs text-[#215558] opacity-70 mb-1">
                                              {{ $q->order }}. {{ $q->question }}
                                            </p>
                                            <textarea
                                              data-answer-id="{{ $q->id }}"
                                              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                                              rows="2">{{ $q->answer }}</textarea>
                                          </div>
                                        @endforeach
                                      </div>

                                      <div class="mt-5 flex justify-end">
                                        <button
                                          type="button"
                                          class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                                          x-on:click="saveAll()"
                                          x-bind:disabled="savingAll"
                                        >
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
                            @endif

                            {{-- ✅ Checkbox --}}
                            <input
                              type="checkbox"
                              :checked="tasks['conduct_intake']"
                              :disabled="!canCheck('conduct_intake')"
                              :class="!canCheck('conduct_intake') ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                              @change="toggle('conduct_intake', $event.target.checked, $event)"
                            >
                          </div>
                        </div>

                      @else
                        {{-- ✅ DEFAULT echte taken rij --}}
                        <div class="flex items-center justify-between">
                          <p
                            class="text-sm font-semibold text-[#215558] transition"
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
                            @change="toggle('{{ $task->type }}', $event.target.checked, $event)"
                          >
                        </div>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
              <div class="bg-[#fff] rounded-4xl p-8">
                <div class="flex items-center justify-between gap-2 mb-3">
                  <h3 class="text-[#215558] font-black text-base shrink-0 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left fa-sm"></i>
                    Logboek
                  </h3>
                </div>
                <p class="text-[#215558] text-xs font-semibold opacity-75"
                  data-status-log-empty
                  @if($statusLogs->isNotEmpty()) style="display:none;" @endif>
                  Er zijn nog geen activiteiten.
                </p>
                <ul class="flex flex-col gap-2 overflow-y-auto"
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
    </div>
  </div>
</div>