@extends('layouts.ness')

@section('title', 'Verificación 2FA por WhatsApp')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center">
    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-xl">
        <div class="mb-6 text-center">
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 mb-2">
                SEGUNDO FACTOR · WHATSAPP OTP
            </span>
            <h1 class="text-2xl font-bold text-white">Verifica tu identidad</h1>
            <p class="text-sm text-slate-400 mt-1">
                Enviamos un código de 6 dígitos al WhatsApp registrado para
                <span class="text-slate-200 font-medium">{{ $usuario->username }}</span>
                (vigente por {{ ceil(($pending['vigencia_segundos'] ?? 300) / 60) }} min).
            </p>
            @if(!empty($pending['debe_activar_2fa']))
                <p class="text-xs text-amber-300 mt-2">
                    Al verificar este código se activará 2FA en tu cuenta.
                </p>
            @endif
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl bg-rose-500/15 border border-rose-500/30 px-4 py-3 text-xs text-rose-200">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 px-4 py-3 text-xs text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.whatsapp.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label for="codigo_otp" class="block text-sm font-medium text-slate-300 mb-1">
                    Código de 6 dígitos
                </label>
                <input id="codigo_otp" name="codigo_otp" type="text" inputmode="numeric" maxlength="6"
                       pattern="[0-9]{6}" required autofocus placeholder="000000"
                       class="w-full text-center tracking-[0.4em] text-xl font-mono rounded-xl bg-slate-950 border border-slate-700 px-4 py-3 text-white focus:border-emerald-500 focus:outline-none">
            </div>

            <button type="submit"
                    id="btn-verificar-otp"
                    class="btn-ness-primario">
                <span>Verificar código</span>
                <span aria-hidden="true">→</span>
            </button>
        </form>

        <div class="mt-4 flex items-center justify-between text-xs text-slate-400">
            <form method="POST" action="{{ route('2fa.whatsapp.resend') }}">
                @csrf
                <button type="submit"
                        style="color: #38bdf8 !important; background: transparent !important; opacity: 1 !important;"
                        class="hover:underline cursor-pointer">
                    Reenviar código por WhatsApp
                </button>
            </form>
            <a href="{{ route('login') }}" class="hover:text-slate-200">Volver al login</a>
        </div>
    </div>
</div>

<style>
    #btn-verificar-otp.btn-ness-primario,
    form #btn-verificar-otp,
    button#btn-verificar-otp[type="submit"] {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        width: 100% !important;
        padding: 13px 20px !important;
        border-radius: 12px !important;
        border: none !important;
        background-color: #0b4a96 !important;
        background-image: linear-gradient(90deg, #073977 0%, #0b53a6 55%, #0284c7 100%) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        letter-spacing: 0.2px !important;
        box-shadow: 0 8px 18px rgba(8, 59, 122, 0.28) !important;
        opacity: 1 !important;
        cursor: pointer !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease !important;
    }

    #btn-verificar-otp.btn-ness-primario:hover,
    button#btn-verificar-otp[type="submit"]:hover {
        filter: brightness(1.06) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 10px 22px rgba(2, 132, 199, 0.34) !important;
        color: #ffffff !important;
        -webkit-text-fill-color: #ffffff !important;
    }

    #btn-verificar-otp.btn-ness-primario:active,
    button#btn-verificar-otp[type="submit"]:active {
        transform: translateY(0) !important;
        box-shadow: 0 4px 10px rgba(8, 59, 122, 0.25) !important;
    }

    #btn-verificar-otp.btn-ness-primario::before,
    #btn-verificar-otp.btn-ness-primario::after {
        display: none !important;
        content: none !important;
    }
</style>
@endsection
