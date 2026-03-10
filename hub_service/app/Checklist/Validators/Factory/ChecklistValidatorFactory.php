<?php

namespace App\Checklist\Validators\Factory;

use App\Checklist\Contracts\ChecklistValidatorInterface;
use App\Checklist\Validators\GermanyChecklistValidator;
use App\Checklist\Validators\UsaChecklistValidator;
use Exception;

class ChecklistValidatorFactory
{
    public function make(string $country): ChecklistValidatorInterface
    {
        $country = strtolower($country);
        return match ($country) {
            'usa' => app(UsaChecklistValidator::class),
            'germany' => app(GermanyChecklistValidator::class),
            default => throw new Exception("Unsupported country")
        };
    }
}
