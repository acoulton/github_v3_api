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

	public function test_unknown_fields_raise_exception()
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
				->with('my/mock/foo',
					   'GET')
				->will($this->returnValue(
						array('url'=>'my/mock/foo',
							  'field_1'=>'loaded')));
		
		$this->assertEquals('loaded', $foo->field_1);
	}
		
}


class Github_Object_Foo extends Github_Object
{
	protected $_fields = array(
		'url' => null,
		'bar' => 'Github_Object_Bar',
		'field_1' => null
	);
}

class Github_Object_Bar extends Github_Object
{
	protected $_fields = array(
		'bar_field' => null
	);
}