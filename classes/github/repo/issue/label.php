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
 * @property string $url 
 * @property string $name 
 * @property string $color 
 */
class Github_Repo_Issue_Label extends Github_Object
{
	protected $_fields = array (
		'url' => NULL,
		'name' => NULL,
		'color' => NULL,
	);
}