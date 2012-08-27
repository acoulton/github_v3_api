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
		  'link' => '<https://api.github.com/repos?page=3&per_page=30>; rel="next"',
	  ),
      'body_file' => NULL,
    ),
  ),
);