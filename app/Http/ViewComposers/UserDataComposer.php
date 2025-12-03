<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class UserDataComposer
{
    public function compose(View $view)
    {
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $user->load('role');
            $permissions = $user->role->permissions ?? [];
            
            $view->with([
                'authUser' => $user,
                'userPermissions' => $permissions
            ]);
        }
    }
}