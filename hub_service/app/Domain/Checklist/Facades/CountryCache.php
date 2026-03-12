<?php

namespace App\Domain\Checklist\Facades;

use App\Domain\Checklist\Services\CountryCacheService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array|null get(string $country)
 * @method static void put(string $country, array $value, int $minutes = 10)
 * @method static bool hasCountry(string $country)
 * @method static void forget(string $country)
 */
class CountryCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CountryCacheService::class;
    }
}
