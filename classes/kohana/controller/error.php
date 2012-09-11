<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Kohana_Controller_Error extends Controller_Page {

	public function before()
	{
	    parent::before();
	    
	    $this->template->content = View::factory('errors/default');
	 
	    $this->template->content->bind('title',$this->template->title);
	    $this->template->content->page = URL::site(rawurldecode(Request::initial()->uri()));
	 
	    // Internal requests only!
	    if ( ! $this->request->is_initial())
	    {
	        if ($message = rawurldecode($this->request->param('message')))
	        {
	            $this->template->content->message = $message;
	        }
	    }
	    else
	    {
	        $this->request->action('404');
	        $this->template->content->message = __("This page cannot be found");
	    }
	 
	    $this->response->status( (int) $this->request->action());
	    
	    // Route errors that don't have their own method to a generic error
	    if ( ! method_exists($this, 'action_'.$this->request->action()))
	    {
	    	$this->request->action('generic');
	    }
	}
	
	
	public function action_generic()
	{
		$this->template->title = __('Error');
	}
	
	/*
	 * Special case different HTTP error types
	 */

	public function action_401()
	{
		$this->template->title = __('Access Denied');
	}

	public function action_404()
	{
		$this->template->title = __('Not Found');
		
		// Force 404 response, as this can be a catch all
		$this->response->status(404);
		
		// Log broken internal links
		if (isset ($_SERVER['HTTP_REFERER']) AND strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) !== FALSE)
		{
    		$this->template->content->local = TRUE;
    		Kohana::$log->add(Log::NOTICE, "Broken Internal Link: ".$_SERVER['HTTP_REFERER']." :: ".$this->template->page);
    	}
	}

	public function action_500()
	{
		$this->template->title = __('Internal Server Error');
	}
	
	public function action_503()
	{
	    $this->template->title = __('Maintenance Mode');
	}

}