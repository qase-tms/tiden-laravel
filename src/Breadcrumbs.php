<?php

declare(strict_types=1);

namespace Tiden\Laravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Tiden\Breadcrumb;
use Tiden\Sdk;

/**
 * Records Laravel activity (SQL, queue jobs, logs) as breadcrumbs on the SDK's
 * scope, so they ride along with the next captured event. Each source is opt-out
 * via config. SQL bindings and log context are intentionally omitted (PII).
 */
final class Breadcrumbs
{
    /** @param array<string,bool> $enabled */
    public static function register(Dispatcher $events, array $enabled): void
    {
        if ($enabled['sql'] ?? true) {
            $events->listen(QueryExecuted::class, static function (QueryExecuted $e): void {
                Sdk::addBreadcrumb(new Breadcrumb(
                    message: $e->sql,
                    category: 'query',
                    type: 'query',
                    data: ['duration_ms' => $e->time, 'connection' => $e->connectionName],
                ));
            });
        }

        if ($enabled['queue'] ?? true) {
            $events->listen(JobProcessing::class, static function (JobProcessing $e): void {
                Sdk::addBreadcrumb(new Breadcrumb(
                    message: $e->job->resolveName(),
                    category: 'queue',
                    type: 'queue',
                    data: ['connection' => $e->connectionName, 'state' => 'processing'],
                ));
            });
            $events->listen(JobProcessed::class, static function (JobProcessed $e): void {
                Sdk::addBreadcrumb(new Breadcrumb(
                    message: $e->job->resolveName(),
                    category: 'queue',
                    type: 'queue',
                    data: ['state' => 'processed'],
                ));
            });
            $events->listen(JobFailed::class, static function (JobFailed $e): void {
                Sdk::addBreadcrumb(new Breadcrumb(
                    message: $e->job->resolveName(),
                    category: 'queue',
                    level: 'error',
                    type: 'queue',
                    data: ['connection' => $e->connectionName, 'state' => 'failed'],
                ));
            });
        }

        if ($enabled['logs'] ?? true) {
            $events->listen(MessageLogged::class, static function (MessageLogged $e): void {
                Sdk::addBreadcrumb(new Breadcrumb(
                    message: (string) $e->message,
                    category: 'log',
                    level: (string) $e->level,
                    type: 'log',
                ));
            });
        }
    }
}
