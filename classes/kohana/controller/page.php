<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Basic template controller for use with error pages
 * Should be superseded by the application template
 * 
 * @abstract
 * @extends Controller_Template
 */
abstract class Kohana_Controller_Page extends Controller_Template {

	public  $template 	= 'templates/html';
			
	public function before()
	{
		parent::before();
		
		$this->template->title = '';
		$this->template->content = View::factory();
	}
	
}