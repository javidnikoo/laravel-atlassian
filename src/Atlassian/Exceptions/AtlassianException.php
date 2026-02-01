<?php

namespace Javidnikoo\LaravelAtlassian\Atlassian\Exceptions;

use Exception;

class AtlassianException extends Exception
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        public readonly array $context = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
