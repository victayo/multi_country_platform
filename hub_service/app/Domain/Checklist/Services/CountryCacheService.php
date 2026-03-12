<?php

namespace App\Domain\Checklist\Services;

use Closure;

class CountryCacheService
{
    private const CHECKLIST_CACHE_PREFIX = 'checklist:';

    public function get(string $country): ?array
    {
        $country = strtolower($country);
        return cache()->get(self::CHECKLIST_CACHE_PREFIX . $country);
    }

    public function put(string $country, array $value, int $minutes = 10): void
    {
        $country = strtolower($country);
        cache()->put(self::CHECKLIST_CACHE_PREFIX . $country, $value, now()->addMinutes($minutes));
    }

    public function remember(string $country, Closure $callback, int $minutes = 10): array
    {
        $country = strtolower($country);

        return cache()->remember(
            self::CHECKLIST_CACHE_PREFIX . $country,
            now()->addMinutes($minutes),
            static fn (): array => $callback()
        );
    }

    public function hasCountry(string $country): bool
    {
        $country = strtolower($country);
        return cache()->has(self::CHECKLIST_CACHE_PREFIX . $country);
    }

    public function forget(string $country): void
    {
        $country = strtolower($country);
        cache()->forget(self::CHECKLIST_CACHE_PREFIX . $country);
    }
}
