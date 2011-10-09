<?php
/**
 * Holds details of a Github issue
 * 
 * @property string $url The API url for the object
 * @property string $html_url The HTML url for the issue
 * @property integer $number The issue number
 * @property string $state The current state of the issue
 * @property string $title The title of the issue
 * @property string $body The body text of the issue
 * @property Github_User $user The user who created the issue
 * @property array $labels An array of labels to apply
 * @property Github_User $assignee The user this issue is assigned to
 * @property Github_Repo_Milestone $milestone The milestone this issue is assigned to
 * @property integer $comments The number of comments
 * @property Github_Repo_Pull $pull_request The pull request linked to this issue
 * @property Github_Timestamp $closed_at When this issue was closed
 * @property Github_Timestamp $created_at When this issue was created
 * @property Github_Timestamp $updated_at When this issue was updated
 * 
 */
class Github_Repo_Issue extends Github_Object
{
	protected $_fields = array(
		'url' => null,
		'html_url' => null,
		'number' => null,
		'state' => null,
		'title' => null,
		'body' => null,
		'user' => 'Github_User',
		'labels' => null, // This is actually an array
		'assignee' => 'Github_User',
		'milestone' => 'Github_Repo_Milestone',
		'comments' => null,
		'pull_request' => 'Github_Repo_Pull',
		'closed_at' => 'Github_Timestamp',
		'created_at' => 'Github_Timestamp',
		'updated_at' => 'Github_Timestamp',
	);
	
	/**
	 * List the comments on the issue
	 * @return array
	 */
	public function get_comments()
	{
		return $this->_api_fetch_collection(
				'/comments', 
				'Github_Repo_Issue_Comment');
	}
	
	/**
	 * Adds a new simple comment to the issue
	 * 
	 * @param string $body_text The body text of the comment (in Github Flavored Markdown)
	 * @return Github_Repo_Issue_Comment
	 */	
	public function add_comment($body_text)
	{
		return $this->_api_new_child(
				'/comments',
				'Github_Repo_Issue_Comment',
				array('body'=>$body_text));
	}
	
	/**
	 * Returns a list of all the events related to an issue
	 * @return array
	 */
	public function get_events()
	{
		return $this->_api_fetch_collection(
				'/events',
				'Github_Repo_Issue_Event');				
	}
	
	/**
	 * Adds either a single label or a collection of labels to the issue
	 * @param mixed $labels 
	 */
	public function add_labels($labels)	
	{
		// Convert a string to a single-element array
		if ( ! is_array($labels))
		{
			$labels = array($labels);
		}
		
		// Add the labels to the issue
		$this->_data['labels'] = $this->_github->api_json(
				$this->url . '/labels', 
				Request::POST, 
				$labels,
				array(
					'expect_status' => '200',
				));
		
		return $this->labels;
	}
	
	/**
	 * Removes a named label from the issue, returning the updated array of
	 * issue labels.
	 * 
	 * @param string $label_name
	 * @return array 
	 */
	public function remove_label($label_name)
	{	
		// Check that the label exists
		$label_found = false;
		foreach ($this->labels as $label)
		{
			if ($label['name'] == $label_name)
			{
				$label_found = true;
				break;
			}
		}
		if ($label_found === false)
		{
			throw new Exception("Unknown label '$label_name'!");
		}
		
		// Remove the label from the issue		
		$this->_data['labels'] = $this->_github->api_json(
				$this->url . "/labels/$label_name",
				Request::DELETE,
				null,
				array('expect_status'=>'200'));
		
		return $this->labels;
	}
	
	/**
	 * Clears all labels from the issue	 
	 */
	public function clear_labels()
	{
		$this->_github->api($this->url . '/labels',
				Request::DELETE);
	}
	
	/**
	 * Transforms modified milestone, assignee and label data ready for submission
	 * to the API.
	 * 
	 * @param array $data	 
	 */
	protected function _transform_modified(&$data)
	{
		// Convert the milestone object to the milestone number
		if (isset($data['milestone']))
		{
			$data['milestone'] = $this->milestone->number;
		}
		
		// Convert the assignee object to the user login
		if (isset($data['assignee']))
		{
			$data['assignee'] = $this->assignee->login;
		}
		
		// Convert the labels array to an array of label names
		if (isset($data['labels']))
		{
			$data['labels'] = array();
			foreach ($this->labels as $label)
			{
				$data['labels'][] = $label;
			}
		}
	}

}