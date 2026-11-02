<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** URL lama (termasuk path berbahasa Indonesia) dialihkan permanen ke halaman yang setara. */
class LegacyRedirectTest extends TestCase
{
    /** @return array<string, array{0: string, 1: string}> */
    public static function legacyPaths(): array
    {
        return [
            'trip' => ['/trip/3', '/catalog/3'],
            'booking' => ['/booking', '/cart'],
            'booking dengan query lama' => ['/booking?trip=1&seats[]=3', '/cart'],
            'booking success' => ['/booking/success', '/payment/success'],
            'promo' => ['/promo', '/'],
            'search tanpa query' => ['/search', '/catalog'],
            'katalog' => ['/katalog', '/catalog'],
            'katalog show' => ['/katalog/3', '/catalog/3'],
            'keranjang' => ['/keranjang', '/cart'],
            'pembayaran' => ['/pembayaran', '/payment'],
            'pembayaran selesai' => ['/pembayaran/selesai', '/payment/success'],
            'cek pesanan' => ['/cek-pesanan', '/check-order'],
            'masuk' => ['/masuk', '/login'],
            'daftar' => ['/daftar', '/register'],
            'akun' => ['/akun', '/account'],
            'akun pesanan' => ['/akun/pesanan', '/account/orders'],
            'akun alamat' => ['/akun/alamat', '/account/addresses'],
            'akun profil' => ['/akun/profil', '/account/profile'],
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
        $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', [
            'from' => 'Jakarta', 'to' => 'Bandung', 'date' => '2026-12-01',
        ]);
    }

    public function test_it_should_keep_keyword_when_redirecting_search(): void
    {
        $response = $this->get('/search?q=bromo&route=bromo');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', ['q' => 'bromo']);
    }

    public function test_it_should_drop_array_and_empty_values_when_redirecting_search(): void
    {
        $response = $this->get('/search?from[]=Jakarta&to=&date[x]=2026-12-01');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', []);
    }

    public function test_it_should_not_redirect_when_visiting_new_payment_url(): void
    {
        $this->get('/payment')->assertOk();
    }

    public function test_it_should_keep_whitelisted_query_when_redirecting_old_catalog(): void
    {
        $response = $this->get('/katalog?route=bromo&from=Jakarta&to=Bandung&q=bus&date=2026-12-01&next=https://evil.test&q2[]=x');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', [
            'route' => 'bromo', 'from' => 'Jakarta', 'to' => 'Bandung', 'q' => 'bus', 'date' => '2026-12-01',
        ]);
    }

    public function test_it_should_keep_date_when_redirecting_old_catalog_detail(): void
    {
        $response = $this->get('/katalog/3?date=2026-12-01&from=Jakarta');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/catalog/3', ['date' => '2026-12-01']);
    }

    public function test_it_should_drop_array_values_when_redirecting_old_catalog(): void
    {
        $response = $this->get('/katalog?from[]=Jakarta&date[x]=2026-12-01&q=');

        $response->assertStatus(301);
        $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', []);
    }

    /** Regresi: closure factory di routes/web.php rusak (500) setelah `route:cache`. */
    public function test_it_should_redirect_with_whitelisted_query_when_routes_are_cached(): void
    {
        $this->artisan('route:cache')->assertSuccessful();

        try {
            $this->refreshApplication();
            $this->assertTrue($this->app->routesAreCached());

            $response = $this->get('/search?q=a&next=https://evil.test');
            $response->assertStatus(301);
            $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', ['q' => 'a']);

            $response = $this->get('/katalog?from=x&next=https://evil.test');
            $response->assertStatus(301);
            $this->assertRedirectQuery($response->headers->get('Location'), '/catalog', ['from' => 'x']);

            $response = $this->get('/katalog/3?date=2026-11-02&from=x');
            $response->assertStatus(301);
            $this->assertRedirectQuery($response->headers->get('Location'), '/catalog/3', ['date' => '2026-11-02']);
        } finally {
            $this->artisan('route:clear');
        }
    }

    #[DataProvider('invalidTripIds')]
    public function test_it_should_return_not_found_when_legacy_trip_id_is_invalid(string $id): void
    {
        $this->get("/trip/{$id}")->assertNotFound();
        $this->get("/katalog/{$id}")->assertNotFound();
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
