<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No registramos nada aquí para Intervention
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        
        // Configurar Intervention/Image para usar GD globalmente
        if (class_exists(\Intervention\Image\ImageManagerStatic::class)) {
            \Intervention\Image\ImageManagerStatic::configure(['driver' => 'gd']);
        }
    }
}