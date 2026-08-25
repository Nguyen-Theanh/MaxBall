<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/admin/products', 'POST', [
    'name' => 'Găng Tay Thủ Môn Nike Cao Cấp - Mã 7453',
    'base_price' => 100,
]);
// We need to simulate being logged in as admin to bypass EnsureAdmin and Auth
$user = App\Models\User::where('role', 'admin')->first();
$app['auth']->guard()->setUser($user);

// We need to bypass CSRF. CSRF only applies to web middleware.
// Let's just remove VerifyCsrfToken middleware for this test.
$app->instance(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() === 302) {
        echo "Redirect: " . $response->headers->get('Location') . "\n";
    } else {
        echo "Body: " . substr($response->getContent(), 0, 500) . "\n";
    }
} catch (\Throwable $e) {
    echo "Exception: " . get_class($e) . ': ' . $e->getMessage() . "\n";
}
