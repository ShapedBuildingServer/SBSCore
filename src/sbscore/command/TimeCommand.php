<?php

namespace sbscore\command;

use pocketmine\plugin\Plugin;
use pocketmine\command\{
    Command, CommandSender, PluginIdentifiableCommand
};
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use sbscore\Core;
use sbscore\task\TimeTransition;

class TimeCommand extends Command implements PluginIdentifiableCommand
{
    /** @var int */
    const DAY = 1000;
    const SUNSET = 12500;
    const NIGHT = 14000;
    const SUNRISE = 23500;

    /** @var Core */
    private $core;

    public function __construct(Core $core)
    {
        parent::__construct('time', 'Manage time.', '/time set <value>', ['day', 'sunset', 'night', 'sunrise']);
        $this->setPermission('sbs.command.time');

        $this->core = $core;
    }

    public function execute(CommandSender $sender, string $alias, array $args)
    {
        switch ($alias) {
            case 'day':
            case 'sunset':
            case 'night':
            case 'sunrise':
                $timeValue = constant('self::' . strtoupper($alias));

                $this->core->setGlobalTime($timeValue);
                $sender->sendMessage("§bGlobal time set to §3{$alias} §e({$timeValue} ticks)");
                break;
            default:
                if (count($args) < 2) {
                    $sender->sendMessage($this->getUsage());

                    return;
                }

                switch ($args[0]) {
                    case 'set':
                        $timeValue = $args[1];

                        $this->core->setGlobalTime($timeValue);
                        $sender->sendMessage("§bGlobal time set to §3{$timeValue} ticks");
                        break;
                    default:
                        $sender->sendMessage($this->getUsage());

                        return;
                }
                break;
        }
    }

    public function getPlugin() :Plugin
    {
        return $this->core;
    }
}
