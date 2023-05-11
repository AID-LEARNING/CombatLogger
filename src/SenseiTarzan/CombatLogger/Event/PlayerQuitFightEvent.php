<?php

namespace SenseiTarzan\CombatLogger\Event;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageEvent;

class PlayerQuitFightEvent extends EntityDamageEvent
{


    public function __construct(Entity $entity)
    {
        parent::__construct($entity, self::CAUSE_CUSTOM, 0,[]);
    }


}