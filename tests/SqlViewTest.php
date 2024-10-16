<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

// Your custom migration class
class CreateTestViews extends Migration
{
    use CleaniqueCoders\Traitify\Concerns\InteractsWithSqlViewMigration;

    // Override the methods to provide custom filenames
    protected function getUpFilename(): string
    {
        return 'test-create-views.sql';
    }

    protected function getDownFilename(): string
    {
        return 'test-drop-views.sql';
    }
}

beforeEach(function () {
    // Create the 'database/sql' directory and add SQL files
    if (! is_dir(database_path('sql'))) {
        mkdir(database_path('sql'), 0755, true);
    }

    // Prepare SQL files with content
    file_put_contents(database_path('sql/test-create-views.sql'), 'CREATE VIEW test_view AS SELECT * FROM test_table;');
    file_put_contents(database_path('sql/test-drop-views.sql'), 'DROP VIEW IF EXISTS test_view;');
});

afterEach(function () {
    // Clean up the SQL files after each test
    @unlink(database_path('sql/test-create-views.sql'));
    @unlink(database_path('sql/test-drop-views.sql'));
    @rmdir(database_path('sql'));
});

it('runs the up migration with the correct SQL file', function () {
    // Mock both DROP VIEW and CREATE VIEW during the up method
    DB::shouldReceive('unprepared')
        ->once()
        ->with('DROP VIEW IF EXISTS test_view;'); // from the down() call

    DB::shouldReceive('unprepared')
        ->once()
        ->with('CREATE VIEW test_view AS SELECT * FROM test_table;'); // from the up() call

    $migration = new CreateTestViews;
    $migration->up(); // Should call both DROP VIEW and CREATE VIEW SQL
});

it('runs the down migration with the correct SQL file', function () {
    // Mock only the DROP VIEW call during the down method
    DB::shouldReceive('unprepared')
        ->once()
        ->with('DROP VIEW IF EXISTS test_view;');

    $migration = new CreateTestViews;
    $migration->down(); // Should call only the DROP VIEW SQL
});

it('throws an exception if the SQL file is not found', function () {
    // Delete the up file to simulate file not found
    @unlink(database_path('sql/test-create-views.sql'));

    $migration = new CreateTestViews;

    // Expect an exception when the SQL file is not found
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage(database_path('sql'.DIRECTORY_SEPARATOR.'test-create-views.sql').' file not found.');

    $migration->up(); // Should throw exception
});
