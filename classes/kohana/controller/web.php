<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Basic template controller for use with the error pages
 * Should be superseded by the application template
 *
 **/
abstract class Kohana_Controller_Web extends Controller_Template {

	public  $template 	= 'templates/html';
			
	public function before()
	{
		parent::before();
		
		$this->template->title = '';
		$this->template->content = '';
	}
	
}