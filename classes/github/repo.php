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
		'url' => null,
		'html_url' => null,
		'clone_url' => null,
		'git_url' => null,
		'ssh_url' => null,
		'svn_url' => null,
		'owner' => 'Github_User',
		'name' => null,
		'description' => null,
		'homepage' => null,
		'language' => null,
		'private' => null,
		'fork' => null,
		'forks' => null,
		'watchers' => null,
		'size' => null,
		'master_branch' => null,
		'open_issues' => null,
		'pushed_at' => 'Github_Timestamp',
		'created_at' => 'Github_Timestamp',
		'organization' => 'Github_Organization',
		'parent' => 'Github_Repo',
		'source' => 'Github_Repo',
		'has_issues' => null,
		'has_wiki' => null,
		'has_downloads' => null,
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
	public function get_issues($filter = 'assigned', $state = 'open', $labels = null,
			$sort = 'created', $direction = 'desc', DateTime $since = null)
	{
		$params = array(
			'filter' => $filter,
			'state' => $state,
			'sort' => $sort,
			'direction' => $direction			
		);
		
		if ($labels !== null)
		{
			$params['labels'] = $labels;
		}
		
		if ($since !== null)
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
	
	public function create_milestone()
	{
		
	}
	
	public function get_contributors()
	{
		
	}
	
	public function get_languages()
	{
		
	}
	
	public function get_teams()
	{
		
	}
	
	public function get_tags()
	{
		
	}
	
	public function get_branches()
	{
		
	}
	
}