<?php
/**
 * Holds the details of a repository tag
 *
 * @package    GithubAPIv3
 * @category   Objects
 * @author     Andrew Coulton
 * @copyright  (c) 2011 Andrew Coulton
 * @license    http://kohanaframework.org/license
 *
 * @property string $zipball_url
 * @property string $tarball_url
 * @property Github_Repo_Git_Commit $commit
 * @property string $name
 */
class Github_Repo_Tag extends Github_Object
{
	protected $_fields = array (
		'zipball_url' => NULL,
		'tarball_url' => NULL,
		'commit' => 'Github_Repo_Git_Commit',
		'name' => NULL,
	);
}
