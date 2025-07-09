<?php

namespace Gnu\Scaffy\Laravel\Adapters;

use Exception;
use Gnu\Scaffy\Laravel\Ports\DatabasePort;
use Illuminate\Support\Facades\DB;

class BaseDatabaseAdapter implements DatabasePort
{
	public string $defaultSchema;

	public function __construct(string $schema = "")
	{
		$this->defaultSchema = $schema;
	}

	public function selectAllTablesFromSchema($customSchema = ""): array
	{
		if (empty($customSchema) && empty($this->defaultSchema)) {
			throw new Exception("No schema provided.");
		}

		$schema = empty($customSchema) ? $this->defaultSchema : $customSchema;

		$query = "SELECT * 
		FROM INFORMATION_SCHEMA.TABLES 
		WHERE TABLE_SCHEMA = ? 
		AND TABLE_TYPE = 'BASE TABLE'";

		$results = DB::select($query, [$schema]);

		return $results;
	}

	public function selectAllTableColumnsFromSchema($tableName, $customSchema = ""): array
	{
		if (empty($customSchema) && empty($this->defaultSchema)) {
			throw new Exception("No schema provided.");
		}

		$schema = empty($customSchema) ? $this->defaultSchema : $customSchema;

		$query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?
              ORDER BY ORDINAL_POSITION";

		$results = DB::select($query, [$tableName, $schema]);

		return $results;
	}

	// this belongs on postgres, i'll put it here for now.

	public function getAllRelations(): array
	{

		$query = "
		SELECT 
		  kcu.table_name AS source_table,
		  kcu.column_name AS source_column,
		  ccu.table_name AS target_table,
		  ccu.column_name AS target_column
		FROM 
		  information_schema.key_column_usage AS kcu
		JOIN 
		  information_schema.constraint_column_usage AS ccu
		  ON kcu.constraint_name = ccu.constraint_name
		  AND kcu.constraint_schema = ccu.constraint_schema
		WHERE 
		  (kcu.constraint_name LIKE '%_foreign%')
		";

		$results = DB::select($query);

		return $results;
	}


	/* 
	*
	* Query to find relations between tables (explained)
	*
	
	SELECT 
	  kcu.table_name AS source_table,
	  kcu.column_name AS source_column,
	  ccu.table_name AS target_table,
	  ccu.column_name AS target_column
	FROM 
	  information_schema.key_column_usage AS kcu
	JOIN 
	  information_schema.constraint_column_usage AS ccu
	  ON kcu.constraint_name = ccu.constraint_name
	  AND kcu.constraint_schema = ccu.constraint_schema
	WHERE 
	  kcu.constraint_name LIKE '%_foreign%';

	*
	* 1. Make the select that's going to create the resulting structure => source_table | source_column | target_table | target_column
	* 2. Specify the table from which you're going to select and stabish an alias, in this case, kcu
	* 3. Join with another table in which the same select is applied
	* 4. Specify the conditions of the join (on, and)
	* 5. Specify the where for the first select (step 1)
	*
	*
	* Other queries
	
	SELECT 
	  kcu.table_name AS source_table,
	  kcu.column_name AS source_column,
	  ccu.table_name AS target_table,
	  ccu.column_name AS target_column

	FROM 
	  information_schema.key_column_usage AS kcu
	JOIN 
	  information_schema.constraint_column_usage AS ccu
	  ON kcu.constraint_name = ccu.constraint_name
	  AND kcu.constraint_schema = ccu.constraint_schema
	WHERE 
	  (kcu.constraint_name LIKE '%_foreign%' 
	  	OR kcu.constraint_name  LIKE '%unique%') 
	  AND 
	  (kcu.column_name LIKE '%id%' 
		AND ccu.column_name LIKE '%id%');

	*
	*/
}
