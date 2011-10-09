<?php defined('SYSPATH') OR die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests GithubObject base class
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
class Github_ObjectTest extends Unittest_TestCase
{
    /**
	 *
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	protected $mock_github = null;
	
	/**
	 * Convenience method to get a new Foo object for testing
	 * @param array $data
	 * @return Github_Object_Foo
	 */
	protected function _get_foo($data = array())
	{
		return new Github_Object_Foo($this->mock_github, $data);
	}
	
	/**
	 * Convenience method to note that method should not call API
	 */
	protected function assert_should_not_call_api()
	{
		$this->mock_github->expects($this->never())
				->method('api');
		$this->mock_github->expects($this->never())
				->method('api_json');
	}
	
	/**
	 * Convenience method to expect an API call and setup response
	 * @param string $url
	 * @param array $return_data
	 * @param string $method
	 * @param boolean $json 
	 */
	protected function assert_should_call_api($url, $return_data, $method = 'GET', $json = true)
	{
		$api_method = $json ? 'api_json' : 'api';
		
		$this->mock_github->expects($this->once())
				->method($api_method)
				->with($url,$method)
				->will($this->returnValue($return_data));
	}

	
	public function setUp()
	{
		parent::setUp();
		$this->mock_github = $this->getMock('Github', array('api', 'api_json'));	
	}

	public function test_reading_unknown_fields_raises_exception()
	{	
		$foo = $this->_get_foo();
		
		try
		{
			$test = $foo->unknown_field;
		}
		catch (Github_Exception_InvalidProperty $e)
		{			
			$msg = $e->getMessage();
			$this->assertContains('unknown_field', $msg);
			$this->assertContains('Github_Object_Foo', $msg);
			return;
		}
		
		$this->fail('Expected Github_Exception_InvalidProperty was not thrown');
	}
	
	public function test_writing_unknown_fields_raises_exception()
	{
		$foo = $this->_get_foo();
		
		try
		{
			$foo->unknown_field = 'bar';
		}
		catch (Github_Exception_InvalidProperty $e)
		{			
			$msg = $e->getMessage();
			$this->assertContains('unknown_field', $msg);
			$this->assertContains('Github_Object_Foo', $msg);
			return;
		}
		
		$this->fail('Expected Github_Exception_InvalidProperty was not thrown');		
	}
	
	public function test_constructor_populates_object_data()
	{
		$this->assert_should_not_call_api();
		$foo = $this->_get_foo(array(
					'url' => 'my/mock/foo',
					'field_1' => 'bar',
					'writeable_field' => 'test'));
		
		$this->assertEquals('my/mock/foo', $foo->url);
		$this->assertEquals('bar', $foo->field_1);
		$this->assertEquals('test', $foo->writeable_field);		
	}

	
	public function test_known_fields_are_returned()
	{
		$foo = $this->_get_foo(array(
			'field_1'=>'bar'));
		
		$this->assert_should_not_call_api();
		
		$this->assertEquals('bar',$foo->field_1);
	}
	
	public function test_object_fields_are_instantiated()
	{
		$foo = $this->_get_foo(array(
				'bar'=>array(
					'bar_field' => 'foo'
				)));
		
		$this->assertInstanceOf('Github_Object_Bar', $foo->bar);
	}
	
	public function test_null_object_fields_are_null()
	{
		$foo = $this->_get_foo(array(
			'bar'=>null));
		
		$this->assertNull($foo->bar);
	}
	
	public function test_loaded_fields_do_not_trigger_loading()
	{
		$foo = $this->_get_foo(array(
			'field_1' => 'bar'));
		
		$this->assert_should_not_call_api();
		
		$test = $foo->field_1;		
	}
	
	public function test_empty_fields_trigger_load()
	{
		$foo = $this->_get_foo(array(
					'url' => 'my/mock/foo'));

		$this->assert_should_call_api('my/mock/foo',
				array('url'=>'my/mock/foo',
					  'field_1'=>'loaded'));
		
		$this->assertEquals('loaded', $foo->field_1);
	}
	
	/**
	 * @expectedException Github_Exception_MissingProperty
	 */
	public function test_loaded_object_is_not_reloaded_for_missing_property()
	{
		$foo = $this->_get_foo(array(
				'url' => 'my/mock/foo'));
				
		$this->assert_should_call_api('my/mock/foo',
				array('url'=>'my/mock/foo'));
		
		$foo->load();
		
		$test = $foo->field_1;				
	}
	
	/**
	 * @expectedException Github_Exception_MissingURL
	 */
	public function test_object_cannot_lazily_load_url()
	{
		$foo = $this->_get_foo();
		$this->assert_should_not_call_api();
		$test = $foo->field_1;
	}
	
	public function test_loading_sets_loaded()
	{
		$foo = $this->_get_foo(array(
			'url'=>'my/mock/foo'));
		
		$this->assert_should_call_api('my/mock/foo',
						array('url'=>'my/mock/foo'));
		
		$this->assertEquals(false, $foo->loaded());
		
		$foo->load();
		
		$this->assertEquals(true, $foo->loaded());
	}
	
	public function test_object_can_delete()
	{
		$foo = $this->_get_foo(array(
			'url'=>'my/mock/foo'));
		
		$this->assert_should_call_api('my/mock/foo', null, 'DELETE', false);
		
		$foo->delete();
	}
	
	/**
	 * @expectedException Github_Exception_MissingURL
	 */
	public function test_cannot_delete_object_without_url()
	{
		$foo = $this->_get_foo(array(
			'field_1'=>'test'));
		
		$this->assert_should_not_call_api();
		$foo->delete();
	}

	public function test_read_only_fields_cannot_be_set()
	{
		$foo = $this->_get_foo();
		
		try
		{
			$foo->field_1 = 'bar';
		}
		catch (Github_Exception_ReadOnlyProperty $e)
		{
			$msg = $e->getMessage();
			$this->assertContains('field_1', $msg);
			$this->assertContains('Github_Object_Foo', $msg);
			$this->assertContains('bar', $msg);
			return;
		}
		
		$this->fail('Expected exception Github_Exception_ReadOnlyProperty was not thrown');
	}
	
	public function test_writeable_fields_are_set()
	{
		$foo = $this->_get_foo();
		$foo->writeable_field = 'bar';
		$this->assertEquals('bar', $foo->writeable_field);		
	}
	
	public function test_changed_objects_are_modified()
	{
		$foo = $this->_get_foo();
		
		$this->assertEquals(false, $foo->modified());
		
		$foo->writeable_field = 'bar';
		
		$this->assertEquals(true, $foo->modified());
	}
	
	public function test_reloaded_objects_are_not_modified()
	{
		$foo = $this->_get_foo();
		
		$foo->writeable_field = 'bar';
		$foo->reload_data(array(
			'field_1' => true
			));
		
		$this->assertEquals(false, $foo->modified());
	}
	
	public function test_setting_unchanged_values_does_not_modify()
	{
		$foo = $this->_get_foo(array('writeable_field'=>'bar'));
		$foo->writeable_field = 'bar';
		$this->assertFalse($foo->modified());
	}
			
	public function test_as_array_returns_scalar_data()
	{
		$this->assert_should_not_call_api();
		$data = array(
			'url' => 'my/mock/foo',
			'field_1' => true,
			'writeable_field' => true,
			'bar' => array(
				'bar_field' => 'test'
			));
		
		$foo = $this->_get_foo($data);
		
		$this->assertEquals($data, $foo->as_array());
	}
	
}


class Github_Object_Foo extends Github_Object
{
	protected $_fields = array(
		'url' => null,
		'bar' => 'Github_Object_Bar',
		'field_1' => null,
		'writeable_field' => true		
	);
}

class Github_Object_Bar extends Github_Object
{
	protected $_fields = array(
		'bar_field' => null
	);
}