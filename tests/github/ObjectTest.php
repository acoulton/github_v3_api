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
	
	public function setUp()
	{
		parent::setUp();
		$this->mock_github = $this->getMock('Github', array('api', 'api_json'));	
	}

	public function test_reading_unknown_fields_raises_exception()
	{				
		$foo = new Github_Object_Foo($this->mock_github, array());
		
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
		$foo = new Github_Object_Foo($this->mock_github, array());
		
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
		$foo = new Github_Object_Foo($this->mock_github,
				array(
					'url' => 'my/mock/foo',
					'field_1' => 'bar',
					'writeable_field' => 'test'));
		
		$this->assertEquals('my/mock/foo', $foo->url);
		$this->assertEquals('bar', $foo->field_1);
		$this->assertEquals('test', $foo->writeable_field);
	}

	
	public function test_known_fields_are_returned()
	{
		$foo = new Github_Object_Foo($this->mock_github, array('field_1'=>'bar'));
		$this->assertEquals('bar',$foo->field_1);
	}
	
	public function test_object_fields_are_instantiated()
	{
		$foo = new Github_Object_Foo($this->mock_github, 
				array('bar'=>array(
					'bar_field' => 'foo'
				)));
		$this->assertInstanceOf('Github_Object_Bar', $foo->bar);
	}
	
	public function test_null_object_fields_are_null()
	{
		$foo = new Github_Object_Foo($this->mock_github, 
				array('bar'=>null));
		
		$this->assertNull($foo->bar);
	}
	
	public function test_loaded_fields_do_not_trigger_loading()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array('field_1' => 'bar'));
		
		$this->mock_github->expects($this->never())
					 ->method('api_json');
		$this->mock_github->expects($this->never())
					->method('api');
		
		$test = $foo->field_1;		
	}
	
	public function test_empty_fields_trigger_load()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array(
					'url' => 'my/mock/foo'
				));
		
		$this->mock_github->expects($this->once())
				->method('api_json')
				->with('my/mock/foo','GET')
				->will($this->returnValue(
						array('url'=>'my/mock/foo',
							  'field_1'=>'loaded')));
		
		$this->assertEquals('loaded', $foo->field_1);
	}
	
	/**
	 * @expectedException Github_Exception_MissingProperty
	 */
	public function test_loaded_object_is_not_reloaded_for_missing_property()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array(
					'url' => 'my/mock/foo',
				));
		
		$this->mock_github->expects($this->once())
				->method('api_json')
				->with('my/mock/foo', 'GET')
				->will($this->returnValue(
						array('url'=>'my/mock/foo')));
		
		$foo->load();
		
		$test = $foo->field_1;				
	}
	
	/**
	 * @expectedException Github_Exception_MissingURL
	 */
	public function test_object_cannot_lazily_load_url()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array());
		
		$test = $foo->field_1;
	}
	
	public function test_loading_sets_loaded()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array('url'=>'my/mock/foo'));
		
		$this->mock_github->expects($this->once())
				->method('api_json')
				->with('my/mock/foo', 'GET')
				->will($this->returnValue(
						array('url'=>'my/mock/foo')));
		
		$this->assertEquals(false, $foo->loaded());
		
		$foo->load();
		
		$this->assertEquals(true, $foo->loaded());
	}
	
	public function test_object_can_delete()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array('url'=>'my/mock/foo'));
		
		$this->mock_github->expects($this->once())
				->method('api')
				->with('my/mock/foo', 'DELETE')
				->will($this->returnValue(null));
		
		$foo->delete();
	}
	
	/**
	 * @expectedException Github_Exception_MissingURL
	 */
	public function test_cannot_delete_object_without_url()
	{
		$foo = new Github_Object_Foo($this->mock_github,
				array('field_1'=>'test'));
		$foo->delete();
	}

	public function test_read_only_fields_cannot_be_set()
	{
		$foo = new Github_Object_Foo($this->mock_github, array());
		
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
		$foo = new Github_Object_Foo($this->mock_github, array());
		$foo->writeable_field = 'bar';
		$this->assertEquals('bar', $foo->writeable_field);		
	}
	
	public function test_changed_objects_are_modified()
	{
		$foo = new Github_Object_Foo($this->mock_github, array());
		
		$this->assertEquals(false, $foo->modified());
		
		$foo->writeable_field = 'bar';
		
		$this->assertEquals(true, $foo->modified());
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