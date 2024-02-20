<?php

namespace Terpz710\RelicsPE;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\player\Player;
use pocketmine\utils\Config;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantManager;

class RelicsManager implements Listener {

    private $plugin;

    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
    }

    public static function createPrismarineRelic(string $rarity): Item {
        $color = "";
        switch ($rarity) {
            case "Common":
                $color = "§e";
                break;
            case "Uncommon":
                $color = "§a";
                break;
            case "Rare":
                $color = "§b";
                break;
            case "Epic":
                $color = "§d";
                break;
            case "Legendary":
                $color = "§1";
                break;
            case "Mythical":
                $color = "§5";
                break;
        }

        $relic = VanillaItems::PRISMARINE_SHARD();
        $relic->setCustomName("§r{$color}{$rarity}§r §frelic");
        $relic->setLore(["§r§e§l(!)§r§f Right/Left click to claim!"]);
        $relic->getNamedTag()->setString("RelicRarity", $rarity);
        
        return $relic;
    }

    public function createPrismarineRelicItem(string $rarity): Item {
        return self::createPrismarineRelic($rarity);
    }

    public static function getAllRelics(): array {
        return ["Common", "Uncommon", "Rare", "Epic", "Legendary", "Mythical"];
    }

    public static function isRelic($item): bool {
        if ($item instanceof Item) {
            $tags = $item->getNamedTag();
            $relicRarity = $tags->getString("RelicRarity", "");
            return $relicRarity !== "" && is_string($relicRarity);
        }
        return false;
    }

    public static function getRelicRarity(Item $item): ?string {
        $tags = $item->getNamedTag();
        return $tags->getString("RelicRarity", null);
    }

    public function onBlockBreak(BlockBreakEvent $event) {
    $player = $event->getPlayer();
    $relicRarity = $this->getRandomRelicRarity();
    
    $color = "";

    if ($relicRarity !== null && $this->chanceToGetRelic($player)) {
        switch ($relicRarity) {
            case "Common":
                $color = "§e";
                break;
            case "Uncommon":
                $color = "§a";
                break;
            case "Rare":
                $color = "§b";
                break;
            case "Epic":
                $color = "§d";
                break;
            case "Legendary":
                $color = "§1";
                break;
            case "Mythical":
                $color = "§5";
                break;
        }

        $relic = $this->createPrismarineRelicItem($relicRarity);
        $player->getInventory()->addItem($relic);
        $player->sendMessage("§a§l(!)§r§f You have uncovered a {$color}{$relicRarity}§r§f relic!");
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
    $player = $event->getPlayer();
    $item = $event->getItem();

    if ($this->isRelic($item)) {
        $relicRarity = $this->getRelicRarity($item);

        if ($player->getInventory()->contains($item)) {
            $player->getInventory()->removeItem($item->setCount(1));

            $rewardsConfig = new Config($this->plugin->getDataFolder() . "rewards.yml", Config::YAML);
            $rewards = $rewardsConfig->get('rewards', []);

            $relicRewards = $rewards[$relicRarity] ?? [];
            if (!empty($relicRewards)) {
                $reward = $relicRewards[array_rand($relicRewards)];
                $itemName = $reward['item'] ?? '';
                $quantity = isset($reward["quantity"]) ? (int) $reward["quantity"] : 1;

                if (!empty($itemName)) {
                    $item = StringToItemParser::getInstance()->parse($itemName);
                    $item->setCount($quantity);

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
                                $player->sendMessage("§c§l(!)§r§f Error: Invalid enchantment key in reward configuration.");
                            }
                        }
                    }

                    $customName = $reward['custom_name'] ?? '';
                    $item->setCustomName(!empty($customName) ? $customName : $item->getVanillaName());
                    
                    $player->getInventory()->addItem($item);
                    $player->sendMessage("§b§l(!)§r§f You received §e{$quantity}x {$item->getCustomName()}§f from the relic!");
                } else {
                    $player->sendMessage("§c§l(!)§r§f Error: Undefined item key in reward configuration!");
                    }
                } else {
                $player->sendMessage("§c§l(!)§r§f Error: No rewards defined for {$relicRarity} rarity!");
                }
            }
        }
    }

    private function getRandomRelicRarity(): ?string {
        $rarities = [
            "Common" => 70,
            "Uncommon" => 20,
            "Rare" => 7,
            "Epic" => 2,
            "Legendary" => 1,
            "Mythical" => 1,
        ];

        $totalChance = array_sum($rarities);
        $random = mt_rand(1, $totalChance);

        foreach ($rarities as $rarity => $chance) {
            if ($random <= $chance) {
                return $rarity;
            }
            $random -= $chance;
        }

        return null;
    }

    private function chanceToGetRelic(Player $player): bool {
        $chance = 0.1;

        return (mt_rand(1, 100) <= $chance * 100);
    }
}
