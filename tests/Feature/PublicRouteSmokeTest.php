<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Smoke test: semua halaman publik tetap render di layout hybrid (@yield + Livewire $slot). */
class PublicRouteSmokeTest extends TestCase
{
    /** @return array<string, array{0: string}> */
    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'cek pesanan' => ['/cek-pesanan'],
            'shop' => ['/shop'],
            'shop show' => ['/shop/cabin-suitcase'],
            'trip' => ['/trip'],
            'kolaborasi' => ['/kolaborasi'],
            'karir' => ['/karir'],
            'katalog' => ['/katalog'],
            'katalog show' => ['/katalog/1'],
            'keranjang' => ['/keranjang'],
            'checkout' => ['/checkout'],
            'pembayaran' => ['/pembayaran'],
            'pembayaran selesai' => ['/pembayaran/selesai'],
            'masuk' => ['/masuk'],
            'daftar' => ['/daftar'],
            'akun' => ['/akun'],
            'akun pesanan' => ['/akun/pesanan'],
            'akun alamat' => ['/akun/alamat'],
            'akun profil' => ['/akun/profil'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_it_should_render_public_page_with_livewire_assets(string $path): void
    {
        foreach (['en', 'id'] as $locale) {
            $this->withSession(['locale' => $locale])->get($path)
                ->assertOk()
                ->assertSee('window.livewireScriptConfig', false)
                ->assertSee('<main id="konten"', false)
                ->assertSeeInOrder(['<title>', ' RouteIndonesia</title>'], false);
        }
    }

    public function test_it_should_cover_every_public_get_route(): void
    {
        $covered = array_map(fn (array $row) => ltrim($row[0], '/'), self::publicPages());

        $missing = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->filter(fn ($route) => $route->getName() !== null && ! str_starts_with($route->getName(), 'livewire.')
                && ! in_array($route->getName(), ['locale.switch', 'storage.local', 'storage.local.upload'], true))
            ->map(fn ($route) => preg_replace(['#\{id\}#', '#\{slug\}#'], ['1', 'cabin-suitcase'], $route->uri()))
            ->map(fn (string $uri) => $uri === '/' ? '' : $uri)
            ->reject(fn (string $uri) => in_array($uri, $covered, true) || ($uri === '' && in_array('', $covered, true)))
            ->values()
            ->all();

        $this->assertSame([], $missing, 'Route GET publik baru belum masuk smoke test.');
    }

    public function test_it_should_keep_js_contract_on_livewire_checkout_pages(): void
    {
        $this->get('/checkout')->assertOk()
            ->assertSee('data-module="checkout"', false)
            ->assertSee('data-empty-url="'.route('cart').'"', false)
            ->assertSee('data-next-url="'.route('payment').'"', false)
            ->assertSee('data-checkout-form wire:ignore', false);

        $this->get('/pembayaran')->assertOk()
            ->assertSee('data-module="checkout-payment"', false)
            ->assertSee('data-back-url="'.route('checkout').'"', false)
            ->assertSee('data-success-url="'.route('payment.success').'"', false)
            ->assertSee('data-payment-ready hidden wire:ignore.self', false)
            ->assertSee('data-proof-panel wire:ignore.self', false)
            ->assertSee('wire:submit="submit"', false);

        $this->get('/pembayaran/selesai')->assertOk()
            ->assertSee('data-module="order-success"', false)
            ->assertSee('data-empty-url="'.route('cart').'" wire:ignore', false);
    }

    public function test_it_should_throttle_livewire_temporary_upload_endpoint(): void
    {
        $route = Route::getRoutes()->getByName('livewire.upload-file');

        $this->assertNotNull($route);
        $this->assertContains('throttle:10,1', $route->gatherMiddleware());
    }
}
