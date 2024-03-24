<?php

namespace QuixLabs\LaravelPulseGraphql\ResolverMiddlewares;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Middleware;

class SendEventToPulseMiddleware extends Middleware
{
    public function __construct(public Closure $record)
    {
    }

    /**
     * @inheritDoc
     */
    public function handle($root, array $args, $context, ResolveInfo $info, Closure $next)
    {
        $startedAt = now();
        $result = $next($root, $args, $context, $info);
        ($this->record)($startedAt, $args, $context, $info);
        return $result;
    }
}
