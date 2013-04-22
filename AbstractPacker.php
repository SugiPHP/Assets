<?php
/**
 * @package    SugiPHP
 * @subpackage Assets
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\Assets;

abstract class AbstractPacker
{
	/**
	 * Configuration settings.
	 * 
	 * @var array
	 */
	protected $config = array();

	/**
	 * List of loaded assets.
	 * 
	 * @var array
	 */
	protected $assets = array();

	/**
	 * Most resent modified time from all added assets.
	 *
	 * @var integer
	 */
	protected $lastModified = 0;

	/**
	 * Asset Packer Constructor
	 * 
	 * @param array $config
	 *  - output_path - the directory where cached files will be created. 
	 *      This should be within your DOCUMENT ROOT and be visible from web. 
	 *  	The server must have write permissions for this path.
	 *  - input_path - the directory where actual uncompressed files are. This can be anywhere in the server.
	 *  - debug - minifications are not done when debug is TRUE; default is FALSE;
	 */
	public function __construct($config)
	{
		$this->setInputPath($config["input_path"]);
		$this->setOutputPath($config["output_path"]);
		$this->setDebug(isset($config["debug"]) ? $config["debug"] : false);
	}

	/**
	 * Returns default input path.
	 *
	 * @return string
	 */
	public function getInputPath()
	{
		return $this->config["input_path"];
	}

	/**
	 * Sets default input path.
	 *
	 * @param string $path
	 */
	public function setInputPath($path)
	{
		$this->config["input_path"] = rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
	}

	/**
	 * Returns default output path.
	 *
	 * @return string
	 */
	public function getOutputPath()
	{
		return $this->config["output_path"];
	}

	/**
	 * Sets default output path.
	 *
	 * @param string $path
	 */
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

	/**
	 * Add asset(s) file(s)
	 * 
	 * @param string|array $assets List of files or a single file. The file can be absolute path or relative to the input_path
	 */
	public function add($assets)
	{
		$assets = (array) $assets;

		// Check one by one and throw an exception if the file is not found
		foreach ($assets as $asset) { 
			// prepend search path?
			if (!$this->isAbsolutePath($asset)) {
				$asset = $this->config["input_path"] . $asset;
			}
				
			// Check the file and gets last modified date
			if (!$mtime = @filemtime($asset)) {
				throw new \Exception("Could not stat $asset");
			}
			
			// make some custom stuff
			$this->addAsset($asset);

			// add it in the list
			$this->assets[] = $asset;

			// last modified time
			$this->lastModified = max($mtime, $this->lastModified);
		}
	}

	/**
	 * Adds asset(s)
	 * 
	 * @param string $assets The file can be absolute path or relative to the input_path
	 */
	abstract protected function addAsset($asset);

	/**
	 * Returns list of all added assets.
	 * 
	 * @return array
	 */
	public function getAssets()
	{
		return $this->assets;
	}
	
	public function dump()
	{
		$filename = $this->getFileName();
		$path = $this->config["output_path"].$filename;

		return file_exists($path) ? file_get_contents($path) : $this->dumpAssets();
	}

	abstract protected function dumpAssets();

	public function pack()
	{
		$filename = $this->getFileName();
		$path = $this->config["output_path"].$filename;

		if (!file_exists($path)) {
			file_put_contents($path, $this->dumpAssets());
		}

		return $filename;
	}

	abstract public function getFileName();

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
