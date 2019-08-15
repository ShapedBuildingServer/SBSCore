<?php

/**
 * Folder plugin loader taken from DevTools.
 */

namespace sbscore\module;

use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;

class ModuleLoader implements PluginLoader{
	/** @var \ClassLoader */
	private $loader;
	public function __construct(\ClassLoader $loader){
		$this->loader = $loader;
	}
	public function canLoadPlugin(string $path) : bool{
		return is_dir($path) and file_exists($path . "/plugin.yml") and file_exists($path . "/src/");
	}
	/**
	 * Loads the plugin contained in $file
	 *
	 * @param string $file
	 */
	public function loadPlugin(string $file) : void{
		$this->loader->addPath("$file/src");
	}
	/**
	 * Gets the PluginDescription from the file
	 *
	 * @param string $file
	 *
	 * @return null|PluginDescription
	 */
	public function getPluginDescription(string $file) : ?PluginDescription{
		if(is_dir($file) and file_exists($file . "/plugin.yml")){
			$yaml = @file_get_contents($file . "/plugin.yml");
			if($yaml != ""){
				return new PluginDescription($yaml);
			}
		}
		return null;
	}
	public function getAccessProtocol() : string{
		return "";
	}
}
