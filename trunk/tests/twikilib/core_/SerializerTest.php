<?php
namespace tests\twikilib\core;

use twikilib\core\Serializer;

use twikilib\core\IInjectedAfterUnserialization;

class TestClassX implements IInjectedAfterUnserialization {
	final public function __wakeup() {
		Serializer::wakeupHandler($this);
	}
}

class TestClassY extends TestClassX {
	public $attrToInject;
}

/**
 * @author Viliam Simko
 */
class SerializerTest extends \PHPUnit_Framework_TestCase {
	function testSerializeUnserialize() {
		$serializer = new Serializer;
		$serializer->attrToInject = 'testval';
		$data = $serializer->serialize( array(new TestClassX, new TestClassY) );
		$list = $serializer->unserialize($data);
		$this->assertEquals(2, count($list) );
		$this->assertInstanceOf('tests\twikilib\core\TestClassX', $list[0]);
		$this->assertInstanceOf('tests\twikilib\core\TestClassY', $list[1]);
		$this->assertObjectNotHasAttribute('attrToInject', $list[0]);
		$this->assertEquals('testval', $list[1]->attrToInject);
	}
}
