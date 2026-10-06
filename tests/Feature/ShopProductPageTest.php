<?php

namespace Tests\Feature;

use Tests\Feature\Concerns\SeededDatabase;
use Tests\TestCase;

/** Detail produk dummy di /shop/{slug}. */
class ShopProductPageTest extends TestCase
{
    use SeededDatabase;

    public function test_it_should_render_product_with_three_related_when_slug_exists(): void
    {
        $this->get('/shop/waterproof-daypack')
            ->assertOk()
            ->assertViewIs('pages.shop-show')
            ->assertViewHas('product', fn (array $product) => $product['slug'] === 'waterproof-daypack')
            ->assertViewHas('related', fn (array $related) => count($related) === 3
                && ! in_array('waterproof-daypack', array_column($related, 'slug'), true));
    }

    public function test_it_should_return_404_when_slug_does_not_exist(): void
    {
        $this->get('/shop/not-a-product')->assertNotFound();
    }

    public function test_it_should_return_404_when_slug_has_invalid_characters(): void
    {
        $this->get('/shop/Waterproof_Daypack')->assertNotFound();
    }

    public function test_it_should_give_every_product_a_unique_slug(): void
    {
        $this->get('/shop')->assertViewHas('products', function (array $products) {
            $slugs = array_column($products, 'slug');

            return count($slugs) === count($products) && count(array_unique($slugs)) === count($slugs);
        });
    }
}
