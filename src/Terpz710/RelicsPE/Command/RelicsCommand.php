<?php

declare(strict_types=1);

namespace Terpz710\RelicsPE\Command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

use Terpz710\RelicsPE\Relics;
use Terpz710\RelicsPE\RelicsManager;

class RelicsCommand extends Command implements PluginOwned {

    private RelicsManager $relicsManager;

    public function __construct(Relics $plugin, RelicsManager $relicsManager) {
        $this->plugin = $plugin;
        $this->relicsManager = $relicsManager;
        parent::__construct("relics", "Relics");
        $this->setPermission("relicspe.cmd");
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
    if ($sender instanceof Player) {
        if (empty($args)) {
            $sender->sendMessage("Available Relics: " . implode(", ", $this->relicsManager->getAllRelics()));
        } else {
            $rarity = $args[0];

            if (in_array($rarity, $this->relicsManager->getAllRelics(), true)) {
                $relic = $this->relicsManager->createPrismarineRelicItem($rarity);
                $sender->getInventory()->addItem($relic);
                $sender->sendMessage("§l§a(!)§r§f You obtained a {$rarity} relic!");
            } else {
                $sender->sendMessage("§l§c(!)§r§f Unknown or unavailable rarity: {$rarity}");
            }
        }
    } else {
        $this->relics->getLogger()->warning("Please use this command in-game!");
        }
    }
}
