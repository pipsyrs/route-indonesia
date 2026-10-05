<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\TravelRoute;
use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_render_cities_routes_and_testimonials_from_database_when_visiting_home(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewHas('cities', fn (array $cities) => count($cities) === 12 && $cities[0] === 'Jakarta')
            ->assertViewHas('popularRoutes', fn (array $routes) => count($routes) === 6)
            ->assertViewHas('testimonials', fn (array $testimonials) => count($testimonials) === 3)
            ->assertViewHas('defaultDate', $this->tomorrow())
            ->assertSee('Rina Kartika')
            ->assertSee('Tasikmalaya');
    }

    public function test_it_should_render_hero_slider_and_search_form_without_promo_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-module="hero-slider"', false)
            ->assertSee('aria-roledescription="carousel"', false)
            ->assertSee('action="'.route('catalog.index').'"', false)
            ->assertDontSee('id="promo"', false)
            ->assertDontSee('JALANHEMAT');
    }

    public function test_it_should_use_light_color_scheme_only(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertStringNotContainsString('prefers-color-scheme: dark', $css);
        $this->assertStringNotContainsString('light dark', $css);
    }

    public function test_it_should_render_check_order_page(): void
    {
        $this->get('/cek-pesanan')->assertOk()->assertViewIs('pages.cek-pesanan');
    }

    public function test_it_should_count_tomorrows_schedules_per_popular_route_when_visiting_home(): void
    {
        $this->get('/')->assertViewHas('popularRoutes', function (array $routes) {
            $byPair = collect($routes)->keyBy(fn ($route) => "{$route['from']}-{$route['to']}");

            return $byPair['Jakarta-Bandung']['trips'] === 8
                && $byPair['Jakarta-Bandung']['price'] === 110000
                && $byPair['Jakarta-Bandung']['duration'] === '± 3 jam'
                && $byPair['Yogyakarta-Semarang']['duration'] === '± 3,5 jam'
                && $byPair['Solo-Yogyakarta']['duration'] === '± 1,5 jam'
                && $byPair['Surabaya-Malang']['trips'] === 3;
        });
    }

    public function test_it_should_hide_unpublished_testimonial_when_visiting_home(): void
    {
        Testimonial::where('name', 'Rina Kartika')->update(['is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('testimonials', fn (array $testimonials) => count($testimonials) === 2)
            ->assertDontSee('Rina Kartika');
    }

    public function test_it_should_skip_route_when_its_city_is_soft_deleted(): void
    {
        $route = $this->routeBetween('Solo', 'Yogyakarta');
        $route->origin()->first()->delete();

        $this->get('/')
            ->assertOk()
            ->assertViewHas('popularRoutes', fn (array $routes) => count($routes) === 5)
            ->assertViewHas('cities', fn (array $cities) => ! in_array('Solo', $cities, true));

        $this->assertNotNull(TravelRoute::find($route->id));
    }
}
