<?php

namespace SenseiTarzan\CombatLogger\Component;

use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use SenseiTarzan\CombatLogger\Class\PlayerCombat;
use SenseiTarzan\CombatLogger\libs\SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SenseiTarzan\CombatLogger\Main;
use SenseiTarzan\CombatLogger\Utils\CustomKnownTranslationFactory;

class PlayerCombatManager
{

    use SingletonTrait;

    /**
     * @var PlayerCombat[] $players
     */
    private array $players = [];
    /**
     * @var int $defaultCounterInTicks
     */
    private int $defaultCounterInTicks;
    /**
     * @var string $optionLogout "kill" or "clone"
     */
    private string $optionLogout;
    /**
     * @var string[] $listCommandsBlocked key: command name value: permission
     */
    private array $listCommandsBlocked = [];
    private int $lastUpdate;

    public function __construct(private Main $plugin)
    {
        $config = $plugin->getConfig();
        $this->defaultCounterInTicks = $config->get("time-of-combat", 5) * 20;
        $this->optionLogout = $config->get("type-action-after-logout", "kill");
        $this->loadPermissionForByPassCommand($config->get("list-commands-blocked", []));
        self::setInstance($this);
        $this->lastUpdate = $plugin->getServer()->getTick();
    }

    public function loadPermissionForByPassCommand(array $list): void
    {
        $permissionManager = PermissionManager::getInstance();
        $logger = $this->plugin->getLogger();
        foreach ($list as $command) {
            $command = strtolower($command);
            $permission = "combatlogger.bypass.{$command}";
            if ($permissionManager->getPermission($permission) === null) {
                $permissionManager->addPermission(new Permission($permission, "CombatLogger bypass {$command}"));
            }
            $logger->info("Added permission {$permission} for bypass command {$command}");
            $this->listCommandsBlocked[$command] = $permission;
        }

    }

    /**
     * @return array
     */
    public function loadPlayer(Player $player): void
    {
        $this->players[strtolower($player->getName())] = new PlayerCombat($player, $this->optionLogout);
    }

    public function unloadPlayer(Player $player): void
    {
        unset($this->players[strtolower($player->getName())]);
    }

    public function getPlayer(Player $player): ?PlayerCombat
    {
        return $this->players[strtolower($player->getName())] ?? null;
    }

    /**
     * @return PlayerCombat[]
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    public function isBlockedCommand(Player $player, string $command): bool
    {
        return isset($this->listCommandsBlocked[strtolower($command)]) && !$this->hasPermissionByPassCommand($player, $command);
    }

    private function getPermissionByPassCommand(string $command): ?string
    {
        return $this->listCommandsBlocked[strtolower($command)] ?? null;
    }

    private function hasPermissionByPassCommand(Player $player, string $command): bool
    {
        return $player->hasPermission($this->getPermissionByPassCommand($command));
    }

    /**
     * @return int
     */
    public function getDefaultCounterInTicks(): int
    {
        return $this->defaultCounterInTicks;
    }

    public function addCombat(Player $player, Player $victim): void
    {
        $playerCombat = $this->getPlayer($player);
        $victimCombat = $this->getPlayer($victim);
        if ($playerCombat === null) return;
        if ($playerCombat->isInCombat() && $playerCombat->getCombat()->getVictim()->getXuid() === $victim->getXuid()
            && $victimCombat->isInCombat() && $victimCombat->getCombat()->getVictim()->getXuid() === $player->getXuid()) {
            $playerCombat->getCombat()->setCounter($this->getDefaultCounterInTicks());
            $victimCombat->getCombat()->setCounter($this->getDefaultCounterInTicks());
            return;
        }
        $playerCombat->startCombat($victim, $this->getDefaultCounterInTicks());
        $victimCombat->startCombat($player, $this->getDefaultCounterInTicks());

    }

    public function removeCombat(Player $player): void
    {
        $playerCombat = $this->getPlayer($player);
        if ($playerCombat === null) return;
        $victim = $playerCombat->getCombat()->getVictimPlayerCombat();
        $playerCombat->setCombat(null);
        $playerCombat->getPlayer()->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::combat_logger_end_message()));
        if ($victim === null) return;
        if ($victim->getCombat()?->getVictim()->getXuid() === $player->getXuid()) {
            $victim->setCombat(null);
            ($player = $victim->getPlayer())->sendMessage(LanguageManager::getInstance()->getTranslateWithTranslatable($player, CustomKnownTranslationFactory::combat_logger_end_message()));
        }
    }

    public function onUpdate(int $currentTick): void
    {
        $tickDiff = $currentTick - $this->lastUpdate;
        $this->lastUpdate = $currentTick;
        foreach ($this->getPlayers() as $playerCombat) {
            $combat = $playerCombat->getCombat();
            if ($combat === null) continue;
            $combat->subtractCounter($tickDiff);
            if ($combat->isExpired()) $this->removeCombat($playerCombat->getPlayer());
        }
    }

    public function onDisable(): void
    {
        foreach ($this->getPlayers() as $playerCombat) {
            $combat = $playerCombat->getCombat();
            if ($combat === null) continue;
            $this->removeCombat($playerCombat->getPlayer());
        }
    }

}