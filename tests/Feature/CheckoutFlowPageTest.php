<?php

namespace Tests\Feature;

use App\Support\DummyShop;
use Tests\TestCase;

/**
 * Keranjang → checkout → pembayaran → selesai: isi dirender JS dari localStorage.
 * Server hanya mengirim kerangka halaman, biaya layanan, dan metode bayar.
 */
class CheckoutFlowPageTest extends TestCase
{
    public function test_it_should_render_cart_shell_with_service_fee(): void
    {
        $this->get('/keranjang')
            ->assertOk()
            ->assertViewIs('pages.cart')
            ->assertViewHas('serviceFee', DummyShop::SERVICE_FEE)
            ->assertViewHas('maxQty', DummyShop::MAX_QTY)
            ->assertSee('data-module="cart-page"', false)
            ->assertSee('data-cart-empty', false);
    }

    public function test_it_should_render_checkout_with_all_payment_methods(): void
    {
        $response = $this->get('/checkout');

        $response->assertOk()
            ->assertViewIs('pages.checkout')
            ->assertViewHas('paymentMethods', DummyShop::paymentMethods())
            ->assertSee('data-module="checkout"', false)
            ->assertSee('data-empty-url="'.route('cart').'"', false)
            ->assertSee('data-next-url="'.route('payment').'"', false);

        foreach (['bca_va', 'bni_va', 'mandiri_va', 'qris'] as $methodId) {
            $response->assertSee('value="'.$methodId.'"', false);
        }
    }

    public function test_it_should_render_payment_with_method_json_for_js(): void
    {
        $response = $this->get('/pembayaran');

        $response->assertOk()
            ->assertViewIs('pages.payment-new')
            ->assertSee('data-module="checkout-payment"', false)
            ->assertSee('data-back-url="'.route('checkout').'"', false)
            ->assertSee('data-success-url="'.route('payment.success').'"', false);

        $this->assertSame(1, preg_match('#<script type="application/json" data-payment-methods>(.*?)</script>#s', $response->getContent(), $match));
        $methods = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame(['bca_va', 'bni_va', 'mandiri_va', 'qris'], array_column($methods, 'id'));
        $this->assertSame(['id', 'name', 'type'], array_keys($methods[0]));
    }

    public function test_it_should_render_success_shell_without_server_data(): void
    {
        $this->get('/pembayaran/selesai')
            ->assertOk()
            ->assertViewIs('pages.payment-success')
            ->assertSee('data-module="order-success"', false)
            ->assertSee('data-success-root', false);
    }

    public function test_it_should_ignore_query_string_on_client_rendered_pages(): void
    {
        foreach (['/keranjang', '/checkout', '/pembayaran', '/pembayaran/selesai'] as $path) {
            $this->get("{$path}?total=1&method[]=x&code=<script>")
                ->assertOk()
                ->assertDontSee('<script>alert', false)
                ->assertDontSee('code=<script>', false);
        }
    }

    public function test_it_should_reject_post_on_display_only_pages(): void
    {
        foreach (['/keranjang', '/checkout', '/pembayaran', '/masuk', '/daftar', '/akun/profil'] as $path) {
            $this->post($path)->assertStatus(405);
        }
    }

    public function test_it_should_show_cart_badge_and_guest_link_in_navbar(): void
    {
        $this->get('/katalog')
            ->assertSee('data-module="cart-badge"', false)
            ->assertSee('data-module="account-nav"', false)
            ->assertSee('href="'.route('cart').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('Promo</a>', false);
    }
}
