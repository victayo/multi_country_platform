<?php

namespace App\Domain\Checklist\Contracts;

interface ChecklistValidatorInterface
{
    public function validate(array $employee): array;
}
