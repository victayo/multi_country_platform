<?php

namespace App\DTO;

class ApiResponse
{
    public function __construct(
        public bool $success,
        public ?string $message = null,
        public mixed $data = null,
        public ?int $statusCode = null
    ) {
    }

    public static function success(string $message, mixed $data = null, ?int $statusCode = 200): self
    {
        return new self(true, $message, $data, $statusCode);
    }

    public static function error(string $message, mixed $data = null, ?int $statusCode = 400): self
    {
        return new self(false, $message, $data, $statusCode);
    }
}
