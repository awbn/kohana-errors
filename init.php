<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Ensure that errors are logged as exceptions 
 */
Kohana::$errors = TRUE;

// Error route for internal error requests
Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]{3}', 'message' => '.*'))
	->defaults(array(
		'controller' => 'error',
	));