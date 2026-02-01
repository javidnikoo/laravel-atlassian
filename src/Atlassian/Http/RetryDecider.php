<?php

namespace Javidnikoo\LaravelAtlassian\Atlassian\Http;

use Illuminate\Http\Client\RequestException;
use Throwable;

final class RetryDecider
{
    public static function shouldRetry(Throwable $e): bool
    {
        if (! $e instanceof RequestException) {
            return true;
        }

        $status = $e->response?->status();

        if ($status === null) {
            return true;
        }

        if ($status >= 500) {
            return true;
        }

        return false;
    }
}
