kohana-errors
=============

* Include the following before Kohana::init()
* Wrap in an if statement to allow the app to override further.

if (!file_exists(APPPATH."classes/kohaha/exception".EXT))
{
	require_once MODPATH."errors/classes/kohana/exception".EXT;
}`