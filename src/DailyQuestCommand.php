<?php

declare(strict_types=1);

namespace phuongaz\dailyquest;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class DailyQuestCommand extends Command {

    public function __construct() {
        parent::__construct("dailyquest", "DailyQuest command", "/dailyquest", ["mission"]);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) :bool {
        if(!$sender instanceof Player) {
            $sender->sendMessage("§c§lSistem ini hanya untuk pemain!");
            return false;
        }else {
            $sender->sendForm(new DailyQuestForm($sender));
        }
        return true;
    }

}
