<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown whenever a quote fetched from cayan-l fails validation and an
 * invoice must NOT be created from it.
 *
 * $reason is a short machine-readable code (used by the controller to build
 * the JSON error response), $message is human-readable.
 */
class QuoteValidationException extends Exception
{
    public string $reason;

    public function __construct(string $reason, string $message)
    {
        parent::__construct($message);
        $this->reason = $reason;
    }
}
