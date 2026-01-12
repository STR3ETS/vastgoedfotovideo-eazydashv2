@php
  $req = $project->onboardingRequest;

  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";
  $labelClass    = $labelClass    ?? "text-[#191D38] font-bold text-xs opacity-50";

  $fullAddress = trim(($req->address ?? '') . ', ' . ($req->postcode ?? '') . ' ' . ($req->city ?? ''));
  $mapsQ = urlencode($fullAddress);

  $saveUrl = route('support.projecten.onboarding.update', $project);
@endphp

<div id="project-location" class="col-span-2 {{ $sectionWrap }}">
  <div class="{{ $sectionHeader }}">
    <p class="text-[#191D38] font-black text-sm">Locatie</p>
  </div>

  <div class="{{ $sectionBody }}">
    <div class="px-6 py-4">
      <div class="flex flex-col lg:flex-row gap-6 lg:items-stretch">

        {{-- Links --}}
        <div class="w-full lg:w-1/2 flex flex-col gap-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Adres --}}
            <div>
              <p class="{{ $labelClass }} mb-1">Adres</p>

              <form
                hx-patch="{{ $saveUrl }}"
                hx-target="#project-location"
                hx-swap="outerHTML"
              >
                @csrf
                @method('PATCH')
                <input type="hidden" name="section" value="location">
                <input type="hidden" name="field" value="address">

                <input
                  name="value"
                  value="{{ $req->address ?? '' }}"
                  class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                  placeholder="Adres..."
                  x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                  x-on:blur="$el.form.requestSubmit()"
                >
              </form>
            </div>

            {{-- Postcode --}}
            <div>
              <p class="{{ $labelClass }} mb-1">Postcode</p>

              <form hx-patch="{{ $saveUrl }}" hx-target="#project-location" hx-swap="outerHTML">
                @csrf
                @method('PATCH')
                <input type="hidden" name="section" value="location">
                <input type="hidden" name="field" value="postcode">

                <input
                  name="value"
                  value="{{ $req->postcode ?? '' }}"
                  class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                  placeholder="Postcode..."
                  x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                  x-on:blur="$el.form.requestSubmit()"
                >
              </form>
            </div>

            {{-- Plaats --}}
            <div>
              <p class="{{ $labelClass }} mb-1">Plaats</p>

              <form hx-patch="{{ $saveUrl }}" hx-target="#project-location" hx-swap="outerHTML">
                @csrf
                @method('PATCH')
                <input type="hidden" name="section" value="location">
                <input type="hidden" name="field" value="city">

                <input
                  name="value"
                  value="{{ $req->city ?? '' }}"
                  class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                  placeholder="Plaats..."
                  x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                  x-on:blur="$el.form.requestSubmit()"
                >
              </form>
            </div>

          </div>

          {{-- Oppervlaktes --}}
          <div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

              @foreach([
                ['surface_home','Oppervlakte woning'],
                ['surface_outbuildings','Bijgebouwen'],
                ['surface_plot','Perceel'],
              ] as [$field,$label])
                <div>
                  <p class="{{ $labelClass }} mb-1">{{ $label }}</p>

                  <form hx-patch="{{ $saveUrl }}" hx-target="#project-location" hx-swap="outerHTML">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="section" value="location">
                    <input type="hidden" name="field" value="{{ $field }}">

                    <div class="relative">
                      <input
                        type="number"
                        min="0"
                        name="value"
                        value="{{ (int)($req->{$field} ?? 0) }}"
                        class="h-9 w-full rounded-full px-4 pr-10 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                        x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                        x-on:blur="$el.form.requestSubmit()"
                      >

                      <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-[#191D38]/50">
                        m²
                      </span>
                    </div>
                  </form>
                </div>
              @endforeach

            </div>
          </div>

        </div>

        {{-- Rechts map --}}
        <div class="w-full lg:w-1/2 lg:flex">
          <div class="w-full h-[260px] lg:h-full rounded-2xl overflow-hidden ring-1 ring-[#191D38]/10 bg-white">
            <iframe
              title="Map"
              class="w-full h-full"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
              src="https://www.google.com/maps?q={{ $mapsQ }}&output=embed"
            ></iframe>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
