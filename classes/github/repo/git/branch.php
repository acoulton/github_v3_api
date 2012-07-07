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
 * @property Github_Repo_Git_Commit $commit
 * @property string $name
 */
class Github_Repo_Git_Branch extends Github_Object
{
	protected $_fields = array (
		'commit' => 'Github_Repo_Git_Commit',
		'name' => NULL
	);
}
