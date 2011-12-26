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
 * @property string $permission 
 * @property integer $members_count 
 * @property string $url 
 * @property integer $repos_count 
 * @property string $name 
 * @property integer $id 
 */
class Github_Organization_Team extends Github_Object
{
	protected $_fields = array (
		'permission' => NULL,
		'members_count' => NULL,
		'url' => NULL,
		'repos_count' => NULL,
		'name' => NULL,
		'id' => NULL,
	);
}