<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Kohana_Exception class.  Extends the internal exception handler.
 * 
 * @extends Kohana_Kohana_Exception
 */
class Kohana_Exception extends Kohana_Kohana_Exception {

	/**
	 * Override the Kohana exception handler to intellegently route errors.
	 * 
	 * @access public
	 * @static
	 * @param Exception $e
	 * @param boolean $exit Exit on completion?
	 * @return void
	 */
	public static function handler(Exception $e, $exit = TRUE)
	{
		// Use the standard exception handler in development and from the command line
		if (Kohana::$environment === Kohana::DEVELOPMENT OR Kohana::$is_cli)
		{	
			// If we are returning from the exception handler (e.g. for tests), just echo the cli portion of Kohana's logic and return
			// We do this because Kohana uses exit() statements to control logic flow, which messes up unit tests
			if ( ! $exit AND Kohana::$is_cli)
			{
				$error = Kohana_Exception::text($e);
				
				// Just display the text of the exception
				echo "\n{$error}\n";

				return;
			}
			
			// Call the inbuilt exception handler
			parent::handler($e);
		}
		else
		{
			try
			{
				if (is_object(Kohana::$log))
				{
					// Add this exception to the log
					Kohana::$log->add(Log::ERROR, Kohana_Exception::text($e));
	
					$strace = Kohana_Exception::text($e)."\n--\n".$e->getTraceAsString();
					Kohana::$log->add(Log::STRACE, $strace);
	
					// Make sure the logs are written
					Kohana::$log->write();
				}
				
				$attributes = array(
                    'action'  => 500,
                    'message' => rawurlencode($e->getMessage())
                );
 
                if ($e instanceof HTTP_Exception)
                {
                    $attributes['action'] = $e->getCode();
                }
                
                // Ensure initial request exists
                if ( ! Request::$initial)
                {
                	Request::factory();
				}
 
                // Error sub-request.
                $response = Request::factory(Route::get('error')->uri($attributes))->execute();
               
                if ($exit)
                {
                	echo $response->send_headers()->body();
                }
                else
                {
                	return $response;
                }
            }
            catch (Exception $e)
            {
                // Clean the output buffer if one exists
                ob_get_level() AND ob_clean();
 
                // Display the exception text
                echo Kohana_Exception::text($e);
 
                // Exit with an error status
                if ($exit)
                {
                	exit(1);
                }
            }
        }
    }
}