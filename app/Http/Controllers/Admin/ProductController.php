<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            $data['thumbnail'] = $request->file('image')->store('products', 'public');
        }

        $data['category_id'] = $this->resolveCategoryId($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['status'] = $request->boolean('status');
        $data['discount_price'] = $data['discount_price'] ?: null;
        unset($data['category_name']);

        $product = Product::create($data);
        $this->storeGalleryImages($product, $request);
        $this->storeVariants($product, $request->input('variants', []));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Da them san pham moi.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validatedData($request);

        if ($request->hasFile('image')) {
            if ($product->thumbnail && !Str::startsWith($product->thumbnail, ['http://', 'https://'])) {
                Storage::disk('public')->delete(ltrim($product->thumbnail, '/'));
            }
            $data['thumbnail'] = $request->file('image')->store('products', 'public');
        }

        $data['category_id'] = $this->resolveCategoryId($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['status'] = $request->boolean('status');
        $data['discount_price'] = $data['discount_price'] ?: null;
        unset($data['category_name']);

        $product->update($data);
        $this->storeGalleryImages($product, $request);
        $this->storeVariants($product, $request->input('variants', []));

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Da cap nhat san pham.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Da xoa san pham.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
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
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['nullable', 'string', 'max:255'],
            'variants.*.sku' => ['nullable', 'string', 'max:255'],
            'variants.*.base_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.discount_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function storeVariants(Product $product, array $variants): void
    {
        $existingIds = $product->variants()->pluck('id')->all();
        $receivedIds = [];

        foreach ($variants as $variantData) {
            if (empty($variantData))
                continue;

            $data = [
                'name' => $variantData['name'] ?? null,
                'sku' => $variantData['sku'] ?? null,
                'base_price' => isset($variantData['base_price']) ? (int) $variantData['base_price'] : 0,
                'discount_price' => $variantData['discount_price'] !== '' ? ($variantData['discount_price'] !== null ? (int) $variantData['discount_price'] : null) : null,
                'stock' => isset($variantData['stock']) ? (int) $variantData['stock'] : null,
            ];

            if (!empty($variantData['id'])) {
                $id = (int) $variantData['id'];
                $updated = $product->variants()->where('id', $id)->update($data);
                if ($updated) {
                    $receivedIds[] = $id;
                    continue;
                }
            }

            $new = $product->variants()->create($data);
            $receivedIds[] = $new->id;
        }

        // delete variants removed in the UI
        $toDelete = array_diff($existingIds, $receivedIds);
        if (!empty($toDelete)) {
            $product->variants()->whereIn('id', $toDelete)->delete();
        }
    }

    private function storeGalleryImages(Product $product, Request $request): void
    {
        if (!$request->hasFile('gallery_images')) {
            return;
        }

        foreach ($request->file('gallery_images') as $file) {
            if (!$file->isValid()) {
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
        return Category::orderBy('name')->get();
    }
}
