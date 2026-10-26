<?php

namespace Tests\Feature;

use App\Support\DummyShop;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Katalog & detail trip: data dummy statis dari DummyShop, tanpa database. */
class CatalogPageTest extends TestCase
{
    public function test_it_should_list_all_dummy_trips_when_no_filter_is_given(): void
    {
        $this->get('/katalog')
            ->assertOk()
            ->assertViewIs('pages.catalog.index')
            ->assertViewHas('trips', fn (array $trips) => count($trips) === count(DummyShop::trips()))
            ->assertViewHas('searchError', null)
            ->assertViewHas('query', fn (array $query) => $query['from'] === null
                && $query['to'] === null
                && $query['q'] === null
                && $query['date'] === null
                && $query['date_label'] === null)
            ->assertDontSee('Berangkat '.now()->addDay()->locale('id')->translatedFormat('l'), false)
            ->assertSee('name="q"', false)
            ->assertDontSee('name="from"', false);
    }

    #[DataProvider('keywordSearches')]
    public function test_it_should_filter_trips_by_keyword_case_insensitively(string $keyword, array $ids): void
    {
        $this->get('/katalog?q='.urlencode($keyword))
            ->assertOk()
            ->assertViewHas('query', fn (array $query) => $query['q'] === trim($keyword))
            ->assertViewHas('trips', fn (array $trips) => array_column($trips, 'id') === $ids)
            ->assertSee('Hasil pencarian');
    }

    /** @return array<string, array{0: string, 1: list<int>}> */
    public static function keywordSearches(): array
    {
        return [
            'kota' => ['bandung', [1, 2, 3, 4]],
            'nama rute' => ['PAPANDAYAN', [1, 2, 3, 4]],
            'operator' => ['  cipaganti ', [1]],
            'kendaraan' => ['elf', [3, 9]],
        ];
    }

    public function test_it_should_show_empty_state_when_keyword_has_no_match(): void
    {
        $this->get('/katalog?q=zzz')
            ->assertOk()
            ->assertViewHas('trips', [])
            ->assertSee('Belum ada jadwal untuk pencarian ini')
            ->assertSee('Coba kata kunci lain');
    }

    public function test_it_should_escape_keyword_in_title_heading_and_inputs(): void
    {
        $this->get('/katalog?'.http_build_query(['q' => '"><script>alert(1)</script>', 'date' => $this->tomorrow()]))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_it_should_ignore_array_keyword_and_cap_its_length(): void
    {
        $this->get('/katalog?q[]=bandung')
            ->assertOk()
            ->assertViewHas('trips', fn (array $trips) => count($trips) === count(DummyShop::trips()));

        $this->get('/katalog?q='.str_repeat('a', 150))
            ->assertOk()
            ->assertViewHas('query', fn (array $query) => mb_strlen($query['q']) === 100);
    }

    public function test_it_should_filter_named_route_and_return_not_found_for_unknown_slug(): void
    {
        $this->get('/katalog?route=bromo')
            ->assertOk()
            ->assertViewHas('trips', fn (array $trips) => array_column($trips, 'id') === [8, 9]);

        $this->get('/katalog?route=atlantis')->assertNotFound();
    }

    public function test_it_should_pass_selected_date_to_header_and_trip_links(): void
    {
        $date = now()->addDay()->toDateString();

        $this->get("/katalog?q=bandung&date={$date}")
            ->assertOk()
            ->assertSee('Berangkat '.now()->addDay()->locale('id')->translatedFormat('l, j F Y'))
            ->assertSee(route('catalog.show', ['id' => 1, 'date' => $date]), false)
            ->assertSee('<input type="hidden" name="q" value="bandung">', false);
    }

    public function test_it_should_filter_by_origin_and_destination_case_insensitively(): void
    {
        $this->get('/katalog?from=jakarta&to=BANDUNG')
            ->assertOk()
            ->assertViewHas('query', fn (array $query) => $query['from'] === 'Jakarta' && $query['to'] === 'Bandung')
            ->assertViewHas('trips', fn (array $trips) => array_column($trips, 'id') === [1, 2, 3]);
    }

    public function test_it_should_filter_by_origin_only(): void
    {
        $this->get('/katalog?from=Yogyakarta')
            ->assertViewHas('trips', fn (array $trips) => array_column($trips, 'id') === [6, 10]);
    }

    public function test_it_should_show_empty_state_when_route_has_no_trip(): void
    {
        $this->get('/katalog?from=Malang&to=Jakarta')
            ->assertOk()
            ->assertViewHas('trips', [])
            ->assertSee('Belum ada jadwal untuk pencarian ini');
    }

    public function test_it_should_show_error_when_origin_equals_destination(): void
    {
        $this->get('/katalog?from=Bandung&to=bandung')
            ->assertOk()
            ->assertViewHas('trips', [])
            ->assertViewHas('searchError', 'Kota asal dan tujuan tidak boleh sama.')
            ->assertSee('Kota asal dan tujuan tidak boleh sama.');
    }

    public function test_it_should_ignore_unknown_city(): void
    {
        $this->get('/katalog?from=Atlantis&to=Bandung')
            ->assertViewHas('query', fn (array $query) => $query['from'] === null && $query['to'] === 'Bandung')
            ->assertViewHas('trips', fn (array $trips) => array_column($trips, 'id') === [1, 2, 3]);
    }

    public function test_it_should_keep_valid_future_date(): void
    {
        $date = now()->addDays(5)->toDateString();

        $this->get("/katalog?date={$date}")->assertViewHas('query', fn (array $query) => $query['date'] === $date);
    }

    #[DataProvider('invalidDates')]
    public function test_it_should_drop_date_when_it_is_invalid(string $date): void
    {
        $this->get("/katalog?{$date}")->assertOk()->assertViewHas('query', fn (array $query) => $query['date'] === null);
    }

    /** @return array<string, array{0: string}> */
    public static function invalidDates(): array
    {
        return [
            'lampau' => ['date=2000-01-01'],
            'meluap' => ['date=2026-02-31'],
            'bebas' => ['date=next%20monday'],
            'array' => ['date[]=2026-12-01'],
            'kosong' => ['date='],
        ];
    }

    public function test_it_should_render_trip_detail_with_cart_snapshot(): void
    {
        $date = now()->addDays(3)->toDateString();

        $response = $this->get("/katalog/1?date={$date}");

        $response->assertOk()
            ->assertViewIs('pages.catalog.show')
            ->assertViewHas('trip', fn (array $trip) => $trip['id'] === 1)
            ->assertViewHas('date', $date)
            ->assertViewHas('maxQty', 6)
            ->assertSee('data-trip-json', false)
            ->assertSee('Pool Cawang');

        $snapshot = $this->tripJson($response->getContent());
        $this->assertSame(1, $snapshot['tripId']);
        $this->assertSame($date, $snapshot['date']);
        $this->assertSame(150000, $snapshot['price']);
        $this->assertSame(6, $snapshot['maxQty']);
        $this->assertSame([['id' => 11, 'name' => 'Pool Cawang', 'time' => '07:00']], $snapshot['pickups']);
    }

    public function test_it_should_limit_quantity_to_seats_left_when_fewer_than_max(): void
    {
        $this->get('/katalog/9')->assertOk()->assertViewHas('maxQty', 2);
    }

    public function test_it_should_default_detail_date_to_tomorrow_when_date_is_past(): void
    {
        $this->get('/katalog/1?date=2000-01-01')->assertViewHas('date', $this->tomorrow());
    }

    #[DataProvider('missingTripIds')]
    public function test_it_should_return_not_found_when_trip_does_not_exist(string $id): void
    {
        $this->get("/katalog/{$id}")->assertNotFound();
    }

    /** @return array<string, array{0: string}> */
    public static function missingTripIds(): array
    {
        return [
            'tidak ada di dummy' => ['999'],
            'sepuluh digit' => ['9999999999'],
            'nol' => ['0'],
            'nol di depan' => ['01'],
            'di luar int' => ['99999999999999999999999'],
            'huruf' => ['abc'],
        ];
    }

    public function test_it_should_escape_script_closing_tag_in_trip_json(): void
    {
        $content = $this->get('/katalog/1')->getContent();
        $json = $this->rawTripJson($content);

        $this->assertStringNotContainsString('</', $json);
    }

    /** @return array<string, mixed> */
    private function tripJson(string $html): array
    {
        return json_decode($this->rawTripJson($html), true, flags: JSON_THROW_ON_ERROR);
    }

    private function rawTripJson(string $html): string
    {
        $this->assertSame(1, preg_match('#<script type="application/json" data-trip-json>(.*?)</script>#s', $html, $match));

        return $match[1];
    }

    private function tomorrow(): string
    {
        return now()->addDay()->toDateString();
    }
}
