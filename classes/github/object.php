<?php
/**
 * Github_Object provides a base class for all core API classes, implementing
 * a standard approach to:
 * 
 *   * Lazy loading of objects (objects can be partially populated with data from
 *     previous API calls.
 *   * Creating and updating existing objects
 *   * Loading related object collections
 *   * Converting object data to arrays
 */
abstract class Github_Object
{
	/**
	 * Reference to the central Github instance - used for API calls
	 * @var Github
	 */
    protected $_github = null;
	
	/**
	 * The current data of the class, only including fields that are loaded
	 * @var array
	 */
    protected $_data = array();
	
	/**
	 * Fields that have been modified since loading from the API
	 * @var array
	 */
	protected $_modified = array();
	
	/**
	 * Metadata defining what fields this object can have and whether they are
	 * other object references or scalar values.
	 * @var array
	 */
    protected $_fields = array();
	
	/**
	 * Whether the class has been loaded from Github
	 * @var boolean
	 */
    protected $_loaded = false;
	
	/**
	 * The name of the default field, to be populated if the class has been
	 * created with a single scalar data value instead of an array
	 * @var string
	 */
	protected $_default_field = null;
    
	/**
	 * Setup the new instance, and store a reference to the Github dependency
	 * for future use with API calls.
	 * 
	 * @param Github $github
	 * @param array $data 
	 */
    public function __construct(Github $github, $data)
    {
        $this->_github = $github;
		$this->reload_data($data);
    }
	
	/**
	 * Clears all modified data and stores the provided data in the instance.
	 * Automatically creates and populates referenced objects where these are
	 * defined in the class metadata.
	 * 
	 * @param array $data 
	 */
	public function reload_data($data)
	{
		// Clear existing storage
		$this->_data = array();
		$this->_modified = array();
		
		// If not passed an array of data, apply the value to the default field
		if (! is_array($data))
		{
			if ($this->_default_field !== null)
			{
				$data = array($this->_default_field => $data);
			}
			else
			{
				//EXCEPTION
				print_r(get_class($this));
				print_r($data);
				exit("bad data");
			}
		}

		// Load the data values into each field
        foreach ($this->_fields as $field=>$type)
        {			
            if (array_key_exists($field, $data))
            {
                if (($type === null) OR ($type === true))
                {
					// Store a scalar value
                    $this->_data[$field] = $data[$field];
                }
				else
				{
					if ($data[$field] === null)
					{
						$this->_data[$field] = null;
					}
					else
					{
						// Create and populate the appropriate child class
						$this->_data[$field] = new $type($this->_github, $data[$field]);
					}
                }
            }
        }
	}
    
	/**
	 * Loads a collection of resources related to a current object (eg commits,
	 * pull requests, etc) and populates their data.
	 * 
	 * @param string $collection_url The url for the collection - relative to the current item
	 * @param string $item_class The class to create for sub-items
	 * @param array $args Any GET arguments to filter the collection with
	 * @return array
	 */
    protected function _api_fetch_collection($collection_url, $item_class, $args = array())
    {
        $url = $this->url . $collection_url . '?' . http_build_query($args);
        
        $api_collection = $this->_github->api_json($url);
        
        $results = array();
        foreach ($api_collection as $api_item)
        {
            $object = new $item_class($this->_github, $api_item);            
            $results[] = $object;
        }
        
        return $results;        
    }
	
	/**
	 * Creates a new item in a collection
	 * @param string $collection_url The base URL for the collection
	 * @param string $item_class The class of object to create with the new item
	 * @param array $data The data values to send
	 * @return Github_Object
	 */
	protected function _api_new_child($collection_url, $item_class, $data = array())
	{
		$new_data = $this->_github->api_json(
				$this->url . $collection_url,
				Request::POST,
				$data);
		
		return new $item_class($this->_github, $data);
	}
	
	/**
	 * Loads the object's data from the Github API - overwriting any changes 
	 * made to the class
	 * 
	 * @return Github_Object 
	 */
	public function load()
	{
		$api_data = $this->_github->api_json($this->url);
		$this->reload_data($api_data);
		$this->_loaded = true;
		return $this;
	}
    
	/**
	 * Returns the data of an object (and any child objects) as an array, with
	 * all values converted to strings or arrays.
	 * 
	 * @return array
	 */
	public function as_array()
	{
		$result = array();
		foreach ($this->_data as $field => $value)
		{
			if ($value instanceof Github_Object)
			{
				$value = $value->as_array();
			}
			elseif (is_object($value))
			{
				$value = (string) $value;
			}
			
			$result[$field] = $value;
		}
		return $result;
	}
    
	/**
	 * Magic property handler - returns field values, autoloading the object if
	 * the required field has not yet been loaded.
	 * @param string $field
	 * @return mixed 
	 */
    public function __get($field)
    {
		// Validate that the requested field exists
        if ( ! array_key_exists($field, $this->_fields))
        {
            throw new Github_Exception_InvalidProperty(
					"No such property :field in :class",
					array(':field'=>$field,
						  ':class' => get_class($this)));
        }
        
        // Check that the field exists
        if ( ! array_key_exists($field, $this->_data))
		{
			// If not loaded, load from github
			if ( ! $this->_loaded)
			{
				// Cannot load the url field!
				if ($field === 'url')
				{
					throw new Github_Exception_MissingURL(
							"Could not lazily load property :property from class :class without an explicit url",
							array(':class'=>get_class($this),
								':property'=>$field));
				}
				$this->load();
			}
			else
			{
				// Exception if class is loaded and field not present
				throw new Github_Exception_MissingProperty(
						"Property :property missing from :class loaded from :url",
						array(':class'=>get_class($this),
							':property'=>$field,
							':url'=>$this->_data['url']));
			}
		}
		
        // Return the field's value
        return $this->_data[$field];        
    }
	
	/**
	 * Magic property handler - sets field values if they are writeable through
	 * the API and marks them as modified
	 * 
	 * @param type $field
	 * @param type $value 
	 */
	public function __set($field, $value)
	{
		// Validate that the requested field exists
        if ( ! array_key_exists($field, $this->_fields))
        {
            throw new Github_Exception_InvalidProperty(
					"No such property :field in :class",
					array(':field'=>$field,
						  ':class' => get_class($this)));
        }
		
		// Check that the field is writeable
		if ( $this->_fields[$field] !== true)
		{
			throw new Github_Exception_ReadOnlyProperty(
					"Readonly property :property in :class could not be set to :value",
					array(
						':property' => $field,
						':class' => get_class($this),
						':value' => $value
					));
		}
		
		// Check if the value has changed
		if (array_key_exists($field, $this->_data)
				AND ($this->_data[$field] === $value))
		{
			return;
		}
		
		// Set the value
		$this->_data[$field] = $value;
		$this->_modified[] = $field;
	}
	
	/**
	 * Permanently deletes an item from Github
	 */
	public function delete()
	{
		$this->_github->api($this->url, Request::DELETE);
		$this->_data = array();
		$this->_loaded = false;
	}
	
	/**
	 * A pre-save hook to transform any data (for example to replace an object
	 * with a primary key) prior to submission back to the API
	 * 
	 * @param array $data 
	 */
	protected function _transform_modified(&$data)
	{		
	}
	
	/**
	 * Saves a changed object back to Github
	 * 
	 * @return boolean
	 */
	public function save()
	{
		if ( ! $this->_modified)
		{
			return false;
		}
		
		// Get data and transform if required
		$data = Arr::extract($this->_data, $this->_modified);
		$this->_transform_modified($data);
		
		// Submit the patch request
		$new_data = $this->_github->api_json(
				$this->url, 
				'PATCH', 
				$data);
		
		// Update our own data and mark as loaded
		$this->reload_data($new_data);
		$this->_loaded = true;
		return true;
	}
	
	/**
	 * Whether the class has been fully loaded or is a proxy
	 * @return boolean
	 */
	public function loaded()
	{
		return $this->_loaded;
	}
	
	/**
	 * Whether any of the class values have been changed
	 * @return boolean
	 */
	public function modified()
	{
		return $this->_modified ? true : false;
	}
	
	/**
	 * The names of the modified fields
	 * @return array
	 */
	public function modified_fields()
	{
		return $this->_modified;
	}
	
}