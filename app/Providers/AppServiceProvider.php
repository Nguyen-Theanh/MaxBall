<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\PromotionAnnouncement;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('chatbot', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?? $request->ip();

            return Limit::perMinutes(5, 20)
                ->by('chatbot:'.$key)
                ->response(fn () => response()->json([
                    'success' => false,
                    'message' => 'Bạn gửi tin nhắn quá nhanh, vui lòng thử lại sau.',
                ], 429));
        });

        // Tự động truyền biến $categories vào file header.blade.php
        View::composer('client.partials.header', function ($view) {
            $categories = Category::with('children')
                ->whereNull('parent_id') // Chỉ lấy danh mục cha
                ->where('status', 1)
                ->get();

            $view->with('categories', $categories);
        });

        View::composer('client.layouts.app', function ($view) {
            $view->with(
                'promotionAnnouncements',
                PromotionAnnouncement::query()
                    ->where('is_active', true)
                    ->latest('id')
                    ->get(),
            );
        });

        Paginator::useBootstrapFive();
    }
}
