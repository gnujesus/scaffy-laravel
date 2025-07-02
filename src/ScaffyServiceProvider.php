<?php

namespace Gnu\Scaffy\Laravel;


use Illuminate\Support\ServiceProvider;
use Gnu\Scaffy\Laravel\LaravelAdapter;

class ScaffyServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->commands([
			LaravelAdapter::class,
		]);
	}
}
