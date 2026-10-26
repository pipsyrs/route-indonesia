<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_it_should_normalize_indonesian_numbers_to_62_format(): void
    {
        $this->assertSame('6281234567890', PhoneNumber::whatsapp('081234567890'));
        $this->assertSame('6281234567890', PhoneNumber::whatsapp(' +62 812-3456-7890 '));
        $this->assertSame('6281234567890', PhoneNumber::whatsapp('6281234567890'));
        $this->assertSame('6281234567', PhoneNumber::whatsapp('081234567'));
    }

    public function test_it_should_reject_invalid_or_non_string_values(): void
    {
        $values = [null, '', '   ', ['0812345678'], true, 81234567890, '0812345', '08123456', '62812345678901234', '6221234567', '1234567890',
            '0812abc4567', '0812345678<script>', '+1 555 1234567', 'https://wa.me/6281234567890'];

        foreach ($values as $value) {
            $this->assertNull(PhoneNumber::whatsapp($value), var_export($value, true));
        }
    }
}
