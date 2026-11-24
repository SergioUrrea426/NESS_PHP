<?php

// config/ness_seguridad.php
// Cumple con RNF-004 (30 min de inactividad), RNF-040 (parámetros en .env, sin secretos en repositorio)
// y RF-005 (2FA sin guardar OTP en PostgreSQL).

return [
    'max_intentos_login' => (int) env('NESS_MAX_INTENTOS_LOGIN', 5),
    'minutos_bloqueo_login' => (int) env('NESS_MINUTOS_BLOQUEO_LOGIN', 15),
    'inactividad_minutos' => (int) env('NESS_INACTIVIDAD_MINUTOS', 30),

    // Roles obligatorios con 2FA (ej. ['ADSIS', 'AC'])
    'roles_2fa_obligatorio' => array_filter(
        array_map('trim', explode(',', env('NESS_ROLES_2FA_OBLIGATORIO', 'ADSIS')))
    ),

    // OTP en memoria/caché (nunca en PostgreSQL)
    'otp_ttl_segundos' => (int) env('NESS_OTP_TTL_SEGUNDOS', 300),
    'otp_max_intentos' => (int) env('NESS_OTP_MAX_INTENTOS', 5),

    // WhatsApp Cloud API (Meta)
    'whatsapp' => [
        'driver' => env('NESS_WHATSAPP_DRIVER', 'log'), // 'log' en dev, 'meta' en prod
        'token' => env('NESS_WHATSAPP_TOKEN'),
        'phone_number_id' => env('NESS_WHATSAPP_PHONE_ID'),
        'template_name' => env('NESS_WHATSAPP_TEMPLATE', 'ness_otp_2fa'),
        'language_code' => env('NESS_WHATSAPP_LANG', 'es_CO'),
    ],
];