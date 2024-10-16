<?php

namespace CleaniqueCoders\Traitify\Concerns;

use Illuminate\Support\Facades\DB;

trait InteractsWithSqlViewMigration
{
    protected string $up_filename = 'create-sql-views.sql';

    protected string $down_filename = 'drop-sql-views.sql';

    public function up()
    {
        $this->down();
        $this->run(
            $this->getUpFilename()
        );
    }

    public function down()
    {
        $this->run(
            $this->getDownFilename()
        );
    }

    protected function getUpFilename(): string
    {
        return $this->up_filename;
    }

    protected function getDownFilename(): string
    {
        return $this->down_filename;
    }

    protected function run($filename)
    {
        $path = $this->getPath($filename);

        if (! file_exists($path)) {
            throw new \Exception("$path file not found.");
        }

        $content = file_get_contents($path);

        DB::unprepared($content);
    }

    protected function getPath($filename): string
    {
        return $this->getStoragePath().DIRECTORY_SEPARATOR.$filename;
    }

    protected function getStoragePath(): string
    {
        return database_path('sql');
    }
}
