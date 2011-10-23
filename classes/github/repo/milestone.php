<?php 
/**
 * <describe the class here>
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
		'state' => null,
		'number' => null,
		'description' => null,
		'url' => null,
		'open_issues' => null,
		'created_at' => 'Github_Timestamp',
		'title' => null,
		'creator' => 'Github_User',
		'closed_issues' => null,
	);
}