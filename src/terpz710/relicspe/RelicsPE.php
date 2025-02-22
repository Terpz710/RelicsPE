<?php

declare(strict_types=1);

namespace terpz710\relicspe;

use pocketmine\plugin\PluginBase;

use function mkdir;
use function is_dir;
use function file_exists;

use terpz710\relicspe\command\RelicsCommand;

final class RelicsPE extends PluginBase {

    protected static self $instance;

    protected function onLoad() : void{
        self::$instance = $this;
    }

    protected function onEnable() : void{
        $this->saveDefaultConfig();
        $this->saveResource("rewards.yml");

        $languageFolder = $this->getDataFolder() . "languages/";
        if (!is_dir($languageFolder)) {
            mkdir($languageFolder, 0777, true);
        }

        $langConfig = [
            "english_messages.yml",
            "spanish_messages.yml",
            "german_messages.yml",
            "french_messages.yml",
            "traditional_chinese_messages.yml",
            "simplified_chinese_messages.yml"
        ];
        
        foreach ($langConfig as $file) {
            if (!file_exists($languageFolder . $file)) {
                $this->saveResource("languages/" . $file, false);
            }
        }

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->getServer()->getCommandMap()->register("RelicsPE", new RelicsCommand($this, "relics", "Give a player a relic"));
    }

    public static function getInstance() : self{
        return self::$instance;
    }
}