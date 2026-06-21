<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Truyền categories vào header
        View::composer('client.partials.header', function ($view) {
            $categories = Category::with('children')
                ->whereNull('parent_id')
                ->where('status', 1)
                ->get();

            $view->with('categories', $categories);
        });

        // Pagination dùng bootstrap
        Paginator::useBootstrapFive();
    }
}
