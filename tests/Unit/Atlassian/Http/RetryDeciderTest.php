<?php

namespace Javidnikoo\LaravelAtlassian\Tests\Unit\Atlassian\Http;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Javidnikoo\LaravelAtlassian\Atlassian\Http\RetryDecider;
use PHPUnit\Framework\TestCase;

class RetryDeciderTest extends TestCase
{
    /** @test */
    public function it_retries_for_non_request_exception(): void
    {
        $this->assertTrue(RetryDecider::shouldRetry(new \RuntimeException('boom')));
    }

    /** @test */
    public function it_retries_when_status_is_null(): void
    {
        $e = new RequestException(new Response(new \GuzzleHttp\Psr7\Response(200)));
        $e->response = null;

        $this->assertTrue(RetryDecider::shouldRetry($e));
    }

    /** @test */
    public function it_retries_for_5xx(): void
    {
        $response = new Response(new \GuzzleHttp\Psr7\Response(500));
        $e = new RequestException($response);

        $this->assertTrue(RetryDecider::shouldRetry($e));
    }

    /** @test */
    public function it_does_not_retry_for_4xx(): void
    {
        $response = new Response(new \GuzzleHttp\Psr7\Response(400));
        $e = new RequestException($response);

        $this->assertFalse(RetryDecider::shouldRetry($e));
    }
}
