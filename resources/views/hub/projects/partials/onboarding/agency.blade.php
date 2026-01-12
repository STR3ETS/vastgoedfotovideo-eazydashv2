@php
  $req = $project->onboardingRequest;

  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5 flex flex-col gap-4";
  $labelClass    = $labelClass    ?? "text-[#191D38] font-bold text-xs opacity-50";

  $saveUrl = route('support.projecten.onboarding.update', $project);
@endphp

<div id="project-agency" class="{{ $sectionWrap }} h-full flex flex-col">
  <div class="{{ $sectionHeader }}">
    <p class="text-[#191D38] font-black text-sm">Makelaardij</p>
  </div>

  <div class="bg-[#191D38]/5 flex-1">
    <div class="px-6 py-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6">

        @foreach([
          ['agency_first_name','Voornaam','text'],
          ['agency_last_name','Achternaam','text'],
          ['agency_phone','Telefoon','text'],
          ['agency_email','E-mail','email'],
        ] as [$field,$label,$type])
          <div>
            <p class="{{ $labelClass }} mb-1">{{ $label }}</p>

            <form hx-patch="{{ $saveUrl }}" hx-target="#project-agency" hx-swap="outerHTML">
              @csrf
              @method('PATCH')
              <input type="hidden" name="section" value="agency">
              <input type="hidden" name="field" value="{{ $field }}">

              <input
                type="{{ $type }}"
                name="value"
                value="{{ $req->{$field} ?? '' }}"
                class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                x-on:blur="$el.form.requestSubmit()"
              >
            </form>
          </div>
        @endforeach

      </div>
    </div>
  </div>
</div>
