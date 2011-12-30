<?php
return array (
  0 => array(
    'method' => 'HEAD',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
    ),
    'query' => array(
      'page' => '1',
      'per_page' => '1',
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(
		  'link' => '<https://api.github.com/repos?page=1&per_page=1>; rel="first", <https://api.github.com/repos?page=2&per_page=1>; rel="last"',
	  ),
      'body_file' => NULL,
    ),
  ),
  1 => array(
    'method' => 'GET',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
    ),
    'query' => array(
      'page' => '1',
      'per_page' => new Mimic_Request_Wildcard_Require,
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(),
      'body_file' => 'response_0.json',
    ),
  ),
  2 => array(
    'method' => 'GET',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
    ),
    'query' => array(
      'page' => '1',
      'per_page' => '30',
	  'test' => 'testing',
	  't2' => '1',
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(),
      'body_file' => 'response_0.json',
    ),
  ),
);