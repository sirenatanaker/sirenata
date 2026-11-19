<?php

namespace Modules\LMS\Services\Grading\Exceptions;

use RuntimeException;

class GeminiRateLimitException extends RuntimeException
{
    public function __construct(
        string $message = 'Gemini API rate limit / service unavailable.',
        public readonly int $httpStatus = 429,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
