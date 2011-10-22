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
	/**
	 * Builds a dummy collection data array
	 * @param integer $count Number of elements
	 * @return array 
	 */
	protected function _get_dummy_collection_data($count)
	{
		for ($i = 1; $i <= $count; $i++)
		{
			$result[] = array(
				'url' => "dummy/$i",
				'seq' => $i
			);
		}
		return $result;
	}
	
	/**
	 * Builds a link header for given types and numbering of pages
	 * @param array $pages
	 * @param integer $per_page
	 * @param string $uri
	 * @return array 
	 */
	protected function _get_link_header($pages, $per_page = 30, $uri = 'https://api.github.com/dummy')
	{		
		foreach ($pages as $page_type=>$page_num)
		{
			$result[] = "<$uri?page=$page_num&per_page=$per_page>; rel=\"$page_type\"";
		}
		return array('Link'=>implode(', ', $result));
	}
	
	/**
	 * Keeps the test methods DRY by standardising the setup of the 
	 * collection and github objects.
	 * 
	 * @param integer $count
	 * @param array $headers
	 * @param Mock_Github $github
	 * @return Github_Collection_PublishTestData 
	 */
	protected function _prepare_collection(&$github = null, $count=3, $headers = array(), $collection_params = array())
	{
		$github = $this->_prepare_github($this->_get_dummy_collection_data($count), 200, $headers);
		
		$collection = new Github_Collection_PublishTestData($github, 'dummy', 'Github_Object_CollectionTest', $collection_params);
				
		return $collection;
	}

	
	public function test_does_not_automatically_submit_request()
	{
		$this->_prepare_collection($github);
		$this->assertNull($github->_test_last_request);	
	}
	
	public function test_adds_parameters_to_url()
	{
		$collection = $this->_prepare_collection($github, 3, array(), 
				array('test'=>'testing', 't2'=>1))
				->load();
		
		$this->assertEquals('testing', $github->_test_last_request->query('test'));
		$this->assertEquals('1', $github->_test_last_request->query('t2'));
	}
	
	public function test_can_set_page_size()
	{
		$collection = $this->_prepare_collection($github);
		$collection->page_size(50);
		$collection->load();
		
		$this->assertEquals(50, $github->_test_last_request->query('per_page'));
		$this->assertEquals(50, $collection->page_size());
	}
	
	public function provider_loads_page_count_from_headers()
	{
		return array(
			array(1, $this->_get_dummy_collection_data(1), 200, array()),
			array(4, null, 200, 
				$this->_get_link_header(array('next'=>2, 'last'=>4))),
		);
	}
	
	/**
	 * @dataProvider provider_loads_page_count_from_headers
	 * @param integer $expect_pages
	 * @param array $response
	 * @param integer $status
	 * @param array $headers 
	 */
	public function test_loads_page_count_from_headers($expect_pages, $response, $status, $headers)
	{
		$github = $this->_prepare_github($response, $status, $headers);		
		$collection = new Github_Collection($github, 'dummy', 'Github_Object');
		$collection->load();
		
		$this->assertEquals($expect_pages, $collection->page_count());		
	}
	
	public function test_stores_single_page_result_count()
	{
		$collection = $this->_prepare_collection($github, 2)
						->load();
		
		$this->assertEquals(2, $collection->count());
		$this->assertEquals(1, $github->_test_request_count);
	}
	
	public function provider_provides_result_count()
	{
		return array(
			array(false, false, 10, 1, 'HEAD'),
			array(false, 1, 30, 1, 'GET'),
			array(true, false, 310, 1, 'HEAD'),
			array(true, 1, 310, 2, 'HEAD'),
			array(true, 9, 280, 2, 'HEAD'),
			// Here the last page of results is loaded, so no count request is required
			array(true, 10, 300, 1, 'GET')
		);
	}
	
	/**
	 * @dataProvider provider_provides_result_count
	 */
	public function test_provides_result_count($multi_page, $load_results, $expect_count, $expect_requests, $expect_method)
	{
		if ($multi_page)
		{
			$link = $this->_get_link_header(array('last'=>10));
		}
		else
		{
			$link = array();
		}		

		// Prepare for the GET request (This will not always be sent)
		$collection = $this->_prepare_collection($github, 30, $link);
		
		if ($load_results)
		{
			$collection->load($load_results);
		}
		
		// Prepare for a HEAD request
		$github->_test_prepare_response('*',null, 200,
				$this->_get_link_header(array('last'=>$expect_count)));
		
		// Validate the item count and request information
		$this->assertEquals($expect_count, $collection->count(), "Assert correct collection count");		
		$this->assertEquals($expect_requests, $github->_test_request_count, "Assert expected number of requests");
		$this->assertEquals($expect_method, $github->_test_last_request->method(), "Assert last request method");				
	}
		
	public function test_loads_first_page()
	{
		$collection = $this->_prepare_collection()
							->load();
		
		// Access the internal storage for testing
		$internal_items = $collection->_get_items();
		$this->assertEquals(3, count($internal_items));
		$this->assertEquals('dummy/3', $internal_items[2]['url']);				
	}
	
	public function test_reserves_space_for_earlier_pages_when_loading()
	{
		$collection = $this->_prepare_collection()
						->load(2);	
		
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
	
	public function test_arrayaccess_returns_objects()
	{
		$collection = $this->_prepare_collection()
						->load(1);
		
		$this->assertInstanceOf('Github_Object_CollectionTest', $collection[1]);			
		$this->assertEquals('dummy/2', $collection[1]->url);		
		return $collection;
	}
	
	/**
	 * @depends test_arrayaccess_returns_objects
	 */
	public function test_iterating_returns_objects($collection)
	{		
		foreach ($collection as $key => $object)
		{			
			$this->assertInstanceOf('Github_Object_CollectionTest', $object);
			$this->assertEquals('dummy/1', $object->url);
			$this->assertEquals('0', $key);
			break;
		}
		
		return $collection;
	}

	/**
	 * @depends test_iterating_returns_objects
	 */
	public function test_objects_are_only_created_once($collection)
	{
		$object = $collection[2];
		$object_2 = $collection[2];
		
		$this->assertEquals($object, $object_2);
		
		foreach ($collection as $key=>$object_3)
		{
			if ($key == 2)
			{
				$this->assertEquals($object, $object_3);
			}			
		}
	}
	
	public function test_should_lazy_load_when_iterating()
	{
		$collection = $this->_prepare_collection($github, 30, 
				$this->_get_link_header(array('last'=> 3)));
		/* @var $github Mock_Github */
		
		// Prepare for the HEAD item count query
		$github->_test_prepare_response('https://api.github.com/dummy?page=1&per_page=1', 
				null, 200 , 
				$this->_get_link_header(array('last'=> 90)));
		
		$i = 0;
		foreach ($collection as $key => $item) 
		{
			$this->assertEquals($i, $key, "Testing collection key");
			$expect_seq = ($i %30) + 1;
			$this->assertEquals($expect_seq, $item->seq, "Testing item sequence");
			$i++;
		}
		
		$this->assertEquals(90, $i, "Testing size of collection");
		$this->assertEquals(4, $github->_test_request_count, "Verifying number of requests");
	}
	
	public function provider_all_items_are_iterable()
	{
		return array(5, 2);
	}
	
	/**
	 * @dataProvider provider_all_items_are_iterable
	 * @param integer $count 
	 */
	public function test_all_items_are_iterable($count)
	{
		$collection = $this->_prepare_collection($github, $count)
						->load();
		
		$i = 0;
		foreach ($collection as $key => $item)
		{
			$this->assertEquals($i, $key);
			$i++;
		}
		$this->assertEquals($count, $i);
	}
	
	/**
	 * @dataProvider provider_all_items_are_iterable
	 */
	public function test_all_items_are_accessible($count)
	{
		
	}
	
	/**
	 * @depends test_arrayaccess_returns_objects
	 * @expectedException BadMethodCallException
	 */
	public function test_array_offsets_are_not_writeable($collection)
	{
		$collection[1] = 'foo';		
	}
	
	/**
	 * @depends test_arrayaccess_returns_objects
	 * @expectedException BadMethodCallException
	 */
	public function test_array_offsets_cannot_be_unset($collection)
	{
		unset($collection[0]);
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
		'url' => null,
		'seq' => null);
}