<?php

namespace SenseiTarzan\CombatLogger\Task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use SenseiTarzan\CombatLogger\Component\PlayerCombatManager;

class CombatLoggerTask extends Task
{

    /**
     * @inheritDoc
     */
    public function onRun(): void
    {
        PlayerCombatManager::getInstance()->onUpdate(Server::getInstance()->getTick());
    }
}