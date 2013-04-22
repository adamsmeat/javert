<?php namespace Adamsmeat\Javert;

use Illuminate\Support\ServiceProvider;
use Adamsmeat\Javert\JavertManager;

class JavertServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('adamsmeat/javert');
		require __DIR__.'/../../routes.php';		
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['javert'] = $this->app->share(function($app)
        {
			return new JavertManager();
        });	
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('javert');
	}

}