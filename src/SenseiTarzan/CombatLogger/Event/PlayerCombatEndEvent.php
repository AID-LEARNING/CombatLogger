<?php

namespace SenseiTarzan\CombatLogger\Event;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;

class PlayerCombatEndEvent extends PlayerEvent implements Cancellable
{
    use CancellableTrait;


    public function __construct(Player $player)
    {
        $this->player = $player;
    }
}