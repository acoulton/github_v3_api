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
 * @property Github_Repo_Git_Commit $object 
 * @property string $ref 
 * @property string $url 
 */
class Github_Repo_Git_Reference extends Github_Object
{
	protected $_fields = array (
		'object' => 'Github_Repo_Git_Commit',
		'ref' => NULL,
		'url' => NULL,
	);
}