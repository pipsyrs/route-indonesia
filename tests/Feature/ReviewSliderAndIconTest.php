<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Slider review 3-per-slide di home dan kelengkapan ikon di semua halaman ber-route. */
class ReviewSliderAndIconTest extends TestCase
{
    use SeededDatabase;

    private function addTestimonials(int $count): void
    {
        foreach (range(1, $count) as $i) {
            Testimonial::create(['name' => "Penumpang Uji {$i}", 'city_name' => 'Bogor', 'rating' => 5, 'body' => "Review uji {$i}", 'is_published' => true]);
        }
    }

    public function test_it_should_hide_slider_controls_when_reviews_fit_in_one_slide(): void
    {
        Testimonial::query()->update(['is_published' => false]);
        $this->addTestimonials(3);

        $html = $this->get('/')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'data-module="testimonial-slider"'));

        $this->assertSame(1, preg_match_all('/\sdata-slide(?=[\s>])/', $section));
        $this->assertStringNotContainsString('data-slider-prev', $section);
        $this->assertStringNotContainsString('data-slider-toggle', $section);
        $this->assertStringContainsString('md:grid-cols-3', $section);
    }

    public function test_it_should_chunk_reviews_by_three_and_render_matching_dots(): void
    {
        Testimonial::query()->update(['is_published' => false]);
        $this->addTestimonials(4);

        $html = $this->get('/')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'data-module="testimonial-slider"'));

        $this->assertSame(2, preg_match_all('/data-slider-dot="\d+"/', $section));
        $this->assertStringContainsString('data-slider-prev', $section);
        $this->assertStringContainsString('data-slider-next', $section);
        $this->assertStringContainsString('data-slider-toggle', $section);
        $this->assertSame(0, preg_match('/data-slider-(prev|next|toggle)[^>]*rounded-full|rounded-full[^>]*data-slider-(prev|next|toggle)/', $html));
    }

    public function test_it_should_cap_reviews_at_three_slides(): void
    {
        $this->addTestimonials(10);

        $this->get('/')->assertOk()->assertViewHas('testimonials', fn (array $items) => count($items) === 9);
    }

    public function test_it_should_render_three_slides_of_three_reviews_from_seeded_data(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'data-module="testimonial-slider"'));

        $this->assertSame(3, preg_match_all('/\sdata-slide(?=[\s>])/', $section));
        $this->assertSame(3, preg_match_all('/data-slider-dot="\d+"/', $section));
        $this->assertStringContainsString('md:grid-cols-3', $section);
    }

    public function test_it_should_keep_testimonial_seeder_idempotent(): void
    {
        $before = Testimonial::count();

        $this->seed(\Database\Seeders\TestimonialSeeder::class);
        $this->seed(\Database\Seeders\TestimonialSeeder::class);

        $this->assertSame($before, Testimonial::count());
    }

    public function test_it_should_render_see_all_products_as_pill_with_brand_hover(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('class="btn rounded-full border border-line-strong text-ink transition-colors duration-200 hover:border-brand-600 hover:bg-brand-600 hover:text-white"', false);
    }

    public function test_it_should_not_render_empty_icons_on_any_routed_page(): void
    {
        $date = now()->addDay()->toDateString();
        $uris = ['/', '/shop', '/shop/travel-neck-pillow', '/trip', '/kolaborasi', '/karir', "/katalog?from=Jakarta&to=Bandung&date={$date}",
            '/katalog/1', '/keranjang', '/checkout', '/pembayaran', '/pembayaran/selesai', '/masuk', '/daftar', '/akun', '/akun/pesanan', '/akun/profil', '/cek-pesanan'];

        foreach ($uris as $uri) {
            $html = $this->get($uri)->assertOk()->getContent();

            $this->assertStringNotContainsString('focusable="false"></svg>', $html, "Ikon kosong di {$uri}");
            $this->assertSame(0, preg_match('/<svg[^>]*stroke-width="(?!1\.5")/', $html), "stroke-width bukan 1.5 di {$uri}");
        }
    }
}
