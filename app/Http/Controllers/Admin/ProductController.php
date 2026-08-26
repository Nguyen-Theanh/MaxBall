<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute as ProductAttributeOption;
use App\Models\Category;
use App\Models\Product;
<<<<<<< Updated upstream
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
=======
use App\Models\ProductAttribute;

>>>>>>> Stashed changes
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category')
            ->withSum('variants', 'stock')
            ->withSum('variants', 'reserved_stock')
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
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->integer('category_id'));
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        $categories = $this->categories();
        $totalStock = ProductVariant::sum('stock');
        $totalReservedStock = ProductVariant::sum('reserved_stock');

        return view('admin.products.index', compact('products', 'categories', 'totalStock', 'totalReservedStock'));
    }

    public function show(Product $product): View
    {
        $product->load('category', 'variants', 'productImages');

        return view('admin.products.show', compact('product'));
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => new Product(['status' => true]),
            'categories' => $this->categories(),
<<<<<<< Updated upstream
            'attributes' => $this->variantAttributes(),
=======
            'attributeLibrary' => $this->attributeLibrary(),
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream

        $variantsInput = $request->input('variants', []);
        if (empty($variantsInput)) {
            $variantsInput = [
                [
                    'id' => null,
                    'name' => 'Mặc định',
                    'sku' => $data['slug'],
                    'base_price' => $data['base_price'],
                    'discount_price' => $data['discount_price'],
                    'stock' => $request->integer('stock', 0),
                ],
            ];
        }
        $this->storeVariants($product, $variantsInput, $request);

        $this->syncProductAverages($product);
=======
        $attributeValueMap = $this->storeProductAttributes($product, $request->input('attributes', []));
        $this->storeVariants($product, $request->input('variants', []), $attributeValueMap);
>>>>>>> Stashed changes

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Da them san pham moi.');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'productImages',
            'productAttributes.values',
            'variants.attributeValues.attribute',
        ]);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories(),
<<<<<<< Updated upstream
            'attributes' => $this->variantAttributes(),
=======
            'attributeLibrary' => $this->attributeLibrary(),
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream

        $variantsInput = $request->input('variants', []);
        if (empty($variantsInput)) {
            $variantsInput = [
                [
                    'id' => $product->variants->first()?->id,
                    'name' => 'Mặc định',
                    'sku' => $data['slug'],
                    'base_price' => $data['base_price'],
                    'discount_price' => $data['discount_price'],
                    'stock' => $request->integer('stock', 0),
                ],
            ];
        }
        $this->storeVariants($product, $variantsInput, $request);

        $this->syncProductAverages($product);
=======
        $attributeValueMap = $this->storeProductAttributes($product, $request->input('attributes', []));
        $this->storeVariants($product, $request->input('variants', []), $attributeValueMap);
>>>>>>> Stashed changes

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

        $cheapestVariant = $variants->sortBy(function ($variant) {
            return $variant->discount_price ?? $variant->base_price;
        })->first();

        $product->updateQuietly([
            'base_price' => $cheapestVariant->base_price,
            'discount_price' => $cheapestVariant->discount_price,
        ]);
    }

    private function validatedData(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($request->route('product')),
            ],
            'slug' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:2048'],
            'image' => ['nullable', 'image', 'max:3072'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:3072'],
            'description' => ['nullable', 'string'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:base_price'],
<<<<<<< Updated upstream
            'stock' => ['nullable', 'integer', 'min:0'],
            'new_attributes' => ['nullable', 'array'],
            'new_attributes.*.name' => ['nullable', 'string', 'max:255'],
            'new_attributes.*.values_text' => ['nullable', 'string', 'max:2000'],
=======
            'attributes' => ['nullable', 'array'],
            'attributes.*.name' => ['nullable', 'string', 'max:255'],
            'attributes.*.values_text' => ['nullable', 'string', 'max:2000'],
>>>>>>> Stashed changes
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'distinct', 'exists:product_variants,id'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.base_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.discount_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
<<<<<<< Updated upstream
            'variants.*.image' => ['nullable', 'image', 'max:3072'],
        ], [
            'variants.*.id.distinct' => 'Một biến thể đang bị gửi lặp lại.',
=======
            'variants.*.options' => ['nullable', 'array'],
            'variants.*.options.*' => ['nullable', 'string', 'max:255'],
>>>>>>> Stashed changes
        ]);

        $validator->after(function ($validator) use ($request): void {
            $seenNames = [];
            $seenSkus = [];
            $product = $request->route('product');

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

                if ($product instanceof Product && ! empty($variant['id']) && array_key_exists('stock', $variant)) {
                    $reservedStock = (int) $product->variants()
                        ->whereKey((int) $variant['id'])
                        ->value('reserved_stock');

                    if ((int) $variant['stock'] < $reservedStock) {
                        $validator->errors()->add(
                            "variants.{$index}.stock",
                            "Tồn kho không được thấp hơn {$reservedStock} sản phẩm đang giữ cho đơn COD."
                        );
                    }
                }
            }

            if ($product instanceof Product && empty($request->input('variants', [])) && $request->filled('stock')) {
                $reservedStock = (int) $product->variants()->value('reserved_stock');

                if ($request->integer('stock') < $reservedStock) {
                    $validator->errors()->add(
                        'stock',
                        "Tồn kho không được thấp hơn {$reservedStock} sản phẩm đang giữ cho đơn COD."
                    );
                }
            }
        });

        return $validator->validate();
    }

<<<<<<< Updated upstream
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
=======
    private function storeProductAttributes(Product $product, array $attributes): array
    {
        $product->productAttributes()->delete();

        $valueMap = [];
        $preparedAttributes = [];

        foreach ($attributes as $attributeData) {
            $name = trim($attributeData['name'] ?? '');
            $values = $this->parseAttributeValues($attributeData['values_text'] ?? '');

            if ($name === '' || empty($values)) {
                continue;
            }

            $attributeKey = $this->normalizeOptionKey($name);
            $preparedAttributes[$attributeKey]['name'] ??= $name;

            foreach ($values as $value) {
                $preparedAttributes[$attributeKey]['values'][$this->normalizeOptionKey($value)] = $value;
            }
        }

        foreach ($preparedAttributes as $preparedAttribute) {
            $attribute = $product->productAttributes()->create([
                'name' => $preparedAttribute['name'],
            ]);

            foreach ($preparedAttribute['values'] as $value) {
                $attributeValue = $attribute->values()->create([
                    'value' => $value,
                ]);

                $valueMap[$this->normalizeOptionKey($preparedAttribute['name'])][$this->normalizeOptionKey($value)] = $attributeValue->id;
            }
        }

        return $valueMap;
    }

    private function storeVariants(Product $product, array $variants, array $attributeValueMap): void
>>>>>>> Stashed changes
    {
        $existingIds = $product->variants()->pluck('id')->all();
        $receivedIds = [];

<<<<<<< Updated upstream
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
=======
        foreach ($variants as $variantData) {
            if ($this->variantIsBlank($variantData)) {
                continue;
            }

            $options = $this->cleanVariantOptions($variantData['options'] ?? []);
            $basePrice = $variantData['base_price'] ?? null;
            $discountPrice = $variantData['discount_price'] ?? null;
            $stock = $variantData['stock'] ?? null;

            $data = [
                'name' => trim($variantData['name'] ?? '') ?: $this->buildVariantName($options),
                'sku' => trim($variantData['sku'] ?? '') ?: null,
                'base_price' => $basePrice !== null && $basePrice !== '' ? (int) $basePrice : (int) $product->base_price,
                'discount_price' => $discountPrice !== null && $discountPrice !== '' ? (int) $discountPrice : null,
                'stock' => $stock !== null && $stock !== '' ? (int) $stock : null,
            ];

            $variant = null;

            if (!empty($variantData['id'])) {
>>>>>>> Stashed changes
                $id = (int) $variantData['id'];
                $variant = $product->variants()->where('id', $id)->first();

                if ($variant) {
<<<<<<< Updated upstream
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
=======
                    $variant->update($data);
                    $receivedIds[] = $id;
                }
            }

            if (!$variant) {
                $variant = $product->variants()->create($data);
                $receivedIds[] = $variant->id;
            }

            $variant->attributeValues()->sync($this->resolveVariantAttributeValueIds($options, $attributeValueMap));
>>>>>>> Stashed changes
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

<<<<<<< Updated upstream
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

=======
>>>>>>> Stashed changes
    private function parseAttributeValues(?string $valuesText): array
    {
        $values = preg_split('/[\r\n,;|]+/u', (string) $valuesText);
        $unique = [];

        foreach ($values as $value) {
            $value = trim($value);

            if ($value === '') {
                continue;
            }

<<<<<<< Updated upstream
            $unique[Str::ascii(Str::lower($value))] = $value;
=======
            $unique[$this->normalizeOptionKey($value)] = $value;
>>>>>>> Stashed changes
        }

        return array_values($unique);
    }

<<<<<<< Updated upstream
    private function generateVariantSku(Product $product, ?string $variantName, int $index): string
    {
        $base = Str::upper(Str::slug($product->slug ?: $product->name, '-'));
        $variant = Str::upper(Str::slug((string) $variantName, '-'));

        return trim($base.'-'.($variant ?: 'VAR-'.($index + 1)), '-');
=======
    private function variantIsBlank(array $variantData): bool
    {
        foreach (['name', 'sku', 'base_price', 'discount_price', 'stock'] as $field) {
            if (isset($variantData[$field]) && trim((string) $variantData[$field]) !== '') {
                return false;
            }
        }

        return empty($this->cleanVariantOptions($variantData['options'] ?? []));
    }

    private function cleanVariantOptions(array $options): array
    {
        $cleanOptions = [];

        foreach ($options as $name => $value) {
            $name = trim((string) $name);
            $value = trim((string) $value);

            if ($name === '' || $value === '') {
                continue;
            }

            $cleanOptions[$name] = $value;
        }

        return $cleanOptions;
    }

    private function buildVariantName(array $options): ?string
    {
        if (empty($options)) {
            return null;
        }

        return implode(' / ', array_values($options));
    }

    private function resolveVariantAttributeValueIds(array $options, array $attributeValueMap): array
    {
        $ids = [];

        foreach ($options as $name => $value) {
            $attributeKey = $this->normalizeOptionKey($name);
            $valueKey = $this->normalizeOptionKey($value);

            if (isset($attributeValueMap[$attributeKey][$valueKey])) {
                $ids[] = $attributeValueMap[$attributeKey][$valueKey];
            }
        }

        return $ids;
    }

    private function normalizeOptionKey(string $value): string
    {
        return Str::ascii(Str::lower(trim($value)));
>>>>>>> Stashed changes
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

    public function checkName(Request $request): JsonResponse
    {
        $name = $request->query('name');
        $ignoreId = $request->query('ignore_id');

        if (! $name) {
            return response()->json(['exists' => false]);
        }

        $query = Product::whereRaw('LOWER(name) = ?', [Str::lower($name)]);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return response()->json(['exists' => $query->exists()]);
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

    private function attributeLibrary()
    {
        $library = [
            'Size' => [
                's' => 'S',
                'm' => 'M',
                'l' => 'L',
                'xl' => 'XL',
                'xxl' => 'XXL',
            ],
            'Màu sắc' => [
                'den' => 'Đen',
                'trang' => 'Trắng',
                'do' => 'Đỏ',
                'xanh' => 'Xanh',
            ],
        ];

        ProductAttribute::with('values')->orderBy('name')->get()->each(function (ProductAttribute $attribute) use (&$library) {
            $name = trim($attribute->name);

            if ($name === '') {
                return;
            }

            $library[$name] ??= [];

            foreach ($attribute->values as $value) {
                $optionValue = trim($value->value);

                if ($optionValue === '') {
                    continue;
                }

                $library[$name][$this->normalizeOptionKey($optionValue)] = $optionValue;
            }
        });

        return collect($library)
            ->map(fn (array $values, string $name) => [
                'name' => $name,
                'values' => array_values($values),
            ])
            ->values();
    }
}
