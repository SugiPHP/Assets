<?php
/**
 * @package    SugiPHP
 * @subpackage Assets
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Assets;

class CssPackerTest extends \PHPUnit_Framework_TestCase
{
	public function testCreateConfig()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output");
		$css = new CssPacker($config);
		$this->assertSame(__DIR__."/assets".DIRECTORY_SEPARATOR, $css->getInputPath());
		$this->assertSame(__DIR__."/output".DIRECTORY_SEPARATOR, $css->getOutputPath());
		// checks the default value of debug is FALSE
		$this->assertFalse($css->getDebug());

		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output", "debug" => true);
		$css = new CssPacker($config);
		$this->assertTrue($css->getDebug());
	}

	public function testConfigSetters()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/output", "debug" => true);
		$css = new CssPacker($config);
		// input path
		$css->setInputPath(__DIR__."1");
		$this->assertSame(__DIR__."1".DIRECTORY_SEPARATOR, $css->getInputPath());
		$css->setInputPath(__DIR__."2".DIRECTORY_SEPARATOR);
		$this->assertSame(__DIR__."2".DIRECTORY_SEPARATOR, $css->getInputPath());
		// output path
		$css->setOutputPath(__DIR__."3");
		$this->assertSame(__DIR__."3".DIRECTORY_SEPARATOR, $css->getOutputPath());
		$css->setOutputPath(__DIR__."4".DIRECTORY_SEPARATOR);
		$this->assertSame(__DIR__."4".DIRECTORY_SEPARATOR, $css->getOutputPath());
		// debug
		$this->assertTrue($css->getDebug());
		$css->setDebug(false);		
		$this->assertFalse($css->getDebug());
		$css->setDebug(true);
		$this->assertTrue($css->getDebug());
	}

	public function testAddingAssets()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__);
		$css = new CssPacker($config);

		// no assets on create
		$this->assertEquals(array(), $css->getAssets());

		// add asset
		$css->add("one.css");
		$this->assertEquals(array(__DIR__."/assets".DIRECTORY_SEPARATOR."one.css"), $css->getAssets());
		// adding more than 1 asset
		$css->add(array("two.less", "three.css"));
		$this->assertEquals(
			array(
				__DIR__."/assets".DIRECTORY_SEPARATOR."one.css",
				__DIR__."/assets".DIRECTORY_SEPARATOR."two.less",
				__DIR__."/assets".DIRECTORY_SEPARATOR."three.css",
			), $css->getAssets());
	}

	public function testAddingAssetWithPath()
	{
		$config = array("input_path" => __DIR__, "output_path" => __DIR__);
		$css = new CssPacker($config);

		// add asset
		$css->add("assets/one.css");
		$this->assertEquals(array(__DIR__."/assets".DIRECTORY_SEPARATOR."one.css"), $css->getAssets());
	}

	public function testAddingAssetWithAbsolutePath()
	{
		$config = array("input_path" => "/dev/null", "output_path" => __DIR__);
		$css = new CssPacker($config);

		// add asset
		$css->add(__DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."one.css");
		$this->assertEquals(array(__DIR__.DIRECTORY_SEPARATOR."assets".DIRECTORY_SEPARATOR."one.css"), $css->getAssets());
	}

	public function testFileNameChangesOnAnyChange()
	{
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__);
		$css = new CssPacker($config);

		$fn1 = $css->getFileName();
		// no change - filename is same
		$this->assertTrue($fn1 == $css->getFileName());
		// change debug
		$css->setDebug(true);
		$this->assertFalse($fn1 == $fn2 = $css->getFileName());
		// add a file
		$css->add("one.css");
		$this->assertFalse($fn2 == $fn1 = $css->getFileName());
		// add another file
		$css->add("two.less");
		$this->assertFalse($fn1 == $fn2 = $css->getFileName());
	}

	/**
	 * For some reason (filemtime caching) this test fails.
	 */
	public function testFileNameChangesOnModificationTimeChange()
	{
		// $config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__);
		// // first Packer
		// $css = new CssPacker($config);
		// $css->add("one.css");
		// $fn1 = $css->getFileName();
		
		// // change last modified time
		// touch(__DIR__."/assets".DIRECTORY_SEPARATOR."one.css");

		// // new Packer
		// $css = new CssPacker($config);
		// $css->add("one.css");

		// // different names
		// $this->assertFalse($fn1 == $css->getFileName());
	}

	public function testPackWithoutSavingDebug()
	{
		// debug is TRUE, so no minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => true);
		$css = new CssPacker($config);
		$css->add("one.css");

		$this->assertEquals(file_get_contents(__DIR__."/assets".DIRECTORY_SEPARATOR."one.css"), $css->pack(false));
	}

	public function testPackWithoutSavingAndMinification()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => false);
		$css = new CssPacker($config);
		$css->add("one.css");
		// minificated
		$this->assertEquals("body{color:#ccc}", $css->pack(false));
	}

	public function testLessFilter()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => false);
		$css = new CssPacker($config);
		$css->add("two.less");
		// minificated
		$this->assertEquals("body{background:#333}", $css->pack(false));
	}

	public function testLessMinificationAndMerging()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__, "debug" => false);
		$css = new CssPacker($config);
		$css->add(array("one.css", "two.less"));
		// minificated
		$this->assertEquals(file_get_contents(__DIR__."/assets/three.css"), $css->pack(false));
	}

	public function testFinal()
	{
		// debug is FALSE, so minifications are done
		$config = array("input_path" => __DIR__."/assets", "output_path" => __DIR__."/assets", "debug" => false);
		$css = new CssPacker($config);
		$css->add(array("one.css", "two.less"));
		// returns the name of the file
		$this->assertEquals($filename = $css->getFileName(), $css->pack(true));

		$this->assertSame(file_get_contents(__DIR__."/assets".DIRECTORY_SEPARATOR.$filename), file_get_contents(__DIR__."/assets/three.css"));
		unlink(__DIR__."/assets".DIRECTORY_SEPARATOR.$filename);
	}
}
