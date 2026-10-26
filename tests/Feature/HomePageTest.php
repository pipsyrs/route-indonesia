<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_render_routes_and_testimonials_when_visiting_home(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertViewMissing('cities')
            ->assertViewHas('popularRoutes', fn (array $routes) => count($routes) === 4)
            ->assertViewHas('testimonials', fn (array $testimonials) => count($testimonials) === 9)
            ->assertViewHas('defaultDate', $this->tomorrow())
            ->assertSee('Rina Kartika')
            ->assertSee('Papandayan');
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

    public function test_it_should_list_dummy_popular_routes_with_min_price_when_visiting_home(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('popularRoutes', function (array $routes) {
            $bySlug = collect($routes)->keyBy('slug');

            return collect($routes)->every(fn ($route) => array_keys($route) === ['slug', 'name', 'price', 'trips'])
                && $bySlug->keys()->all() === ['papandayan', 'bromo', 'prambanan', 'karimunjawa']
                && $bySlug['papandayan']['price'] === 110000
                && $bySlug['papandayan']['trips'] === 4
                && $bySlug['bromo']['price'] === 90000
                && $bySlug['prambanan']['price'] === 285000
                && $bySlug['karimunjawa']['price'] === 120000;
        });

        foreach (['papandayan', 'bromo', 'prambanan', 'karimunjawa'] as $slug) {
            $response->assertSee('/katalog?route='.$slug.'&amp;date='.$this->tomorrow(), false);
        }
    }

    public function test_it_should_hide_unpublished_testimonial_when_visiting_home(): void
    {
        Testimonial::where('name', 'Rina Kartika')->update(['is_published' => false]);

        $this->get('/')
            ->assertOk()
            ->assertViewHas('testimonials', fn (array $testimonials) => count($testimonials) === 8)
            ->assertDontSee('Rina Kartika');
    }
}
