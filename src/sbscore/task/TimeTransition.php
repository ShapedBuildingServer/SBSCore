<?php

namespace sbscore\task;

use pocketmine\level\Level;
use pocketmine\scheduler\Task;
use sbscore\Core;

class TimeTransition extends Task
{
    const TRANSITION_VALUE = 100;

    /** @var Level */
    private $level;
    /** @var int */
    private $currentTime;
    /** @var int */
    private $desiredTime;
    /** @var bool */
    private $decrement = false;

    public function __construct(Level $level, int $timeValue)
    {
        $this->level = $level;
        $this->currentTime = $level->getTime();
        $this->desiredTime = round($timeValue, -3);

        if ($this->currentTime > $timeValue) $this->decrement = true;
    }

    public function onRun(int $tick)
    {
        // Guard in case the level has been unloaded for some reason.
        if ($this->level == null) {
            $this->cancel();

            return;
        }

        if (round($this->level->getTime(), -3) == $this->desiredTime) {
            Core::getInstance()->getScheduler()->scheduleDelayedTask(new TimeTransition($this->level, Core::getInstance()->getConfig()->get('defaultTime')), 5 * 60 * 20);
            $this->cancel();

            return;
        }

        if ($this->decrement) {
            $this->currentTime -= self::TRANSITION_VALUE;
        } else {
            $this->currentTime += self::TRANSITION_VALUE;
        }

        $this->level->setTime($this->currentTime);
    }

    private function cancel()
    {
        Core::getInstance()->getScheduler()->cancelTask($this->getTaskId());
    }
}
