<?php

/**
 * -----------------------------------------------------------------------------
 * 
 * Disclaimer: This method requires to write the schema to prevent mass generation
 * of a ridiculus amount of tables, for those databases that are huge. You'll rarely need 
 * that many models at one (we're talking about a hundred to a thousand tables). For said
 * reasons, the user is required to know the schema of the tables he wants to import.
 *
 * Author: GNU Jesus <jotamartinezd@gmail.com>
 * Created: July 3, 2025
 *
 * -----------------------------------------------------------------------------
 */

namespace Gnu\Scaffy\Laravel\Adapters;

use Gnu\Scaffy\Core\Ports\FrameworkPort;
use Gnu\Scaffy\Core\Helpers\IOHelper;
use Gnu\Scaffy\Laravel\Ports\DatabasePort;
use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Config\Repository as Config;


class LaravelAdapter extends Command implements FrameworkPort
{

	// TODO: Dependency Injection

	private $dbAdapter;

	public function __construct(Container $app)
	{
		parent::__construct();
		$this->dbAdapter = $app->make(DatabasePort::class);
	}

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'scaffy:generate {--table=} {--schema=} {--output=} {--with-relations?} {--database?}';

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
		$schema = $this->option('schema') ?? '';
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
		try {
			$results = $this->dbAdapter->selectAllTablesFromSchema($schema);

			// Let's see what we get back
			$this->info("Raw results:");
			foreach ($results as $result) {
				$this->line("Table: " . $result->TABLE_NAME);
			}

			// Return just the table names
			return array_map(function ($table) {
				return $table->TABLE_NAME;
			}, $results);
		} catch (\Exception $e) {
			$this->error($e->getMessage());
			exit;
		}
	}

	function getTableColumns(string $tableName, string $schema): array
	{
		try {
			$results = $this->dbAdapter->selectAllTableColumnsFromSchema($tableName, $schema);
			return $results;
		} catch (\Exception $e) {
			$this->error($e->getMessage());
			exit;
		}
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
