<?php
return array (
  0 => array(
    'method' => 'GET',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(
      'page' => '1',
      'per_page' => '30',
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(
		  'link' => '<https://api.github.com/repos?page=2&per_page=30>; rel="next", <https://api.github.com/repos?page=3&per_page=30>; rel="last"',
	  ),
      'body_file' => '0-29.json',
    ),
  ),
  1 => array(
    'method' => 'GET',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(
      'page' => '2',
      'per_page' => '30',
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(
		  'link' => '<https://api.github.com/repos?page=1&per_page=30>; rel="first", <https://api.github.com/repos?page=1&per_page=30>; rel="prev", <https://api.github.com/repos?page=3&per_page=30>; rel="next", <https://api.github.com/repos?page=3&per_page=30>; rel="last"',
	  ),
      'body_file' => '30-59.json',
    ),
  ),
  2 => array(
    'method' => 'GET',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(
      'page' => '3',
      'per_page' => '30',
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(
		  'link' => '<https://api.github.com/repos?page=1&per_page=30>; rel="first", <https://api.github.com/repos?page=2&per_page=30>; rel="prev", <https://api.github.com/repos?page=3&per_page=30>; rel="last"',
	  ),
      'body_file' => '60-89.json',
    ),
  ),
  3 => array(
    'method' => 'HEAD',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(
      'page' => '1',
      'per_page' => '1',
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(
		  'link' => '<https://api.github.com/repos?page=1&per_page=1>; rel="first", <https://api.github.com/repos?page=2&per_page=1>; rel="next", <https://api.github.com/repos?page=90&per_page=1>; rel="last"',
	  ),
      'body_file' => NULL,
    ),
  ),



);