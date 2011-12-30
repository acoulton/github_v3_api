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
abstract class Github_APITestBase extends Mimic_Unittest_Testcase
{
	public function setUp()
	{
		parent::setUp();

		// Use the mimic data files belonging to this module
		$path = realpath(__DIR__.'/../test_data/mimic/');
		$this->mimic->base_path($path);
		$this->mimic->enable_recording(TRUE);
		//$this->mimic->external_client('Request_Client_Curl');
	}


	/**
	 * Helper method to ease creation of a mock Github class complete with expected request and response
	 * @param string $response_body
	 * @param string $response_status
	 * @param array $response_headers
	 * @return Mock_Github
	 */
	protected function _prepare_github($response_body = NULL, $response_status = '200', $response_headers = array())
	{
		throw new Exception("Old test!");
	}

}