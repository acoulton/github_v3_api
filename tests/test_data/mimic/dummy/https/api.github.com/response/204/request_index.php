<?php
return array (
  0 => array(
    'method' => 'DELETE',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(),
    'response' => array(
      'status' => 204,
      'headers' => array(),
      'body_file' => '../body.json',
    ),
  ),
);