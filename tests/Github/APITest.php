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
class Github_APITest extends Github_APITestBase
{
	protected $_mimic_default_scenario = 'github';

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_only_valid_properties_are_available()
	{
		$github = new Github();
		$this->assertEquals('Not This!', $github->a_foo_thing);
	}

	/**
	 * @return array
	 */
	public function provider_should_make_relative_url_absolute()
	{
		return array(
			array('/users/acoulton',Github::$base_url . 'users/acoulton'),
			array('https://api.github.com/users/acoulton', 'https://api.github.com/users/acoulton'));
	}

	/**
	 * @dataProvider provider_should_make_relative_url_absolute
	 */
	public function test_should_make_relative_url_absolute($api_url, $expected)
	{
		$github = new Github();
		$github->api($api_url);
		$this->assertMimicLastRequestURL($expected);
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
			array('GET', TRUE, "200", TRUE),
			array('POST', TRUE, "201", TRUE),
			array('PUT', "404", "404", TRUE),
			// Should throw exception
			array('GET', TRUE, "404", FALSE),
			array('POST', "200", "201", FALSE),
			array('PUT', "200", "500", FALSE),
			array('GET', array('200','202'), '200', TRUE),
			array('GET', array('200','202'), '202', TRUE),
			array('GET', array('200','202'), '404', FALSE),
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
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Setup a non-default expected status if required
		if ($expect_status !== TRUE)
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
			$github = new Github;
			$github->api('/response/'.$fake_status, $request_method, NULL, $options);
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
				$this->assertContains('/response', $msg);
				$this->assertContains('gh_response_string', $msg);
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
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test the request handling
		$github = new Github;

		$github->api('/body', 'POST', $api_body, array('request_content_type'=>$request_content_type));

		$this->assertMimicLastRequestBody($expect_request_body);
		$this->assertMimicLastRequestHeader('Content-Type', $request_content_type);
	}

	public function test_can_specify_response_content_type()
	{
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test the request handling
		$github = new Github();
		$github->api('/body', 'GET', NULL, array('response_content_type'=>'application/dummy.content'));

		// Have to do this from the headers - Kohana request only parses incoming Accept header from $_SERVER
		$this->assertMimicLastRequestHeader('Accept', 'application/dummy.content');
	}

	public function provider_can_specify_request_method()
	{
		return array(
			array('GET',200),
			array('POST',201),
			array('PUT',200),
			array('PATCH',200),
			array('DELETE',204)
		);
	}

	/**
	 * @dataProvider provider_can_specify_request_method
	 * @param string $method
	 * @param string $response_status
	 */
	public function test_can_specify_request_method($method, $response_status)
	{
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test request handling
		$github = new Github;
		$github->api('/response/'.$response_status,$method);
		$this->assertMimicLastRequestMethod($method);
	}

	public function test_response_headers_are_available()
	{
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test header handling
		$github = new Github;

		// Should be NULL before a request
		$this->assertEquals(NULL, $github->api_response_headers());

		$github->api('/headers');

		// Should make the headers available after a request
		$headers = $github->api_response_headers();
		$this->assertEquals('test', $headers['X-test-header']);

		// And following a new request, headers should be reset
		$github->api('/response/200');
		$this->assertEquals(array(), $github->api_response_headers()->getArrayCopy());
	}

	public function test_rate_limit_information_available()
	{
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test request handling
		$github = new Github;

		$github->api('/limit/info');

		$this->assertEquals(5000, $github->rate_limit);
		$this->assertEquals(4966, $github->rate_limit_remaining);
	}

	public function test_rate_limit_blocks_further_requests()
	{
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test request handling
		$github = new Github;

		$github->api('/limit/reached');
		$this->assertEquals(0, $github->rate_limit_remaining);

		/*
		 * For the next attempted request, the API should throw an exception
		 * before trying to make a request.
		 */

		try
		{
			$github->api('/response/200');
		}
		catch (Github_Exception_RateLimitExceeded $e)
		{
			$this->assertMimicRequestCount(1);
			return $github;
		}

		$this->fail('Excpected Github_Exception_RateLimitExceeded was not thrown!');
	}

	/**
	 * Once the API has triggered the rate limit block, the user should
	 * be able to reset it. If the reset fails, this test would throw a
	 * Github_Exception_RateLimitExceeded
	 *
	 * @depends test_rate_limit_blocks_further_requests
	 * @param Github $github
	 */
	public function test_rate_limit_can_be_reset(Github $github)
	{
		// Setup for the fake requests and responses
		$this->mimic->load_scenario('dummy');
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);

		// Test handling
		$github->api_reset_rate_limit();
		$github->api('/response/200');

		// Test that a new request was made
		$this->assertMimicRequestCount(1);
	}

	public function test_no_authentication_by_default()
	{
		$github = $this->_prepare_github();
		$github->api('/dummy');

		$this->assertNull($github->_test_last_request->headers('Authorization'));
	}

	/**
	 * @expectedException Github_Exception_Unauthorized
	 */
	public function test_unauthorized_request_throws_exception()
	{
		$github = $this->_prepare_github(NULL, '401');
		$github->api('/dummy');
	}

	public function test_basic_authentication()
	{
		$github = $this->_prepare_github();
		$github->api_authenticate_basic('test','pwd');
		$github->api('/dummy');

		$this->assertEquals('Basic '.base64_encode('test:pwd'), $github->_test_last_request->headers('Authorization'));
	}

	public function test_oauth_authentication()
	{
		$github = $this->_prepare_github();
		$github->api_authenticate_oauth('foo');
		$github->api('/dummy');

		$this->assertEquals('token foo', $github->_test_last_request->headers('Authorization'));
	}

	public function test_individual_response_headers_available()
	{
		$github = $this->_prepare_github(NULL,200,array('Test-foo'=>'ok'));
		$github->api('dummy');

		$this->assertEquals('ok', $github->api_response_headers('Test-foo'));
		$this->assertNull($github->api_response_headers('Test-bar'));
	}

}