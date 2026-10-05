<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** URL lama dialihkan permanen ke halaman katalog/keranjang yang setara. */
class LegacyRedirectTest extends TestCase
{
    /** @return array<string, array{0: string, 1: string}> */
    public static function legacyPaths(): array
    {
        return [
            'trip' => ['/trip/3', '/katalog/3'],
            'booking' => ['/booking', '/keranjang'],
            'booking dengan query lama' => ['/booking?trip=1&seats[]=3', '/keranjang'],
            'payment' => ['/payment', '/pembayaran'],
            'booking success' => ['/booking/success', '/pembayaran/selesai'],
            'promo' => ['/promo', '/'],
            'search tanpa query' => ['/search', '/katalog'],
        ];
    }

    #[DataProvider('legacyPaths')]
    public function test_it_should_redirect_permanently_when_visiting_legacy_url(string $from, string $to): void
    {
        $this->get($from)->assertStatus(301)->assertRedirect($to);
    }

    public function test_it_should_keep_only_whitelisted_query_when_redirecting_search(): void
    {
        $response = $this->get('/search?from=Jakarta&to=Bandung&date=2026-12-01&passengers=3&next=https://evil.test');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/katalog', [
            'from' => 'Jakarta', 'to' => 'Bandung', 'date' => '2026-12-01',
        ]);
    }

    public function test_it_should_drop_array_and_empty_values_when_redirecting_search(): void
    {
        $response = $this->get('/search?from[]=Jakarta&to=&date[x]=2026-12-01');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/katalog', []);
    }

    #[DataProvider('invalidTripIds')]
    public function test_it_should_return_not_found_when_legacy_trip_id_is_invalid(string $id): void
    {
        $this->get("/trip/{$id}")->assertNotFound();
    }

    /** @return array<string, array{0: string}> */
    public static function invalidTripIds(): array
    {
        return ['nol' => ['0'], 'nol di depan' => ['01'], 'huruf' => ['abc'], 'di luar int' => ['99999999999999999999999']];
    }

    /** @param  array<string, string>  $expected */
    private function assertRedirectQuery(?string $location, string $path, array $expected): void
    {
        $this->assertNotNull($location);
        $this->assertSame(url($path), strtok($location, '?'));
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame($expected, $query);
    }
}
