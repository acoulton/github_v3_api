<?php
/**
 * Top-level interface to the GitHub API, which encapsulates all HTTP request
 * processing and offers functions to retrieve specific top-level Github objects.
 * All objects returned from the Github class are lazily loaded.
 *
 *     $github = new Github;
 *     $user = $github->get_user()
 *				->load();
 *
 * @property integer $rate_limit The current API call rate limit (requests per hour)
 * @property integer $rate_limit_remaining The number of requests remaining before the rate limit is exceeded
 */
class Github
{
	public static $base_url = 'https://api.github.com/';

	protected $_rate_limit = NULL;
	protected $_rate_limit_remaining = NULL;

	protected $_auth_mode = NULL;
	protected $_auth_credential = NULL;

	protected $_response_headers = NULL;

    protected $_repos = array();

	protected $_default_expect_status = array(
			Request::GET => '200',
			Request::POST => '201',
			Request::PUT => '200',
			'PATCH' => '200',
			Request::DELETE => '204',
			Request::HEAD => '200'
			);

	/**
	 * Accessor for read-only public properties
	 * @param string $property
	 * @return mixed
	 */
	public function __get($property)
	{
		switch ($property)
		{
			case 'rate_limit':
			case 'rate_limit_remaining':
				$property = "_$property";
				return $this->$property;
		}
		throw new InvalidArgumentException("The class ".get_class($this)." does not have a property $property");
	}


	/**
	 * Internal method to get a new Request object, to allow extension for testability
	 * @param string $url
	 * @return Request
	 */
	protected function _new_request($url)
	{
		return Request::factory($url);
	}

	/**
	 * Wraps a raw call to the Github API, including setting up authentication
	 * and checking for a valid response. See also [Github::api_json] which adds
	 * a further layer to decode a JSON response to an array.
	 *
	 * If passed an array in $body and $content_type of application/json, will
	 * automatically convert to a json string.
	 *
	 * @param string $url The API URL - relative paths will be translated to fullly qualified
	 * @param string $method The request method - defaults to GET
	 * @param mixed $body Either an array or a string body
	 * @param array $options An options array, including request_content_type, response_content_type and expect_status
	 * @return Response
	 */
	public function api($url, $method = Request::GET,
			$body = NULL, $options = array())
	{
		if ($this->_rate_limit_remaining === '0')
		{
			throw new Github_Exception_RateLimitExceeded("Github API rate limit of :limit requests per hour has been exceeded - could not make request to :url",
					array(':limit'=>$this->_rate_limit,
						':url'=>$url));
		}

		// Convert to an absolute url if required
		if (strpos($url, '://') === FALSE)
		{
			if (substr($url,0,1) === '/')
			{
				$url = substr($url, 1);
			}
			$url = self::$base_url.$url;
		}

		$this->_response_headers = NULL;

		// Fill out the options
		$options = Arr::merge(
				array(
					'request_content_type' => 'application/json',
					'response_content_type' => 'application/json',
					'expect_status' => $this->_default_expect_status[$method]
				),
				$options);

		// Allow for multiple expected status values
		if ( ! is_array($options['expect_status']))
		{
			$options['expect_status'] = array($options['expect_status']);
		}

		$request_content_type = $options['request_content_type'];
		$response_content_type = $options['response_content_type'];

		// Create the request
		$request = $this->_new_request($url)
					->method($method);

		// Set up the body
		if (($request_content_type == 'application/json') AND is_array($body))
		{
			$request->body(json_encode($body));
		}
		else
		{
			$request->body($body);
		}

		// Set up headers
		$request->headers('Accept', $response_content_type);
		$request->headers('Content-type', $request_content_type);

		// Authenticate if credentials are set
		if ($this->_auth_mode !== NULL)
		{
			$request->headers('Authorization', "$this->_auth_mode $this->_auth_credential");
		}

		// Execute the request
		$response = $request->execute();
		$this->_response_headers = $response->headers();

		// Process the rate limit information
		$this->_rate_limit = $response->headers('X-RateLimit-Limit');
		$this->_rate_limit_remaining = $response->headers('X-RateLimit-Remaining');

		// Check for response status
		$status = $response->status();

		if ( ! in_array($status, $options['expect_status']))
		{
			switch ($status)
			{
				case '401':
					throw new Github_Exception_Unauthorized(
							"Not authorised to access :url - message :message",
							array(':url'=>$url,
								':message'=>$response->body()));
				default:
					throw new Github_Exception_BadHTTPResponse(
							"Unexpected :actual response from :url with message :message - expected a response in (:expected)",
						array(':actual'=>$status,
							':url'=>$url,
							':expected'=>implode(', ',$options['expect_status']),
							':message'=>$response->body()));
			}
		}

		return $response;

	}

	public function api_reset_rate_limit()
	{
		$this->_rate_limit = NULL;
		$this->_rate_limit_remaining = NULL;
	}

	public function api_authenticate_basic($user, $pwd)
	{
		$this->_auth_mode='Basic';
		$this->_auth_credential = base64_encode("$user:$pwd");
	}

	public function api_authenticate_oauth($token)
	{
		$this->_auth_mode='token';
		$this->_auth_credential = $token;
	}

	/**
	 * Sends an API call and decodes the result from JSON into an array
	 *
	 * @param string $url The API URL - relative paths will be translated to fullly qualified
	 * @param string $method The request method - defaults to GET
	 * @param mixed $body Either an array or a string body
	 * @param array $options An options array, including request_content_type, response_content_type and expect_status
	 * @return array
	 */
	public function api_json($url, $method = Request::GET,
			$body = NULL, $options = array())
	{
		$response = $this->api($url, $method, $body, $options);
		return json_decode($response->body(),TRUE);
	}

	/**
	 * Returns the response headers from the most recent API request -
	 * either the full set or, if a key is provided, a single header.
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function api_response_headers($key = NULL)
	{
		if ($key === NULL)
		{
			return $this->_response_headers;
		}
		else
		{
			return Arr::get($this->_response_headers, $key);
		}
	}

	/**
	 * Returns an individual repository object.
	 *
	 * @param string $username
	 * @param string $repo
	 * @return Github_Repo
	 */
	public function get_repo($username, $repo)
	{
        if ( ! isset($this->_repos["$username/$repo"]))
        {
            $this->_repos["$username/$repo"] = new Github_Repo($this,
					array('url'=>"repos/$username/$repo",
						'owner'=>$username,
						'name'=>$repo));
        }
        return $this->_repos["$username/$repo"];
    }

	/**
	 * Loads information for a specific user, or the current user if no username
	 * is passed in.
	 *
	 * @param string $user
	 * @return Github_User
	 */
	public function get_user($username = NULL)
	{
		$url = ($username === NULL) ? 'user' : "users/$username";

		return new Github_User(
				$this,
				array(
					'url'=> $url
				));
	}

}