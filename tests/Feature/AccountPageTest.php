<?php

namespace Tests\Feature;

use App\Support\DummyShop;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Halaman akun mode demo: tanpa auth, data dummy, form tidak dikirim ke server. */
class AccountPageTest extends TestCase
{
    /** @return array<string, array{0: string, 1: string}> */
    public static function accountPages(): array
    {
        return [
            'masuk' => ['/masuk', 'pages.account.login'],
            'daftar' => ['/daftar', 'pages.account.register'],
            'dashboard' => ['/akun', 'pages.account.dashboard'],
            'pesanan' => ['/akun/pesanan', 'pages.account.orders'],
            'profil' => ['/akun/profil', 'pages.account.profile'],
        ];
    }

    #[DataProvider('accountPages')]
    public function test_it_should_render_account_page_without_login(string $path, string $view): void
    {
        $this->assertGuest();

        $this->get($path)
            ->assertOk()
            ->assertViewIs($view)
            ->assertSee('data-module="account-forms"', false);
    }

    #[DataProvider('accountPages')]
    public function test_it_should_not_post_account_forms_to_server(string $path): void
    {
        $content = $this->get($path)->getContent();

        $this->assertDoesNotMatchRegularExpression('/<form[^>]*method="post"/i', $content);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*type="password"[^>]*name=/i', $content, 'Field sandi tanpa name: tidak ikut terkirim bila JS gagal.');
    }

    public function test_it_should_redirect_login_and_register_to_dashboard_via_js(): void
    {
        $dashboard = 'data-redirect="'.route('account.dashboard').'"';

        $this->get('/masuk')->assertSee('data-form="login"', false)->assertSee($dashboard, false);
        $this->get('/daftar')->assertSee('data-form="register"', false)->assertSee($dashboard, false);
    }

    public function test_it_should_show_three_latest_orders_on_dashboard(): void
    {
        $latest = array_slice(DummyShop::orders(), 0, 3);

        $this->get('/akun')
            ->assertViewHas('user', DummyShop::user())
            ->assertViewHas('orders', $latest)
            ->assertSee($latest[0]['code'])
            ->assertDontSee(DummyShop::orders()[3]['code']);
    }

    public function test_it_should_list_all_orders_newest_first_with_status_labels(): void
    {
        $orders = DummyShop::orders();
        $dates = array_column($orders, 'date');
        $sorted = $dates;
        rsort($sorted);

        $this->assertSame($sorted, $dates);

        $response = $this->get('/akun/pesanan')->assertViewHas('orders', $orders);
        foreach ($orders as $order) {
            $response->assertSee($order['code']);
        }
    }

    public function test_it_should_compute_order_total_from_trip_price_and_service_fee(): void
    {
        foreach (DummyShop::orders() as $order) {
            $trip = DummyShop::findTrip($order['trip_id']);
            $this->assertSame($trip['price'] * $order['qty'] + DummyShop::SERVICE_FEE, $order['total']);
        }
    }

    public function test_it_should_prefill_profile_with_dummy_user(): void
    {
        $user = DummyShop::user();

        $this->get('/akun/profil')
            ->assertSee('value="'.e($user['email']).'"', false)
            ->assertSee('data-form="profile"', false)
            ->assertSee('data-form="password"', false);
    }

    public function test_it_should_offer_logout_that_redirects_home(): void
    {
        $this->get('/akun')
            ->assertSee('data-action="logout"', false)
            ->assertSee('data-redirect="'.route('home').'"', false);
    }
}
