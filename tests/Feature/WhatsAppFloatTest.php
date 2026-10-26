<?php

namespace Tests\Feature;

use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Tombol WhatsApp melayang: tampil hanya bila nomor valid, href wa.me ter-encode. */
class WhatsAppFloatTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_render_wa_link_with_encoded_message(): void
    {
        config(['services.whatsapp.number' => '0812-3456 7890', 'services.whatsapp.message' => 'Halo & tanya "trip"?']);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-whatsapp-float', false)
            ->assertSee('href="https://wa.me/6281234567890?text=Halo%20%26%20tanya%20%22trip%22%3F"', false)
            ->assertSee('target="_blank" rel="noopener noreferrer"', false)
            ->assertSee('aria-label="Chat with us on WhatsApp"', false);
    }

    public function test_it_should_omit_text_param_when_message_empty(): void
    {
        config(['services.whatsapp.number' => '6281234567890', 'services.whatsapp.message' => '']);

        $this->get('/')->assertOk()->assertSee('href="https://wa.me/6281234567890"', false);
    }

    public function test_it_should_truncate_long_message(): void
    {
        config(['services.whatsapp.number' => '6281234567890', 'services.whatsapp.message' => str_repeat('a', 600)]);

        $this->get('/')->assertOk()
            ->assertSee('?text='.str_repeat('a', 500).'"', false)
            ->assertDontSee(str_repeat('a', 501), false);
    }

    public function test_it_should_hide_when_number_is_missing_or_invalid(): void
    {
        foreach ([null, '', '12345', 'javascript:alert(1)'] as $number) {
            config(['services.whatsapp.number' => $number]);

            $this->get('/')->assertOk()->assertDontSee('data-whatsapp-float', false)->assertDontSee('wa.me', false);
        }
    }

    public function test_it_should_render_on_livewire_page_and_translate_label(): void
    {
        config(['services.whatsapp.number' => '081234567890']);

        $this->withSession(['locale' => 'id'])->get('/checkout')
            ->assertOk()
            ->assertSee('href="https://wa.me/6281234567890', false)
            ->assertSee('aria-label="Chat dengan kami di WhatsApp"', false);
    }
}
