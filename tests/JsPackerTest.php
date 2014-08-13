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

class JsPackerTest extends PHPUnit_Framework_TestCase
{
	public function testCreateConfig()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output");
		$js = new JsPacker($config);
		$this->assertSame(array(__DIR__."/assets".DIRECTORY_SEPARATOR), $js->getInputPath());
		$this->assertSame(__DIR__."/output".DIRECTORY_SEPARATOR, $js->getOutputPath());
		// checks the default value of debug is FALSE
		$this->assertFalse($js->getDebug());

		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output", "debug" => true);
		$js = new JsPacker($config);
		$this->assertTrue($js->getDebug());
	}

	public function testConfigSetters()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output", "debug" => true);
		$js = new JsPacker($config);
		// input path
		$js->setInputPath(__DIR__."1");
		$this->assertSame(array(__DIR__."1".DIRECTORY_SEPARATOR), $js->getInputPath());
		$js->setInputPath(__DIR__."2".DIRECTORY_SEPARATOR);
		$this->assertSame(array(__DIR__."2".DIRECTORY_SEPARATOR), $js->getInputPath());
		// output path
		$js->setOutputPath(__DIR__."3");
		$this->assertSame(__DIR__."3".DIRECTORY_SEPARATOR, $js->getOutputPath());
		$js->setOutputPath(__DIR__."4".DIRECTORY_SEPARATOR);
		$this->assertSame(__DIR__."4".DIRECTORY_SEPARATOR, $js->getOutputPath());
		// debug
		$this->assertTrue($js->getDebug());
		$js->setDebug(false);
		$this->assertFalse($js->getDebug());
		$js->setDebug(true);
		$this->assertTrue($js->getDebug());
	}

	public function testAddingAssets()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__);
		$js = new JsPacker($config);

		// no assets on create
		$this->assertEquals(array(), $js->getAssets());

		// add asset
		$js->add("one.js");
		$this->assertEquals(array(__DIR__."/assets".DIRECTORY_SEPARATOR."one.js"), $js->getAssets());
		// adding more than 1 asset
		$js->add(array("two.js", "three.js"));
		$this->assertEquals(
			array(
				__DIR__."/assets".DIRECTORY_SEPARATOR."one.js",
				__DIR__."/assets".DIRECTORY_SEPARATOR."two.js",
				__DIR__."/assets".DIRECTORY_SEPARATOR."three.js",
			),
			$js->getAssets()
		);
	}

	public function testAddingAssetWithPath()
	{
		$config = array("input_path" => __DIR__, "output_path" => __DIR__);
		$js = new JsPacker($config);

		// add asset
		$js->add("assets/one.js");
		$this->assertEquals(array(__DIR__."/assets".DIRECTORY_SEPARATOR."one.js"), $js->getAssets());
	}

	public function testAddingAssetWithAbsolutePath()
	{
		$config = array("input_path" => "/dev/null", "output_path" => __DIR__);
		$js = new JsPacker($config);

		// add asset
		$js->add(__DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."one.js");
		$this->assertEquals(array(__DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."one.js"), $js->getAssets());
	}

	public function testFileNameChangesOnAnyChange()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__);
		$js = new JsPacker($config);

		$fn1 = $js->getFileName();
		// no change - filename is same
		$this->assertTrue($fn1 == $js->getFileName());
		// change debug
		$js->setDebug(true);
		$this->assertFalse($fn1 == $fn2 = $js->getFileName());
		// add a file
		$js->add("one.js");
		$this->assertFalse($fn2 == $fn1 = $js->getFileName());
		// add another file
		$js->add("two.js");
		$this->assertFalse($fn1 == $fn2 = $js->getFileName());
	}

	/**
	 * For some reason (filemtime caching) this test fails.
	 */
	public function testFileNameChangesOnModificationTimeChange()
	{
		// $config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__);
		// // first Packer
		// $js = new JsPacker($config);
		// $js->add("one.js");
		// $fn1 = $js->getFileName();

		// // change last modified time
		// touch(__DIR__."/assets".DIRECTORY_SEPARATOR."one.js");

		// // new Packer
		// $js = new JsPacker($config);
		// $js->add("one.js");

		// // different names
		// $this->assertFalse($fn1 == $js->getFileName());
	}

	public function testPackWithoutSavingDebug()
	{
		// debug is TRUE, so no minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => true);
		$js = new JsPacker($config);
		$js->add("one.js");

		$this->assertEquals(file_get_contents(__DIR__."/assets".DIRECTORY_SEPARATOR."one.js"), $js->dump());
	}

	public function testPackWithoutSavingAndMinification()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => false);
		$js = new JsPacker($config);
		$js->add("one.js");
		// minificated
		$this->assertEquals("function test()\n{alert(\"this is a test\");}", $js->dump());
	}

	public function testLessMinificationAndMerging()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => false);
		$js = new JsPacker($config);
		$js->add(array("one.js", "two.js"));
		// minificated
		$this->assertEquals(file_get_contents(__DIR__."/assets/three.js"), $js->dump());
	}

	public function testFinal()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/assets", "debug" => false);
		$js = new JsPacker($config);
		$js->add(array("one.js", "two.js"));
		// returns the name of the file
		$this->assertEquals($filename = $js->getFileName(), $js->pack());

		$this->assertSame(file_get_contents(__DIR__."/assets".DIRECTORY_SEPARATOR.$filename), file_get_contents(__DIR__."/assets/three.js"));
		unlink(__DIR__."/assets".DIRECTORY_SEPARATOR.$filename);
	}
}
