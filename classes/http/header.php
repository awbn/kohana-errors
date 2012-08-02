<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Header extends Kohana_HTTP_Header {
	
	protected function _send_headers_to_php(array $headers, $replace)
	{
		
		
		if (isset($_SERVER['stripct']) AND $_SERVER['stripct'] == "true")
		{
			//Strip content type header for cgi/fpm.  See e.g. http://www.magentocommerce.com/boards/viewthread/229253/#t383462
			if (in_array(strtolower(substr(php_sapi_name(), 0, 3)), array('cgi', 'fpm')))
	        {
			
				foreach($headers as $key => $header)
				{
					 // parse name
		             if (!$pos = strpos($header, ':'))
		             	continue;
		                        
		             $name = strtolower(substr($header, 0, $pos));
				
		             if ($name == "content-type")
		             	unset($headers[$key]);
				
				}
			}
        }
        
        return parent::_send_headers_to_php($headers, $replace);
	
	}
	
}