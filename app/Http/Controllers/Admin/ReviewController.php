<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Services\ReviewMediaUploader;
use App\Support\ReviewMediaRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::query()
            ->with(['user', 'product', 'orderDetail.variant', 'media'])
            ->when($request->filled('visibility'), function ($query) use ($request): void {
                $query->where('is_visible', $request->input('visibility') === 'visible');
            })
            ->when($request->filled('source'), function ($query) use ($request): void {
                $query->where('is_admin_review', $request->input('source') === 'admin');
            })
            ->when($request->filled('rating'), function ($query) use ($request): void {
                $query->where('rating', (int) $request->input('rating'));
            })
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('content', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'status']);

        $statistics = [
            'total' => Review::count(),
            'visible' => Review::where('is_visible', true)->count(),
            'hidden' => Review::where('is_visible', false)->count(),
            'admin' => Review::where('is_admin_review', true)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'products', 'statistics'));
    }

    public function store(Request $request, ReviewMediaUploader $mediaUploader)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'content' => ['nullable', 'string', 'max:1000'],
            'is_visible' => ['required', 'boolean'],
            'public_name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                function (string $attribute, mixed $value, $fail) use ($request): void {
                    $publicName = trim((string) $value);

                    if (
                        strcasecmp($publicName, (string) $request->user()->name) === 0
                        || strcasecmp($publicName, (string) $request->user()->email) === 0
                    ) {
                        $fail('Tên công khai không được để lộ tên hoặc email của tài khoản admin.');
                    }
                },
            ],
            ...ReviewMediaRules::rules(),
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm cần đánh giá.',
            'product_id.exists' => 'Sản phẩm được chọn không tồn tại.',
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.between' => 'Số sao đánh giá phải từ 1 đến 5.',
            'content.max' => 'Nội dung đánh giá không được vượt quá 1.000 ký tự.',
            'public_name.required' => 'Vui lòng nhập tên hiển thị công khai.',
            'public_name.min' => 'Tên công khai phải có ít nhất 3 ký tự.',
            'public_name.max' => 'Tên công khai không được vượt quá 50 ký tự.',
            ...ReviewMediaRules::messages(),
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $validated,
                $mediaUploader,
                &$storedPaths
            ): void {
                $review = Review::create([
                    'user_id' => $request->user()->id,
                    'product_id' => $validated['product_id'],
                    'rating' => $validated['rating'],
                    'content' => filled($validated['content'] ?? null)
                        ? trim($validated['content'])
                        : null,
                    'is_visible' => (bool) $validated['is_visible'],
                    'is_admin_review' => true,
                    'public_name' => trim($validated['public_name']),
                ]);

                $storedPaths = $mediaUploader->store($review, $request->file('media', []));
            });
        } catch (Throwable $exception) {
            $mediaUploader->delete($storedPaths);

            throw $exception;
        }

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', "Đã đăng đánh giá dưới tên {$validated['public_name']}.");
    }

    public function updateVisibility(Request $request, Review $review)
    {
        $validated = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);

        $review->update([
            'is_visible' => (bool) $validated['is_visible'],
        ]);

        return back()->with(
            'success',
            $review->is_visible
                ? 'Đánh giá đã được hiển thị trở lại.'
                : 'Đánh giá đã được ẩn khỏi trang sản phẩm.'
        );
    }
}
