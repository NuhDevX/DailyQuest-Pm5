<?php

declare(strict_types=1);

namespace phuongaz\dailyquest;

use jojoe77777\FormAPI\CustomForm;
use phuongaz\dailyquest\util\Utils;
use pocketmine\player\Player;

class DailyQuestForm extends CustomForm {

    public function __construct(Player $player, ?callable $callable = null) {
        parent::__construct($callable);
        $this->setTitle("§l§6Ｍｉｓｓｉｏｎ");
        $this->addLabel("§f§lTarget hari ini adalah:");
        $target = Utils::parseTarget(DailyQuest::getInstance()->getYamlProvider()->getTarget());
        $value = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
        $reward = DailyQuest::getInstance()->getYamlProvider()->getReward();
        $this->addLabel("§f§l" . $target . " §ex" . $value . "§fkali");
        $this->addLabel("§f§lHadiah:§e ". $reward . " §fxu");// xu apaan dah? ga bisa di translate jir

        $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
        $completed = $session->getCompleted();
        if($completed >= $value) {
            $this->addLabel("§a§lAnda telah menyelesaikan quest §e§l{$completed}§r§a/§e§l{$value} hari ini!");
        } else {
            $this->addLabel("§c§lAnda belum menyelesaikan quest Anda §e§l{$completed}§r§c/§e§l{$value} hari ini!");
        }
    }


}
