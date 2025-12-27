<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait CheckRole
{
    protected function checkRole(Request $request, array $roles)
    {
        if (!Auth::check()) {
            abort(401, 'Silakan login terlebih dahulu');
        }

        $user = Auth::user();

        if (!in_array($user->role, $roles)) {
            abort(403, 'Akses ditolak');
        }

        return $user;
    }
}
