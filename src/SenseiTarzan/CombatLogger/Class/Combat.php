<?php
namespace SenseiTarzan\CombatLogger\Class;

use pocketmine\player\Player;
use SenseiTarzan\CombatLogger\Component\PlayerCombatManager;

class Combat
{

    public function __construct(private Player $victim, private int $counter)
    {

    }

    /**
     * @return Player
     */
    public function getVictim(): Player
    {
        return $this->victim;
    }

    public function getVictimPlayerCombat(): ?PlayerCombat
    {
        return PlayerCombatManager::getInstance()->getPlayer($this->victim);
    }

    /**
     * @return int
     */
    public function getCounter(): int
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     */
    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public function subtractCounter(int $counter): void
    {
        $this->counter -= $counter;
    }


    public function isExpired(): bool
    {
        return $this->counter <= 0;
    }
}
