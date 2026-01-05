@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
    <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
        <div class="flex-1 w-full overflow-hidden flex flex-col min-h-0">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <input
                    type="text"
                    placeholder="Zoeken op persoon..."
                    class="h-9 bg-white border border-gray-200 flex items-center px-4 w-[300px] rounded-full text-xs text-[#191D38] font-medium outline-none"
                    >
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative">
                    <select
                        class="h-9 bg-white border border-gray-200 pl-4 pr-10 rounded-full text-xs text-[#191D38] font-medium outline-none appearance-none cursor-pointer"
                    >
                        <option value="newest">Nieuwste eerst</option>
                        <option value="oldest">Oudste eerst</option>
                        <option value="title_asc">Titel A–Z</option>
                        <option value="title_desc">Titel Z–A</option>
                        <option value="status">Status</option>
                    </select>
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#191D38]/60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    </div>
                </div>
            </div>
            <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
                <div class="grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                    <p class="text-[#191D38] font-bold text-xs opacity-50">ID</p>
                    <p class="text-[#191D38] font-bold text-xs opacity-50">Onboarding door</p>
                    <p class="text-[#191D38] font-bold text-xs opacity-50">Onboarding op</p>
                    <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
                    <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
                </div>
            </div>
            <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl">
                <div class="px-6 py-5 divide-y divide-[#191D38]/10">
                    <div class="py-3 pt-0 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                        <div class="text-[#191D38] font-semibold text-sm">1</div>
                        <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                        <div class="text-[#191D38] text-sm">05-01-2026</div>
                        <div class="text-[#2A324B] bg-[#2A324B]/20 text-xs font-semibold rounded-full py-1.5 text-center">Concept</div>
                        <div class="justify-end text-[#191D38] flex items-center gap-2">
                            <button class="cursor-pointer">
                                <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                            </button>
                        </div>
                    </div>
                    <div class="py-3 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                        <div class="text-[#191D38] font-semibold text-sm">2</div>
                        <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                        <div class="text-[#191D38] text-sm">05-01-2026</div>
                        <div class="text-[#87A878] bg-[#87A878]/20 text-xs font-semibold rounded-full py-1.5 text-center">Voltooid</div>
                        <div class="justify-end text-[#191D38] flex items-center gap-2">
                            <button class="cursor-pointer">
                                <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                            </button>
                        </div>
                    </div>
                    <div class="py-3 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                        <div class="text-[#191D38] font-semibold text-sm">3</div>
                        <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                        <div class="text-[#191D38] text-sm">05-01-2026</div>
                        <div class="text-[#DF2935] bg-[#DF2935]/20 text-xs font-semibold rounded-full py-1.5 text-center">Geannuleerd</div>
                        <div class="justify-end text-[#191D38] flex items-center gap-2">
                            <button class="cursor-pointer">
                                <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                            </button>
                        </div>
                    </div>
                    <div class="py-3 pb-0 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
                        <div class="text-[#191D38] font-semibold text-sm">4</div>
                        <div class="text-[#191D38] text-sm">Lorem, ipsum.</div>
                        <div class="text-[#191D38] text-sm">05-01-2026</div>
                        <div class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold rounded-full py-1.5 text-center">Gearchiveerd</div>
                        <div class="justify-end text-[#191D38] flex items-center gap-2">
                            <button class="cursor-pointer">
                                <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection