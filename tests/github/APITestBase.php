<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Provides base functionality for tests that require interaction with
 * the Github API
 *
 * @group github
 * @group github.base
 *
 * @package    GithubAPIv3
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2011 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
abstract class Github_APITestBase extends Unittest_TestCase
{
	protected static $old_modules = array();

	/**
	 * Setups the filesystem for test view files
	 *
	 * @return null
	 */
	public static function setupBeforeClass()
	{
		self::$old_modules = Kohana::modules();

		$new_modules = self::$old_modules+array(
			'test_apitest_data' => realpath(__DIR__.'/../test_data/')
		);
		Kohana::modules($new_modules);
	}

	/**
	 * Restores the module list
	 *
	 * @return null
	 */
	public static function teardownAfterClass()
	{
		Kohana::modules(self::$old_modules);
	}
	
	
	/**
	 * Helper method to ease creation of a mock Github class complete with expected request and response
	 * @param string $response_body
	 * @param string $response_status
	 * @param array $response_headers
	 * @return Mock_Github 
	 */
	protected function _prepare_github($response_body = null, $response_status = '200', $response_headers = array())
	{
		$github = new Mock_Github();
		$github->_test_prepare_response($response_body, $response_status, $response_headers);
		
		return $github;		
	}
	
}