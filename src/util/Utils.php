<?php

declare(strict_types=1);

namespace phuongaz\dailyquest\util;

use JsonException;
use phuongaz\dailyquest\DailyQuest;
use pocketmine\player\Player;

class Utils {

    public static function checkTodayQuest(string $type) :bool {
        $target = DailyQuest::getInstance()->getYamlProvider()->getTarget();
        return $target === $type;
    }

    public static function parseTarget(string $target) :string {
        return match($target) {
            "break" => "menghancurkan",
            "place" => "menaruh",
            "craft" => "membuat",
            "enchant" => "meng enchant",
            "fish" => "memancing",
            "sellcrop" => "hasil bertani",
        };
    }

    /**
     * @throws JsonException
     */
    public static function randomQuest() :void {
        $target = DailyQuest::getInstance()->getYamlProvider()->getTarget();
        $value = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
        $reward = DailyQuest::getInstance()->getYamlProvider()->getReward();

        $newTarget = self::getRandomTarget();
        $newValue = self::getRandomValue($newTarget);
        $newReward = self::getRandomReward($newTarget, $newValue);
        DailyQuest::getInstance()->getYamlProvider()->setTarget($newTarget);
        DailyQuest::getInstance()->getYamlProvider()->setTargetValue($newValue);
        DailyQuest::getInstance()->getYamlProvider()->setReward($newReward);
        DailyQuest::getInstance()->getYamlProvider()->save();
        DailyQuest::getInstance()->getServer()->broadcastMessage("§7[§bＭｉｓｓｉｏｎ§7] §l§fQuest hari ini adalah " . Utils::parseTarget($newTarget) . " §ex" . $newValue . "§f secara acak dan akan mendapatkan §e" . $newReward . " koin");;
    }

    public static function getRandomTarget() :string {
        $targets = ["break", "place", "enchant", "fish", "sellcrop"];
        return $targets[array_rand($targets)];
    }

    public static function getRandomValue(string $target) :int {
        return match($target){
            "break", "place" => rand(500, 1000),
            "sellcrop" => rand(100, 300),
            "enchant" => rand(3, 10),
            "fish" => rand(30, 50),
        };
    }

    public static function getRandomReward(string $target, int $value) :int {
        return match($target){
            "break", "place" => $value * 50,
            "sellcrop", "enchant" => $value * 100,
            "fish" => $value * 100,
        };
    }

    public static function isFinishedQuest(Player $player) :bool {
        $value = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
        $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
        if($session === null) return false;
        return  $session->getCompleted() > $value;
    }

}
