<?php

declare(strict_types=1);

namespace phuongaz\dailyquest\task;

use JsonException;
use phuongaz\dailyquest\DailyQuest;
use phuongaz\dailyquest\util\Utils;
use pocketmine\scheduler\Task;

class ResetQuestTask extends Task {

    /**
     * @throws JsonException
     */
    public function onRun() :void {
        $date = date("d-m-Y");
        $config = DailyQuest::getInstance()->getYamlProvider()->getDate();
        if($date !== $config) {
            DailyQuest::getInstance()->getYamlProvider()->setDate($date);
            DailyQuest::getInstance()->getServer()->broadcastMessage("§a§lSistem telah mengreset quest harian!");
            Utils::randomQuest();
            DailyQuest::getInstance()->getSQLProvider()->dropTable();
            DailyQuest::getInstance()->getSQLProvider()->initTable();
        }
    }

}
