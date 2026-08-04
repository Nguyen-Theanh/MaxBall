<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute as ProductAttributeOption;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->input('q'));

                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('slug', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->integer('status'));
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['status' => true]),
            'categories' => $this->categories(),
            'attributes' => $this->variantAttributes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['thumbnail'] = $request->file('image')->store('products', 'public');
        }

        $data['category_id'] = $this->resolveCategoryId($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['status'] = $request->boolean('status');
        $data['discount_price'] = ($data['discount_price'] ?? null) ?: null;
        unset($data['category_name']);

        $product = Product::create($data);
        $this->storeInlineAttributes($request->input('new_attributes', []));
        $this->storeGalleryImages($product, $request);
        $this->storeVariants($product, $request->input('variants', []), $request);
        $this->syncProductAverages($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Da them san pham moi.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories(),
            'attributes' => $this->variantAttributes(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            if ($product->thumbnail && ! Str::startsWith($product->thumbnail, ['http://', 'https://'])) {
                Storage::disk('public')->delete(ltrim($product->thumbnail, '/'));
            }
            $data['thumbnail'] = $request->file('image')->store('products', 'public');
        }

        $data['category_id'] = $this->resolveCategoryId($request);
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['name']);
        $data['status'] = $request->boolean('status');
        $data['discount_price'] = ($data['discount_price'] ?? null) ?: null;
        unset($data['category_name']);

        $product->update($data);
        $this->storeInlineAttributes($request->input('new_attributes', []));
        $this->storeGalleryImages($product, $request);
        $this->storeVariants($product, $request->input('variants', []), $request);
        $this->syncProductAverages($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Da cap nhat san pham.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->variants()->whereHas('orderDetails')->exists()) {
            return redirect()
                ->route('admin.products.index')
                ->with(
                    'error',
                    "Không thể xóa sản phẩm vì sản phẩm này đã phát sinh đơn hàng.\nVui lòng ẩn sản phẩm thay vì xóa để bảo toàn dữ liệu đơn hàng."
                );
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã xóa sản phẩm.');
    }

    private function syncProductAverages(Product $product): void
    {
        $variants = $product->variants()->get();
        if ($variants->isEmpty()) {
            return;
        }

        $totalBase = 0;
        $totalDiscount = 0;
        $hasAnyDiscount = false;

        foreach ($variants as $variant) {
            $totalBase += $variant->base_price;
            if ($variant->discount_price !== null) {
                $hasAnyDiscount = true;
                $totalDiscount += $variant->discount_price;
            } else {
                $totalDiscount += $variant->base_price;
            }
        }

        $count = $variants->count();
        $product->updateQuietly([
            'base_price' => (int) round($totalBase / $count),
            'discount_price' => $hasAnyDiscount ? (int) round($totalDiscount / $count) : null,
        ]);
    }

    private function syncProductAverages(Product $product): void
    {
        $variants = $product->variants()->get();
        if ($variants->isEmpty()) {
            return;
        }

        $totalBase = 0;
        $totalDiscount = 0;
        $hasAnyDiscount = false;

        foreach ($variants as $variant) {
            $totalBase += $variant->base_price;
            if ($variant->discount_price !== null) {
                $hasAnyDiscount = true;
                $totalDiscount += $variant->discount_price;
            } else {
                $totalDiscount += $variant->base_price;
            }
        }

        $count = $variants->count();
        $product->updateQuietly([
            'base_price' => (int) round($totalBase / $count),
            'discount_price' => $hasAnyDiscount ? (int) round($totalDiscount / $count) : null,
        ]);
    }

    private function validatedData(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:3072'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:3072'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:base_price'],
            'new_attributes' => ['nullable', 'array'],
            'new_attributes.*.name' => ['nullable', 'string', 'max:255'],
            'new_attributes.*.values_text' => ['nullable', 'string', 'max:2000'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'distinct', 'exists:product_variants,id'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.base_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.discount_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.image' => ['nullable', 'image', 'max:3072'],
        ], [
            'variants.*.id.distinct' => 'Một biến thể đang bị gửi lặp lại.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $seenNames = [];
            $seenSkus = [];

            foreach ($request->input('variants', []) as $index => $variant) {
                if (! is_array($variant)) {
                    continue;
                }

                $nameKey = $this->variantIdentityKey($variant['name'] ?? null);
                if ($nameKey !== '') {
                    if (isset($seenNames[$nameKey])) {
                        $validator->errors()->add(
                            "variants.{$index}.name",
                            'Biến thể này đã tồn tại trong sản phẩm.'
                        );
                    } else {
                        $seenNames[$nameKey] = $index;
                    }
                }

                $skuKey = Str::upper(trim((string) ($variant['sku'] ?? '')));
                if ($skuKey !== '') {
                    if (isset($seenSkus[$skuKey])) {
                        $validator->errors()->add(
                            "variants.{$index}.sku",
                            'Mã SKU đang bị trùng với một biến thể khác.'
                        );
                    } else {
                        $seenSkus[$skuKey] = $index;
                    }
                }
            }
        });

        return $validator->validate();
    }

    private function variantIdentityKey(?string $value): string
    {
        return Str::of((string) $value)
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/\s*-\s*/', '-')
            ->replaceMatches('/\s+/', ' ')
            ->value();
    }

    private function storeVariants(Product $product, array $variants, Request $request): void
    {
        $existingIds = $product->variants()->pluck('id')->all();
        $receivedIds = [];

        foreach ($variants as $variantIndex => $variantData) {
            if (empty($variantData)) {
                continue;
            }

            $data = [
                'name' => $variantData['name'] ?? null,
                'sku' => trim($variantData['sku'] ?? '') ?: $this->generateVariantSku($product, $variantData['name'] ?? '', (int) $variantIndex),
                'base_price' => isset($variantData['base_price']) ? (int) $variantData['base_price'] : 0,
                'discount_price' => ($variantData['discount_price'] ?? '') !== '' ? ($variantData['discount_price'] !== null ? (int) $variantData['discount_price'] : null) : null,
                'stock' => isset($variantData['stock']) ? (int) $variantData['stock'] : null,
            ];

            $variantImage = $request->file("variants.{$variantIndex}.image");

            if (! empty($variantData['id'])) {
                $id = (int) $variantData['id'];
                $variant = $product->variants()->where('id', $id)->first();

                if ($variant) {
                    if ($variantImage && $variantImage->isValid()) {
                        $this->deleteVariantImage($variant->image_url);
                        $data['image_url'] = $variantImage->store('products/variants', 'public');
                    }

                    $variant->update($data);
                    $receivedIds[] = $id;

                    continue;
                }
            }

            if ($variantImage && $variantImage->isValid()) {
                $data['image_url'] = $variantImage->store('products/variants', 'public');
            }

            $new = $product->variants()->create($data);
            $receivedIds[] = $new->id;
        }

        // delete variants removed in the UI
        $toDelete = array_diff($existingIds, $receivedIds);
        if (! empty($toDelete)) {
            $product->variants()->whereIn('id', $toDelete)->get()->each(function ($variant) {
                $this->deleteVariantImage($variant->image_url);
            });
            $product->variants()->whereIn('id', $toDelete)->delete();
        }
    }

    private function deleteVariantImage(?string $path): void
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }

        Storage::disk('public')->delete(ltrim($path, '/'));
    }

    private function storeInlineAttributes(array $attributes): void
    {
        foreach ($attributes as $attributeData) {
            $name = trim($attributeData['name'] ?? '');
            $values = $this->parseAttributeValues($attributeData['values_text'] ?? '');

            if ($name === '' || empty($values)) {
                continue;
            }

            $attribute = ProductAttributeOption::firstOrCreate(['name' => $name]);
            $nextSortOrder = (int) $attribute->values()->max('sort_order') + 1;

            foreach ($values as $value) {
                $attribute->values()->firstOrCreate(
                    ['value' => $value],
                    ['sort_order' => $nextSortOrder++]
                );
            }
        }
    }

    private function parseAttributeValues(?string $valuesText): array
    {
        $values = preg_split('/[\r\n,;|]+/u', (string) $valuesText);
        $unique = [];

        foreach ($values as $value) {
            $value = trim($value);

            if ($value === '') {
                continue;
            }

            $unique[Str::ascii(Str::lower($value))] = $value;
        }

        return array_values($unique);
    }

    private function generateVariantSku(Product $product, ?string $variantName, int $index): string
    {
        $base = Str::upper(Str::slug($product->slug ?: $product->name, '-'));
        $variant = Str::upper(Str::slug((string) $variantName, '-'));

        return trim($base.'-'.($variant ?: 'VAR-'.($index + 1)), '-');
    }

    private function storeGalleryImages(Product $product, Request $request): void
    {
        if (! $request->hasFile('gallery_images')) {
            return;
        }

        foreach ($request->file('gallery_images') as $file) {
            if (! $file->isValid()) {
                continue;
            }

            $product->productImages()->create([
                'image_url' => $file->store('products/details', 'public'),
            ]);
        }
    }

    private function resolveCategoryId(Request $request): int
    {
        if ($request->filled('category_name') && trim($request->input('category_name')) !== '') {
            $name = trim($request->input('category_name'));

            return Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'status' => true]
            )->id;
        }

        if ($request->filled('category_id')) {
            return $request->integer('category_id');
        }

        return Category::firstOrCreate(
            ['slug' => 'uncategorized'],
            ['name' => 'Uncategorized', 'status' => true]
        )->id;
    }

    private function categories()
    {
        $categories = Category::with('parent')->get();

        return $categories->map(function ($category) {
            $category->display_name = $category->parent_id
                ? $category->parent->name.' > '.$category->name
                : $category->name;

            return $category;
        })->sortBy('parent_id');
    }

    private function variantAttributes()
    {
        return ProductAttributeOption::with('values')->orderBy('name')->get();
    }
}
