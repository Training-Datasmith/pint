<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\ElaborateSummary;
use App\Actions\FixCode;
use App\Commands\DefaultCommand;
use Illuminate\Support\ServiceProvider;

class CommandsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bindMethod([DefaultCommand::class, 'handle'], fn ($command) => $command->handle(
            resolve(FixCode::class),
            resolve(ElaborateSummary::class)
        ));
    }
}
