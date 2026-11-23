@extends('layouts.ness')

@section('title', 'Verificación 2FA por WhatsApp')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endpush

@section('content')
<div class="min-h-[80vh] flex items-center justify-center p-2 sm:p-4">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-5 sm:p-8 shadow-xl text-slate-800">
        <div class="mb-6 text-center">
            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 mb-2">
                SEGUNDO FACTOR · WHATSAPP OTP
            </span>
            <h1 class="text-2xl font-bold text-[#0b3b80]">Verifica tu identidad</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ !empty($pending['enviado']) ? 'Enviamos' : 'Se intentó enviar' }} un código de 6 dígitos al WhatsApp registrado para
                <span class="text-slate-800 font-medium">{{ $usuario->username }}</span>
                (vigente por {{ ceil(($pending['vigencia_segundos'] ?? 300) / 60) }} min).
            </p>
            @if(!empty($pending['debe_activar_2fa']))
                <p class="text-xs text-amber-300 mt-2">
                    Al verificar este código se activará 2FA en tu cuenta.
                </p>
            @endif
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-xs text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-xs text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('2fa.whatsapp.verify') }}" class="space-y-4">
            @csrf
            <div>
                <label for="codigo_otp" class="block text-sm font-medium text-slate-700 mb-1">
                    Código de 6 dígitos
                </label>
                <input id="codigo_otp" name="codigo_otp" type="text" inputmode="numeric" maxlength="6"
                       pattern="[0-9]{6}" required autofocus placeholder="000000"
                       class="w-full text-center tracking-[0.4em] text-xl font-mono rounded-xl bg-slate-50 border border-slate-300 px-4 py-3 text-slate-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 focus:outline-none">
            </div>

            <button type="submit"
                    id="btn-verificar-otp"
                    class="btn-ness-primario">
                <span>Verificar código</span>
                <span aria-hidden="true">→</span>
            </button>
        </form>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
            <form method="POST" action="{{ route('2fa.whatsapp.resend') }}">
                @csrf
                <button type="submit"
                        class="text-sky-700 hover:text-sky-900 hover:underline font-semibold cursor-pointer">
                    Reenviar código por WhatsApp
                </button>
            </form>
            <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900">Volver al login</a>
        </div>
    </div>
</div>
@endsection
