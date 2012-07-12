<?php
/**
 * Holds the details of a repository branch
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
class Github_Repo_Branch extends Github_Object
{
	protected $_fields = array (
		'commit' => 'Github_Repo_Git_Commit',
		'name' => NULL
	);
}
