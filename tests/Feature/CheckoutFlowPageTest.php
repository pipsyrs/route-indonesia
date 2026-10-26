<?php

namespace Tests\Feature;

use App\Livewire\Pages\Checkout;
use App\Livewire\Pages\Payment;
use App\Livewire\Pages\PaymentSuccess;
use App\Support\DummyShop;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
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
            ->assertViewHas('provinces', DummyShop::provinces())
            ->assertViewMissing('paymentMethods');
    }

    public function test_it_should_render_checkout_address_and_referral_fields_without_passenger_groups(): void
    {
        $html = $this->get('/checkout')
            ->assertOk()
            ->assertSee('data-field="address_street" data-rule="address"', false)
            ->assertSee('data-field="address_province" data-rule="option"', false)
            ->assertSee('data-field="address_city" data-rule="place"', false)
            ->assertSee('data-field="address_district" data-rule="place"', false)
            ->assertSee('data-field="address_postal" data-rule="postal"', false)
            ->assertSee('data-field="referral_source" data-rule="option"', false)
            ->assertSee('data-field="instagram" data-rule="instagram"', false)
            ->assertSee('data-field="referral_code" data-rule="referral"', false)
            ->assertSee('<option value="Jawa Barat">Jawa Barat</option>', false)
            ->assertSee('data-seat-error hidden', false)
            ->assertSee('data-seat-error-message', false)
            ->assertSee('data-checkout-submit', false)
            ->assertSee('name="method" value="bank_transfer"', false)
            ->assertDontSee('data-passenger-groups', false)
            ->assertDontSee('data-passenger-group-template', false)
            ->assertDontSee('data-same-as-contact', false)
            ->getContent();

        foreach (['instagram', 'tiktok', 'friend', 'google', 'other'] as $source) {
            $this->assertStringContainsString("<option value=\"{$source}\">", $html);
        }
        // checkout.js memanggil Livewire.find(root.closest('[wire:id]')): root modul = root komponen.
        $this->assertMatchesRegularExpression('/<div[^>]*wire:id="[^"]+"[^>]*data-module="checkout"/', $html);
    }

    public function test_it_should_render_hidden_saved_address_picker_hooks_on_checkout(): void
    {
        $this->get('/checkout')
            ->assertOk()
            ->assertSee('data-saved-addresses hidden', false)
            ->assertSee('data-saved-address-list', false)
            ->assertSee('name="saved-address" value="new"', false)
            ->assertSee('Pakai alamat lain')
            ->assertSee('data-saved-address-template', false)
            ->assertSee('data-address-edit aria-controls="address-fields" aria-expanded="false" hidden', false)
            ->assertSee('aria-live="polite" data-address-hint hidden', false)
            ->assertSee('Lengkapi provinsi dan kecamatan.')
            ->assertSee('data-address-fields', false)
            ->assertSee('data-save-address-option hidden', false)
            ->assertSee('data-save-address>', false)
            ->assertSee('Simpan ke buku alamat');
    }

    public function test_it_should_keep_checked_state_classes_inside_new_address_card_class_attribute(): void
    {
        $html = $this->get('/checkout')->assertOk()->getContent();

        // Kutip nyasar memutus atribut class sehingga peer-checked:* hilang.
        $this->assertMatchesRegularExpression(
            '#<input type="radio" name="saved-address" value="new" class="peer sr-only">\s*<span class="[^"]*\bborder-dashed\b[^"]*\bpeer-checked:border-brand-600\b[^"]*peer-focus-visible:outline-brand-500">#',
            $html,
        );
    }

    public function test_it_should_render_cart_seat_modal_and_locked_checkout_hooks(): void
    {
        $this->get('/keranjang')
            ->assertOk()
            ->assertSee('id="seat-modal"', false)
            ->assertSee('data-seat-modal', false)
            ->assertSee('data-seat-grid', false)
            ->assertSee('data-seat-save disabled', false)
            ->assertSee('data-seat-driver-template', false)
            ->assertSee('data-seat-aisle-template', false)
            ->assertSee('data-seat-cell-template', false)
            ->assertSee('data-seat-block', false)
            ->assertSee('data-modal-open="seat-modal" data-seat-open', false)
            ->assertSee('data-field="seat-action"', false)
            ->assertSee('data-field="seat-count"', false)
            ->assertSee('data-qty-control', false)
            ->assertSee('data-checkout-link', false)
            ->assertSee('data-checkout-hint hidden', false);
    }

    public function test_it_should_render_success_item_template_with_seats_instead_of_passenger_names(): void
    {
        $this->get('/pembayaran/selesai')
            ->assertOk()
            ->assertSee('data-seat-line', false)
            ->assertSee('data-field="seats"', false)
            ->assertDontSee('data-field="names"', false)
            ->assertDontSee('Penumpang:', false);
    }

    public function test_it_should_accept_valid_seat_selection(): void
    {
        $this->assertSame(['valid' => true, 'errors' => []], $this->validateSeats([$this->seatItem(5, 2)]));
        $this->assertSame(['valid' => true, 'errors' => []], $this->validateSeats([]));
    }

    public function test_it_should_reject_occupied_seat_with_message(): void
    {
        $item = $this->seatItem(5, 1);
        $item['seats'] = [DummyShop::occupiedSeats(5, $item['date'])[0]];

        $result = $this->validateSeats([$item]);

        $this->assertFalse($result['valid']);
        $this->assertSame("Kursi {$item['seats'][0]} sudah terisi. Pilih ulang kursi.", $result['errors'][$item['key']]);
    }

    /** @param  callable(array<string, mixed>): array<string, mixed>  $mutate */
    #[DataProvider('invalidSeatItems')]
    public function test_it_should_reject_invalid_seat_item(callable $mutate): void
    {
        $item = $mutate($this->seatItem(5, 2));

        $result = $this->validateSeats([$item]);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey($item['key'], $result['errors']);
    }

    /** @return array<string, array{0: callable}> */
    public static function invalidSeatItems(): array
    {
        return [
            'kursi duplikat' => [fn (array $item) => ['seats' => [$item['seats'][0], $item['seats'][0]]] + $item],
            'jumlah kursi beda qty' => [fn (array $item) => ['seats' => [$item['seats'][0]]] + $item],
            'kursi di luar denah' => [fn (array $item) => ['seats' => [$item['seats'][0], '99']] + $item],
            'sel sopir' => [fn (array $item) => ['seats' => [$item['seats'][0], 'D']] + $item],
            'kursi bukan string' => [fn (array $item) => ['seats' => [1, 2]] + $item],
            'trip tidak ada' => [fn (array $item) => ['tripId' => 999, 'key' => "999|{$item['date']}"] + $item],
            'tripId string' => [fn (array $item) => ['tripId' => '5'] + $item],
            'tanggal lampau' => [fn (array $item) => ['date' => '2000-01-01', 'key' => '5|2000-01-01'] + $item],
            'tanggal meluap' => [fn (array $item) => ['date' => '2099-02-31', 'key' => '5|2099-02-31'] + $item],
            'key tidak cocok' => [fn (array $item) => ['key' => '1|'.$item['date']] + $item],
            'qty melebihi maks' => [fn (array $item) => ['qty' => 7] + $item],
            'qty nol' => [fn (array $item) => ['qty' => 0, 'seats' => []] + $item],
            'seats bukan array' => [fn (array $item) => ['seats' => '1,2'] + $item],
        ];
    }

    #[DataProvider('malformedSeatPayloads')]
    public function test_it_should_reject_malformed_seat_payload_without_server_error(mixed $payload): void
    {
        $result = $this->validateSeats($payload);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /** @return array<string, array{0: mixed}> */
    public static function malformedSeatPayloads(): array
    {
        return [
            'string' => ['abc'],
            'null' => [null],
            'objek bukan list' => [['a' => ['key' => '5|2099-01-01']]],
            'item bukan array' => [['x']],
            'tanpa key' => [[['tripId' => 5]]],
            'key terlalu panjang' => [[['key' => str_repeat('a', 41)]]],
            'lebih dari 20 item' => [array_fill(0, 21, ['key' => '5|2099-01-01'])],
        ];
    }

    public function test_it_should_not_error_when_validate_seats_is_called_without_argument(): void
    {
        $result = Livewire::test(Checkout::class)->call('validateSeats')->effects['returns'][0];

        $this->assertFalse($result['valid']);
    }

    /** @return array<string, mixed> item trip dengan kursi kosong pertama sebanyak qty */
    private function seatItem(int $tripId, int $qty): array
    {
        $date = now()->addDays(3)->toDateString();
        $trip = DummyShop::findTrip($tripId);
        $free = array_values(array_diff(DummyShop::seatCodes(DummyShop::seatLayout($trip['vehicle'])), DummyShop::occupiedSeats($tripId, $date)));

        return ['key' => "{$tripId}|{$date}", 'tripId' => $tripId, 'date' => $date, 'qty' => $qty, 'seats' => array_slice($free, 0, $qty)];
    }

    /** @return array{valid: bool, errors: array<string, string>} */
    private function validateSeats(mixed $items): array
    {
        return Livewire::test(Checkout::class)->call('validateSeats', $items)->effects['returns'][0];
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
