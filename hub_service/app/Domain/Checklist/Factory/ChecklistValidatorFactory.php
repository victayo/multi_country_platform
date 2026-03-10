<?php

namespace App\Domain\Checklist\Factory;

use App\Domain\Checklist\Validators\GermanyChecklistValidator;
use App\Domain\Checklist\Validators\UsaChecklistValidator;
use App\Domain\Checklist\Contracts\ChecklistValidatorInterface;
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
