<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests the behaviour of the Github_Collection object - which provides
 * support for paginated and parameterised resultsets
 *
 * @group github
 * @group github.base
 *
 * @package    GithubAPIv3
 * @category   Tests
 * @author     Andrew Coulton
 * @copyright  (c) 2011 Andrew Coulton
 * @license    http://kohanaframework.org/license
 */
class Github_CollectionTest extends Github_APITestBase
{

	public function test_does_not_automatically_submit_request()
	{
		$github = $this->_prepare_github();		
		$collection = new Github_Collection($github, 'dummy', 'Github_Object');
		$this->assertNull($github->_test_last_request);	
	}
	
	public function test_adds_parameters_to_url()
	{
		$github = $this->_prepare_github();
		$collection = new Github_Collection($github, 'dummy', 'Github_Object', array('test'=>'testing','t2'=>1));
		$collection->load();
		
		$this->assertEquals('testing', $github->_test_last_request->query('test'));
		$this->assertEquals('1', $github->_test_last_request->query('t2'));
	}
	
	public function test_loads_page_count_from_headers()
	{
		$github = $this->_prepare_github(null,200,
				array('Link'=>'<https://api.github.com/repos?page=2&per_page=30>; rel="next", <https://api.github.com/repos?page=4&per_page=30>; rel="last"));'));
		
		$collection = new Github_Collection($github, 'dummy', 'Github_Object');
		$collection->load();
		
		$this->assertEquals(4, $collection->page_count());		
	}
	
	protected function _prepare_simple_collection()
	{
		$github = $this->_prepare_github(array(
					array('url' => 'dummy/1'),
					array('url' => 'dummy/2'),
					array('url' => 'dummy/3')));
		
		$collection = new Github_Collection_PublishTestData($github, 'dummy', 'Github_Object_CollectionTest');
		
		return $collection;
	}
	
	public function test_loads_first_page()
	{
		$collection = $this->_prepare_simple_collection();
		
		$collection->load();
		
		// Access the internal storage for testing
		$internal_items = $collection->_get_items();
		$this->assertEquals(3, count($internal_items));
		$this->assertEquals('dummy/3', $internal_items[2]['url']);				
	}
	
	public function test_reserves_space_for_earlier_pages_when_loading()
	{
		$collection = $this->_prepare_simple_collection();
		$collection->load(2);	
		
		// Access the internal storage for testing
		$internal_items = $collection->_get_items();
		$this->assertEquals(33, count($internal_items));
		$this->assertEquals('dummy/1', $internal_items[30]['url']);		
		
		return $collection;
	}
	
	/**
	 * @depends test_reserves_space_for_earlier_pages_when_loading
	 */
	public function test_loads_earlier_pages_to_reserved_space($collection)
	{
		$collection->load(1);
		
		$internal_items = $collection->_get_items();		
		$this->assertEquals(33, count($internal_items));
		$this->assertEquals('dummy/1', $internal_items[0]['url']);
		$this->assertEquals('dummy/2', $internal_items[31]['url']);
		
		return $collection;
	}
	
	/**
	 *
	 * @depends test_loads_earlier_pages_to_reserved_space
	 */
	public function test_new_pages_do_not_affect_existing($collection)
	{
		$collection->load(3);
		
		$internal_items = $collection->_get_items();
		
		$this->assertEquals(63, count($internal_items));
		$this->assertEquals('dummy/1', $internal_items[0]['url']);
		$this->assertEquals('dummy/2', $internal_items[31]['url']);
		$this->assertEquals('dummy/3', $internal_items[62]['url']);
	}	
	
}

class Github_Collection_PublishTestData extends Github_Collection
{
	public function _get_items()
	{
		return $this->_items;
	}
}

class Github_Object_CollectionTest extends Github_Object
{
	protected $_fields = array(
		'url' => null);
}