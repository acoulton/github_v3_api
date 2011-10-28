<?php

class Github_Collection implements ArrayAccess, Iterator
{
	protected $_github = NULL;
	protected $_url = NULL;
	protected $_item_class = NULL;
	protected $_params = array();
	protected $_page_count = NULL;
	protected $_page_size = 30;
	protected $_items = array();
	protected $_current_item = 0;

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
	 * Returns the base URL of this collection
	 * @return string
	 */
	public function base_url()
	{
		return $this->_url;
	}
	
	/**
	 * Assembles filter and other params with the base URL and 
	 * pagination data to return the appropriate API URL
	 * 
	 * @param integer $page
	 * @param integer $per_page
	 * @return string 
	 */
	protected function _build_page_url($page, $per_page = NULL)
	{
		if ($per_page === NULL)
		{
			$per_page = $this->_page_size;
		}
		
		$params = Arr::merge($this->_params, array(
			'page' => (int) $page,
			'per_page' => (int) $per_page
 		));
		
		$url = $this->_url.'?'.http_build_query($params);
		return $url;
 	}
	
	/**
	 * Loads a page of results into the collection
	 * 
	 * @param integer $page The page number to load
	 * @return Github_Collection
	 */
	public function load($page = NULL)
	{	
		if ($page === NULL)
		{
			$page = 1;
		}
		
		$url = $this->_build_page_url($page);
		
		// Issue the API request
		$collection_data = $this->_github->api_json($url);
		
		// Parse the Link header to detect pagination data
		$this->_page_count = $this->_parse_api_link_header();
		
		if ( ! $collection_data)
		{
			return;
		}
		
		// Set aside empty space for preceding result pages if required
		$start_index = ($page - 1) * $this->_page_size;
		$data_size = count($this->_items);
		for ($i = $data_size; $i < $start_index; $i++)
		{
			$this->_items[$i] = NULL;
		}
		
		// Store the returned data in the result array
		foreach ($collection_data as $key => $item)
		{
			$this->_items[$key + $start_index] = $item;
		}			
		
		return $this;
	}
	
	/**
	 * Parses the Link: header to extract the number of pages
	 * of data available for this collection
	 * @param string $header	  
	 * @return integer
	 */
	protected function _parse_api_link_header()
	{
		$header = $this->_github->api_response_headers('Link');
		
		if ( ! $header)
		{
			return 1;
		}
		
		// Extract the "last" page link
		if ( ! preg_match('_<(https://[^>]+)>; rel="last"_', $header, $matches))
		{
			throw new InvalidArgumentException("Could not parse the link header '$header'");
		}
		
		// Parse the link url to get the page count
		parse_str(parse_url($matches[1],PHP_URL_QUERY), $link_params);		
		return $link_params['page'];
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
	public function page_size($size = NULL)
	{
		if ($size === NULL)
		{
			return $this->_page_size;
		}
		else
		{
			$this->_page_size = $size;
		}
	}
	
	/**
	 * Returns the number of items in the collection - if the
	 * last page of results has not been loaded then this method
	 * will load the count and reserve space. If the last page of
	 * results has been loaded then the size of the collection is
	 * returned.
	 */
	public function count()
	{
		/**
		 * - If nothing has been loaded, page_count will be NULL
		 * - If some pages have been loaded, there will be some items in the 
		 *    collection, but not the full number of pages worth
		 * e.g. With 1 page of 30 items, min count is 1
		 *		With 10 pages of 30 items, min count is 271
		 */
		$expect_min_count = (($this->_page_count - 1) * $this->_page_size) + 1;
		if (($this->_page_count === NULL)
			OR count($this->_items) < $expect_min_count)
		{
			// Get the number of items with a HEAD request
			$this->_github->api(
					$this->_build_page_url(1,1),
					Request::HEAD);
			$count = $this->_parse_api_link_header();
			
			// Store the number of pages and prepare the items array
			$this->_page_count = ceil($count / $this->_page_size);
			
			for ($i = count($this->_items); $i < $count; $i++)
			{
				$this->_items[$i] = NULL;
			}
						
						
		}		
		return count($this->_items);
	}
	
	/**
	 * Array Access methods
	 */
	
	/**
	 * Checks whether the offset is within range of the collection.
	 * 
	 * @param integer $offset 
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $offset < $this->count();
	}
	
	public function offsetGet($offset)
	{
		// If not loaded then lazily load this page
		if ( ! isset($this->_items[$offset]))
		{
			$this->load(1 + floor($offset/$this->_page_size));
		}
		
		if ( ! $this->_items[$offset] instanceof Github_Object)
		{
			$this->_items[$offset] = new $this->_item_class($this->_github, $this->_items[$offset]);
		}
		
		return $this->_items[$offset];
	}
	
	public function offsetSet($offset, $value)
	{
		throw new BadMethodCallException("Cannot set value of items - Github_Collection is a readonly set");
	}
	
	public function offsetUnset($offset)
	{
		throw new BadMethodCallException("Cannot unset items - Github_Collection is a readonly set");
	}
	
	/**
	 * Iterator interface
	 */
	
	public function current()
	{		
		// Use offsetGet so that always return an object	
		return $this->offsetGet($this->_current_item);
	}
	
	public function key()
	{
		return $this->_current_item;
	}
	
	public function next()
	{
		$this->_current_item++;
	}
	
	public function rewind()
	{
		$this->_current_item = 0;
	}
	
	public function valid()
	{
		return $this->_current_item < $this->count();
	}
}