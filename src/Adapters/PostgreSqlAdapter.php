<?php

namespace Gnu\Scaffy\Laravel\Adapters;

use Gnu\Scaffy\Laravel\Ports\DatabasePort;

class PostgreSqlAdapter extends BaseDatabaseAdapter implements DatabasePort
{
	public function __construct($schema = "")
	{
		parent::__construct($schema);
	}
}
