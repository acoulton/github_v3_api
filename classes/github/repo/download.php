<?php
/**
 * Holds the details of a repository package download
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
		'download_count' => NULL,
		'description' => NULL,
		'url' => NULL,
		'created_at' => 'Github_Timestamp',
		'size' => NULL,
		'content_type' => NULL,
		'name' => NULL,
		'id' => NULL,
		'html_url' => NULL,
	);
}
