<?php

declare(strict_types=1);

namespace terpz710\relicspe\manager;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\EnchantmentInstance;

use pocketmine\data\bedrock\EnchantmentIdMap;

use pocketmine\player\Player;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

use function mt_rand;
use function array_sum;
use function array_keys;
use function array_column;

use terpz710\relicspe\RelicsPE;

final class RelicsManager {
    use SingletonTrait;

    protected RelicsPE $plugin;

    protected Config $config;

    public function __construct() {
        $this->plugin = RelicsPE::getInstance();
        $this->config = $this->plugin->getConfig();
    }

    public function createPrismarineRelic(Player $player, string $rarity) : Item{
        $relicData = $this->config->get("relics", [])[$rarity];

        if (!$relicData) {
            $player->sendMessage($rarity . " not found, this message shouldnt pop up. Let the plugin owner know!");
            return VanillaItems::PRISMARINE_SHARD();
        }

        $name = $relicData["item_name"];
        $lore = $relicData["item_lore"];

        $relic = VanillaItems::PRISMARINE_SHARD();
        $relic->setCustomName($name);
        $relic->setLore($lore);
        $relic->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(RelicsPE::FAKE_ENCH_ID), 1));
        $relic->getNamedTag()->setString("rarity", $rarity);

        return $relic;
    }

    public function getAllRelics() : array{
        return array_keys($this->config->get("relics", []));
    }

    public function getRelicRarity(Item $item) : ?string{
        return $item->getNamedTag()->getString("rarity");
    }

    public function getRandomRelicRarity() : ?string{
        $relics = $this->config->get("relics", []);
        if (empty($relics)) {
            return null;
        }

        $totalChance = array_sum(array_column($relics, "chance"));
        $random = mt_rand(1, $totalChance);

        foreach ($relics as $rarity => $data) {
            $chance = $data["chance"];
            if ($random <= $chance) {
                return $rarity;
            }
            $random -= $chance;
        }

        return null;
    }
}
