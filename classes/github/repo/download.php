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
 * @property integer $download_count 
 * @property string $description 
 * @property string $url 
 * @property Github_Timestamp $created_at 
 * @property integer $size 
 * @property string $content_type 
 * @property string $name 
 * @property integer $id 
 * @property string $html_url 
 */
class Github_Repo_Download extends Github_Object
{
	protected $_fields = array (
		'download_count' => null,
		'description' => null,
		'url' => null,
		'created_at' => 'Github_Timestamp',
		'size' => null,
		'content_type' => null,
		'name' => null,
		'id' => null,
		'html_url' => null,
	);
}