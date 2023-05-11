<?php

namespace SenseiTarzan\CombatLogger\Utils;

use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class CustomKnownTranslationFactory
{

    public static function combat_logger_start_message(Player $player, int $time): Translatable{
        return new Translatable(CustomKnownTranslationKeys::COMBAT_LOGGER_START_MESSAGE, [
            "player" => $player->getName(),
            "time" => $time
        ]);
    }

    public static function combat_logger_end_message(): Translatable{
        return new Translatable(CustomKnownTranslationKeys::COMBAT_LOGGER_END_MESSAGE, [
        ]);
    }

    public static function combat_logger_nametag(string  $clone, int $time): Translatable{
        return new Translatable(CustomKnownTranslationKeys::COMBAT_LOGGER_NAMETAG, [
            "clone" => $clone,
            "time" => $time
        ]);
    }

    public static function combat_logger_no_access_command_message(string $command): Translatable{
        return new Translatable(CustomKnownTranslationKeys::COMBAT_LOGGER_NO_ACCESS_COMMAND_MESSAGE, [
            "command" => $command
        ]);
    }



}