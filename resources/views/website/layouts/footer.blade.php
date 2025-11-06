<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<div class="fixed z-[100] left-2 bottom-2 md:left-8 md:bottom-8 flex items-center gap-2">
    <a href="tel:0031262340400" aria-label="Bel ons: 026 234 0400"  
        class="w-10 h-10 rounded-full bg-white hover:bg-[#215558] transition duration-300 group flex cursor-pointer items-center justify-center" 
        style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-5 h-5 text-[#215558] group-hover:text-white transition duration-300 fill-current">
            <path d="M376 32C504.1 32 608 135.9 608 264C608 277.3 597.3 288 584 288C570.7 288 560 277.3 560 264C560 162.4 477.6 80 376 80C362.7 80 352 69.3 352 56C352 42.7 362.7 32 376 32zM384 224C401.7 224 416 238.3 416 256C416 273.7 401.7 288 384 288C366.3 288 352 273.7 352 256C352 238.3 366.3 224 384 224zM352 152C352 138.7 362.7 128 376 128C451.1 128 512 188.9 512 264C512 277.3 501.3 288 488 288C474.7 288 464 277.3 464 264C464 215.4 424.6 176 376 176C362.7 176 352 165.3 352 152zM176.1 65.4C195.8 60 216.4 70.1 224.2 88.9L264.7 186.2C271.6 202.7 266.8 221.8 252.9 233.2L208.8 269.3C241.3 340.9 297.8 399.3 368.1 434.2L406.7 387C418 373.1 437.1 368.4 453.7 375.2L551 415.8C569.8 423.6 579.9 444.2 574.5 463.9L573 469.4C555.4 534.1 492.9 589.3 416.6 573.2C241.6 536.1 103.9 398.4 66.8 223.4C50.7 147.1 105.9 84.6 170.5 66.9L176 65.4z"/>
        </svg>
    </a>

    <a href="mailto:info@eazyonline.nl" aria-label="Mail ons: info@eazyonline.nl" 
        class="w-10 h-10 rounded-full bg-white hover:bg-[#215558] transition duration-300 group flex cursor-pointer items-center justify-center" 
        style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-5 h-5 text-[#215558] group-hover:text-white transition duration-300 fill-current">
            <path d="M320 128C214 128 128 214 128 320C128 426 214 512 320 512C337.7 512 352 526.3 352 544C352 561.7 337.7 576 320 576C178.6 576 64 461.4 64 320C64 178.6 178.6 64 320 64C461.4 64 576 178.6 576 320L576 352C576 405 533 448 480 448C450.7 448 424.4 434.8 406.8 414.1C384 435.1 353.5 448 320 448C249.3 448 192 390.7 192 320C192 249.3 249.3 192 320 192C347.9 192 373.7 200.9 394.7 216.1C400.4 211.1 407.8 208 416 208C433.7 208 448 222.3 448 240L448 352C448 369.7 462.3 384 480 384C497.7 384 512 369.7 512 352L512 320C512 214 426 128 320 128zM384 320C384 284.7 355.3 256 320 256C284.7 256 256 284.7 256 320C256 355.3 284.7 384 320 384C355.3 384 384 355.3 384 320z"/>
        </svg>
    </a>

    <a href="/website" class="w-fit px-4 text-white font-semibold h-10 rounded-full bg-[#215558] hover:bg-gray-800 transition duration-300 group flex cursor-pointer items-center justify-center text-xs whitespace-nowrap" style="box-shadow: 0px 1px 2px -1px #0000001A; box-shadow: 0px 1px 3px 0px #0000001A;">
        Start gratis preview
    </a>
</div>
<div class="w-full py-[4rem] px-[1rem] md:px-[7rem] bg-[#f4f5f7] overflow-hidden relative">
  <h2 class="fade-in-up text-[#215558] leading-tight text-4xl font-extrabold mb-[2rem] text-center">
    Wat klanten zeggen
  </h2>

  <!-- Rij 1 -->
  <div class="animate-marquee flex gap-6">
    <div class="flex gap-6">
      <!-- Testimonial 1 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Professioneel resultaat</h3>
        <p class="text-[#215558] italic mb-6">“Eazy heeft elke versie van onze website naar een hoger niveau getild. Ze snappen exact wat je als ondernemer nodig hebt.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/thegrind.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Donny Roelvink</h4>
            <p class="text-[#215558] text-xs">Eigenaar TheGrind</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 2 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Uitstekende samenwerking</h3>
        <p class="text-[#215558] italic mb-6">“Samenwerken met Eazy voelt als een gedeeld avontuur. Ze denken altijd mee en bouwen écht mee aan ons merk.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/kapotsterk.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Wouter Smit</h4>
            <p class="text-[#215558] text-xs">Eigenaar KapotSterk</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 3 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Snelle oplevering</h3>
        <p class="text-[#215558] italic mb-6">“Binnen no-time hadden we een op maat gemaakte website die precies laat zien waar ons bedrijf voor staat. Supertevreden.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/barbarosdetailing.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Baris Yildirim</h4>
            <p class="text-[#215558] text-xs">Eigenaar Barbaros Detailing</p>
          </div>
        </div>
      </div>
    </div>

    <!-- DUPLICATE -->
    <div class="flex gap-6">
      <!-- Testimonial 1 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Professioneel resultaat</h3>
        <p class="text-[#215558] italic mb-6">“Eazy heeft elke versie van onze website naar een hoger niveau getild. Ze snappen exact wat je als ondernemer nodig hebt.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/thegrind.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Donny Roelvink</h4>
            <p class="text-[#215558] text-xs">Eigenaar TheGrind</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 2 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Uitstekende samenwerking</h3>
        <p class="text-[#215558] italic mb-6">“Samenwerken met Eazy voelt als een gedeeld avontuur. Ze denken altijd mee en bouwen écht mee aan ons merk.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/kapotsterk.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Wouter Smit</h4>
            <p class="text-[#215558] text-xs">Eigenaar KapotSterk</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 3 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Snelle oplevering</h3>
        <p class="text-[#215558] italic mb-6">“Binnen no-time hadden we een op maat gemaakte website die precies laat zien waar ons bedrijf voor staat. Supertevreden.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/barbarosdetailing.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Baris Yildirim</h4>
            <p class="text-[#215558] text-xs">Eigenaar Barbaros Detailing</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rij 2 -->
  <div class="animate-marquee-reverse flex gap-6 mt-8">
    <div class="flex gap-6">
      <!-- Testimonial 4 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Creatief ontwerp</h3>
        <p class="text-[#215558] italic mb-6">“Van idee tot eindproduct: Eazy leverde een strak, modern en uniek design dat onze visie perfect weerspiegelt.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-top" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/2befit.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Roy Koenders</h4>
            <p class="text-[#215558] text-xs">Eigenaar 2BeFit Lifestyle</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 5 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Goede communicatie</h3>
        <p class="text-[#215558] italic mb-6">“Vanaf dag één goede communicatie, snelle updates en een team dat je écht meeneemt in het proces. Heel professioneel.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/huisjekaatsheuvel.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Nienke Roseboom</h4>
            <p class="text-[#215558] text-xs">Eigenaresse Huisje Kaatsheuvel</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 6 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Efficiënte samenwerking</h3>
        <p class="text-[#215558] italic mb-6">“Onze oude websites voldeden niet meer aan onze visie. Eazy ontwikkelde een volledig nieuw concept dat onze verwachtingen overtrof.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/blowertechnic.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Bas & David</h4>
            <p class="text-[#215558] text-xs">Eigenaren BlowerTechnic</p>
          </div>
        </div>
      </div>
    </div>

    <!-- DUPLICATE -->
    <div class="flex gap-6">
      <!-- Testimonial 4 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Creatief ontwerp</h3>
        <p class="text-[#215558] italic mb-6">“Van idee tot eindproduct: Eazy leverde een strak, modern en uniek design dat onze visie perfect weerspiegelt.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-top" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/2befit.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Roy Koenders</h4>
            <p class="text-[#215558] text-xs">Eigenaar 2BeFit Lifestyle</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 5 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Goede communicatie</h3>
        <p class="text-[#215558] italic mb-6">“Vanaf dag één goede communicatie, snelle updates en een team dat je écht meeneemt in het proces. Heel professioneel.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/huisjekaatsheuvel.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Nienke Roseboom</h4>
            <p class="text-[#215558] text-xs">Eigenaresse Huisje Kaatsheuvel</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 6 -->
      <div class="min-w-[350px] bg-white p-[2.5rem] rounded-3xl shadow">
        <h3 class="text-xl font-semibold text-[#0F9B9F] mb-3">Efficiënte samenwerking</h3>
        <p class="text-[#215558] italic mb-6">“Onze oude websites voldeden niet meer aan onze visie. Eazy ontwikkelde een volledig nieuw concept dat onze verwachtingen overtrof.”</p>
        <div class="flex items-center gap-3">
          <div class="w-[32px] h-[32px] rounded-full bg-cover bg-center" style='background-image: url("{{ asset("assets/eazyonline/projecten/profielfotos/blowertechnic.webp") }}")'></div>
          <div>
            <h4 class="text-[#215558] font-bold text-sm">Bas & David</h4>
            <p class="text-[#215558] text-xs">Eigenaren BlowerTechnic</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
@keyframes marquee {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
@keyframes marquee-reverse {
  0%   { transform: translateX(-50%); }
  100% { transform: translateX(0); }
}
.animate-marquee {
  width: 200%;
  animation: marquee 35s linear infinite;
}
.animate-marquee-reverse {
  width: 200%;
  animation: marquee-reverse 35s linear infinite;
}
@media (max-width: 768px) {
  .animate-marquee {
    animation-duration: 10s; /* was 35s */
  }
  .animate-marquee-reverse {
    animation-duration: 10s; /* was 35s */
  }
}
</style>
<div class="w-full bg-[url(https://i.imgur.com/DXXpMGQ.png)] bg-cover bg-center py-[4rem] px-[1rem] md:px-[7rem]">
    @if (!request()->routeIs('contact'))
    <div class="max-w-[1200px] mx-auto flex flex-col md:flex-row gap-8 md:ap-16 relative mb-16">
        <div class="fade-in-up w-full md:w-1/2 bg-white/85 border-[2px] border-white rounded-3xl p-[2.5rem]">
            <h3 class="flex items-center gap-2 mb-[1rem]">
                <span class="leading-tight text-xl font-semibold text-[#0F9B9F]">Gegevens</span>
            </h3>
            <h2 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Klaar om je te helpen en je vragen te beantwoorden</h2>
            <p class="text-lg leading-tight font-medium text-[#215558] mb-[1.5rem]">Je kunt ons gerust een appje sturen, bellen, e-mailen en chatten.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-300 bg-white p-[1.5rem] rounded-3xl">
                    <h2 class="text-[#215558] leading-tight text-lg font-bold mb-[1.5rem]">Telefonisch</h2>
                    <p class="text-base leading-tight font-medium text-[#215558]">
                        <a href="tel:+31262340400" class="hover:underline">(+31) 026 23 40 400</a><br>
                        <a href="tel:+31642261622" class="hover:underline">(+31) 06 422 616 22</a><br>
                        <a href="tel:+31648848808" class="hover:underline">(+31) 06 488 488 08</a>
                    </p>
                </div>
                <div class="border border-gray-300 bg-white p-[1.5rem] rounded-3xl">
                    <h2 class="text-[#215558] leading-tight text-lg font-bold mb-[1.5rem]">Email</h2>
                    <p class="text-base leading-tight font-medium text-[#215558]">
                        <a href="mailto:info@eazyonline.nl" class="hover:underline">info@eazyonline.nl</a><br>
                        <a href="mailto:support@eazyonline.nl" class="hover:underline">support@eazyonline.nl</a><br>
                        <a href="mailto:raphael@eazyonline.nl" class="hover:underline">raphael@eazyonline.nl</a>
                    </p>
                </div>
                <div class="border border-gray-300 bg-white p-[1.5rem] rounded-3xl relative flex flex-col justify-center">
                    <h2 class="text-[#215558] leading-tight text-lg font-bold mb-[1.5rem]">Waar we zijn</h2>
                    <a href="https://www.google.com/maps/dir/?api=1&destination=Mercatorweg+28,+6827DC+Arnhem" 
                        target="_blank" 
                        rel="noopener noreferrer"
                        class="block text-base leading-tight font-medium text-[#215558] hover:underline">
                        Mercatorweg 28<br>6827DC Arnhem
                    </a>
                    <div class="hidden md:block absolute z-[1] left-auto md:left-[-5rem] right-[6rem] md:right-auto -top-[4rem] md:-top-[5rem]">
                        <p class="text-[#215558] text-2xl rotate-[10deg] md:rotate-[-15deg] caveat-font pb-[1rem] -ml-[-5rem] md:-ml-[7rem] text-center">Bakkie<br>koffie?</p>
                        <svg class="rotate-[-165deg] md:rotate-[-180deg] scale-x-[-1] md:scale-x-[1]" width="64" height="75" viewBox="0 0 64 75" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M55.7651 56.674C56.9303 55.675 57.9599 54.784 58.9625 53.92C59.8296 55 61.2115 55.945 61.4554 57.16C62.4851 61.9121 63.1896 66.7182 63.9212 71.4973C64.3277 74.2513 63.1625 75.3584 60.3986 74.8994C56.9845 74.3323 53.5161 73.8733 50.2103 72.9553C49.1535 72.6583 48.4761 71.0383 47.609 70.0123C48.8012 69.4183 49.9664 68.4192 51.2128 68.3382C52.7574 68.2302 54.3561 68.8512 56.6322 69.2832C45.1432 40.0417 25.8232 18.0093 -5.31099e-08 1.21501C0.216774 0.810004 0.433548 0.405005 0.677422 -2.76792e-06C1.89677 0.378004 3.22451 0.566994 4.28129 1.188C16.0142 8.12713 26.3109 16.8213 35.3883 26.9465C43.5716 36.0726 50.427 46.1168 55.7922 56.674L55.7651 56.674Z" fill="#215558"/>
                        </svg>
                    </div>
                    <p class="text-[#215558] text-2xl rotate-[10deg] block md:hidden caveat-font text-center absolute z-1 right-6">Bakkie<br>koffie?</p>
                </div>
                <div class="border border-gray-300 bg-white p-[1.5rem] rounded-3xl">
                    <h2 class="text-[#215558] leading-tight text-lg font-bold mb-[1.5rem]">Informatie</h2>
                    <p class="text-base leading-tight font-medium text-[#215558]">KVK: 89259890<br>BTW: NL864926856B01</p>
                </div>
            </div>
        </div>
        <div class="fade-in-up w-full md:w-1/2 bg-white/85 border-[2px] border-white rounded-3xl p-[2.5rem]">
            <h3 class="flex items-center gap-2 mb-[1rem]">
                <span class="leading-tight text-xl font-semibold text-[#0F9B9F]">Formulier</span>
            </h3>
            <h2 class="text-[#215558] leading-tight text-4xl font-extrabold mb-[1.5rem]">Vragen of opmerkingen?</h2>
            <p class="text-lg leading-tight font-medium text-[#215558] mb-[1.5rem]">Definieer je doelen en identificeer gebieden waar eazyonline waarde kan toevoegen aan jouw onderneming.</p>
            <div id="formFeedback" class="mb-[1.5rem] text-sm"></div>
            <form id="contactForm" action="#" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-[#215558] mb-2">Naam</label>
                    <input type="text" id="name" name="name" class="w-full p-3 border border-[#d1d5db] bg-white rounded-lg outline-none focus:ring-2 focus:ring-[#0F9B9F] focus:border-transparent duration-300" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-[#215558] mb-2">E-mail</label>
                    <input type="email" id="email" name="email" class="w-full p-3 border border-[#d1d5db] bg-white rounded-lg outline-none focus:ring-2 focus:ring-[#0F9B9F] focus:border-transparent duration-300" required>
                </div>
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-[#215558] mb-2">Bericht</label>
                    <textarea id="message" name="message" class="w-full min-h-[70px] max-h-[70px] p-3 border border-[#d1d5db] bg-white rounded-lg outline-none focus:ring-2 focus:ring-[#0F9B9F] focus:border-transparent duration-300" required></textarea>
                </div>
                <button type="submit" class="bg-[#0F9B9F] w-fit text-white text-base font-medium px-6 py-3 rounded-full">
                    Verstuur bericht
                </button>
            </form>
        </div>
    </div>
    <div class="max-w-[1200px] mx-auto">
        <hr class="mb-14 border-[#fff]/25">
    </div>
    @endif
    <div class="max-w-[1200px] mx-auto">
        <h2 class="text-[#fff] text-xl md:text-2xl font-bold text-center md:text-start">eazyonline</h2>
        <h2 class="text-[#215558] text-md md:text-lg font-bold mb-[2rem] text-center md:text-start">Jouw digitale collega's.</h2>
        <div class="w-full grid grid-cols-2 md:grid-cols-4 gap-10 justify-between mb-[3rem]">
            <div>
                <h4 class="text-xl font-bold leading-tight text-[#215558] mb-[1rem]">Eazy</h4>
                <ul class="flex flex-col gap-2">
                    <li><a href="/" class="text-lg font-medium text-[#215558]">Home</a></li>
                    <li><a href="/over-ons/het-verhaal" class="text-lg font-medium text-[#215558]">Het verhaal van Eazyonline</a></li>
                    <li><a href="/over-ons/team" class="text-lg font-medium text-[#215558]">Ontmoet ons team</a></li>
                    <li><a href="/projecten" class="text-lg font-medium text-[#215558]">Projecten</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xl font-bold leading-tight text-[#215558] mb-[1rem]">Ontdekken</h4>
                <ul class="flex flex-col gap-2">
                    <li><a href="/website#pakketten" class="text-lg font-medium text-[#215558]">Pakketten website</a></li>
                    <li><a href="/socialmedia#pakketten" class="text-lg font-medium text-[#215558]">Pakketten socialmedia</a></li>
                    <li><a href="/branding#pakketten" class="text-lg font-medium text-[#215558]">Pakketten branding</a></li>
                    <li><a href="/seo#pakketten" class="text-lg font-medium text-[#215558]">Pakketten seo/sea</a></li>
                    <li><a href="/marketing#pakketten" class="text-lg font-medium text-[#215558]">Pakketten marketing</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xl font-bold leading-tight text-[#215558] mb-[1rem]">Support</h4>
                <ul class="flex flex-col gap-2 mb-[1rem]">
                    <li><a href="https://webmail.sitehub.io/" target="_blank" class="text-lg font-medium text-[#215558]">Webmail inloggen</a></li>
                    <li><a href="/email-instellingen" class="text-lg font-medium text-[#215558]">E-mail instellingen</a></li>
                    <li><a href="/faq" class="text-lg font-medium text-[#215558]">Veelgestelde vragen</a></li>
                </ul>
                <ul class="flex flex-col gap-2">
                    <li><a href="/login" class="text-lg font-medium text-[#215558]">Service Hub</a></li>
                    <li><a href="/login" class="text-lg font-medium text-[#215558]">Kennisbank</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xl font-bold leading-tight text-[#215558] mb-[1rem]">Producten</h4>
                <ul class="flex flex-col gap-2">
                    <li>
                        <a href="/website" class="text-lg font-medium text-[#215558] flex items-center gap-2">
                            <i class="fa-solid fa-globe-pointer text-[#215558] fa-lg min-w-[26px]"></i> 
                            Eazy Website
                        </a>
                    </li>
                    <li>
                        <a href="/socialmedia" class="text-lg font-medium text-[#215558] flex items-center gap-2">
                            <i class="fa-solid fa-at text-[#215558] fa-lg min-w-[26px]"></i> 
                            Eazy Socials
                        </a>
                    </li>
                    <li>
                        <a href="/branding" class="text-lg font-medium text-[#215558] flex items-center gap-2">
                            <i class="fa-solid fa-paint-roller text-[#215558] fa-lg min-w-[26px]"></i> 
                            Eazy Branding
                        </a>
                    </li>
                    <li>
                        <a href="/seo" class="text-lg font-medium text-[#215558] flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass text-[#215558] fa-lg min-w-[26px]"></i> 
                            Eazy Vindbaarheid
                        </a>
                    </li>
                    <li>
                        <a href="/marketing" class="text-lg font-medium text-[#215558] flex items-center gap-2">
                            <i class="fa-solid fa-megaphone text-[#215558] fa-lg min-w-[26px]"></i> 
                            Eazy Marketing
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <p class="caveat-font text-2xl text-[#215558] mb-2">Volg ons op de socials!</p>
        <div class="mb-[3rem] flex items-center gap-4">
            <a href="https://nl.linkedin.com/company/eazyonline" target="_blank" 
                class="w-10 h-10 bg-white hover:bg-[#215558] transition duration-300 rounded-full flex items-center justify-center group">
                <span class="sr-only">LinkedIn</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-5 h-5 text-[#215558] group-hover:text-white transition duration-300 fill-current">
                    <path d="M196.3 512L103.4 512L103.4 212.9L196.3 212.9L196.3 512zM149.8 172.1C120.1 172.1 96 147.5 96 117.8C96 103.5 101.7 89.9 111.8 79.8C121.9 69.7 135.6 64 149.8 64C164 64 177.7 69.7 187.8 79.8C197.9 89.9 203.6 103.6 203.6 117.8C203.6 147.5 179.5 172.1 149.8 172.1zM543.9 512L451.2 512L451.2 366.4C451.2 331.7 450.5 287.2 402.9 287.2C354.6 287.2 347.2 324.9 347.2 363.9L347.2 512L254.4 512L254.4 212.9L343.5 212.9L343.5 253.7L344.8 253.7C357.2 230.2 387.5 205.4 432.7 205.4C526.7 205.4 544 267.3 544 347.7L544 512L543.9 512z"/>
                </svg>
            </a>

            <a href="https://www.instagram.com/eazy.online/" target="_blank" 
                class="w-10 h-10 bg-white hover:bg-[#215558] transition duration-300 rounded-full flex items-center justify-center group">
                <span class="sr-only">Instagram</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-5 h-5 text-[#215558] group-hover:text-white transition duration-300 fill-current">
                    <path d="M320.3 205C256.8 204.8 205.2 256.2 205 319.7C204.8 383.2 256.2 434.8 319.7 435C383.2 435.2 434.8 383.8 435 320.3C435.2 256.8 383.8 205.2 320.3 205zM319.7 245.4C360.9 245.2 394.4 278.5 394.6 319.7C394.8 360.9 361.5 394.4 320.3 394.6C279.1 394.8 245.6 361.5 245.4 320.3C245.2 279.1 278.5 245.6 319.7 245.4zM413.1 200.3C413.1 185.5 425.1 173.5 439.9 173.5C454.7 173.5 466.7 185.5 466.7 200.3C466.7 215.1 454.7 227.1 439.9 227.1C425.1 227.1 413.1 215.1 413.1 200.3zM542.8 227.5C541.1 191.6 532.9 159.8 506.6 133.6C480.4 107.4 448.6 99.2 412.7 97.4C375.7 95.3 264.8 95.3 227.8 97.4C192 99.1 160.2 107.3 133.9 133.5C107.6 159.7 99.5 191.5 97.7 227.4C95.6 264.4 95.6 375.3 97.7 412.3C99.4 448.2 107.6 480 133.9 506.2C160.2 532.4 191.9 540.6 227.8 542.4C264.8 544.5 375.7 544.5 412.7 542.4C448.6 540.7 480.4 532.5 506.6 506.2C532.8 480 541 448.2 542.8 412.3C544.9 375.3 544.9 264.5 542.8 227.5zM495 452C487.2 471.6 472.1 486.7 452.4 494.6C422.9 506.3 352.9 503.6 320.3 503.6C287.7 503.6 217.6 506.2 188.2 494.6C168.6 486.8 153.5 471.7 145.6 452C133.9 422.5 136.6 352.5 136.6 319.9C136.6 287.3 134 217.2 145.6 187.8C153.4 168.2 168.5 153.1 188.2 145.2C217.7 133.5 287.7 136.2 320.3 136.2C352.9 136.2 423 133.6 452.4 145.2C472 153 487.1 168.1 495 187.8C506.7 217.3 504 287.3 504 319.9C504 352.5 506.7 422.6 495 452z"/>
                </svg>
            </a>

            <a href="https://www.facebook.com/eazyonline.nl/" target="_blank" 
                class="w-10 h-10 bg-white hover:bg-[#215558] transition duration-300 rounded-full flex items-center justify-center group">
                <span class="sr-only">Facebook</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-5 h-5 text-[#215558] group-hover:text-white transition duration-300 fill-current">
                    <path d="M576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 440 146.7 540.8 258.2 568.5L258.2 398.2L205.4 398.2L205.4 320L258.2 320L258.2 286.3C258.2 199.2 297.6 158.8 383.2 158.8C399.4 158.8 427.4 162 438.9 165.2L438.9 236C432.9 235.4 422.4 235 409.3 235C367.3 235 351.1 250.9 351.1 292.2L351.1 320L434.7 320L420.3 398.2L351 398.2L351 574.1C477.8 558.8 576 450.9 576 320z"/>
                </svg>
            </a>

            <a href="https://twitter.com/eazyonline" target="_blank" 
                class="w-10 h-10 bg-white hover:bg-[#215558] transition duration-300 rounded-full flex items-center justify-center group">
                <span class="sr-only">Twitter / X</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" class="w-5 h-5 text-[#215558] group-hover:text-white transition duration-300 fill-current">
                    <path d="M453.2 112L523.8 112L369.6 288.2L551 528L409 528L297.7 382.6L170.5 528L99.8 528L264.7 339.5L90.8 112L236.4 112L336.9 244.9L453.2 112zM428.4 485.8L467.5 485.8L215.1 152L173.1 152L428.4 485.8z"/>
                </svg>
            </a>



        </div>
        <div class="w-full flex flex-col md:flex-row md:gap-8">
            <p class="text-lg font-normal leading-tight text-[#215558]">© 2025 Eazyonline</p>
            <p class="hidden md:block text-lg font-normal leading-tight text-[#215558]">|</p>
            <a href="/algemene-voorwaarden" class="text-lg font-normal leading-tight text-[#215558]">Algemene voorwaarden</a>
            <p class="hidden md:block text-lg font-normal leading-tight text-[#215558]">|</p>
            <a href="/privacybeleid" class="text-lg font-normal leading-tight text-[#215558]">Privacy</a>
        </div>
    </div>
</div>
<script>
    document.getElementById('contactForm').addEventListener('submit', async function(e) {
        e.preventDefault(); // voorkom standaard submit

        let form = e.target;
        let formData = new FormData(form);

        try {
            let response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: formData
            });

            let result = await response.json();

            if (response.ok) {
                document.getElementById('formFeedback').innerHTML =
                    `<p class="text-green-600 font-medium">${result.message ?? 'Bedankt! Je bericht is verstuurd.'}</p>`;
                form.reset();
            } else {
                let errors = result.errors ? Object.values(result.errors).flat().join('<br>') : 'Er is iets misgegaan.';
                document.getElementById('formFeedback').innerHTML =
                    `<p class="text-red-600 font-medium">${errors}</p>`;
            }
        } catch (err) {
            document.getElementById('formFeedback').innerHTML =
                `<p class="text-red-600 font-medium">Er is een fout opgetreden. Probeer later opnieuw.</p>`;
        }
    });
</script>