<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PopulateMigrationsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        $migrationPath = database_path('migrations');
        if (!is_dir($migrationPath)) {
            return;
        }

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

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        // No-op
    }
}
