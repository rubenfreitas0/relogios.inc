<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception tipada para erros durante o processo de checkout.
 * Transporta o HTTP status code adequado para a resposta da API.
 */
class CheckoutException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 422,
    ) {
        parent::__construct($message);
    }
}
