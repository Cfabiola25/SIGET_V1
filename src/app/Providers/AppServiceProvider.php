<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! function_exists('reicon')) {
            require_once __DIR__.'/../helpers.php';
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('reicon', function (string $expression): string {
            return "<?php echo \\App\\Support\\Reicon::render({$expression}); ?>";
        });
    }
}
