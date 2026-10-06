<?php

namespace Tests\Feature;

use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Banner Girls Trip, product-card di home/shop, dan ikon sosial footer. */
class HomeBannerAndFooterTest extends TestCase
{
    use SeededDatabase;

    private const SOCIAL_ENV = ['SOCIAL_INSTAGRAM_URL', 'SOCIAL_TIKTOK_URL', 'SOCIAL_FACEBOOK_URL', 'COMMUNITY_URL'];

    protected function tearDown(): void
    {
        foreach (self::SOCIAL_ENV as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        parent::tearDown();
    }

    public function test_it_should_render_girls_trip_banner_before_testimonials_with_cta_to_trip(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['id="produk-title"', 'id="girls-trip-title"', 'id="testimoni-title"'], false)
            ->assertSee('href="'.route('trip').'"', false)
            ->assertSee('images.unsplash.com', false);
    }

    public function test_it_should_link_every_product_card_to_its_detail_page_on_home_and_shop(): void
    {
        foreach (['/', '/shop'] as $uri) {
            $response = $this->get($uri)->assertOk();

            foreach (['travel-neck-pillow', 'waterproof-daypack', 'universal-travel-adapter', 'packing-cube-set', 'cabin-suitcase', 'leather-weekender-bag'] as $slug) {
                $response->assertSee('href="'.route('shop.show', $slug).'"', false);
            }
        }
    }

    public function test_it_should_render_new_products_detail_in_both_locales(): void
    {
        foreach (['cabin-suitcase', 'leather-weekender-bag'] as $slug) {
            $this->get("/shop/{$slug}")->assertOk()->assertViewHas('related', fn (array $related) => count($related) === 3);
            $this->withSession(['locale' => 'id'])->get("/shop/{$slug}")->assertOk()
                ->assertDontSee('Cabin Suitcase')->assertDontSee('Leather Weekender Bag')->assertDontSee('Luggage');
        }
    }

    public function test_it_should_not_submit_product_purchase_to_server(): void
    {
        $html = $this->get('/shop/travel-neck-pillow')->assertOk()->getContent();

        $this->assertSame(0, preg_match('/<form[^>]*\smethod="post"/i', $html));
        $this->assertSame(0, preg_match('/<form[^>]*\saction=/i', $html));
        $this->assertSame(1, preg_match('/<form[^>]*id="product-buy-form"[^>]*method="dialog"|<form[^>]*method="dialog"[^>]*id="product-buy-form"/', $html));
    }

    public function test_it_should_render_product_detail_in_indonesian(): void
    {
        $this->withSession(['locale' => 'id'])
            ->get('/shop/travel-neck-pillow')
            ->assertOk()
            ->assertSee('<html lang="id">', false)
            ->assertDontSee('Add to cart')
            ->assertDontSee('Back to Shop')
            ->assertDontSee('You may also like');
    }

    public function test_it_should_hide_bank_list_and_social_icons_when_env_is_empty(): void
    {
        config(['services.social' => ['instagram' => null, 'tiktok' => null, 'facebook' => null]]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Mandiri')
            ->assertDontSee('QRIS</li>', false)
            ->assertDontSee('target="_blank"', false);
    }

    public function test_it_should_render_social_icons_with_safe_new_tab_attributes_when_configured(): void
    {
        config(['services.social' => [
            'instagram' => 'https://instagram.com/routeid',
            'tiktok' => null,
            'facebook' => 'https://facebook.com/routeid',
        ]]);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://instagram.com/routeid" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('href="https://facebook.com/routeid" target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('aria-label="Instagram (opens in a new tab)"', false)
            ->assertDontSee('TikTok (opens', false);
    }

    public function test_it_should_reject_non_https_social_urls_from_env(): void
    {
        $values = [
            'SOCIAL_INSTAGRAM_URL' => 'javascript:alert(1)',
            'SOCIAL_TIKTOK_URL' => 'http://tiktok.com/@routeid',
            'SOCIAL_FACEBOOK_URL' => 'https://facebook.com/routeid',
        ];
        foreach ($values as $key => $value) {
            $_ENV[$key] = $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }

        $social = (require config_path('services.php'))['social'];

        $this->assertNull($social['instagram']);
        $this->assertNull($social['tiktok']);
        $this->assertSame('https://facebook.com/routeid', $social['facebook']);
    }

    public function test_it_should_place_community_section_between_girls_trip_and_testimonials(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['id="girls-trip-title"', 'id="community-title"', 'id="testimoni-title"'], false)
            ->assertSee('alt="Behind The Route"', false)
            ->assertSee(asset('images/btr.png'), false);

        $this->assertFileExists(public_path('images/btr.png'));
    }

    public function test_it_should_link_community_button_to_collaboration_when_url_is_empty(): void
    {
        config(['services.community_url' => null]);

        $html = $this->get('/')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'id="community-title"'), 2000);

        $this->assertStringContainsString('href="'.route('collaboration').'"', $section);
        $this->assertStringNotContainsString('target="_blank"', $section);
    }

    public function test_it_should_open_community_url_in_new_tab_when_configured(): void
    {
        config(['services.community_url' => 'https://example.com/komunitas']);

        $html = $this->get('/')->assertOk()->getContent();
        $section = substr($html, strpos($html, 'id="community-title"'), 2000);

        $this->assertMatchesRegularExpression('#href="https://example.com/komunitas"\s+target="_blank" rel="noopener noreferrer"#', $section);
    }

    public function test_it_should_render_community_texts_in_indonesian(): void
    {
        $this->withSession(['locale' => 'id'])->get('/')
            ->assertOk()
            ->assertDontSee('Join the community')
            ->assertDontSee('a community of travelers');
    }

    public function test_safe_url_should_only_accept_https_with_host(): void
    {
        $cases = [
            'https://example.com' => 'https://example.com',
            '  https://example.com/x  ' => 'https://example.com/x',
            'HTTPS://example.com' => 'HTTPS://example.com',
            'https://' => null,
            'https:///path' => null,
            'https:example.com' => null,
            '//example.com' => null,
            'http://example.com' => null,
            'javascript:alert(1)' => null,
            'data:text/html,x' => null,
            ' javascript:alert(1)//https://x' => null,
        ];

        foreach ($cases as $input => $expected) {
            $this->assertSame($expected, \App\Support\SafeUrl::https($input), "input: [{$input}]");
        }

        $this->assertNull(\App\Support\SafeUrl::https(null));
        $this->assertNull(\App\Support\SafeUrl::https(['https://example.com']));
    }

    public function test_it_should_filter_community_url_from_env(): void
    {
        foreach (['javascript:alert(1)' => null, 'http://example.com/komunitas' => null, '' => null, 'https://example.com/komunitas' => 'https://example.com/komunitas'] as $value => $expected) {
            $_ENV['COMMUNITY_URL'] = $_SERVER['COMMUNITY_URL'] = $value;
            putenv("COMMUNITY_URL={$value}");

            $this->assertSame($expected, (require config_path('services.php'))['community_url'], $value);
        }
    }
}
