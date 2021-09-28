<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    private array $errors;

    public function __construct(string $message, array $errors)
    {
        array_unshift($errors, $message);

        parent::__construct(implode(PHP_EOL, $errors));

        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
