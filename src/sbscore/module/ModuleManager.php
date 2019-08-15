<?php

namespace sbscore\module;

use pocketmine\plugin\{
    PluginBase, PluginLoadOrder
};
use sbscore\Core;

class ModuleManager
{
    /** @var Plugin[] */
    private static $modules = [];

    public static function enableModules(Core $core)
    {
        $core->getServer()->getPluginManager()->registerInterface(new ModuleLoader($core->getServer()->getLoader()));
		$core->getServer()->getPluginManager()->loadPlugins(self::getModulesPath(), [ModuleLoader::class]);
		$core->getServer()->enablePlugins(PluginLoadOrder::STARTUP);

        foreach ($core->getServer()->getPluginManager()->getPlugins() as $plugin) {
            if ($plugin instanceof PluginModule) {
                $name = $plugin->getDescription()->getName();
                self::$modules[$name] = $plugin;

                $core->getLogger()->info("§aLoaded module '$name'");
            }
        }
    }

    public static function getModulesPath() :string
    {
        return __DIR__ . '/';
    }
}
