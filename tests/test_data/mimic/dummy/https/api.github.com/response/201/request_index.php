<?php
return array (
  0 => array(
    'method' => 'POST',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(),
    'response' => array(
      'status' => 201,
      'headers' => array(),
      'body_file' => '../body.json',
    ),
  ),
);