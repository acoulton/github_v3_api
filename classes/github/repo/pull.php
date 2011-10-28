<?php
/**
 * Holds the details of a pull request on a repository
 * 
 * @property string $url The API URL for this object
 * @property string $html_url The HTML page for this pull request
 * @property string $diff_url The URL for this pull request as a diff
 * @property string $patch_url The URL for this pull request as a patch
 * @property string $issue_url The URL for the issue related to this pull
 * @property integer $number The pull request number
 * @property string $state The current state (open|closed)
 * @property string $title The title of the pull request
 * @property string $body The body text of the pull request (in Github Flavoured Markdown)
 * @property Github_Timestamp $created_at When the pull request was created
 * @property Github_Timestamp $updated_at When the pull request was updated
 * @property Github_Timestamp $closed_at When the pull request was closed
 * @property Github_Timestamp $merged_at When the pull request was merged
 * @property boolean $merged If the pull request has been merged
 * @property boolean $mergeable If the pull request is mergeable
 * @property Github_User $merged_by The user that merged the pull
 * @property integer $comments The number of code (pull) comments
 * @property integer $commits The number of commits
 * @property integer $additions The number of additions
 * @property integer $deletions The number of deletions
 * @property integer $changed_files The number of changed files
 * @property Github_Repo_Pull_Branch $head Details of what is to be pulled
 * @property Github_Repo_Pull_Branch $base Details of what to merge onto
 */
class Github_Repo_Pull extends Github_Object
{      
   /**
    * Constants for pull request state
    */
   const STATE_OPEN = 'open';
   const STATE_CLOSED = 'closed';
   
   /**
    * The related issue, returned by [Github_Repo_Pull::get_issue]
    * @var Github_Repo_Issue
    */
   protected $_issue = NULL;

   protected $_fields = array(
       'url' => NULL,
       'html_url' => NULL,
       'diff_url' => NULL,
       'patch_url' => NULL,
       'issue_url' => NULL,
       'number' => NULL,
       'state' => NULL,
       'title' => NULL,
       'body' => NULL,
       'created_at' => 'Github_Timestamp',
       'updated_at' => 'Github_Timestamp',
       'closed_at' => 'Github_Timestamp',
       'merged_at' => 'Github_Timestamp',
       'merged' => NULL,
       'mergeable' => NULL,
       'merged_by' => 'Github_User',
       'comments' => NULL,
       'commits' => NULL,
       'additions' => NULL,
       'deletions' => NULL,
       'changed_files' => NULL,
       'head' => 'Github_Repo_Pull_Branch',
       'base' => 'Github_Repo_Pull_Branch',
       );
   
   /**
    * Returns the details of the related issue - lazily loaded in case only the
    * comments are required
    * 
    * @return Github_Repo_Issue
    */
   public function get_issue()
   {
	   if ( ! $this->_issue)
	   {
		   $this->_issue = new Github_Repo_Issue(
				   $this->_github,
				   array(
					   'url' => preg_replace(
							'_https://github.com/([^/]+)/([^/]+)/issues/([0-9]+)_',
							'https://api.github.com/repos/$1/$2/issues/$3',
							$this->issue_url),
				   ));
	   }
	   
	   return $this->_issue;
   }
   
   public function get_commits()
   {
	   
   }
   
   public function get_files()
   {
	   
   }
   
   public function get_file_comments()
   {
	   
   }
   
   public function add_file_comment()
   {
	   
   }
   
   public function merge_pull($commit_message)
   {
	   
   }
   
   /**
    * Simple comments on pull requests are actually comments on their related
    * issue - proxy method here for ease.
    * 
    * @param string $body_text 
    */
   public function add_simple_comment($body_text)
   {
	   $this->get_issue()->add_comment($body_text);
   }
   
   /**
    * Some API responses nest a pull request object with only the HTML url and
    * no API url - Github_Repo_Pull may need to convert to avoid an infinite loop
    * when trying to load the rest of the object's data.
    * 
    * Other fields are handled by [Github_Object::__get].
    * 
    * @param string $field 
    * @return mixed
    */
   public function __get($field)
   {
	   // Find the API URL by parsing the html_url
	   if (($field === 'url') AND ( ! isset ($this->_data['url'])))
	   {
		   if (isset($this->_data['html_url']))
		   {
			   $this->_data['url'] = preg_replace(
					   '_https://github.com/([^/]+)/([^/]+)/pull/([0-9]+)_',
					   'https://api.github.com/repos/$1/$2/pulls/$3',
					   $this->_data['html_url']);
			   return $this->_data['url'];
		   }
	   }
	   
	   return parent::__get($field);
   }
           
}