@php
  $req = $project->onboardingRequest;

  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5 py-4 flex flex-col gap-4";
  $labelClass    = $labelClass    ?? "text-[#191D38] font-bold text-xs opacity-50";

  $saveUrl = route('support.projecten.onboarding.update', $project);
@endphp

<div id="project-contact" class="{{ $sectionWrap }} h-full flex flex-col">
  <div class="{{ $sectionHeader }}">
    <p class="text-[#191D38] font-black text-sm">Contactpersoon</p>
  </div>

  <div class="bg-[#191D38]/5 py-4 flex flex-col gap-4 flex-1">
    <div class="px-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @foreach([
          ['contact_first_name','Voornaam','text'],
          ['contact_last_name','Achternaam','text'],
        ] as [$field,$label,$type])
          <div>
            <p class="{{ $labelClass }} mb-1">{{ $label }}</p>

            <form hx-patch="{{ $saveUrl }}" hx-target="#project-contact" hx-swap="outerHTML">
              @csrf
              @method('PATCH')
              <input type="hidden" name="section" value="contact">
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

    <div class="px-6 flex flex-col gap-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        @foreach([
          ['contact_email','E-mail','email'],
          ['contact_phone','Telefoon','text'],
        ] as [$field,$label,$type])
          <div>
            <p class="{{ $labelClass }} mb-1">{{ $label }}</p>

            <form hx-patch="{{ $saveUrl }}" hx-target="#project-contact" hx-swap="outerHTML">
              @csrf
              @method('PATCH')
              <input type="hidden" name="section" value="contact">
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

      <div>
        <p class="{{ $labelClass }} mb-2">Updates</p>

        <form hx-patch="{{ $saveUrl }}" hx-target="#project-contact" hx-swap="outerHTML">
          @csrf
          @method('PATCH')
          <input type="hidden" name="section" value="contact">
          <input type="hidden" name="field" value="contact_updates">
          <input type="hidden" name="value" :value="$refs.cb.checked ? '1' : '0'">

          <label class="inline-flex items-center gap-2 text-sm font-semibold text-[#191D38]">
            <input
              x-ref="cb"
              type="checkbox"
              class="h-4 w-4 rounded border-[#191D38]/20"
              {{ ($req->contact_updates ?? false) ? 'checked' : '' }}
              x-on:change="$el.form.requestSubmit()"
            >
            Op de hoogte houden
          </label>
        </form>
      </div>

    </div>
  </div>
</div>
