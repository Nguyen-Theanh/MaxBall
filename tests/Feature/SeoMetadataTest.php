<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_layout_contains_dynamic_basic_seo_metadata(): void
    {
        $response = $this->get(route('client.home'))
            ->assertOk()
            ->assertSee('<title>MaxBall - Đỉnh Cao Phong Cách Thể Thao</title>', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('<link rel="canonical" href="'.route('client.home').'">', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta property="og:description"', false)
            ->assertSee('<meta property="og:image"', false)
            ->assertSee('<meta property="og:url" content="'.route('client.home').'">', false)
            ->assertSee('<meta property="og:type" content="website">', false);

        $this->assertNotSame('', $this->metaContent($response->getContent(), 'description', 'name'));
    }

    public function test_product_page_uses_current_product_data_for_metadata_and_json_ld(): void
    {
        $category = $this->category();
        $description = '<p>Áo thi đấu <strong>thoáng khí</strong> dành cho tập luyện và thi đấu. '.str_repeat('Chất liệu nhẹ, co giãn tốt. ', 8).'</p>';
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm SEO động',
            'slug' => 'san-pham-seo-dong',
            'description' => $description,
            'thumbnail' => 'https://example.com/images/seo-product.jpg',
            'status' => true,
            'base_price' => 250000,
            'discount_price' => 199000,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Đỏ - M',
            'sku' => 'SEO-DONG-M',
            'base_price' => 250000,
            'discount_price' => 199000,
            'stock' => 7,
            'reserved_stock' => 2,
        ]);

        $response = $this->get(route('client.products.show', [
            'slug' => $product->slug,
            'reviews_page' => 2,
        ]))->assertOk();
        $content = $response->getContent();
        $canonical = route('client.products.show', $product->slug);
        $metaDescription = $this->metaContent($content, 'description', 'name');

        $this->assertStringContainsString('<title>Sản phẩm SEO động | MaxBall</title>', $content);
        $this->assertStringContainsString('Áo thi đấu thoáng khí', $metaDescription);
        $this->assertStringNotContainsString('<strong>', $metaDescription);
        $this->assertLessThanOrEqual(160, mb_strlen($metaDescription));
        $this->assertSame($canonical, $this->canonicalUrl($content));
        $this->assertSame('Sản phẩm SEO động | MaxBall', $this->metaContent($content, 'og:title'));
        $this->assertSame('https://example.com/images/seo-product.jpg', $this->metaContent($content, 'og:image'));
        $this->assertSame($canonical, $this->metaContent($content, 'og:url'));
        $this->assertSame('product', $this->metaContent($content, 'og:type'));

        $schema = $this->productSchema($content);
        $this->assertSame('Product', $schema['@type']);
        $this->assertSame('Sản phẩm SEO động', $schema['name']);
        $this->assertSame('SEO-DONG-M', $schema['sku']);
        $this->assertSame(['https://example.com/images/seo-product.jpg'], $schema['image']);
        $this->assertSame('199000', $schema['offers']['price']);
        $this->assertSame('VND', $schema['offers']['priceCurrency']);
        $this->assertSame('https://schema.org/InStock', $schema['offers']['availability']);
        $this->assertArrayNotHasKey('review', $schema);
        $this->assertArrayNotHasKey('aggregateRating', $schema);

        $xpath = $this->xpath($content);
        $productImage = $xpath->query('//img[@src="https://example.com/images/seo-product.jpg"]')->item(0);
        $this->assertNotNull($productImage);
        $this->assertSame('Sản phẩm SEO động', $productImage->getAttribute('alt'));
    }

    public function test_product_page_uses_safe_fallbacks_when_optional_data_is_missing(): void
    {
        $product = Product::create([
            'category_id' => $this->category()->id,
            'name' => 'Sản phẩm chưa đủ nội dung',
            'slug' => 'san-pham-chua-du-noi-dung',
            'description' => null,
            'thumbnail' => null,
            'status' => true,
            'base_price' => 300000,
            'discount_price' => null,
        ]);

        $response = $this->get(route('client.products.show', $product->slug))->assertOk();
        $content = $response->getContent();
        $schema = $this->productSchema($content);

        $this->assertStringContainsString('Khám phá Sản phẩm chưa đủ nội dung tại MaxBall', $this->metaContent($content, 'description', 'name'));
        $this->assertStringEndsWith('/favicon.ico', $this->metaContent($content, 'og:image'));
        $this->assertArrayNotHasKey('image', $schema);
        $this->assertArrayNotHasKey('sku', $schema);
        $this->assertArrayNotHasKey('availability', $schema['offers']);
        $this->assertSame('300000', $schema['offers']['price']);
    }

    public function test_robots_file_allows_crawling_and_blocks_private_areas_without_a_fixed_domain(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString("User-agent: *\nAllow: /", str_replace("\r\n", "\n", $robots));
        foreach (['/admin', '/cart', '/gio-hang', '/checkout', '/thanh-toan', '/login', '/register'] as $path) {
            $this->assertStringContainsString("Disallow: {$path}", $robots);
        }
        $this->assertStringNotContainsString('localhost', $robots);
        $this->assertStringNotContainsString('127.0.0.1', $robots);
    }

    private function category(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'danh-muc-seo'],
            ['name' => 'Danh mục SEO', 'status' => true],
        );
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function metaContent(string $html, string $key, string $attribute = 'property'): string
    {
        $node = $this->xpath($html)->query("//meta[@{$attribute}='{$key}']")->item(0);

        $this->assertNotNull($node, "Không tìm thấy thẻ meta {$key}.");

        return $node->getAttribute('content');
    }

    private function canonicalUrl(string $html): string
    {
        $node = $this->xpath($html)->query("//link[@rel='canonical']")->item(0);

        $this->assertNotNull($node, 'Không tìm thấy canonical URL.');

        return $node->getAttribute('href');
    }

    /** @return array<string, mixed> */
    private function productSchema(string $html): array
    {
        $node = $this->xpath($html)->query("//script[@type='application/ld+json']")->item(0);

        $this->assertNotNull($node, 'Không tìm thấy Product JSON-LD.');
        $schema = json_decode($node->textContent, true, 512, JSON_THROW_ON_ERROR);

        $this->assertIsArray($schema);

        return $schema;
    }
}
