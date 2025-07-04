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
}
