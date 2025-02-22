<?php

declare(strict_types=1);

namespace terpz710\relicspe\utils;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TextColor;

use function mkdir;
use function is_dir;
use function str_replace;

use terpz710\relicspe\RelicsPE;

class Message {

    protected string $message;

    public function __construct(string $msgKey, array|string|null $tags = null, array|string|null $replacements = null) {
        $lang = RelicsPE::getInstance()->getConfig()->get("language");

        $languageFolder = RelicsPE::getInstance()->getDataFolder() . "languages/";

        if (!is_dir($languageFolder)) {
            mkdir($languageFolder, 0777, true);
        }

        if ($lang === "en") {
            $languageConfig = new Config($languageFolder . "english_messages.yml");
        } elseif ($lang === "es") {
            $languageConfig = new Config($languageFolder . "spanish_messages.yml");
        } elseif ($lang === "fr") {
            $languageConfig = new Config($languageFolder . "german_messages.yml");
        } elseif ($lang === "de") {
            $languageConfig = new Config($languageFolder . "french_messages.yml");
        } elseif ($lang === "zh-t") {
            $languageConfig = new Config($languageFolder . "traditional_chinese_messages.yml");
        } elseif ($lang === "zh-s") {
            $languageConfig = new Config($languageFolder . "simplified_chinese_messages.yml");
        } else {
            throw new \InvalidArgumentException("Invalid language type specified in the configuration: " . $lang);
        }

        $msg = $languageConfig->get($msgKey);

        if ($tags !== null && $replacements !== null) {
            $tags = (array) $tags;
            $replacements = (array) $replacements;

            $msg = str_replace($tags, $replacements, $msg);
        }

        $this->message = TextColor::colorize($msg);
    }

    public function __toString() : string{
        return $this->message;
    }
}