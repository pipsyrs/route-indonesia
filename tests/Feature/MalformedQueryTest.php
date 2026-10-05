<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Query string tidak valid (array, tipe salah, nilai ekstrem) tidak boleh menghasilkan 500. */
class MalformedQueryTest extends TestCase
{
    use SeededDatabase;

    /** @return array<string, array{0: string}> */
    public static function malformedQueries(): array
    {
        $long = str_repeat('A', 5000);

        return [
            'from/to array' => ['from[]=Jakarta&to[a]=Bandung'],
            'date array' => ['date[]=2026-10-04'],
            'date overflow' => ['date=2026-02-31'],
            'date bebas' => ['date=next%20monday'],
            'date jauh' => ['date=9999-12-31'],
            'date lampau' => ['date=2000-01-01'],
            'passengers array' => ['passengers[]=2'],
            'passengers negatif' => ['passengers=-5'],
            'passengers raksasa' => ['passengers=99999999999999999999'],
            'notice array' => ['notice[]=penuh'],
            'trip array' => ['trip[]=1&seats[]=3'],
            'seats string' => ['trip=1&seats=3'],
            'seats bersarang' => ['trip=1&seats[][]=3'],
            'seats key asosiatif' => ['trip=1&seats[x]=3'],
            'pickup/dropoff array' => ['trip=1&seats[]=3&pickup[]=1&dropoff[a]=2'],
            'method array' => ['trip=1&seats[]=3&method[]=qris'],
            'string sangat panjang' => ["from={$long}&to={$long}&notice={$long}&method={$long}&trip={$long}"],
            'sql injection' => ["from=Jakarta'%20OR%201=1--&trip=1%20OR%201=1&seats[]=3"],
            'null byte' => ['from=Jak%00arta&trip=1%00&seats[]=3%00'],
        ];
    }

    #[DataProvider('malformedQueries')]
    public function test_it_should_never_return_server_error_when_query_is_malformed(string $query): void
    {
        foreach (['/', '/search', '/katalog', '/katalog/1', '/keranjang', '/checkout', '/pembayaran', '/pembayaran/selesai', '/masuk', '/akun/pesanan', '/cek-pesanan'] as $path) {
            $status = $this->get("{$path}?{$query}")->getStatusCode();

            $this->assertLessThan(500, $status, "{$path}?".mb_substr($query, 0, 80)." → {$status}");
        }
    }

    public function test_it_should_fall_back_to_all_cities_and_tomorrow_when_catalog_input_is_invalid(): void
    {
        $this->get('/katalog?from[]=Surabaya&to=Atlantis&date=2026-02-31')
            ->assertOk()
            ->assertViewHas('query', fn ($query) => $query['from'] === null
                && $query['to'] === null
                && $query['date'] === $this->tomorrow());
    }

    public function test_it_should_raise_past_date_to_tomorrow_when_browsing_catalog(): void
    {
        $this->get('/katalog?date=2000-01-01')
            ->assertOk()
            ->assertViewHas('query', fn ($query) => $query['date'] === $this->tomorrow());
    }
}
