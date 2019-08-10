<?php

namespace sbscore;

use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use sbscore\command\TimeCommand;

class Core extends PluginBase
{
    /** @var Core */
    private static $instance = null;

    public function onEnable()
    {
        self::$instance = $this;

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();

        self::loadAllLevels();

        $this->preprocessCommands();
        $this->executeStartupCommands();
    }

    private function preprocessCommands()
    {
        $commandMap = $this->getServer()->getCommandMap();
        $command = $commandMap->getCommand('time');
        $command->setLabel('_time');
        $command->unregister($commandMap);

        $timeCommand = new TimeCommand($this);
        $commandMap->register('sbs', $timeCommand);
    }

    public function setGlobalTime(int $value, bool $stopTime = true)
    {
        foreach ($this->getServer()->getLevels() as $level) {
            $level->setTime($value);

            if ($stopTime) $level->stopTime();
        }
    }

    private static function loadAllLevels()
    {
        $worlds = glob(Server::getInstance()->getDataPath() . 'worlds/*', GLOB_ONLYDIR);
        self::getInstance()->getLogger()->info(TextFormat::AQUA . "Beginning loading of " . TextFormat::DARK_AQUA . count($worlds) . TextFormat::AQUA . " worlds.");

        foreach ($worlds as $worldPath) {
            $levelName = basename($worldPath);
            if (!Server::getInstance()->loadLevel($levelName)) continue;

            $level = Server::getInstance()->getLevelByName($levelName);
            $level->setTime(self::getInstance()->getConfig()->get('startupTime'));
            $level->stopTime();
        }

        self::getInstance()->getLogger()->info(TextFormat::AQUA . "World loading complete.");
    }

    private function executeStartupCommands()
    {
        foreach ($this->getConfig()->get('startupCommands') as $startupCommand) {
            $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $startupCommand);
        }
    }

    public static function getInstance() :Core
    {
        return self::$instance;
    }
}
