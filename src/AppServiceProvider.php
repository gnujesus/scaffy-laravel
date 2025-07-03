<?php

namespace Gnu\Scaffy\Laravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
use Gnu\Scaffy\Laravel\Adapters\LaravelAdapter;
use Gnu\Scaffy\Laravel\Ports\DatabasePort;
use Gnu\Scaffy\Laravel\Adapters\MsSqlServerAdapter;

class AppServiceProvider extends ServiceProvider
{

	/*
 *
 * NOTE: Quick reminder that this is wrong.
 *
 */

	/*
	public $driver;

	public function __construct(Config $config)
	{
		$this->driver = $config->get('database.default');
	}
	*/

	public function register()
	{
		$config = $this->app->make(Config::class);

		$this->app->bind(DatabasePort::class, function ($app) use ($config) {
			$driver = $config->get("database.default");

			return match ($driver) {
				'sqlsrv' => MsSqlServerAdapter::class,
				default =>  throw new \Exception("Unsupported DB Driver: {$driver}")
			};
		});

		$this->commands([
			LaravelAdapter::class,
		]);
	}
}
