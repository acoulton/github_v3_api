<?php
return array (
  0 => array(
    'method' => 'GET',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(),
    'response' => array(
      'status' => 200,
      'headers' => array(
		  'x-ratelimit-limit' => '5000',
		  'x-ratelimit-remaining' => '0',
	  ),
      'body_file' => NULL,
    ),
  ),
);