<?php

declare(strict_types=1);

namespace Tiden\Laravel\Tests;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Tiden\Client;
use Tiden\Options;
use Tiden\Sdk;
use Tiden\Transport\NullTransport;

final class IntegrationTest extends TestCase
{
    public function test_config_is_merged(): void
    {
        $this->assertSame('http://test@localhost/proj', config('tiden.dsn'));
        $this->assertSame('testing', config('tiden.environment'));
    }

    public function test_reported_exception_is_captured_and_sent(): void
    {
        // Swap the SDK's transport for an in-memory one so we can inspect the
        // envelope the bridge would send (provider boot already wired the
        // reportable callback to Sdk::captureException).
        $transport = new NullTransport;
        Sdk::bind(new Client(new Options(dsn: 'http://k@localhost/proj'), $transport));

        $this->app->make(ExceptionHandler::class)->report(new \RuntimeException('boom from laravel'));

        $this->assertCount(1, $transport->envelopes);
        $body = json_decode(explode("\n", rtrim((string) $transport->last(), "\n"))[2], true);
        $this->assertSame('php', $body['platform']);
        $this->assertSame('RuntimeException', $body['exception']['values'][0]['type']);
        $this->assertSame('boom from laravel', $body['exception']['values'][0]['value']);
    }
}
