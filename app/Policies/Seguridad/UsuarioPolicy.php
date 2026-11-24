<?php

namespace App\Policies\Seguridad;

use App\Models\Seguridad\Usuario;

class UsuarioPolicy
{
    public function viewAny(Usuario $authUser): bool
    {
        $codigo = strtoupper((string) optional($authUser->rol)->codigo);

        return in_array($codigo, ['ADSIS', 'AC'], true);
    }

    public function create(Usuario $authUser): bool
    {
        $codigo = strtoupper((string) optional($authUser->rol)->codigo);

        return in_array($codigo, ['ADSIS', 'AC'], true);
    }

    public function update(Usuario $authUser, Usuario $targetUser): bool
    {
        return $this->canManageTarget($authUser, $targetUser);
    }

    public function toggleEstado(Usuario $authUser, Usuario $targetUser): bool
    {
        if ((int) $authUser->id_usuario === (int) $targetUser->id_usuario) {
            return false;
        }

        return $this->canManageTarget($authUser, $targetUser);
    }

    private function canManageTarget(Usuario $authUser, Usuario $targetUser): bool
    {
        $codigoAuth = strtoupper((string) optional($authUser->rol)->codigo);

        if ($codigoAuth === 'ADSIS') {
            return true;
        }

        if ($codigoAuth !== 'AC') {
            return false;
        }

        $nivelAuth = (int) (optional($authUser->rol)->nivel_jerarquia ?? 0);
        $nivelTarget = (int) (optional($targetUser->rol)->nivel_jerarquia ?? 0);
        $codigoTarget = strtoupper((string) optional($targetUser->rol)->codigo);

        if ($codigoTarget === 'ADSIS') {
            return false;
        }

        return $nivelAuth > $nivelTarget;
    }
}