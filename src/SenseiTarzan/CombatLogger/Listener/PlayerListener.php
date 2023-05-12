<?php

namespace SenseiTarzan\CombatLogger\Listener;

use pocketmine\command\utils\CommandStringHelper;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\CommandEvent;
use pocketmine\player\Player;
use SenseiTarzan\CombatLogger\Component\PlayerCombatManager;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SenseiTarzan\CombatLogger\Utils\CustomKnownTranslationFactory;
use SenseiTarzan\ExtraEvent\Class\EventAttribute;

class PlayerListener
{

    #[EventAttribute(EventPriority::LOWEST)]
    public function onJoin(PlayerJoinEvent $event): void
    {
        PlayerCombatManager::getInstance()->loadPlayer($event->getPlayer());
    }

    #[EventAttribute(EventPriority::LOWEST)]
    public function onQuit(PlayerQuitEvent $event): void
    {
        PlayerCombatManager::getInstance()->unloadPlayer($event->getPlayer());
    }

    #[EventAttribute(EventPriority::MONITOR)]
    public function onAttack(EntityDamageByEntityEvent $event): void
    {
        if ($event->isCancelled()) return;
        $victim = $event->getEntity();
        $damager = $event->getDamager();
        if ($damager instanceof Player && $victim instanceof Player) {
            PlayerCombatManager::getInstance()->addCombat($damager, $victim);
        }
    }

    #[EventAttribute(EventPriority::LOWEST)]
    public function onCommand(CommandEvent $event)
    {
        $player = $event->getSender();
        if ($player instanceof Player) {
            $playerCombat = PlayerCombatManager::getInstance()->getPlayer($player);
            if ($playerCombat === null || !$playerCombat->isInCombat()) {
                return;
            }
            $args = CommandStringHelper::parseQuoteAware($event->getCommand());
            $command = array_shift($args);
            if ( PlayerCombatManager::getInstance()->isBlockedCommand($player,$command)) {
                $event->cancel();
                $player->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::combat_logger_no_access_command_message($command)));
            }
        }

    }
}