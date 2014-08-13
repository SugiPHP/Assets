<?php
/**
 * @package    SugiPHP
 * @subpackage Assets
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Assets;

use PHPUnit_Framework_TestCase;

class PackerTest extends PHPUnit_Framework_TestCase
{
	public function testCreateConfig()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output");
		$packer = new DummyPacker($config);
		$this->assertTrue(is_array($packer->getInputPath())); // input path is always array
		$this->assertTrue(is_string($packer->getOutputPath())); // output path is only one, so returns string
		$this->assertSame(array(__DIR__."/assets".DIRECTORY_SEPARATOR), $packer->getInputPath());
		$this->assertSame(__DIR__."/output".DIRECTORY_SEPARATOR, $packer->getOutputPath());
		// checks the default value of debug is FALSE
		$this->assertFalse($packer->getDebug());

		// adding a debug in configuration
		$config["debug"] = true;
		$packer = new DummyPacker($config);
		$this->assertTrue($packer->getDebug());
	}

	public function testConfigSetters()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output", "debug" => true);
		$packer = new DummyPacker($config);
		// input path
		$packer->setInputPath(__DIR__."1");
		$this->assertSame(array(__DIR__."1".DIRECTORY_SEPARATOR), $packer->getInputPath());
		$packer->setInputPath(__DIR__."2".DIRECTORY_SEPARATOR);
		$this->assertSame(array(__DIR__."2".DIRECTORY_SEPARATOR), $packer->getInputPath());
		// output path
		$packer->setOutputPath(__DIR__."3");
		$this->assertSame(__DIR__."3".DIRECTORY_SEPARATOR, $packer->getOutputPath());
		$packer->setOutputPath(__DIR__."4".DIRECTORY_SEPARATOR);
		$this->assertSame(__DIR__."4".DIRECTORY_SEPARATOR, $packer->getOutputPath());
		// debug
		$this->assertTrue($packer->getDebug());
		$packer->setDebug(false);
		$this->assertFalse($packer->getDebug());
		$packer->setDebug(true);
		$this->assertTrue($packer->getDebug());
	}

	public function testAddInputPath()
	{
		$config = array("input_path" => "1", "output_path" => __DIR__."/output");
		$packer = new DummyPacker($config);
		$packer->addInputPath("2");
		$this->assertSame(array("1/", "2/"), $packer->getInputPath());
	}

	public function testPopInputPath()
	{
		$config = array("input_path" => array("1", "2"), "output_path" => __DIR__."/output");
		$packer = new DummyPacker($config);
		$this->assertSame(array("1/", "2/"), $packer->getInputPath());
		$packer->popInputPath();
		$this->assertSame(array("1/"), $packer->getInputPath());
		$packer->popInputPath();
		$this->assertSame(array(), $packer->getInputPath());
	}

	public function testPrependInputPath()
	{
		$config = array("input_path" => "1", "output_path" => __DIR__."/output");
		$packer = new DummyPacker($config);
		$packer->prependInputPath("2");
		$this->assertSame(array("2/", "1/"), $packer->getInputPath());
	}

	public function testShiftInputPath()
	{
		$config = array("input_path" => array("1", "2"), "output_path" => __DIR__."/output");
		$packer = new DummyPacker($config);
		$packer->shiftInputPath();
		$this->assertSame(array("2/"), $packer->getInputPath());
		$packer->shiftInputPath();
		$this->assertSame(array(), $packer->getInputPath());
	}
}
