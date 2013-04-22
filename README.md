# Javert

Authentication, Authorization, OAuth package


#### Installation

```php
<?php
return array(
	'providers' => 'Adamsmeat\Javert\JavertServiceProvider',
	'aliases' => array(
		'Helpers' => 'Adamsmeat\Javert\JavertFacade',
	)
);
?>

### Javert objects
- JavertResource 
	-(blog, forum, thread, user, etc.)
	-(resources will have attributes(id, ))

- JavertIntention (edit.article, update.profile, hide.page, etc.)
- JavertRestriction (restriction::(edit.article))
- JavertPrivilege (JavertPrivilege::can(edit.article))
- JavertUser ()
- JavertGroup (mod[editor, user, writer]) - collection of roles
- JavertRole (article.editor[edit, publish, unpublish])


JavertUser
	->privileges(
		// resource => array of privileges
		'article' => ['edit', 'publish', 'unpublish']	
	)


Samples:
function restrict($resource_intention) {
	Event::fire('restrict:route_access')
}

A user visits a route '/promos'.
The resource here is 'route'.
restrict(route_access)
how to bypass?
user should have privilege(sources could be User privileges, group)
function JavertUser::can('access_route', ['id'=>'1', 'route_path' => 'anasha/profile'])) {
	getPrivileges()
}