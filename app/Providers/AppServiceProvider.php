<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    Relation::morphMap([
        'quiz_question' => \App\Models\QuizQuestion::class,
        'live_question' => \App\Models\LiveSetQuestion::class,
    ]);
}
}
