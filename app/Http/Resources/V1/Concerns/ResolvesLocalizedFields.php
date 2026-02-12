<?php

namespace App\Http\Resources\V1\Concerns;

trait ResolvesLocalizedFields
{
    protected function localized(string $field): mixed
    {
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $fallbackLocale = $locale === 'ar' ? 'en' : 'ar';

        $localized = data_get($this->resource, "{$field}_{$locale}");
        if ($localized !== null && $localized !== '') {
            return $localized;
        }

        $fallback = data_get($this->resource, "{$field}_{$fallbackLocale}");
        if ($fallback !== null && $fallback !== '') {
            return $fallback;
        }

        return data_get($this->resource, $field);
    }
}
