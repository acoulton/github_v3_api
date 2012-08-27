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
      'per_page' => new Mimic_Request_Wildcard_Require,
    ),
    'response' => array(
      'status' => 200,
      'headers' => array(),
      'body_file' => 'response_0.json',
    ),
  )
);