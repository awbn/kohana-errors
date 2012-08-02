<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Header extends Kohana_HTTP_Header {
	
	/* See http://www.magentocommerce.com/boards/viewthread/229253/#t383462 */
	protected function _send_headers_to_php(array $headers, $replace)
	{
		//Strip content type header for cgi-fpm
		if (in_array(substr(php_sapi_name(), 0, 3), array('cgi', 'fpm')))
        {
        	// remove duplicate headers
            $to_remove = array('status', 'content-type');

            // already sent headers
            $sent = array();
            foreach (headers_list() as $header)
            {
                // parse name
                if (!$pos = strpos($header, ':'))
                        continue;
                        
                $sent[strtolower(substr($header, 0, $pos))] = true;
            }

            $queued = array();

            //Loop through and remove
        	foreach($headers as $key => $header)
        	{ 
               // parse name
                if (!$pos = strpos($header, ':'))
                        continue;
                        
                $name = strtolower(substr($header, 0, $pos));

                if (in_array($name, $to_remove))
                {
	                // check sent headers
                    if (in_array($name,$sent) AND $sent[$name])
                    {
                     	unset($headers[$key]);
                        continue;
                    }
                    
                    //Check queued headers
                    if (!is_null($existing = $queued[$name]))
                    {
                    	$headers[$existing] = $header;
                        unset($headers[$key]);
                    }
                    else
                    {
                    	$queued[$name] = $key;
                    }
                }
             }
        }
        
        return parent::_send_headers_to_php($headers, $replace);
	
	}
	
}