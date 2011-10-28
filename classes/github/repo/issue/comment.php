<?php

class Github_Repo_Issue_Comment extends Github_Object
{
	protected $_fields = array(
		'url' => NULL,
		'body' => NULL,
		'user' => 'Github_User',
		'created_at' => 'Github_Timestamp',
		'updated_at' => 'Github_Timestamp',
	);
	
}