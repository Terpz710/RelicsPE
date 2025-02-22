<?php

declare(strict_types=1);

namespace terpz710\relicspe\command;

use pocketmine\command\CommandSender;

use pocketmine\player\Player;

use pocketmine\Server;

use terpz710\relicspe\RelicsPE;

use terpz710\relicspe\manager\RelicsManager;

use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\args\RawStringArgument;

class RelicsCommand extends BaseCommand {

    protected function prepare() : void{
        $this->setPermission("relicspe.cmd");
        $this->registerArgument(0, new RawStringArgument("player"));
        $this->registerArgument(1, new RawStringArgument("rarity"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void{
        if (!$sender instanceof Player) {
            RelicsPE::getInstance()->getLogger()->warning("Please use this command in-game!");
            return;
        }

        $relicsManager = RelicsManager::getInstance();

        if (empty($args)) {
            $sender->sendMessage("Available Relics: " . implode(", ", $relicsManager->getAllRelics()));
            return;
        }

        if (!isset($args["player"], $args["rarity"])) {
            $sender->sendMessage($this->getUsage());
            return;
        }

        $playerName = $args["player"];
        $rarity = $args["rarity"];

        $targetPlayer = Server::getInstance()->getPlayerByPrefix($playerName);
        if (!$targetPlayer instanceof Player) {
            $sender->sendMessage("§e" . $playerName . " §fwas not found. Make sure they're online!");
            return;
        }

        if (!in_array($rarity, $relicsManager->getAllRelics(), true)) {
            $sender->sendMessage("Unknown rarity: §c" . $rarity);
            return;
        }

        $relic = $relicsManager->createPrismarineRelic($targetPlayer, $rarity);
        $sender->getInventory()->addItem($relic);
        $sender->sendMessage("You gave " . $targetPlayer->getName() . " a " . $rarity . " relic!");
        $targetPlayer->sendMessage("You obtained a " . $rarity . " relic from " . $sender->getName() . "!");
    }
}