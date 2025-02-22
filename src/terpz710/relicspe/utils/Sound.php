<?php

declare(strict_types=1);

namespace terpz710\relicspe\utils;

use pocketmine\player\Player;

use pocketmine\network\mcpe\protocol\PlaySoundPacket;

use pocketmine\utils\SingletonTrait;

final class Sound {
    use SingletonTrait;

    public function playSound(Player $player, string $sound, float|int $volume, float|int $pitch) : void{
        $pos = $player->getPosition();
        $packet = PlaySoundPacket::create($sound, $pos->getX(), $pos->getY(), $pos->getZ(), $volume, $pitch);

        $player->getNetworkSession()->sendDataPacket($packet);
    }
}