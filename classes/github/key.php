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
 * @property string $key 
 * @property string $title 
 * @property integer $id 
 */
class Github_Key extends Github_Object
{
	protected $_fields = array (
		'url' => NULL,
		'key' => NULL,
		'title' => NULL,
		'id' => NULL,
	);
}