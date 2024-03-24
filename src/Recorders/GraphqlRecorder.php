<?php

namespace QuixLabs\LaravelPulseGraphql\Recorders;

use QuixLabs\LaravelPulseGraphql\ResolverMiddlewares\SendEventToPulseMiddleware;
use GraphQL\Language\Printer;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Laragraph\Utils\RequestParser;
use Laravel\Pulse\Concerns\ConfiguresAfterResolving;
use Laravel\Pulse\Pulse;
use Rebing\GraphQL\GraphQL;

class GraphqlRecorder
{

    use      ConfiguresAfterResolving;

    /**
     * Create a new recorder instance.
     */
    public function __construct(
        protected Pulse         $pulse,
        protected Repository    $config,
        protected RequestParser $parser
    )
    {
        //
    }

    /**
     * Register the recorder.
     */
    public function register(callable $record, Application $app): void
    {
        $this->afterResolving(
            $app, GraphQL::class,
            function () use (&$record) {
                \Rebing\GraphQL\Support\Facades\GraphQL::appendGlobalResolverMiddleware(new SendEventToPulseMiddleware($record));
            }
        );
    }

    public function record(Carbon $startedAt, array $args, $context, ResolveInfo $info): void
    {

        if (!Route::is('graphql*') || !in_array($info->parentType, ["Query", "Mutation"])) {
            return;
        }

        $schemaName = Arr::first(Route::current()->parameters()) ?? Config::get('graphql.default_schema', 'default');

        $operationType = $info->operation->operation;
        $query = $info->fieldName;

//        $operation = $info->operation->name->value ?? null;
//        $fields = array_keys(Arr::dot($info->getFieldSelection(PHP_INT_MAX)));
//        $vars = $this->formatVariableDefinitions($info->operation->variableDefinitions);


        $duration = ($startedAt->diffInMilliseconds());
        $this->pulse->record(
            type: 'graphql_request',
            key: json_encode([
                $schemaName, $operationType, $query
            ], flags: JSON_THROW_ON_ERROR),
            value: $duration,
            timestamp: $startedAt,
        )->max()->avg()->count();
    }

    private function formatVariableDefinitions(?iterable $variableDefinitions = []): array
    {
        return collect($variableDefinitions)
            ->map(function ($def) {
                return Printer::doPrint($def);
            })
            ->toArray();
    }
}
