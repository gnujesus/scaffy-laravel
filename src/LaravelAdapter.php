<?php

namespace Gnu\Scaffy\Laravel;

use Gnu\Scaffy\Core\Ports\FrameworkPort;
use Gnu\Scaffy\Core\Helpers\IOHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LaravelAdapter extends Command implements FrameworkPort
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'scaffy:generate {--table=} {--schema=} {--output=} {--with-relations}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "Generate models based on the database entities.
    --schema                              Specify the table schema.
                                          Default: NULL.

    --table                               Specify the table you want to scaffold.
                                          Default: NULL.
                                          If no table is provided, all tables from the provided schema are generated.

    --output (COMING SOON)		  Specify the output directory for your models.
                                          Default: app/Models.

    --with-relations (COMING SOON)        Specify if you want to generate the scaffold with it's relations.
                                          Default: false.


                            ";

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$table = $this->option('table');
		$withRelations = $this->option('with-relations');
		$schema = $this->option('schema');
		$outputDir = $this->option('output');

		try {
			if ($table) {
				$this->info("Generating model for table: {$table}");
				$this->generateModel($table, $schema);
			} else {
				$this->info("Generating models from all tables in Admission schema");
				$tables = $this->getAllTables($schema);
				$this->info("Found " . count($tables) . " tables");

				foreach ($tables as $table) {
					$this->generateModel($table, $schema);
				}
			}
		} catch (\Exception $e) {
			$this->error("An error has ocurred: {$e->getMessage()}");
		}
	}

	function getAllTables(string $schema): array
	{
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'";
		$results = DB::select($query, [$schema]);

		// Let's see what we get back
		$this->info("Raw results:");
		foreach ($results as $result) {
			$this->line("Table: " . $result->TABLE_NAME);
		}

		// Return just the table names
		return array_map(function ($table) {
			return $table->TABLE_NAME;
		}, $results);
	}

	function getTableColumns(string $tableName, string $schema): array
	{
		$query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?
              ORDER BY ORDINAL_POSITION";

		$results = DB::select($query, [$tableName, $schema]);

		return $results;
	}

	function generateModel(string $tableName, string $schema): bool
	{
		try {
			$this->info("Processing table: {$tableName}");

			$columns = $this->getTableColumns($tableName, $schema);
			$this->line("Found " . count($columns) . " columns");

			$modelName = $this->getModelName($tableName);
			$this->line("Generated model name: " . $modelName);

			$fillable = $this->getFillableArray($columns);
			$this->line("Fillable fields: " . count($fillable));

			$content = $this->generateModelContent($modelName, $tableName, $fillable, $schema);

			$this->saveModelFile($modelName, $content);

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	function getModelName(string $tableName): string
	{
		if (!str_contains($tableName, '_') && !str_contains($tableName, ' ')) {
			if (ctype_upper($tableName)) {
				return ucwords(strtolower($tableName));
			}
			return ucwords($tableName);
		}

		return str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($tableName))));
	}

	function getFillableArray(array $columns): array
	{
		$fillable = [];

		foreach ($columns as $column) {
			$fillable[] = $column->COLUMN_NAME;
		}

		return $fillable;
	}

	function generateModelContent(string $modelName, string $tableName, array $fillable, string $schema): string
	{
		$fillableString = "'" . implode("',\n        '", $fillable) . "'";

		$content = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    protected \$fillable = [
        {$fillableString}
    ];

    protected \$guarded = []; // Allow mass assignment

    public \$timestamps = false;

    public function getTable()
    {
        return '{$schema}.{$tableName}';
    }
}";

		return $content;
	}

	function saveModelFile(string $modelName, string $content): bool
	{
		try {
			// NOTE: This might not work
			$modelsPath = IOHelper::findRootDirectory("/app/Models");

			if (!file_exists($modelsPath)) {
				mkdir($modelsPath, 0755, true);
			}

			$filePath = $modelsPath . '/' . $modelName . '.php';

			file_put_contents($filePath, $content);

			$this->line("✅ Model created successfully at path: " . $filePath);

			return true;
		} catch (\Exception $e) {
			$this->error("❎ Uncaught Exception: An error has ocurred. " . $e);
			return false;
		}
	}
}
