<?php namespace Adamsmeat\Javert;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class JavertFacade extends LaravelFacade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'javert'; }

}