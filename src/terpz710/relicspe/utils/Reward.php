<?php

declare(strict_types=1);

namespace terpz710\relicspe\utils;

use pocketmine\player\Player;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;

use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

use function array_rand;
use function class_exists;

use terpz710\relicspe\RelicsPE;

use terpz710\relicspe\manager\RelicsManager;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantManager;

final class Reward {
    use SingletonTrait;

    protected RelicsPE $plugin;

    public function __construct() {
        $this->plugin = RelicsPE::getInstance();
    }

    public function giveReward(Player $player, Item $relic) : void{
        $relicRarity = RelicsManager::getInstance()->getRelicRarity($relic);
        
        if ($relicRarity === null) {
            $player->sendMessage("This message shouldnt appear! Let the plugin owner know!");
            return;
        }

        if ($player->getInventory()->contains($relic)) {
            $player->getInventory()->removeItem($relic->setCount(1));

            $rewardsConfig = new Config($this->plugin->getDataFolder() . "rewards.yml");
            $rewards = $rewardsConfig->get('rewards', []);

            $relicRewards = $rewards[$relicRarity];
            if (!empty($relicRewards)) {
                $reward = $relicRewards[array_rand($relicRewards)];
                $this->giveItemReward($player, $reward);
            } else {
                $player->sendMessage("Error: No rewards defined for {$relicRarity} rarity!");
            }
        }
    }

    private function giveItemReward(Player $player, array $reward) : void{
        $itemName = $reward['item'];

        if (empty($itemName)) {
            $player->sendMessage("Error: Undefined item key in reward configuration!");
            return;
        }

        $item = StringToItemParser::getInstance()->parse($itemName);
        if ($item === null) {
            $player->sendMessage("Error: Invalid item key in reward configuration.");
            return;
        }

        if (isset($reward['enchantments'])) {
            foreach ($reward['enchantments'] as $enchantmentName => $enchantmentLevel) {
                $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentName);

                if ($enchantment === null && class_exists(CustomEnchantManager::class)) {
                    $enchantment = CustomEnchantManager::getEnchantmentByName($enchantmentName);
                }

                if ($enchantment !== null) {
                    $enchantmentInstance = new EnchantmentInstance($enchantment, (int) $enchantmentLevel);
                    $item->addEnchantment($enchantmentInstance);
                } else {
                    $player->sendMessage("Error: Invalid enchantment key in reward configuration.");
                }
            }
        }

        $customName = $reward['custom_name'] ?? $item->getVanillaName();

        if (isset($reward['custom_name'])) {
            $item->setCustomName($reward['custom_name']);
        }

        $quantity = isset($reward["quantity"]) ? (int) $reward["quantity"] : 1;

        if (isset($reward['quantity'])) {
            $item->setCount($reward['quantity']);
        }

        $player->getInventory()->addItem($item);
        $player->sendMessage((string) new Message("relic_claimed_message", ["{item_name}", "{amount}"], [$customName, $quantity]));
    }
}