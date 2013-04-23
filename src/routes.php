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

/*
|--------------------------------------------------------------------------
| Some routes used for links and demo
|--------------------------------------------------------------------------
|
*/

Route::get('terms', function()
{
    return View::make('page.terms');
});

Route::get('privacy', function()
{
    return View::make('page.privacy');
});

Route::get('about', function()
{
    return View::make('page.about');
});


// user
Route::get('dashboard', array('as' => 'dashboard', 'before' => 'auth', function()
{
	$form = Config::get('helpers::form');
	$form['attr']['url'] = 'dashboard/profile';
    return View::make('page.dashboard', compact('form'));
}));
Route::post('dashboard/profile', array('before' => 'auth', function()
{
    $rules = array(
    	'full_name' => array(
    		'required', 
    		'min:6'
    	),
    	'email' => array(
    		'required', 
    		'email',
    		'unique:users,email,'.Auth::user()->id
    	),    	
    );

    $validator = Validator::make(Input::all(), $rules);

    if ($validator->fails())
    {
        return Redirect::to('dashboard#profile')
        ->withErrors($validator)
        ;
    }
    else {
	    $user = Auth::user();	
	    $user->full_name = Input::get('full_name');
	    $user->email = Input::get('email');
	    $user->save();

		Session::flash('alert', array(
			'type' => 'success',
			'text' => 'Profile updated successfully.'
		));
		return Redirect::to('dashboard#profile');	     	
    }
}));

// register
Route::get('registration', array('as' => 'registration', 'before' => 'guest', function()
{
	$form = Config::get('helpers::form');
    return View::make('page.registration', compact('form'));
}));

Route::post('registration', function()
{
    // Differentiate between oauth-based vs manual(password used) type login

    $rules = [
    	'email' => [
    		'required', 
    		'email', 
    		'unique:users'
    	],
    	'password' => [
    		'required', 
    		//'confirmed',
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
	$user->first_name = Input::get('first_name');
	$user->last_name = Input::get('last_name');
	$user->email = Input::get('email');
	$user->password = Hash::make(Input::get('password'));
	$user->group = Input::get('group');
	$user->save();

	Auth::loginUsingId($user->id);    
    return Redirect::route('dashboard');
  
});

// authentication
Route::get('login', array('as' => 'login', 'before' => 'guest', function()
{
	$form = Config::get('helpers::form');
    return View::make('page.login', compact('form'));
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
	else return Redirect::route('login')->withErrors(array('text' => 'Wrong combination.'));    
});

Route::get('password/reset', array('before' => 'guest', function()
{
	$form = Config::get('helpers::form');
	$form['attr']['url'] = 'password/reset/checkmail';
    return View::make('page.reset', compact('form'));
}));

Route::get('password/update/{token}/{user_id}', function($token, $user_id)
{
	//var_dump($email, $token, $user = User::where('email', $email)->first(), $user->token);die;

	$user = User::where('id', $user_id)->first();

	if ($user->token == $token)
		Auth::login($user);

	// change token
	$user->token = md5(time());
	$user->save();

	return Redirect::to('password/new');
});

Route::get('password/new', array('before'=>'auth', function()
{
	$form = Config::get('helpers::form');
    return View::make('page.password_new', compact('form'));
}));

Route::post('password/new', array('before'=>'auth', function()
{
    $rules = array(
    	'password' => array(
    		'required', 
    		'confirmed',
    		'min:6'
    	),
    );

    $validator = Validator::make(Input::all(), $rules);

    if ($validator->fails())
    {
        return Redirect::to('password/new')
        ->withErrors($validator)
        ;
    }
    else {
	    $user = Auth::user();	
	    $user->password = 	Hash::make(Input::get('password'));
	    $user->save();
		Session::flash('alert', array(
			'type' => 'success',
			'text' => 'Password updated successfully.'
		));
		return Redirect::to('dashboard');	     	
    }
}));

Route::post('password/reset/checkmail', array('before' => 'guest', function()
{
    $rules = array(
    	'email' => array(
    		'required', 
    		'email',
    		'exists:users',
    	),
    );

    $validator = Validator::make(Input::all(), $rules, $messages = array(
	    'email.exists'    => 'No account was found with the provided email.',
	));

    if ($validator->fails())
    {
        return Redirect::to('password/reset')
        ->withErrors($validator)
        ->withInput() // Input flashing
        ;
    }
    else {

		$user = User::where('email', '=', Input::get('email'))->first();
		// sendmail

		$data = array(
			'user' => $user,
			'token' => md5(time()),
		);

		//save new token
		$user->token = $data['token'];
		$user->save();

		Mail::send('emails.auth.reset', $data, function($m) use($data)
		{
		    $m->to($data['user']['email'], $data['user']['full_name'])->subject('Password Reset: TextAManager.com');
		});

		Session::flash('alert', array(
			'type' => 'info',
			'text' => 'Please check your email for instructions.'
		));
		return Redirect::to('/');
    }   
	
   
}));

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


Route::group(array('prefix' => '/', 'as'=>'page'), function()
{

	Route::get('terms', function()
	{
	    return View::make('page.terms');;
	});

	Route::get('privacy', function()
	{
	    return View::make('page.privacy');
	});

});