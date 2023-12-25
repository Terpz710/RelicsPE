<?php

declare(strict_types=1);

namespace Terpz710\RelicsPE;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;

use Terpz710\RelicsPE\Command\RelicsCommand;
use Terpz710\RelicsPE\RelicsManager;

class Relics extends PluginBase {

    public function onEnable(): void {
        $this->saveResource("rewards.yml");
        $config = new Config($this->getDataFolder() . "rewards.yml", Config::YAML);

        $relicsManager = new RelicsManager($this);
        $this->getServer()->getPluginManager()->registerEvents(new RelicsManager($this), $this);

        $this->getServer()->getCommandMap()->register("relics", new RelicsCommand($this, $relicsManager));
    }
}