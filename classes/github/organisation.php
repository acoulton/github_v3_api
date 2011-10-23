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
class Github_Organisation extends Github_Object
{
	protected $_fields = array (
		'type' => null,
		'following' => null,
		'email' => null,
		'avatar_url' => null,
		'public_gists' => null,
		'blog' => null,
		'url' => null,
		'login' => null,
		'created_at' => 'Github_Timestamp',
		'followers' => null,
		'name' => null,
		'public_repos' => null,
		'company' => null,
		'id' => null,
		'html_url' => null,
		'location' => null,
	);
}