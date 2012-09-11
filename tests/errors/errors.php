<?php

/**
 * Tests the errors module
 *
 * @group kohana-errors
 */
Class KohanaErrorsTest extends Unittest_TestCase
{

	/**
	 * Stores the previous environment state
	 * 
	 * @var string
	 * @access private
	 */
	protected $_old_environment;

	/**
	 * Set environment to production.  We only want to run tests against production.
	 * 
	 * @access public
	 * @return void
	 */
	function setUp()
	{
		parent::setUp();
		$this->_old_environment = Kohana::$environment;
		Kohana::$environment = Kohana::PRODUCTION;
	}
	
	/**
	 * Reset environment variable for other tests
	 * 
	 * @access public
	 * @return void
	 */
	function tearDown()
	{
		Kohana::$environment = $this->_old_environment;
		parent::tearDown();
	}
	
	/**
	 * Ensure the error route exists.
	 * 
	 * @access public
	 * @return void
	 */
	function test_error_route_exists()
	{
		$this->assertInstanceOf('Route',Route::get('error'));
	}
	
	/**
	 * Provides a set of uris to test the error controller against
	 * 
	 * @access public
	 * @return void
	 */
	function provider_error_route()
	{
		return array(
			array('error/401/This%20Is%20a%20401%20Error', 401, '401'),
			array('error/404/This%20Is%20a%20404%20Error', 404, '404'),
			array('error/500/This%20Is%20a%20500%20Error', 500, '500'),
			array('error/503/This%20Is%20a%20503%20Error', 503, '503'),
			array('error/505/This%20Is%20a%20505%20Error', 505, 'generic'), // Probably aren't catching a 505 error
		);
	}
	
	/**
	 * Tests the error route.  Cannot be the initial request
	 * 
	 * @dataProvider provider_error_route
	 * @access public
	 * @param string $uri
	 * @param int $expected
	 * @param string $action
	 * @return void
	 */
	function test_error_route($uri,$status,$action)
	{	
		$this->setEnvironment(array(
			'Kohana::$is_cli' => FALSE,
			'Request::$initial' => Request::factory(),
		));

		$request = Request::factory($uri);
		$response = $request->execute();
		
		$this->assertSame($response->status(), $status);
		$this->assertSame($request->action(), $action);
		
	}
	
	/**
	 * Ensure that the error route is only accessible internally.
	 * Should trigger a 404 if it is not the initial request
	 * 
	 * @access public
	 * @return void
	 
	 */
	function test_error_route_not_accessible()
	{
		$this->setEnvironment(array(
			'Kohana::$is_cli' => FALSE,
			'Request::$initial' => NULL,
		));
		
		$request = Request::factory('error/500/SomeErrorMessage');
		
		$this->assertTrue($request->is_initial());
		
		$response = $request->execute();
		
		$this->assertSame($response->status(), 404);
	}
	
	/**
	 * Data for the test_exception_handler test case
	 * 
	 * @access public
	 * @return array
	 */
	function provider_exception_handler()
	{
		return array(
			array('HTTP_Exception_404', 404 ),
			array('HTTP_Exception_500', 500 ),
			array('HTTP_Exception_503', 503 ),
			array('Kohana_Exception', 500),
			array('ErrorException', 500),
			array('Exception', 500),
		);	
	}
	
	/**
	 * Test the exception handler.
	 * 
	 * @dataProvider provider_exception_handler
	 * @access public
	 * @param string $exception exception class
	 * @param int $expected status code
	 * @return void
	 */
	function test_exception_handler($exception, $expected)
	{
		$this->setEnvironment(array(
			'Kohana::$is_cli' => FALSE,
			'Request::$initial' => NULL,
			
		));
		
		$e = new $exception('foobar');
		
		$response = Kohana_Exception::handler($e, TRUE);
		
		$this->assertSame($response->status(), $expected);
	}
	
	/**
	 * Tests throwing an exception in CLI
	 * 
	 * @access public
	 * @return void
	 */
	function test_exception_handler_cli()
	{
		
		$this->setEnvironment(array(
			'Kohana::$is_cli' => TRUE,
			'Request::$initial' => NULL,
			
		));
		
		$e = new Kohana_Exception("foobar");
		$error = Kohana_Exception::text($e);
		
		$this->expectOutputString("\n{$error}\n");
		
		Kohana_Exception::handler($e, TRUE);
	}
	
	/**
	 * Provides a set of URIs to test against
	 * 
	 * @access public
	 * @return void
	 */
	function provider_uris()
	{
		return array(
			array('SomeFakeController/SomeFakeAction', 404),
			array('errors-tests/SomeFakeAction', 404),
			array('errors-tests/phperror', 500),
			array('errors-tests/servererror', 500),
			array('errors-tests/accessdenied', 401),
			array('errors-tests/hiddenaction', 404),
			array('errors-tests/specialerror', 505),
			array('errors-tests/generalex', 500),
		);
	}
	
	/**
	 * Create a fake route and test some uris.
	 * 
	 * @dataProvider provider_uris
	 * @access public
	 * @param string $uri
	 * @param int $expected expected status code
	 * @return void
	 */
	function test_uris($uri, $expected)
	{		
		$this->setEnvironment(array(
			'Kohana::$is_cli' => FALSE,
			'Request::$initial' => NULL,
		));
		
		$route = new Route('errors-tests/<action>');
		
		$route->defaults(array(
			'controller' => 'KohanaErrorsTest',
			'action'     => 'index',
		));
		
		try
		{
			Request::factory($uri, NULL, array($route))->execute();
		}
		catch(Exception $e)
		{
			$response = Kohana_Exception::handler($e, TRUE);
			$this->assertSame($response->status(), $expected);
		}
		
	}

}


/**
 * Dummy controller to test URIs
 * 
 * @extends Controller
 */
class Controller_KohanaErrorsTest extends Controller
{
	public function action_phperror()
	{
		include 'fake_file.php';
	}
	
	public function action_generalex()
	{
		throw new Exception('foobar');
	}
	
	public function action_servererror()
	{
		throw new HTTP_Exception_500('Internal Server Error');
	}
	
	public function action_accessdenied()
	{
		throw new HTTP_Exception_401('Access Denied');
	}
	
	public function action_hiddenaction()
	{
		throw new HTTP_Exception_404('Hidden');
	}
	
	public function action_specialerror()
	{
		throw new HTTP_Exception_505('likely unhandled special case');
	}
} 