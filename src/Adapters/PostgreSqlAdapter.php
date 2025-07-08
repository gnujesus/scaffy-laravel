<?php

namespace Gnu\Scaffy\Laravel\Adapters;

use Exception;
use Gnu\Scaffy\Laravel\Ports\DatabasePort;
use Illuminate\Support\Facades\DB;

class PostgreSqlAdapter extends BaseDatabaseAdapter implements DatabasePort
{
	public function __construct($schema = "")
	{
		parent::__construct($schema);
	}

	#[\Override]
	public function selectAllTablesFromSchema($customSchema = ""): array
	{
		if (empty($customSchema) && empty($this->defaultSchema)) {
			throw new Exception("No schema provided.");
		}

		$schema = empty($customSchema) ? $this->defaultSchema : $customSchema;

		$query = "SELECT *
		FROM information_schema.tables
		WHERE table_schema = ? 
		AND table_type = 'BASE TABLE'";

		$results = DB::select($query, [$schema]);

		return $results;
	}

	#[\Override]
	public function selectAllTableColumnsFromSchema($tableName, $customSchema = ""): array
	{
		if (empty($customSchema) && empty($this->defaultSchema)) {
			throw new Exception("No schema provided.");
		}

		$schema = empty($customSchema) ? $this->defaultSchema : $customSchema;

		$query = "SELECT column_name, data_type, is_nullable 
              FROM information_schema.columns
              WHERE table_name = ? AND table_schema = ?
              ORDER BY ordinal_position";


		$results = DB::select($query, [$tableName, $schema]);

		return $results;
	}
}
