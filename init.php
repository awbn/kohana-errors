<?php defined('SYSPATH') or die('No direct script access.');

// Error Route for internal error requests
Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]{3}', 'message' => '.*'))
	->defaults(array(
		'controller' => 'error',
	));