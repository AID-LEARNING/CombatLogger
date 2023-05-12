<?php

namespace SenseiTarzan\CombatLogger;

use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use SenseiTarzan\CombatLogger\Component\PlayerCombatManager;
use SenseiTarzan\CombatLogger\Entity\CloneAttacker;
use SenseiTarzan\LanguageSystem\Component\LanguageManager;
use SenseiTarzan\Path\PathScanner;
use SenseiTarzan\CombatLogger\Listener\PlayerListener;
use SenseiTarzan\CombatLogger\Task\CombatLoggerTask;
use SenseiTarzan\ExtraEvent\Component\EventLoader;
use Symfony\Component\Filesystem\Path;

class Main extends PluginBase
{

    protected function onLoad(): void
    {
        if (!file_exists(Path::join($this->getDataFolder(), "config.yml"))) {
            foreach (PathScanner::scanDirectoryGenerator($search =  Path::join(dirname(__DIR__,3) , "resources")) as $file){
                @$this->saveResource(str_replace($search, "", $file));
            }
        }
        new LanguageManager($this);
        EntityFactory::getInstance()->register(CloneAttacker::class, function (World $world, CompoundTag $nbt): CloneAttacker {
            return new CloneAttacker(EntityDataHelper::parseLocation($nbt,$world), Human::parseSkinNBT($nbt), $nbt);
        }, ["CloneAttacker"]);

        new PlayerCombatManager($this);
    }

    protected function onEnable(): void
    {
        EventLoader::loadEventWithClass($this,  PlayerListener::class);
        LanguageManager::getInstance()->loadCommands("combat_logger");
        $this->getScheduler()->scheduleRepeatingTask(new CombatLoggerTask(), 20);
    }


    protected function onDisable(): void
    {
        PlayerCombatManager::getInstance()->onDisable();
    }

}