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
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\ScssphpFilter;
use Assetic\Filter\CSSMinFilter;

class CssPacker extends AbstractPacker
{
	/**
	 * Presets
	 */
	private $lessPresets = array();

	/**
	 * CSSpacker constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		// default FileNameTemplate
		$this->setFilename("_*.css");
		// AbstractPacker constructor
		parent::__construct($config);
		// Used to determine if we need to load LessPHP filter
		$this->config["less_filter"] = false;
		$this->config["scss_filter"] = false;
	}

	/**
	 * @inheritdoc
	 */
	protected function addAsset($asset)
	{
		// if it is a less file than we'll need LessPHP filter
		if (strpos($asset, ".less") !== false) {
			$this->config["less_filter"] = true;
		} elseif (strpos($asset, ".scss") !== false) {
			$this->config["scss_filter"] = true;
		}
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
			if (substr($asset, -5) === ".less") {
				$filters[] = "less";
			} elseif (substr($asset, -5) === ".scss") {
				$filters[] = "scss";
			}
			$filters[] = "?min";
			$assetObj = $factory->createAsset($asset, $filters);
			$buffer .= $assetObj->dump();
		}

		return $buffer;
	}

	protected function getAsseticFactory()
	{
		if (empty($this->config["input_path"][0])) {
			throw new \Exception('Empty input path');
		}
		$factory = new AssetFactory($this->config["input_path"][0], $this->config["debug"]);
		$factory->setDefaultOutput("");

		// add a FilterManager to the AssetFactory
		$fm = new FilterManager();
		$factory->setFilterManager($fm);
		// adding some filters to the filter manager
		if ($this->config["less_filter"]) {
			$lessphpFilter = new LessphpFilter();
			if ($this->lessPresets) {
				$lessphpFilter->setPresets($this->lessPresets);
			}
			$fm->set("less", $lessphpFilter);
		}
		if ($this->config["scss_filter"]) {
			$scssFilter = new ScssphpFilter();
			$fm->set("scss", $scssFilter);
		}
		$fm->set("min", new CSSMinFilter());

		return $factory;
	}

	/**
	 * Set an array with presets
	 * @param array $presets
	 */
	public function setPresets($presets)
	{
		$this->lessPresets = (array)$presets;
	}
}
