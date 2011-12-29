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
 * @property array $parents 
 * @property string $sha 
 * @property string $message 
 * @property string $url 
 * @property Github_Repo_Git_Tree $tree 
 * @property Github_Repo_Git_Author $author 
 * @property Github_Repo_Git_Author $committer 
 */
class Github_Repo_Git_Commit extends Github_Object
{
	protected $_fields = array (
		'parents' => 'array',
		'sha' => NULL,
		'message' => NULL,
		'url' => NULL,
		'tree' => 'Github_Repo_Git_Tree',
		'author' => 'Github_Repo_Git_Author',
		'committer' => 'Github_Repo_Git_Author',
	);
}