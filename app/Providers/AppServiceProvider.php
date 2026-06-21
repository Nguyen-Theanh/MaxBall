<?php

namespace App\Providers;
use App\Models\Category;
use Illuminate\Support\Facades\View;

use Illuminate\Pagination\Paginator;
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
        // Tự động truyền biến $categories vào file header.blade.php
        View::composer('client.partials.header', function ($view) {
            $categories = Category::with('children')
                ->whereNull('parent_id') // Chỉ lấy danh mục cha
                ->where('status', 1)
                ->get();
                
            $view->with('categories', $categories);
        });
        Paginator::useBootstrapFive();
    }
}
