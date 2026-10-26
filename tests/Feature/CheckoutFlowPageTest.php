<?php

namespace Tests\Feature;

use App\Livewire\Pages\Checkout;
use App\Livewire\Pages\Payment;
use App\Livewire\Pages\PaymentSuccess;
use App\Support\DummyShop;
use Livewire\Livewire;
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

    public function test_it_should_render_checkout_as_livewire_page_with_payment_methods(): void
    {
        $this->get('/checkout')
            ->assertOk()
            ->assertSeeLivewire(Checkout::class)
            ->assertSee('<title>Checkout | ', false);

        Livewire::test(Checkout::class)
            ->assertViewIs('livewire.pages.checkout')
            ->assertViewHas('serviceFee', DummyShop::SERVICE_FEE)
            ->assertViewMissing('paymentMethods');
    }

    public function test_it_should_render_payment_as_livewire_page_with_bank_accounts(): void
    {
        $this->get('/pembayaran')
            ->assertOk()
            ->assertSeeLivewire(Payment::class)
            ->assertSee('<title>Pembayaran | ', false);

        Livewire::test(Payment::class)
            ->assertViewIs('livewire.pages.payment')
            ->assertViewMissing('paymentMethods');
    }

    public function test_it_should_render_success_as_livewire_page(): void
    {
        $this->get('/pembayaran/selesai')
            ->assertOk()
            ->assertSeeLivewire(PaymentSuccess::class)
            ->assertSee('<title>Bukti pembayaran terkirim | ', false);

        Livewire::test(PaymentSuccess::class)->assertViewIs('livewire.pages.payment-success');
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
