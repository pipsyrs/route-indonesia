<?php

namespace Tests\Unit;

use App\Support\SafeUrl;
use PHPUnit\Framework\TestCase;

class SafeUrlTest extends TestCase
{
    public function test_it_should_accept_https_url_with_host(): void
    {
        $this->assertSame('https://chat.whatsapp.com/abc', SafeUrl::https('https://chat.whatsapp.com/abc'));
        $this->assertSame('HTTPS://example.com', SafeUrl::https('  HTTPS://example.com '));
    }

    public function test_it_should_reject_non_https_empty_or_hostless_values(): void
    {
        foreach (['javascript:alert(1)', 'http://example.com', '', '   ', 'https://', 'https:///path', '//example.com', 'example.com', null, ['https://x.com']] as $value) {
            $this->assertNull(SafeUrl::https($value), var_export($value, true));
        }
    }
}
