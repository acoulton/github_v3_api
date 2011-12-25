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
 * @property string $type 
 * @property integer $following 
 * @property string $email 
 * @property string $avatar_url 
 * @property integer $public_gists 
 * @property string $blog 
 * @property string $url 
 * @property string $login 
 * @property Github_Timestamp $created_at 
 * @property integer $followers 
 * @property string $name 
 * @property integer $public_repos 
 * @property string $company 
 * @property integer $id 
 * @property string $html_url 
 * @property string $location 
 */
class Github_Organization extends Github_Object
{
	protected $_fields = array (
		'type' => NULL,
		'following' => NULL,
		'email' => NULL,
		'avatar_url' => NULL,
		'public_gists' => NULL,
		'blog' => NULL,
		'url' => NULL,
		'login' => NULL,
		'created_at' => 'Github_Timestamp',
		'followers' => NULL,
		'name' => NULL,
		'public_repos' => NULL,
		'company' => NULL,
		'id' => NULL,
		'html_url' => NULL,
		'location' => NULL,
	);
}