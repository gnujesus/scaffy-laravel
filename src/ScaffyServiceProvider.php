<?php

namespace Gnu\Scaffy\Laravel;


use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository as Config;
use Gnu\Scaffy\Laravel\Adapters\LaravelAdapter;
use Gnu\Scaffy\Laravel\Ports\DatabasePort;
use Gnu\Scaffy\Laravel\Ports\MsSqlServerAdapter;

class ScaffyServiceProvider extends ServiceProvider
{
	public $driver;

	public function __construct(Config $config)
	{
		$this->driver = $config->get('database.default');
	}

	public function register()
	{
		$this->commands([
			LaravelAdapter::class,
		]);

		$this->app->bind(DatabasePort::class, function ($app) {
			match ($this->driver) {
				'sqlsrv' => new MsSqlServerAdapter(),
				default =>  throw new \Exception("Unsupported DB Driver: {$this->driver}")
			};
		});
	}
}
