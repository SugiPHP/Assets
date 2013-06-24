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
	 * File Name Template. Asterix (*) will be replaced with
	 * unique name based on all assets added, configuration settings
	 * and last modification time.
	 *
	 * @var string
	 */
	protected $filenameTemplate = "*";

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
		if (isset($config["file_name"])) {
			$this->setFilename($config["file_name"]);
		}
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
	 * Sets default input path or an array with paths.
	 *
	 * @param string $path
	 */
	public function setInputPath($path)
	{
		if(is_array($path)){
			$this->config["input_path"] = array_map(array($this,'_preparePath'), $path);
		}
		else
			$this->config["input_path"] = (array)$this->_preparePath($path);
		
		
	}
	
	/**
	 * Prepares an input path
	 * @param string $path A path to an input directory
	 * @return string The prepared path
	 */
	private function _preparePath($path){
		return rtrim($path, "/\\") . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Appends a path to the beggining of the array
	 * @param mixed $path String with the path or array with paths
	 */
	public function pushInputPath($path){
		$this->config["input_path"] = array_merge((array)$path,(array)$this->config["input_path"]);
	}
	
	/**
	 * Gets the first path of the array and removes it from the paths
	 * @return string The popped path
	 */
	public function popInputPath(){
		$input_path = (array)$this->config["input_path"];
		$poped_path = array_shift($input_path);
		$this->config["input_path"] = $input_path;
		return $poped_path;
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

	/**
	 * Sets output filename template.
	 * 
	 * @param string $filenameTemplate
	 */
	public function setFilename($filenameTemplate)
	{
		$this->filenameTemplate = $filenameTemplate;
	}

	/**
	 * Returns unique output file name (if * exists in the filename template).
	 *
	 * @return string
	 */
	public function getFileName()
	{
		if (strpos($this->filenameTemplate, "*") === false) {
			return $this->filenameTemplate;
		}

		$serial = serialize($this->config) . serialize($this->assets) . serialize($this->lastModified);
		$filename = substr(sha1($serial), 0, 11);

		return str_replace("*", $filename, $this->filenameTemplate);
	}

	/**
	 * Returns debug configuration option.
	 *
	 * @return bool
	 */
	public function getDebug()
	{
		return $this->config["debug"];
	}

	/**
	 * Sets debug configuration option. If this is set some minifications
	 * will not be done to ease debugging in development environment.
	 * 
	 * @param boolean $debug
	 */
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
				//Stack of paths
				if(is_array($this->config["input_path"])){
					$assetFound = FALSE;
					foreach ($this->config["input_path"] as $input_path){
						$fileName = $input_path . $asset;
						if(file_exists($fileName)){
							$this->_addSingleAsset($fileName);
							$assetFound = TRUE;
							break;
						}
					}
					//Throws an exception if an asset is not found
					if(!$assetFound)
						throw new \Exception("Could not find $asset");
				}
				//single path
				else{
					$this->_addSingleAsset($this->config["input_path"] . $asset);
				}
				
			}
			
		}
	}
	
	private function _addSingleAsset($asset){
			
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
	
	/**
	 * Dumps all assets files as one packed and minified version.
	 *
	 * @return string
	 */
	public function dump()
	{
		$filename = $this->getFileName();
		$path = $this->config["output_path"].$filename;

		return file_exists($path) ? file_get_contents($path) : $this->dumpAssets();
	}

	abstract protected function dumpAssets();

	/**
	 * Saves packed and minified assets in a file with unique name.
	 * Returns the filename without the path.
	 *
	 * @return string Unique filename of the saved file
	 */
	public function pack()
	{
		$filename = $this->getFileName();
		$path = $this->config["output_path"].$filename;

		if (!file_exists($path)) {
			file_put_contents($path, $this->dumpAssets());
		}

		return $filename;
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
