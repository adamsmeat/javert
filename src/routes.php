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
	$user = new User;
	$user->name = 'paolo';
	$user->password = 'paolo';

	$data = array(
		'user' => $user,
	);
	//var_dump($user);
	//var_dump(App::make('javert'));
	return View::make('layout', $data);
});


//




/*
|--------------------------------------------------------------------------
| Some routes used for links and demo
|--------------------------------------------------------------------------
|
*/


Route::get('terms', function()
{
    return View::make('page_terms');;
});

Route::get('privacy', function()
{
    return View::make('page_privacy');
});

Route::get('about', function()
{
    return View::make('page_about');
});

// register
Route::get('registration', array('as' => 'registration', 'before' => 'guest', function()
{
	$form = Config::get('helpers::form');
    return View::make('page_registration', compact('form'));
}));

// user
Route::get('dashboard', array('as' => 'dashboard', 'before' => 'auth', function()
{
	$form = Config::get('helpers::form');
	$form['attr']['url'] = 'dashboard';
    return View::make('page_dashboard', compact('form'));
}));



Route::post('registration', function()
{
    // Differentiate between oauth-based vs manual(password used) type login

    $rules = [
    	'username' => [
    		'required', 
    		'unique:users',
    		'min:6'
    	],
    	'email' => [
    		'required', 
    		'email', 
    		'unique:users'
    	],
    	'password' => [
    		'required', 
    		'confirmed',
    		'min:8' 
    	]
    ];


    $validator = Validator::make(Input::all(), $rules);


    if ($validator->fails())
    {
        return Redirect::route('registration')
        ->withErrors($validator)
        ->withInput() // Input flashing
        ;
    }

    // The user's credentials are valid...
    //$event = Event::fire('user.registration.success', array(Auth::user()));

    $user = new User;
	$user->username = Input::get('username');
	$user->email = Input::get('email');
	$user->password = Hash::make(Input::get('password'));
	$user->save();

	Auth::loginUsingId($user->id);    
    return Redirect::route('dashboard');
  
});

// authentication
Route::get('login', array('as' => 'login', 'before' => 'guest', function()
{
	$form = Config::get('helpers::form');
	$form['attr']['url'] = 'login';
    return View::make('page_login', compact('form'));
}));

Route::post('login', function()
{
    // Validation? Not in my quickstart!
    // No, but really, I'm a bad person for leaving that out
    // Differentiate between oauth-based vs manual(password used) type login
	if (Auth::attempt([
		'email' => Input::get('email'),
		'password' => Input::get('password')
	]))
	{
	    // The user's credentials are valid...
	    $event = Event::fire('user.manual.login.success', array(Auth::user()));
	    return Redirect::to('dashboard');
	}
	else return Redirect::route('login');    
});
Route::get('logout', ['as' => 'logout', 'before' => 'auth', function()
{
	Auth::logout();
    return Redirect::route('login');
}]);


// oauth
Route::get('social/{action?}', ['as' => 'hybridauth', function($action='')
{
	//return Response::make('whoami');

	// check URL segment
	if ($action == "auth") {
		// process authentication
		try {
			Hybrid_Endpoint::process();
		}
		catch (Exception $e) {
			// redirect back to http://URL/social/
			return Redirect::route('hybridauth');
		}
		return;
	}
	try {
		// create a HybridAuth object
		$socialAuth = new Hybrid_Auth(Config::get('hybridauth'));

		// authenticate with Facebook
		$provider = $socialAuth->authenticate("facebook");
		// fetch user profile
		$userProfile = $provider->getUserProfile();
	}
	catch(Exception $e) {
		// exception codes can be found on HybBridAuth's web site
		return "Error!";
	}
	// access user profile data
	echo "Connected with: <b>{$provider->id}</b><br />";
	echo "As: <b>{$userProfile->displayName}</b><br />";
	echo "<pre>" . print_r( $userProfile, true ) . "</pre><br />";

	// logout
	$provider->logout();	
}]);