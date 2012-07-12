<?php
/**
 * Holds the details of a repository milestone
 *
 * @package    GithubAPIv3
 * @category   Objects
 * @author     Andrew Coulton
 * @copyright  (c) 2011 Andrew Coulton
 * @license    http://kohanaframework.org/license
 *
 * @property Github_Timestamp $due_on
 * @property string $state
 * @property integer $number
 * @property string $description
 * @property string $url
 * @property integer $open_issues
 * @property Github_Timestamp $created_at
 * @property string $title
 * @property Github_User $creator
 * @property integer $closed_issues
 */
class Github_Repo_Milestone extends Github_Object
{
	protected $_fields = array (
		'due_on' => 'Github_Timestamp',
		'state' => NULL,
		'number' => NULL,
		'description' => NULL,
		'url' => NULL,
		'open_issues' => NULL,
		'created_at' => 'Github_Timestamp',
		'title' => NULL,
		'creator' => 'Github_User',
		'closed_issues' => NULL,
	);
}
