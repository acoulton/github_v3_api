<?php
/**
 * Holds details of an individual event
 * @property string $url The API url for this object
 * @property Github_User $actor The user that generated the event
 * @property string $event The type of event
 * @property string $commit_id The ID of the related commit, if applicable
 * @property Github_Timestamp $created_at When the event happened
 */
class Github_Repo_Issue_Event extends Github_Object
{
	protected $_fields = array(
		'url' => NULL,
		'actor' => 'Github_User',
		'event' => NULL,
		'commit_id' => NULL,
		'created_at' => 'Github_Timestamp',
	);
}