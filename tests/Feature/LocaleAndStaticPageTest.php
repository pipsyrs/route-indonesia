<?php

namespace Tests\Feature;

use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Switch bahasa via session (ADR 0001) dan halaman statis baru (ADR 0003). */
class LocaleAndStaticPageTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_default_to_english_when_session_has_no_locale(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Product catalog')
            ->assertDontSee('Katalog produk');
    }

    public function test_it_should_switch_to_indonesian_and_redirect_back_to_internal_previous_page(): void
    {
        $this->withHeader('referer', route('shop'))
            ->get('/lang/id')
            ->assertRedirect(route('shop'))
            ->assertSessionHas('locale', 'id');

        $this->get('/')
            ->assertOk()
            ->assertSee('<html lang="id">', false)
            ->assertSee('Katalog produk')
            ->assertSee('Kolaborasi');
    }

    public function test_it_should_switch_back_to_english_after_indonesian(): void
    {
        $this->withSession(['locale' => 'id'])->get('/lang/en')->assertSessionHas('locale', 'en');

        $this->get('/')->assertSee('<html lang="en">', false)->assertSee('Collaboration');
    }

    public function test_it_should_return_404_for_unsupported_locale(): void
    {
        $this->get('/lang/fr')->assertNotFound()->assertSessionMissing('locale');
        $this->get('/lang/ID')->assertNotFound();
    }

    public function test_it_should_fall_back_to_english_when_session_locale_is_not_whitelisted(): void
    {
        $this->withSession(['locale' => 'fr'])
            ->get('/')
            ->assertOk()
            ->assertSee('<html lang="en">', false);
    }

    public function test_it_should_redirect_home_when_referer_is_external(): void
    {
        $this->withHeader('referer', 'https://evil.example.com/phish')
            ->get('/lang/id')
            ->assertRedirect(route('home'));
    }

    public function test_it_should_redirect_home_when_referer_only_shares_host_prefix(): void
    {
        $this->withHeader('referer', url('/').'.evil.com/phish')
            ->get('/lang/id')
            ->assertRedirect(route('home'));
    }

    public function test_it_should_redirect_home_when_referer_is_a_lang_route(): void
    {
        $this->withHeader('referer', route('locale.switch', 'en'))
            ->get('/lang/id')
            ->assertRedirect(route('home'));
    }

    public function test_it_should_render_new_static_pages(): void
    {
        $pages = ['/shop' => 'pages.shop', '/trip' => 'pages.trips', '/kolaborasi' => 'pages.collaboration', '/karir' => 'pages.career'];

        foreach ($pages as $uri => $view) {
            $this->get($uri)->assertOk()->assertViewIs($view);
        }

        $this->get('/shop')->assertViewHas('products', fn (array $products) => count($products) > 0);
    }

    public function test_it_should_render_new_static_pages_in_indonesian(): void
    {
        foreach (['/shop', '/trip', '/kolaborasi', '/karir'] as $uri) {
            $this->withSession(['locale' => 'id'])->get($uri)->assertOk()->assertSee('<html lang="id">', false);
        }
    }

    public function test_it_should_show_new_menu_and_hide_catalog_and_check_order_links(): void
    {
        $response = $this->get('/')->assertOk();

        foreach (['shop', 'trip', 'collaboration', 'career'] as $name) {
            $response->assertSee('href="'.route($name).'"', false);
        }

        $response->assertDontSee('href="'.route('order.check').'"', false)
            ->assertDontSee('>Katalog<', false)
            ->assertViewHas('products', fn (array $products) => count($products) === 6);
    }
}
