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
	protected $_mimic_default_scenario = 'dummy';

	public function setUp()
	{
		parent::setUp();

		// These tests all use dummy request recordings to isolate test functionality
		$this->mimic->enable_recording(FALSE);
		$this->mimic->enable_updating(FALSE);
	}

	public function test_does_not_automatically_submit_request()
	{
		$collection = new Github_Collection(new Github, '/collection/basic', 'Github_Object_CollectionTest');
		$this->assertMimicRequestCount(0);
	}

	public function test_adds_parameters_to_url()
	{
		$collection = new Github_Collection(new Github, '/collection/basic', 'Github_Object_CollectionTest',
				array('test'=>'testing', 't2'=>1));
		$collection->load();

		$this->assertMimicLastRequestQuery('test','testing');
		$this->assertMimicLastRequestQuery('t2','1');
	}

	public function test_can_set_page_size()
	{
		$collection = new Github_Collection(new Github, '/collection/basic', 'Github_Object_CollectionTest');
		$collection->page_size(50);
		$collection->load();

		$this->assertMimicLastRequestQuery('per_page', 50);
		$this->assertEquals(50, $collection->page_size());
	}

	public function provider_loads_page_count_from_headers()
	{
		return array(
			array(1, 'single'),
			array(3, 'large'),
		);
	}

	/**
	 * @dataProvider provider_loads_page_count_from_headers
	 * @param integer $expect_pages
	 * @param string $collection
	 */
	public function test_loads_page_count_from_headers($expect_pages, $collection)
	{
		$collection = new Github_Collection(new Github, '/collection/'.$collection, 'Github_Object_CollectionTest');
		$collection->load();

		$this->assertEquals($expect_pages, $collection->page_count());
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_exception_on_invalid_link_header()
	{
		$collection = new Github_Collection(new Github, '/collection/badlink', 'Github_Object_CollectionTest');
		$collection->load();
	}

	public function test_stores_single_page_result_count()
	{
		$collection = new Github_Collection(new Github, '/collection/basic', 'Github_Object_CollectionTest');
		$collection->load();

		$this->assertEquals(2, $collection->count());
		$this->assertMimicRequestCount(1);
	}

	public function provider_provides_result_count()
	{
		return array(
			array('basic', FALSE, 2, 1, 'HEAD'),
			array('basic', 1, 2, 1, 'GET'),
			array('large', FALSE, 90, 1, 'HEAD'),
			array('large', 1, 90, 2, 'HEAD'),
			array('large', 2, 90, 2, 'HEAD'),
			// Here the last page of results is loaded, so no count request is required
			array('large', 3, 90, 1, 'GET')
		);
	}

	/**
	 * @dataProvider provider_provides_result_count
	 */
	public function test_provides_result_count($collection, $load_result_page, $expect_count, $expect_requests, $expect_last_method)
	{
		$collection = new Github_Collection(new Github, '/collection/'.$collection, 'Github_Object_CollectionTest');

		if ($load_result_page)
		{
			$collection->load($load_result_page);
		}

		// Validate the item count and request information
		$this->assertEquals($expect_count, $collection->count(), "Assert correct collection count");
		$this->assertMimicRequestCount($expect_requests);
		$this->assertMimicLastRequestMethod($expect_last_method);
	}

	public function test_loads_first_page()
	{
		$collection = new Github_Collection_PublishTestData(new Github, '/collection/basic', 'Github_Object_CollectionTest');
		$collection->load();

		// Access the internal storage for testing
		$internal_items = $collection->_get_items();
		$this->assertEquals(2, count($internal_items));
		$this->assertEquals('https://api.github.com/object/1', $internal_items[1]['url']);
	}

	public function test_reserves_space_for_earlier_pages_when_loading()
	{
		$collection = new Github_Collection_PublishTestData(new Github, '/collection/large', 'Github_Object_CollectionTest');
		$collection->load(2);

		// Access the internal storage for testing
		$internal_items = $collection->_get_items();
		$this->assertEquals(60, count($internal_items));
		$this->assertEquals('https://api.github.com/object/30', $internal_items[30]['url']);

		return $collection;
	}

	/**
	 * @depends test_reserves_space_for_earlier_pages_when_loading
	 */
	public function test_loads_earlier_pages_to_reserved_space($collection)
	{
		$collection->load(1);

		$internal_items = $collection->_get_items();
		$this->assertEquals(60, count($internal_items));
		$this->assertEquals('https://api.github.com/object/0', $internal_items[0]['url']);
		$this->assertEquals('https://api.github.com/object/31', $internal_items[31]['url']);

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

		$this->assertEquals(90, count($internal_items));
		$this->assertEquals('https://api.github.com/object/0', $internal_items[0]['url']);
		$this->assertEquals('https://api.github.com/object/31', $internal_items[31]['url']);
		$this->assertEquals('https://api.github.com/object/62', $internal_items[62]['url']);
	}

	public function test_arrayaccess_returns_objects()
	{
		$collection = new Github_Collection(new Github, '/collection/large', 'Github_Object_CollectionTest');
		$collection->load(1);

		$this->assertInstanceOf('Github_Object_CollectionTest', $collection[1]);
		$this->assertEquals('https://api.github.com/object/1', $collection[1]->url);
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
			$this->assertEquals("https://api.github.com/object/0", $object->url);
			$this->assertEquals("0", $key);
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
		$collection = new Github_Collection(new Github, '/collection/large', 'Github_Object_CollectionTest');

		$i = 0;
		foreach ($collection as $key => $item)
		{
			$this->assertEquals($i, $key, "Testing collection key");
			$this->assertEquals($i, $item->seq, "Testing item sequence");
			$i++;
		}

		$this->assertEquals(90, $i, "Testing size of collection");
		$this->assertMimicRequestCount(4);
	}

	public function test_all_items_are_iterable()
	{
		$collection = new Github_Collection(new Github, '/collection/basic', 'Github_Object_CollectionTest');

		$i = 0;
		foreach ($collection as $key => $item)
		{
			$this->assertEquals($i, $key);
			$i++;
		}
		$this->assertEquals(2, $i);
	}

	public function test_all_items_are_accessible()
	{
		$collection = new Github_Collection(new Github, '/collection/basic', 'Github_Object_CollectionTest');

		for ($i = 0; $i < 2; $i++)
		{
			$this->assertInstanceOf('Github_Object_CollectionTest', $collection[$i]);
		}
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

	/**
	 * @depends test_arrayaccess_returns_objects
	 */
	public function test_array_offset_can_be_tested($collection)
	{
		$this->assertTrue(isset($collection[1]));
		$this->assertFalse(isset($collection[91]));
	}

	public function test_isset_supports_lazy_loading()
	{
		$collection = new Github_Collection(new Github, '/collection/large', 'Github_Object_CollectionTest');

		$this->assertTrue(isset($collection[89]));
		$this->assertFalse(isset($collection[90]));
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
		'url' => NULL,
		'seq' => NULL);
}