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
            // Ensure migrations table exists and is populated.
            // This prevents duplicate column errors when DB was seeded from SQL dump
            // without migration records, or when migrations table is missing entirely.
            if (Schema::hasTable('migrations')) {
                $count = DB::table('migrations')->count();
                if ($count === 0) {
                    $this->populateMigrationsTable();
                }
            } else {
                Schema::create('migrations', function ($table) {
                    $table->increments('id');
                    $table->string('migration');
                    $table->integer('batch');
                });
                $this->populateMigrationsTable();
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

    /**
     * Populate the migrations table with all existing migration files.
     *
     * @return void
     */
    private function populateMigrationsTable()
    {
        $migrationPath = database_path('migrations');
        if (!is_dir($migrationPath)) return;
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
