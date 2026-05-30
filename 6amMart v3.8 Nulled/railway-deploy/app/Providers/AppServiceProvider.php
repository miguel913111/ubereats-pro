<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Traits\AddonHelper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    use AddonHelper;
    /**
     * Register any application services. 
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // Force HTTPS when behind proxy (Railway, etc.)
        if (request()->header('x-forwarded-proto') === 'https' || request()->getScheme() === 'https') {
            \URL::forceScheme('https');
        }
        if(request()->header('x-forwarded-host')) {
            \URL::forceRootUrl('https://' . request()->header('x-forwarded-host'));
        }

        try
        {
            // If migrations table exists but is empty, populate it with all existing migrations.
            // This prevents duplicate column errors when DB was seeded from SQL dump.
            if (Schema::hasTable('migrations')) {
                $count = DB::table('migrations')->count();
                if ($count === 0) {
                    $migrationPath = database_path('migrations');
                    if (is_dir($migrationPath)) {
                        $files = File::files($migrationPath);
                        $inserts = [];
                        foreach ($files as $file) {
                            $filename = $file->getFilename();
                            if (pathinfo($filename, PATHINFO_EXTENSION) === 'php') {
                                $inserts[] = [
                                    'migration' => pathinfo($filename, PATHINFO_FILENAME),
                                    'batch' => 1,
                                ];
                            }
                        }
                        if (!empty($inserts)) {
                            DB::table('migrations')->insert($inserts);
                        }
                    }
                }
            }
        }
        catch(\Exception $e)
        {
            // Silently ignore if DB is not yet available
        }

        try
        {
            Request::macro('isAny', function (array $patterns) {
                return collect($patterns)->contains(fn ($pattern) => Request::is($pattern));
            });

            Config::set('addon_admin_routes',$this->get_addon_admin_routes());
            Config::set('get_payment_publish_status',$this->get_payment_publish_status());
            Paginator::useBootstrap();
            foreach(Helpers::get_view_keys() as $key=>$value)
            {
                view()->share($key, $value);
            }
        }
        catch(\Exception $e)
        {

        }

    }
}


