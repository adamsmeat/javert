<?php


/*
class _User extends Pimple{}
class _ResourceDefinition extends Pimple{
	public function __construct(){
		return [
			['id' => 1, 'name' => 'article','type' => 'multi', 'doables' => 'create, edit, publish, unpublish'],
			['id' => 2, 'name' => 'site_membership', 'type' => 'single', 'doables' => 'register']
		];
	}
}
class _Privilege extends Pimple {
	public function __construct(){
		return [
			['id' => 1, 'name' => 'edit_article[1]'],
			['id' => 2, 'name' => 'edit_article[2]'],
		];
	}
}

$_u = new _User;
$_r = new _ResourceDefinition;

// a resource can be a single entity, or multi, it will have attributes
$_u['privileges'] = array('access_route[1]', 'register_site');
Route::get('javert', function() use($_u)
{
	echo 'javert page';
	var_dump(array(
		$_u,
	));
});
*/

Route::get('javert', function()
{
	var_dump(App::make('javert'));
});