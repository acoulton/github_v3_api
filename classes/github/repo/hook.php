<?php
/**
 * Holds the details of a post-receive web or service hook for a repository
 *
 * @package    GithubAPIv3
 * @category   Objects
 * @author     Andrew Coulton
 * @copyright  (c) 2011 Andrew Coulton
 * @license    http://kohanaframework.org/license
 *
 * @property Github_Timestamp $updated_at
 * @property array $last_response
 * @property string $url
 * @property integer $active
 * @property Github_Timestamp $created_at
 * @property array $events
 * @property string $name
 * @property array $config
 * @property integer $id
 */
class Github_Repo_Hook extends Github_Object
{
	protected $_fields = array (
		'updated_at' => 'Github_Timestamp',
		'last_response' => NULL,
		'url' => NULL,
		'active' => NULL,
		'created_at' => 'Github_Timestamp',
		'events' => NULL,
		'name' => NULL,
		'config' => NULL,
		'id' => NULL,
	);
}
