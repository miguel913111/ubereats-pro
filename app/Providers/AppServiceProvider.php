<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Traits\AddonHelper;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

        //TODO: need to remove after 3.8 development
        if (app()->environment('local')) {
            if (request()->header('x-forwarded-proto') === 'https' || request()->getScheme() === 'https') {
                \URL::forceScheme('https');
            }
            if(request()->header('x-forwarded-host')) {
                \URL::forceRootUrl('https://' . request()->header('x-forwarded-host'));
            }
        }

        try
        {
            // Ensure the database schema is fully up to date.
            // If migrations table is missing or has fewer records than migration files,
            // run migrate:fresh to create all required tables.
            $needsFresh = !Schema::hasTable('migrations');
            if (!$needsFresh) {
                $migrationCount = DB::table('migrations')->count();
                $files = File::files(database_path('migrations'));
                $fileCount = count(array_filter($files, function ($file) {
                    return pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'php';
                }));
                $needsFresh = $migrationCount < $fileCount;
            }
            if ($needsFresh && !defined('MIGRATION_FRESH_RUNNING')) {
                define('MIGRATION_FRESH_RUNNING', true);
                Artisan::call('migrate:fresh', ['--force' => true]);
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
