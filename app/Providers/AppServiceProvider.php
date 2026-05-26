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
            // Use a global variable to prevent recursion during migrate:fresh
            if (!isset($GLOBALS['MIGRATION_FRESH_RUNNING']) && !Schema::hasTable('addon_settings')) {
                $GLOBALS['MIGRATION_FRESH_RUNNING'] = true;
                // Drop all existing tables to ensure clean state
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $tableName = array_values((array)$table)[0];
                    Schema::dropIfExists($tableName);
                }
                Artisan::call('migrate:fresh', ['--force' => true]);
            }
        }
        catch(\Exception $e)
        {
            info('Auto migrate:fresh error: ' . $e->getMessage());
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
