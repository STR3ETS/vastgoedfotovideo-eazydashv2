@php
  $meId = auth()->id();
@endphp

<div class="flex flex-col gap-3">
  @forelse($messages as $m)
    @php
      $isMe = (int) $m->user_id === (int) $meId;
    @endphp

    <div class="w-full flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
      <div class="max-w-[85%] min-w-[70%] rounded-tr-xl rounded-tl-xl p-4
        {{ $isMe ? 'bg-[#009AC3] text-[#fff] rounded-bl-xl' : 'bg-white text-[#191D38] rounded-br-xl' }}
      ">
        <p class="text-[11px] font-semibold opacity-60 truncate mb-1">
          {{ $isMe ? 'Jij' : ($m->user?->name ?? 'Onbekend') }}
        </p>
        @if(!empty($m->body))
          <p class="text-sm leading-relaxed">
            {{ $m->body }}
          </p>
        @endif
        <div class="flex items-center justify-between gap-3 mt-2">
          @if($m->attachments?->count())
            <div class="space-y-1">
              @foreach($m->attachments as $a)
                <a
                  href="{{ route('support.projecten.taken.chat.attachments.download', ['project' => $project, 'task' => $task, 'attachment' => $a]) }}"
                  class="block text-xs opacity-70 hover:opacity-100 transition duration-200 truncate flex items-center gap-2
                    {{ $isMe ? 'text-[#fff]' : 'text-[#191D38]' }}"
                  title="{{ $a->original_name }}"
                >
                  <i class="fa-solid fa-paperclip fa-sm"></i>
                  <span class="pb-1">{{ $a->original_name }}</span>
                </a>
              @endforeach
            </div>
          @endif
          <p class="text-[10px] font-semibold opacity-50">
            {{ optional($m->created_at)->format('H:i') }}
          </p>
        </div>
      </div>
    </div>
  @empty
    <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
      Nog geen berichten.
    </div>
  @endforelse
</div>
