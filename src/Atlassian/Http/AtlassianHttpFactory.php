<?php

namespace Javidnikoo\LaravelAtlassian\Atlassian\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class AtlassianHttpFactory
{
    public static function make(array $config): PendingRequest
    {
        return Http::baseUrl(rtrim($config['base_url'], '/').'/')
            ->withBasicAuth($config['email'], $config['api_token'])
            ->acceptJson()
            ->contentType('application/json')
            ->timeout((int) $config['timeout'])
            ->retry(
                times: (int) ($config['retries'] ?? 3),
                sleepMilliseconds: (int) ($config['retry_delay_ms'] ?? 1000),
                when: fn ($e) => RetryDecider::shouldRetry($e)
            );
    }
}
