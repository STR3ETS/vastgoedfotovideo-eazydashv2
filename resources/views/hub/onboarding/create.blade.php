@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
    <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
        <div class="flex-1 w-full overflow-hidden flex flex-col min-h-0 items-center justify-center">
            <h1 class="text-[#191D38] text-4xl font-black tracking-tight text-center mb-4">Aanvraag starten.</h1>
            <p class="text-[#191D38] text-sm text-center mb-6">
                <strong>Goed je te zien!</strong> 👋<br>
                We begeleiden je stap voor stap bij het aanvragen van je producten.
            </p>
            <p class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-6 px-4 rounded-full py-1.5 mb-6">
                We hebben een paar gegevens nodig om je aanvraag te verwerken.<br>Het duurt maar een paar minuten om alles in te vullen.
            </p>
            <a href="{{ route('support.onboarding.step1') }}" class="bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white py-3 rounded-full font-semibold">
                Onboarding starten
            </a>
        </div>
    </div>
</div>
@endsection