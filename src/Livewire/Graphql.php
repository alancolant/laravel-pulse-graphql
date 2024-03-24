<?php

namespace QuixLabs\LaravelPulseGraphql\Livewire;

use Illuminate\Support\Facades\View;
use Laravel\Pulse\Livewire\Card;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Url;

class Graphql extends Card
{
    #[Url(as: 'graphql')]
    public string $orderBy = 'slowest';

    #[Lazy]
    public function render()
    {
        [$data, $time, $runAt] = $this->remember(
            fn() => $this->aggregate(
                'graphql_request',
                ['max', 'count', 'avg'],
                match ($this->orderBy) {
                    'count' => 'count',
                    'avg' => 'avg',
                    default => 'max',
                })->map(function ($row) {
                [$schemaName, $operationType, $operation] = json_decode($row->key, flags: JSON_THROW_ON_ERROR);
                return (object)[
                    'schemaName'    => $schemaName,
                    'operationType' => $operationType,
                    'operation'     => $operation,
                    'count'         => $row->count,
                    'slowest'       => $row->max,
                    'average'       => $row->avg,
                ];
            }),
            $this->orderBy,
        );
        return View::make('graphql::livewire.graphql', [
            'time'  => $time,
            'runAt' => $runAt,
            'data'  => $data,
        ]);
    }
}
