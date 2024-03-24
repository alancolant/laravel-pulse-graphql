<?php

namespace QuixLabs\LaravelPulseGraphql;

use QuixLabs\LaravelPulseGraphql\Livewire\Graphql;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Livewire\LivewireManager;

class PulseGraphqlServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'graphql');

        $this->callAfterResolving('livewire', function (LivewireManager $livewire, Application $app) {
            $livewire->component('pulse.graphql', Graphql::class);
        });
    }
}
