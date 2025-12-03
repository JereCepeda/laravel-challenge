<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\ViewComposers\UserDataComposer;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Aplicar a vistas específicas
        View::composer([
            'dashboard.*',
            'layouts.app',
            'layouts.dashboard'
        ], UserDataComposer::class);
    }

    public function register()
    {
        //
    }
}