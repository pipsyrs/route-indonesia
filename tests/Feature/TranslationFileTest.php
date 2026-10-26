<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** lang/id.json valid, tanpa key ganda, dan mencakup string viewer 3D. */
class TranslationFileTest extends TestCase
{
    public function test_it_should_be_valid_json_without_duplicate_keys(): void
    {
        $raw = file_get_contents(lang_path('id.json'));
        $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);

        preg_match_all('/^\s*"((?:[^"\\\\]|\\\\.)*)"\s*:/m', $raw, $matches);
        $duplicates = array_keys(array_filter(array_count_values($matches[1]), fn (int $count) => $count > 1));

        $this->assertSame([], $duplicates);
        $this->assertCount(count($matches[1]), $decoded);
    }

    /** @return array<string, array{0: string}> */
    public static function translatedViews(): array
    {
        return [
            'product viewer modal' => ['components/product-viewer-modal.blade.php'],
            'shop show' => ['pages/shop-show.blade.php'],
        ];
    }

    #[DataProvider('translatedViews')]
    public function test_it_should_translate_every_string_used_in_view(string $view): void
    {
        $source = file_get_contents(resource_path('views/'.$view));
        preg_match_all("/__\\('((?:[^'\\\\]|\\\\.)*)'/", $source, $matches);
        $translations = json_decode(file_get_contents(lang_path('id.json')), true, flags: JSON_THROW_ON_ERROR);

        $missing = array_values(array_diff(array_unique($matches[1]), array_keys($translations)));

        $this->assertSame([], $missing);
    }

    public function test_it_should_render_3d_viewer_in_indonesian(): void
    {
        $this->withSession(['locale' => 'id'])->get('/shop/cabin-suitcase')
            ->assertOk()
            ->assertSee('Coba Virtual')
            ->assertSee('Pilih varian')
            ->assertSee('Hijau Zaitun')
            ->assertDontSee('Try Virtually');
    }
}
