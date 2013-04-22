<?php
/**
 * @package    SugiPHP
 * @subpackage Assets
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Assets;

use Assetic\Factory\AssetFactory;
use Assetic\FilterManager;

class JsPacker extends AbstractPacker
{
	/**
	 * JsPacker constructor
	 * 
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		parent::__construct($config);
	}

	/**
	 * @inheritdoc
	 */
	protected function addAsset($assets)
	{
	}

	/**
	 * @inheritdoc
	 */
	protected function dumpAssets()
	{
		$factory = $this->getAsseticFactory();
		$filters = array();
		$buffer = "";

		foreach ($this->assets as $asset) {
			$filters[] = "?jshrink";
			$assetObj = $factory->createAsset($asset, $filters);
			$buffer .= $assetObj->dump();
		}

		return $buffer;
	}

	public function getFileName()
	{
		$str = serialize($this->config) . serialize($this->assets) . serialize($this->lastModified);

		return "_".substr(sha1($str), 0, 11).".js";
	}

	protected function getAsseticFactory()
	{
		$factory = new AssetFactory($this->config["input_path"], $this->config["debug"]);
		$factory->setDefaultOutput("");

		// add a FilterManager to the AssetFactory
		$fm = new FilterManager();
		$factory->setFilterManager($fm);
		$fm->set("jshrink", new JShrinkFilter());

		return $factory;
	}
}
