<?php

namespace Tests\Feature;

use Tests\TestCase;

/** UI dummy: judul rute di detail trip, chip tipe trip, dan tab cek pesanan. */
class TripDetailAndOrderCheckUiTest extends TestCase
{
    public function test_it_should_title_trip_detail_with_operator_and_route_name_subtitle(): void
    {
        $this->get('/catalog/1')
            ->assertOk()
            ->assertSeeInOrder(['<h1 class="text-3xl font-extrabold tracking-tight text-ink md:text-4xl">Cipaganti</h1>', 'Papandayan'], false)
            ->assertDontSee('Hiace Premio</span>', false)
            ->assertSee('Deskripsi')
            ->assertSee('Perjalanan bersama Cipaganti. Kabin dibersihkan')
            ->assertDontSee('Fasilitas')
            ->assertSee('Titik kumpul di Pool Cawang, Jakarta.')
            ->assertDontSee('Pool Cawang (07:00)');
    }

    public function test_it_should_show_rest_area_only_on_long_trips(): void
    {
        $this->get('/catalog/1')->assertOk()->assertDontSee('Rest area');
        $this->get('/catalog/10')->assertOk()->assertSee('Rest area');
    }

    public function test_it_should_render_trip_type_chip_on_catalog_cards(): void
    {
        $this->get('/catalog')
            ->assertOk()
            ->assertSee('Private')
            ->assertSee('Open Trip');
    }

    public function test_it_should_render_check_order_tabs_with_one_visible_panel(): void
    {
        $html = $this->get('/check-order')
            ->assertOk()
            ->assertSee('role="tablist"', false)
            ->assertSee('data-demo-code="RID-DEMO26"', false)
            ->assertSee('data-demo-code="ORD-DEMO2026"', false)
            ->assertSee('data-rule="booking-code"', false)
            ->assertSee('data-rule="order-code"', false)
            ->getContent();

        $this->assertSame(2, substr_count($html, 'role="tab"'));
        $this->assertSame(1, substr_count($html, 'aria-selected="true"'));
        $this->assertSame(1, preg_match('/id="cek-panel-booking" role="tabpanel" aria-labelledby="cek-tab-booking"\s*>/', $html));
        $this->assertSame(1, preg_match('/id="cek-panel-order" role="tabpanel" aria-labelledby="cek-tab-order"\s+hidden/', $html));
        $this->assertSame(0, preg_match('/<form[^>]*data-order-check-form[^>]*\s(method|action)=/i', $html));
    }
}
