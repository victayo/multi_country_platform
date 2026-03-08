<?php

namespace App\Contracts;

interface PublisherInterface
{
    public function publish(string $event, array $data): void;
}
