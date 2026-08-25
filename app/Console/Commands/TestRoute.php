<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use App\Models\User;

class TestRoute extends Command
{
    protected $signature = 'test:route';
    protected $description = 'Test POST route';

    public function handle()
    {
        $user = User::where('role', 'admin')->first();
        auth()->login($user);

        $request = Request::create('/admin/products', 'POST', [
            'name' => 'Găng Tay Thủ Môn Nike Cao Cấp - Mã 7453',
            'base_price' => 100,
        ]);
        // bypass CSRF for test
        $this->laravel->instance(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, new class {
            public function handle($request, $next) { return $next($request); }
        });

        try {
            $kernel = $this->laravel->make(\Illuminate\Contracts\Http\Kernel::class);
            $response = $kernel->handle($request);
            $this->info("Status: " . $response->getStatusCode());
            if ($response->getStatusCode() === 302) {
                $this->info("Redirect: " . $response->headers->get('Location'));
            } else {
                $this->info("Body: " . substr($response->getContent(), 0, 100));
            }
        } catch (\Throwable $e) {
            $this->error("Exception: " . get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
