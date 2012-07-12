<?php

/**
 * Holds details of an individual repository on GitHub
 * 
 * @property string $url The API base url for this repository
 * @property string $html_url The URL for browsing this repository as HTML
 * @property string $clone_url The HTTPS URL for cloning this repository
 * @property string $git_url The git read-only URL for cloning this repository
 * @property string $ssh_url The SSH read/write URL for this repository
 * @property string $svn_url The URL to access this repository via SVN
 * @property Github_User $owner The user that owns the repository
 * @property string $name The repository's name
 * @property string $description A description of the repository
 * @property string $homepage The (possibly external) homepage for the project
 * @property string $language The language this project is written in
 * @property boolean $private Whether this is a private repo
 * @property boolean $fork Whether this is a fork of another repository
 * @property integer $forks The number of forks this repository has
 * @property integer $watchers The number of watchers this repository has
 * @property integer $size The size of the repository
 * @property string $master_branch The name of the master branch
 * @property integer $open_issues The number of open issues
 * @property Github_Timestamp $pushed_at The time this repository was last pushed to
 * @property Github_Timestamp $created_at The time this repository was created
 * @property Github_Organization $organization The organization the repository belongs to
 * @property Github_Repo $parent The parent repository (may be itself, if not a fork)
 * @property Github_Repo $source The original forked repository, or this repository
 * @property boolean $has_issues Whether or not issues are enabled
 * @property boolean $has_wiki Whether or not wiki is enabled
 * @property boolean $has_downloads Whether or not downloads are enabled
 */
class Github_Repo extends Github_Object
{
	protected $_fields = array(
		'url' => NULL,
		'html_url' => NULL,
		'clone_url' => NULL,
		'git_url' => NULL,
		'ssh_url' => NULL,
		'svn_url' => NULL,
		'owner' => 'Github_User',
		'name' => TRUE,
		'description' => TRUE,
		'homepage' => TRUE,
		'language' => NULL,
		'private' => TRUE,
		'fork' => NULL,
		'forks' => NULL,
		'watchers' => NULL,
		'size' => NULL,
		'master_branch' => NULL,
		'open_issues' => NULL,
		'pushed_at' => 'Github_Timestamp',
		'created_at' => 'Github_Timestamp',
		'organization' => 'Github_Organization',
		'parent' => 'Github_Repo',
		'source' => 'Github_Repo',
		'has_issues' => TRUE,
		'has_wiki' => TRUE,
		'has_downloads' => TRUE,
		);
	
	/**
	 * Fetch a list of pull requests on this repository
	 * 
	 *     $repo->get_pulls(Github_Repo_Pull::STATE_OPEN);
	 * 
	 * @param string $state Defaults to open if none provided
	 * @return Github_Collection
	 */
	public function get_pulls($state = Github_Repo_Pull::STATE_OPEN)
	{
		return $this->_api_fetch_collection(
			"pulls",				
			'Github_Repo_Pull',
			array('state' => $state));
	}
	
	/**
	 * Fetch a list of issues on the repository
	 * @return Github_Collection
	 */
	public function get_issues($filter = 'assigned', $state = 'open', $labels = NULL,
			$sort = 'created', $direction = 'desc', DateTime $since = NULL)
	{
		$params = array(
			'filter' => $filter,
			'state' => $state,
			'sort' => $sort,
			'direction' => $direction			
		);
		
		if ($labels !== NULL)
		{
			$params['labels'] = $labels;
		}
		
		if ($since !== NULL)
		{
			$params['since'] = $since->format('c');
		}
		
		return $this->_api_fetch_collection(
				"issues", 
				'Github_Repo_Issue',
				$params);
	}
	
	/**
	 * Fetch a list of events associated with the repository
	 * @return Github_Collection
	 */
	public function get_events()
	{
		return $this->_api_fetch_collection(
				"issues/events", 
				'Github_Repo_Issue_Event');
	}
	
	/**
	 * Fetch all issue labels associated with this repository
	 * @return Github_Collection
	 */
	public function get_issue_labels()
	{
		return $this->_api_fetch_collection(
				"labels", 
				'Github_Repo_Issue_Label');	
	}
	
	/**
	 * Returns all milestones associated with this repository
	 * 
	 * @param string $state Filter with state - open|closed
	 * @param string $sort Sort by - due_date|completeness
	 * @param string $direction Sort order - asc|desc
	 * @return Github_Collection 
	 */	
	public function get_milestones($state = 'open', $sort = 'due_date', $direction = 'desc')
	{
		return $this->_api_fetch_collection(
				"milestones", 
				'Github_Repo_Issue_Milestone',
				array(
					'state' => $state,
					'sort' => $sort,
					'direction' => $direction
				));
	}
		
	/**
	 * Returns the contributors who have been involved with this repository
	 * 
	 * @param boolean $anon Include anonymous contributors in results
	 * @return Github_Collection
	 */
	public function get_contributors($anon)
	{
		return $this->_api_fetch_collection('contributors', 'Github_User');
	}
	
	/**
	 * Returns an associative array of the languages that are featured in
	 * this repository, and the number of lines of code involved.
	 * 
	 * @return array
	 */
	public function get_languages()
	{
		return $this->_github->api_json($this->url.'/teams');
	}
	
	/**
	 * Returns the teams that are associated with this repository
	 * 
	 * @return Github_Collection
	 */
	public function get_teams()
	{
		return $this->_api_fetch_collection('teams', 'Github_Organization_Team');
	}
	
	
	/**
	 * Returns a collection of this repository's tags
	 * 
	 * @return Github_Collection
	 */
	public function get_tags()
	{
		return $this->_api_fetch_collection('tags', 'Github_Repo_Tag');
	}
	
	/**
	 * Returns the branches in the repository
	 * 
	 * @return Github_Collection
	 */
	public function get_branches()
	{
		return $this->_api_fetch_collection('branches', 'Github_Repo_Branch');
	}
	
	/**
	 * Returns a collection of Github_Users for the collaborators on the repo
	 * 
	 * @return Github_Collection
	 */
	public function get_collaborators()
	{
		return $this->_api_fetch_collection('collaborators', 'Github_User');
	}
	
	/**
	 * Checks to see if a user is a collaborator
	 * 
	 * @param Github_User|string $user  The user to check
	 * @return boolean
	 */
	public function is_collaborator($user)
	{
		if ($user instanceof Github_User)
		{
			$user = $user->login;
		}			
		
		$response = $this->_github->api($this->url."/collaborators/$user", Request::GET, NULL, array('expected_status'=>array(204,404)));
		
		return ($response->status() == '204');
	}
	
	/**
	 * Adds a user as a collaborator on the repository
	 * 
	 * @param Github_User|string $user The user to add
	 */
	public function add_collaborator($user)
	{
		if ($user instanceof Github_User)
		{
			$user = $user->login;
		}			
		
		$this->_github->api($this->url."/collaborators/$user", Request::PUT, NULL, array('expected_status'=>204));
	}
	
	/**
	 * Removes a user as a collaborator on the repository
	 * 
	 * @param Github_User|string $user The user to remove
	 */
	public function remove_collaborator($user)
	{
		if ($user instanceof Github_User)
		{
			$user = $user->login;
		}			
		
		$this->_github->api($this->url."/collaborators/$user", Request::DELETE);	
	}
	
}