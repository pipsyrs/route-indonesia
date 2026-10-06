<?php

namespace App\Support;

/** Validasi URL dari env sebelum dipakai di href. */
class SafeUrl
{
    /** URL https dengan host; selain itu null. */
    public static function https(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $url = trim($value);
        $parts = parse_url($url);

        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') === '') {
            return null;
        }

        return $url;
    }
}
