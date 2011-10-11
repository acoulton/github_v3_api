<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests interactions with the Github API
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
class Github_APITest extends Unittest_TestCase
{
	
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
			

	/**	 
	 * @return array
	 */
	public function provider_should_make_relative_url_absolute()
	{
		return array(
			array('/my/mock/foo',Github::$base_url . '/my/mock/foo'),
			array('https://api.github.com/dummy', 'https://api.github.com/dummy'));
	}
	
	/**
	 * @dataProvider provider_should_make_relative_url_absolute
	 */
	public function test_should_make_relative_url_absolute($api_url, $expected)
	{
		$github = new Mock_Github();
		$github->api($api_url);
		$this->assertEquals($expected, $github->_test_last_request->uri());
	}
	
	/**
	 * Data provider for testing that the API verifies expected response status
	 * @return array
	 */
	public function provider_should_verify_response_status()
	{
		// Request method, expect status, actual status, should pass
		return array(	
			// Should all be OK
			array('GET', true, "200", true),
			array('POST', true, "201", true),
			array('PUT', "404", "404", true),
			// Should throw exception
			array('GET', true, "404", false),
			array('POST', "200", "201", false),
			array('PUT', "200", "500", false)
			);
	}
	
	/**
	 * @dataProvider provider_should_verify_response_status
	 * @param string $request_method
	 * @param string $default_status
	 * @param string $fake_status
	 * @param boolean $should_pass
	 */
	public function test_should_verify_response_status($request_method, $expect_status, $fake_status, $should_pass)
	{
		// Setup the stub Github object to return the given result
		$github = $this->_prepare_github('gh_error_string', $fake_status);
		
		// Setup a non-default expected status if required
		if ($expect_status !== true)
		{
			$options = array('expect_status'=>$expect_status);
		}
		else
		{
			$options = array();
		}
		
		// Test behaviour
		try
		{
			$github->api('/dummy', $request_method, null, $options);
		}
		catch (Github_Exception_BadHTTPResponse $e)
		{
			// Check if this request was supposed to throw exception			
			if ($should_pass)
			{
				// If not, bubble up
				throw $e;
			}
			else
			{
				// Test passed, verify message contents
				$msg = $e->getMessage();
				$this->assertContains('/dummy', $msg);
				$this->assertContains('gh_error_string', $msg);
				$this->assertContains($fake_status, $msg);
				return;
			}
		}
		
		// Should only reach here if no exception
		if ( ! $should_pass)
		{
			$this->fail('Expected Github_Exception_BadHTTPResponse was not thrown');
		}
	}
	
	public function provider_converts_request_body_by_content_type()
	{
		return array(
			array(
				array('convert'=>'me'), 'application/json', '{"convert":"me"}'),
			array(
				'leave_me', 'text/html', 'leave_me'
			),
		);
	}
	
	/**
	 * Tests that requests are correctly populated with content type and that 
	 * where appropriate data is automatically serialised or otherwise converted
	 * to the specified content type.
	 * 
	 * @dataProvider provider_converts_request_body_by_content_type
	 * @param mixed $api_body
	 * @param string $request_content_type
	 * @param string $expect_request_body 
	 */
	public function test_converts_request_body_by_content_type($api_body, $request_content_type, $expect_request_body)
	{
		$github = new Mock_Github;
		
		$github->api('/dummy', 'GET', $api_body, array('request_content_type'=>$request_content_type));
		
		$this->assertEquals($expect_request_body, $github->_test_last_request->body());
		$this->assertEquals($request_content_type, $github->_test_last_request->headers('Content-type'));
	}
	
	public function test_can_specify_response_content_type()
	{
		$github = new Mock_Github();
		$github->api('/dummy', 'GET', null, array('response_content_type'=>'application/dummy.content'));
		
		// Have to do this from the headers - Kohana request only parses incoming Accept header from $_SERVER
		$this->assertEquals($github->_test_last_request->headers('Accept'), 'application/dummy.content');
	}

}

/**
 * The Github class is tightly coupled with Request, because of the way this exists as a static factory pattern in Kohana. Therefore we mock the Githu
 * class to allow us to intercept the Request object and provide mock responses.
 */
class Mock_Github extends Github
{
	/**
	 * A reference to the most recent request
	 * @var Request
	 */
	public $_test_last_request = null;
	
	/**
	 * Data to be passed to the next API call
	 * @var array
	 */
	protected $_test_response_data = array(
		'body' => null,
		'status' => '200',
		'headers' => array());
	
	/**
	 * Prepares the mock API to return a response
	 * @param string $response_body
	 * @param string $response_status
	 * @param array $response_headers 
	 */
	public function _test_prepare_response($response_body = null, $response_status = '200', $response_headers = array())
	{
		$this->_test_response_data['body'] = $response_body;
		$this->_test_response_data['status'] = $response_status;
		$this->_test_response_data['headers'] = $response_headers;
	}
	
	/**
	 * Injects a request mock into the API execution to allow testing of the request/response flow without interacting with Gith
	 * @param string $url
	 * @return Request 
	 */
	protected function _new_request($url)
	{
		// Mock a request
		$this->_test_last_request = PHPUnit_Framework_MockObject_Generator::getMock(
          'Request',
          array('execute'),
          array($url));
		
		// Setup a response object
		$response = $this->_test_last_request->create_response();
		$response->body($this->_test_response_data['body']);
		$response->status($this->_test_response_data['status']);
		$response->headers($this->_test_response_data['headers']);
		
		// Configure the request to return the response
		$this->_test_last_request->expects(new PHPUnit_Framework_MockObject_Matcher_InvokedCount(1))
				->method('execute')
				->will(new PHPUnit_Framework_MockObject_Stub_Return($response));
		
		// And pass the request object back into the API class
		return $this->_test_last_request;		
	}
	
}