kohana-errors
=============

## Getting Started

* Enable the module in your bootstrap
* Add the following code before Kohana::init()

	if (!file_exists(APPPATH."classes/kohaha/exception".EXT))
	{
		require_once MODPATH."errors/classes/kohana/exception".EXT;
	}

* Add ERRORS = TRUE in your Kohana::init() call 

## Usage
* This module relies on a web template controller (Controller_Web) to provide a friendly way to present errors
** A very basic one is stubbed out with the expectation that the application will override it
* In CLI and non-production environments, the standard Kohana exception handler will be used

## Notes

* The exception handler is initilized as soon as Kohana::init is called, which happens before module init.
* The handler is wrapped in a check that will not load it if an application-level handler is defined instead.
* Do not rely on the View::factory->__toString() magic method
** If there is a fatal error in the view Kohana calls into Kohana::exception_handler directly without actually throwing an exception
** This ensures that __toString() returns a string, but at the expense of preventing the exception handler from correctly setting the http return status
** On systems using FastCGI/FPM, this will also trigger a duplicate content-type header error preventing apache from correctly handling the response
** Good practice to reply on View::factory()->render() instead :)

## License

This is licensed under the [same license as Kohana](http://kohanaframework.org/license).