<?php

namespace Gnu\Scaffy\Laravel\Ports;

use Gnu\Scaffy\Laravel\Ports\DatabasePort;

class MsSqlServerAdapter implements DatabasePort
{
	public function selectAllTablesFromSchema(): string
	{
		$query = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'";
		return $query;
	}

	public function selectAllTableColumnsFromSchema(): string
	{
		$query = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
              FROM INFORMATION_SCHEMA.COLUMNS
              WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?
              ORDER BY ORDINAL_POSITION";
		return $query;
	}
}
