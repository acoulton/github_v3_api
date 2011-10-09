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
	protected $github = null;
	
	public function setUp()
	{
		parent::setUp();
		$this->github = $this->getMock('Github', array('api'));	
	}

	public function test_unknown_fields_raise_exception()
	{				
		$foo = new Github_Object_Foo($this->github, array());
		
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
}


class Github_Object_Foo extends Github_Object
{
	protected $_fields = array(
		'url' => null,
		'bar' => 'Github_Object_Bar',
		'field_1' => null
	);
}