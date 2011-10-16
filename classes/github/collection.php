<?php

class Github_Collection
{
	protected $_github = null;
	protected $_url = null;
	protected $_item_class = null;
	protected $_params = array();
	protected $_page_count = null;
	protected $_page_size = 30;
	protected $_items = array();

	/**
	 * Creates a new Github_Collection object
	 * @param Github $github
	 * @param string $collection_url
	 * @param string $item_class
	 * @param array $args 
	 */
	public function __construct(Github $github, $collection_url, $item_class, $params = array())
	{
		$this->_github = $github;
		$this->_url = $collection_url;
		$this->_item_class = $item_class;
		$this->_params = $params;
	}
	
	/**
	 * Loads a page of results into the collection
	 * 
	 * @param integer $page The page number to load
	 */
	public function load($page = null)
	{	
		if ($page === null)
		{
			$page = 1;
		}
		
		// Compose the URL complete with GET params
		$params = Arr::merge($this->_params, array(
			'page'=> (integer) $page));
		
		$url = $this->_url . '?' . http_build_query($params);		
		
		// Issue the API request
		$collection_data = $this->_github->api_json($url);
		
		// Parse the Link header to detect pagination data
		$this->_parse_link_header($this->_github->api_response_headers('Link'));
		
		if ( ! $collection_data)
		{
			return;
		}
		
		// Set aside empty space for preceding result pages if required
		$start_index = ($page - 1) * $this->_page_size;
		$data_size = count($this->_items);
		for ($i = $data_size; $i < $start_index; $i++)
		{
			$this->_items[$i] = null;
		}
		
		// Store the returned data in the result array
		foreach ($collection_data as $key => $item)
		{
			$this->_items[$key + $start_index] = $item;
		}				
	}
	
	/**
	 * Parses the Link: header to extract the number of pages
	 * of data available for this collection
	 * @param string $header	  
	 */
	protected function _parse_link_header($header)
	{
		if ( ! $header)
		{
			$this->_page_count = 1;
			return;
		}
		
		// Extract the "last" page link
		if (! preg_match('_<(https://[^>]+)>; rel="last"_', $header, $matches))
		{
			throw new InvalidArgumentException("Could not parse the link header '$header'");
		}
		
		// Parse the link url to get the page count
		parse_str(parse_url($matches[1],PHP_URL_QUERY), $link_params);		
		$this->_page_count = $link_params['page'];
	}
	
	/**
	 * Returns the number of pages of results
	 * @return integer
	 */
	public function page_count()
	{
		return $this->_page_count;
	}
	
	/**
	 * Returns or sets the number of pages of results
	 * @param integer $size
	 * @return integer 
	 */
	public function page_size($size = null)
	{
		if ($size === null)
		{
			return $this->_page_size;
		}
		else
		{
			$this->_page_size = $size;
		}
	}
	
}