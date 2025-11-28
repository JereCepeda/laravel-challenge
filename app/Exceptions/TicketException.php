<?php

namespace App\Exceptions;

use Exception;

class TicketException extends Exception
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }
}