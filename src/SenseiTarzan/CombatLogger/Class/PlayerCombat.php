<?php

namespace SenseiTarzan\CombatLogger\Class;

use pocketmine\player\Player;
use SenseiTarzan\CombatLogger\Component\PlayerCombatManager;
use SenseiTarzan\CombatLogger\Entity\CloneAttacker;
use SenseiTarzan\CombatLogger\Event\PlayerCombatEndEvent;
use SenseiTarzan\CombatLogger\Event\PlayerQuitFightEvent;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SenseiTarzan\CombatLogger\Utils\CustomKnownTranslationFactory;

class PlayerCombat
{
    private ?Combat $combat = null;


    /**
     * @param Player $player
     * @param string $optionLogout "kill" or "clone"
     */
    public function __construct(private Player $player, private string $optionLogout)
    {
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function hasCombat(): bool
    {
        return $this->combat !== null;
    }

    public function isInCombat(): bool
    {
        return $this->hasCombat() && !$this->combat->isExpired();
    }

    /**
     * @param Combat|null $combat
     */
    public function setCombat(?Combat $combat): void
    {
        $this->combat = $combat;
    }

    /**
     * @return Combat|null
     */
    public function getCombat(): ?Combat
    {
        return $this->combat;
    }

    public function startCombat(Player $victim, int $counter): void
    {
        $this->setCombat(new Combat($victim, $counter));
        $this->getPlayer()->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($this->getPlayer(), CustomKnownTranslationFactory::combat_logger_start_message($victim, (floor($counter / 20)))));
    }

    public function __destruct()
    {
        if ($this->isInCombat()) {
            $event = new PlayerCombatEndEvent($this->getPlayer());
            $event->call();
            if ($event->isCancelled()) return;
            PlayerCombatManager::getInstance()->removeCombat($this->getCombat()->getVictim());
            if ($this->optionLogout === "kill") {
                $this->getPlayer()->kill();
            } elseif ($this->optionLogout === "clone") {
                $clone = new CloneAttacker(($player = $this->getPlayer())->getLocation(), $player->getSkin(), $player->saveNBT()->setString("NameVictim", $player->getName()));
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
                $player->getOffHandInventory()->clearAll();
                $player->getXpManager()->setXpAndProgress(0, 0);
                $player->setLastDamageCause(new PlayerQuitFightEvent($player));
                $player->kill();
                $clone->spawnToAll();
            }
        }
    }
}