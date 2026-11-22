<?php
// app/Http/Middleware/SetPostgresSessionContext.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetPostgresSessionContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ip = $request->ip() ?? '';
        $userAgent = substr((string) $request->userAgent(), 0, 255);
        $sesionUuid = (string) $request->session()->get('ness_sesion_uuid', '');

        DB::statement("SELECT set_config('app.current_user_id', ?, true)", [$user ? (string) $user->id_usuario : '']);
        DB::statement("SELECT set_config('app.client_ip', ?, true)", [$ip]);
        DB::statement("SELECT set_config('app.user_agent', ?, true)", [$userAgent]);
        DB::statement("SELECT set_config('app.session_uuid', ?, true)", [$sesionUuid]);

        return $next($request);
    }
}