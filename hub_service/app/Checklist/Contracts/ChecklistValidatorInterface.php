<?php

namespace App\Checklist\Contracts;

interface ChecklistValidatorInterface
{
    public function validate(array $employee): array;
}
