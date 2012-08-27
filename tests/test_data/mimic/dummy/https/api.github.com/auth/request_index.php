<?php
return array (
  0 => array(
    'method' => '*',
    'headers' => array(
      'accept' => 'application/json',
      'content-type' => 'application/json',
	  'authorization' => new Mimic_Request_Wildcard_Require,
	  'content-length' => new Mimic_Request_Wildcard_Require,
    ),
    'query' => array(),
    'response' => array(
      'status' => 200,
      'headers' => array(),
      'body_file' => NULL,
    ),
  ),
);