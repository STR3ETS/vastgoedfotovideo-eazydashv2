<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="/assets/favicon.webp">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: 'Inter Tight', sans-serif; }
    </style>
</head>
<body class="bg-[#eff5f7]">

<div class="w-full h-screen flex items-center justify-center bg-[#F5EFED] bg-cover bg-center">
    <div class="w-1/2 h-full flex items-center justify-center relative">
        <img class="max-h-10 absolute z-999 top-4 left-4" src="/assets/vastgoedfotovideo/logo-full.png" alt="VastgoedFotoVideo">
        <div class="w-full md:w-[25rem] p-6 bg-white rounded-2xl relative flex flex-col items-center">
            <div class="absolute z-[1] -top-[4rem]">
                <h1 class="text-[#191D38] text-4xl font-black tracking-tight">Welkom terug.</h1>
            </div>
            <div class="absolute z-[1] -bottom-[3rem]">
                <h2 class="text-[#191D38]/20 text-sm font-semibold tracking-tight">Een software in samenwerking met <a href="https://www.eazyonline.nl" target="_blank" class="underline">Eazyonline</a></h2>
            </div>
            <form id="login-form" action="{{ session('token_input') ? route('support.verify_token') : route('support.send_token') }}" method="POST" class="w-full flex flex-col items-center">
                @csrf
                @if ($errors->any())
                    <div class="text-[#DF2935] font-semibold text-sm p-2 bg-[#DF2935]/20 w-full rounded-full text-center mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif
                <input type="email" name="email" id="email" placeholder="Voer je e-mailadres in"
                    value="{{ old('email', session('email')) }}"
                    class="form-control w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] mb-6 focus:outline-none focus:ring-[#009AC3] transition"
                    @if(session('token_input')) readonly style="opacity: 50%; cursor: not-allowed;" @endif>
                        @if(session('email'))
                            <div class="feedback w-full text-sm text-[#87A878] bg-[#87A878]/20 rounded p-2 text-center mb-3">
                                Er is een inlogcode verstuurd naar <strong>{{ session('email') }}</strong>. Controleer je e-mail.
                            </div>
                        @endif
                @if(session('token_input'))
                <div id="token-group" class="w-full relative flex flex-col items-center space-y-4">
                    <input type="hidden" id="temp_token" name="temp_token" value="{{ session('temp_token') }}">
                    <div class="grid grid-cols-6 gap-2 sm:gap-4 pb-4 w-full max-w-xs sm:max-w-md">
                        @for ($i = 0; $i < 6; $i++)
                            <input 
                                type="text" 
                                maxlength="1" 
                                class="w-full h-12 sm:h-14 text-center text-[#191D38] text-lg border border-[#191D38]/10 rounded otp-box bg-white focus:outline-none focus:border-[#009AC3] transition duration-300"
                                autofocus="{{ $i === 0 ? 'true' : 'false' }}">
                        @endfor
                    </div>
                </div>
                @endif
                @if (!session('token_input'))
                    <button type="submit" class="bg-[#009AC3] w-full text-white py-3 rounded-full font-semibold">
                        Aanmelden
                    </button>
                @endif
            </form>
            @if(session('token_input'))
                <form action="{{ route('support.resend_token') }}" method="POST" class="mt-3 w-full text-center">
                    @csrf
                    <input type="hidden" name="email" value="{{ session('email') }}">
                    <input type="hidden" name="temp_token" value="{{ session('temp_token') }}">
                    <button type="submit" class="text-sm text-[#191D38]/70 cursor-pointer  underline">Geen code ontvangen? Vraag een nieuwe aan</button>
                </form>

                <p class="text-sm text-[#191D38]/50" id="edit-email">
                    Wil je het e-mailadres wijzigen? Klik <a href="#" class="underline">hier</a>.
                </p>
            @endif
        </div>
    </div>
    <div class="w-1/2 h-full p-4">
        <div class="w-full h-full rounded-4xl bg-[url('/assets/vastgoedfotovideo/auth/login-bg.jpg')] bg-cover bg-center"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editLink = document.getElementById('edit-email');

        if (editLink) {
            editLink.addEventListener('click', function (e) {
                e.preventDefault();

                const emailInput = document.getElementById('email');
                emailInput.removeAttribute('readonly');
                emailInput.style.opacity = '100%';
                emailInput.style.cursor = 'auto';

                document.querySelectorAll('#token-group').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.feedback').forEach(el => el.style.display = 'none');

                const tempTokenInput = document.getElementById('temp_token');
                if (tempTokenInput) {
                    tempTokenInput.remove();
                }

                document.querySelectorAll('.otp-box').forEach(input => input.value = '');

                const loginForm = document.getElementById('login-form');
                loginForm.action = "{{ route('support.send_token') }}";

                const submitBtn = loginForm.querySelector("button[type='submit']");
                submitBtn.textContent = 'Aanmelden';

                editLink.style.display = 'none';
            });
        }

        const inputs = document.querySelectorAll('.otp-box');
        const hiddenInput = document.getElementById('temp_token');

        inputs.forEach((input, index) => {
            input.addEventListener('input', () => {
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateToken();
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                if (paste.length === inputs.length) {
                    e.preventDefault();
                    paste.split('').forEach((char, i) => {
                        inputs[i].value = char;
                    });
                    inputs[inputs.length - 1].focus();
                    updateToken();
                }
            });
        });

        function updateToken() {
            hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const inputs = document.querySelectorAll(".otp-box");
        const form = document.getElementById("login-form");

        inputs.forEach((input, index) => {
            input.addEventListener("input", function () {
                this.value = this.value.replace(/\D/, '');

                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                checkAndSubmit();
            });

            input.addEventListener("keydown", function (e) {
                if (e.key === "Backspace" && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener("paste", function (e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                const digits = pasteData.replace(/\D/g, '').slice(0, 6);

                digits.split('').forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                    }
                });

                if (digits.length === 6) {
                    checkAndSubmit();
                }
            });
        });

        function checkAndSubmit() {
            const allFilled = Array.from(inputs).every(i => i.value.length === 1);
            if (allFilled) {
                form.submit();
            }
        }
    });
</script>
</body>
</html>
