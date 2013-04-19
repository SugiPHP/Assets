<?php

namespace SugiPHP\Assets;

use Assetic\Factory\AssetFactory;
use Assetic\FilterManager;
use Assetic\Filter\LessphpFilter;
use Assetic\Filter\CssMinFilter;

class CssPacker
{
	protected $config = array();
	protected $assets = array();
	protected $lastModified = 0;

	/**
	 * CSSpacker constructor
	 * 
	 * @param array $config
	 *  - output_path - the directory where cached files will be created. 
	 *      This should be within your DOCUMENT ROOT and be visible from web. 
	 *  	The server must have write permissions for this path.
	 *  - input_path - the directory where actual uncompressed files are. This can be anywhere in the server.
	 */
	public function __construct(array $config)
	{
		$config["input_path"] = rtrim($config["input_path"], "/\\") . DIRECTORY_SEPARATOR;
		$config["debug"] = isset($config["debug"]) ? $config["debug"] : false;
		$config["less_filter"] = isset($config["less_filter"]) ? $config["less_filter"] : false;

		$this->config = $config;
	}

	/**
	 * Adds css file(s)
	 * 
	 * @param string|array $assets List of files or a single file. The file can be absolute path or relative to the input_path
	 */
	public function add($assets)
	{
		$assets = (array) $assets;

		// Check one by one and throw an exception if the file is not found
		foreach ($assets as $asset) {
			// if it is a less file than we'll need LessPHP filter
			if (strpos($asset, ".less") !== false) {
				$this->config["less_filter"] = true;
			}

			// prepend search path?
			if (!$this->isFullPath($asset)) {
				$asset = $this->config["input_path"] . $asset;
			}
			
			// Check the file and gets last modified date
			if ($mtime = @filemtime($asset)) {
				$this->assets[] = $asset;
			} else {
				throw new \Exception("Could not stat $asset");
			}

			// last modified time
			$this->lastModified = max($mtime, $this->lastModified);
		}
	}

	public function dump()
	{
		$factory = $this->getAsseticFactory();
		$filters = array();

		foreach ($this->assets as $asset) {
			if (strpos($asset, ".less") !== false) {
				$filters[] = "less";
			}
			$filters[] = "?min";
			$assetObj = $factory->createAsset($asset, $filters);
			echo $assetObj->dump();
		}
	}

	protected function getAsseticFactory()
	{
		$factory = new AssetFactory($this->config["input_path"], $this->config["debug"]);
		$factory->setDefaultOutput("");

		// add a FilterManager to the AssetFactory
		$fm = new FilterManager();
		$factory->setFilterManager($fm);
		// adding some filters to the filter manager
		if ($this->config["less_filter"]) {
			$fm->set("less", new LessphpFilter());
		}
		$fm->set("min", new CssMinFilter());

		return $factory;
	}

	/**
	 * Check if the file/path is given with absolute path.
	 * 
	 * @param  string $path
	 * @return bool
	 */
	protected function isFullPath($path)
	{
		// *nix style
		if ($path[0] == "/") {
			return true;
		}

		// windows
		if (preg_match("#[a-z]:\\.+#iU", $path)) {
			return true;
		}

		return false;
	}
}
