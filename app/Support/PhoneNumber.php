<?php

namespace App\Support;

/** Normalisasi nomor telepon dari env sebelum dipakai di href. */
class PhoneNumber
{
    /** Nomor WhatsApp format 628xxx; selain itu null. */
    public static function whatsapp(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $number = str_replace([' ', '-', '+'], '', trim($value));

        if (str_starts_with($number, '08')) {
            $number = '62'.substr($number, 1);
        }

        return preg_match('/^628[0-9]{7,12}$/D', $number) === 1 ? $number : null;
    }
}
