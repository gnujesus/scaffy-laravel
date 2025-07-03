<?php

namespace Gnu\Scaffy\Laravel\Ports;

/*
*
* Note: This names should be more accurate, since the methods don't return the result 
* from the select, but rather the query to be executed by Illuminate's DB Facade.
*/

interface DatabasePort
{
	function selectAllTablesFromSchema(): string;
	function selectAllTableColumnsFromSchema(): string;
}
