<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PathsRepository;
use App\Project;
use App\Repositories\ConfigurationJsonRepository;
use App\Repositories\GitPathsRepository;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputInterface;

class RepositoriesServiceProvider extends ServiceProvider
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
        $this->app->singleton(ConfigurationJsonRepository::class, function (): \App\Repositories\ConfigurationJsonRepository {
            $input = resolve(InputInterface::class);
            $config = $input->getOption('config') ?: Project::path().'/pint.json';

            return new ConfigurationJsonRepository(
                $input->getOption('no-config') ? null : $config,
                $input->getOption('preset'),
            );
        });

        $this->app->singleton(PathsRepository::class, fn () => new GitPathsRepository(
            Project::path(),
        ));
    }
}
