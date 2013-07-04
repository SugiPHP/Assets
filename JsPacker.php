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
		// default FileNameTemplate
		$this->setFilename("_*.js");

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

	protected function getAsseticFactory()
	{
		if(empty($this->config["input_path"][0]))
			throw new \Exception ('Empty input path');
		$factory = new AssetFactory($this->config["input_path"][0], $this->config["debug"]);
		$factory->setDefaultOutput("");

		// add a FilterManager to the AssetFactory
		$fm = new FilterManager();
		$factory->setFilterManager($fm);
		$fm->set("jshrink", new JShrinkFilter());

		return $factory;
	}
}
