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
	 *  - debug - minifications are not done when debug is TRUE; default is FALSE;
	 */
	public function __construct(array $config)
	{
		$this->setInputPath($config["input_path"]);
		$this->setOutputPath($config["output_path"]);
		$this->setDebug(isset($config["debug"]) ? $config["debug"] : false);
		// Used to determine if we need to load LessPHP filter
		$this->config["less_filter"] = false;
	}

	public function getInputPath()
	{
		return $this->config["input_path"];
	}

	public function setInputPath($path)
	{
		$this->config["input_path"] = rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
	}

	public function getOutputPath()
	{
		return $this->config["output_path"];
	}

	public function setOutputPath($path)
	{
		$this->config["output_path"] = rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
	}

	public function getDebug()
	{
		return $this->config["debug"];
	}

	public function setDebug($debug)
	{
		$this->config["debug"] = $debug;
	}

	public function getAssets()
	{
		return $this->assets;
	}
	
	/**
	 * Adds css file(s)
	 * 
	 * @param string|array $assets List of files or a single file. The file can be absolute path or relative to the input_path
	 */
	public function addAsset($assets)
	{
		$assets = (array) $assets;

		// Check one by one and throw an exception if the file is not found
		foreach ($assets as $asset) {
			// if it is a less file than we'll need LessPHP filter
			if (strpos($asset, ".less") !== false) {
				$this->config["less_filter"] = true;
			}

			// prepend search path?
			if (!$this->isAbsolutePath($asset)) {
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

	public function pack($save = true)
	{
		$filename = $this->getFileName();
		$path = $this->config["output_path"].$filename;

		if ($save) {
			if (!file_exists($path)) {
				file_put_contents($path, $this->dumpAssets());
			}

			return $filename;
		}

		return file_exists($path) ? file_get_contents($path) : $this->dumpAssets();
	}

	public function getFileName()
	{
		$str = serialize($this->config) . serialize($this->assets) . serialize($this->lastModified);

		return "_".substr(sha1($str), 0, 11).".css";
	}

	protected function dumpAssets()
	{
		$factory = $this->getAsseticFactory();
		$filters = array();
		$buffer = "";

		foreach ($this->assets as $asset) {
			if (substr($asset, -5) === ".less") {
				$filters[] = "less";
			}
			$filters[] = "?min";
			$assetObj = $factory->createAsset($asset, $filters);
			$buffer .= $assetObj->dump();
		}

		return $buffer;
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
	 * Check if the file is given with absolute path.
	 * 
	 * @param  string $path
	 * @return bool
	 */
	protected function isAbsolutePath($path)
	{
		// *nix style
		if ($path[0] == "/") {
			return true;
		}

		// windows
		if ((strlen($path) > 3) and ctype_alpha($path[0]) and ($path[1] == ":") and ($path[2] = "\\")) {
			return true;
		}

		return false;
	}
}
