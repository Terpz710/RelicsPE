<?php

declare(strict_types=1);

namespace terpz710\relicspe;

use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;

use terpz710\relicspe\manager\RelicsManager;

use terpz710\relicspe\utils\Sound;
use terpz710\relicspe\utils\Reward;
use terpz710\relicspe\utils\Message;

class EventListener implements Listener {

    protected RelicsManager $manager;

    public function __construct(protected RelicsPE $plugin) {
        $this->plugin = $plugin;
        $this->manager = RelicsManager::getInstance();
    }

    public function onBlockBreak(BlockBreakEvent $event) : void{
        $player = $event->getPlayer();
        $chance = $this->plugin->getConfig()->get("relic_drop_chance");
        $sound = $this->plugin->getConfig()->get("sound");
        $volume = $this->plugin->getConfig()->get("sound_volume");
        $pitch = $this->plugin->getConfig()->get("sound_pitch");

        if (mt_rand(1, 100) > $chance) {
            return;
        }

        $relicRarity = $this->manager->getRandomRelicRarity();

        if ($relicRarity !== null) {
            $relic = $this->manager->createPrismarineRelic($player, $relicRarity);
            $relicData = $this->plugin->getConfig()->get("relics", [])[$relicRarity];

            if ($relicData === null) {
                return;
            }

            $relicName = $relicData["name"];
            $player->getInventory()->addItem($relic);
            Sound::getInstance()->playSound($player, $sound, $volume, $pitch);
            $player->sendMessage((string) new Message("relic_found_message", ["{relic_name}"], [$relicName]));
            $player->sendTitle((string) new Message("relic_found_title", ["{relic_name}"], [$relicName]));
            $player->sendSubTitle((string) new Message("relic_found_subtitle", ["{relic_name}"], [$relicName]));
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event) : void{
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getNamedTag()->getTag("rarity")) {
            Reward::getInstance()->giveReward($player, $item);
        }
    }
}