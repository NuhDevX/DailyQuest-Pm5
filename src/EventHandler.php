<?php

declare(strict_types=1);

namespace phuongaz\dailyquest;

use BlockHorizons\Fireworks\item\Fireworks;
use Exception;
use labalityowo\Lcoin\Balance;
use labalityowo\Lcoin\Main;
use phuongaz\core\components\WorldProtect;
use phuongaz\core\Core;
use phuongaz\dailyquest\event\PlayerProgressEvent;
use phuongaz\dailyquest\session\Session;
use phuongaz\dailyquest\util\Utils;
use phuongaz\fishing\event\PlayerFishingEvent;
use phuongaz\enchantextra\event\PlayerUpgradeItemEvent;
use phuongaz\season\event\PlayerSellCropEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventHandler implements Listener {

    //thank you chatGpt
    public function onLogin(PlayerLoginEvent $event) :void {
        $player = $event->getPlayer();
        $provider = DailyQuest::getInstance()->getSQLProvider()->getPlayerData($player);
        $provider->onCompletion(function(int $data) use ($player) :void {
            $session = new Session($player->getName(), $data);
            DailyQuest::getInstance()->getSessionManager()->addSession($session);
        }, fn() => null);
    }

    public function onJoin(PlayerJoinEvent $event) :void {
        $reward = DailyQuest::getInstance()->getYamlProvider()->getReward();
        $target = DailyQuest::getInstance()->getYamlProvider()->getTarget();
        $value = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
        $player = $event->getPlayer();
        $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
        $completed = $session->getCompleted();
        $mess = "§7[§bＭｉｓｓｉｏｎ§7] §l§fQuest hari ini adalah " . Utils::parseTarget($target). " §ex" . $value . " §fapa saja dan akan mendapatkan §e" . $reward . " §fkoin";
        if($completed >= $value) {
            $mess = "§aKamu telah menyelesaikan target hari ini §e§l{$completed}§r§a/§e§l{$value}!";
        }
        $player->sendMessage($mess);
    }

    public function onQuit(PlayerQuitEvent $event) :void {
        $player = $event->getPlayer();
        $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
        if ($session !== null) {
            DailyQuest::getInstance()->getSQLProvider()->updatePlayerData($player, $session->getCompleted());
            DailyQuest::getInstance()->getSessionManager()->removeSession($player->getName());
        }
    }

    public function onSellCrop(PlayerSellCropEvent $event) :void {
        if($event->isCancelled()) return;
        if(Utils::checkTodayQuest("sellcrop")) {
            if(Utils::isFinishedQuest($event->getPlayer())) return;
            $player = $event->getPlayer();
            $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
            if ($session === null) {
                return;
            }
            $session->addCompleted();
            $target = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
            $session->addCompleted();
            $player->sendPopup("§l§fKamu telah menjual§e " . $session->getCompleted() . "§f/§e". $target . " §fhasil panen");
        }
    }


    public function onBreak(BlockBreakEvent $event) :void {
        /**@var WorldProtect $protectComponent*/
        $protectComponent = Core::getInstance()->getComponent(WorldProtect::class);
        if(!$protectComponent->canEdit($event->getPlayer(), $event->getBlock()->getPosition())){
            return;
        }
        if($event->isCancelled()) return;
        if(Utils::checkTodayQuest("break")) {
            $player = $event->getPlayer();
            if(Utils::isFinishedQuest($player)) return;
            $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
            if ($session === null) {
                return;
            }
            $target = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
            $session->addCompleted();
            $player->sendPopup("§l§fKamu telah menghancurkan§e " . $session->getCompleted() . "§f/§e". $target . " §fblok");
        }
    }

    public function onPlace(BlockPlaceEvent $event) :void {
        /**@var WorldProtect $protectComponent*/
        $protectComponent = Core::getInstance()->getComponent(WorldProtect::class);
        if(!$protectComponent->canEdit($event->getPlayer(), $event->getBlock()->getPosition())){
            return;
        }
        if($event->isCancelled()) return;
        if(Utils::checkTodayQuest("place")) {
            $player = $event->getPlayer();
            if(Utils::isFinishedQuest($player)) return;
            $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
            if ($session === null) {
                return;
            }
            $target = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
            $session->addCompleted();
            $player->sendPopup("§f§lKamu telah menaruh §e" . $session->getCompleted() . "§f/§e". $target . " §fblok");
        }
    }

    public function onUpgrade(PlayerUpgradeItemEvent $event) :void  {
        if($event->isCancelled()) return;
        if(Utils::checkTodayQuest("enchant")) {
            $player = $event->getPlayer();
            if(Utils::isFinishedQuest($player)) return;
            $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
            if ($session === null) {
                return;
            }
            $target = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
            $session->addCompleted();
            $player->sendPopup("§l§fKamu telah meng-upgrade§e " . $session->getCompleted() . "§f/§e". $target . " §fitem");
        }
    }

    public function onFish(PlayerFishingEvent $event) :void {
        if(Utils::checkTodayQuest("fish")) {
            $player = $event->getPlayer();
            if(Utils::isFinishedQuest($player)) return;
            $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
            if ($session === null) {
                return;
            }
            $target = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
            $session->addCompleted();
            $player->sendPopup("§l§fKamu telah memancing§e " . $session->getCompleted() . "§f/§e". $target . " §fikan");
        }
    }

    /**
     * @throws Exception
     */
    public function onProgress(PlayerProgressEvent $event) :void {
        $player = $event->getPlayer();
        $completed = $event->getCompleted();
        $target = DailyQuest::getInstance()->getYamlProvider()->getTargetValue();
        if($completed > $target) {
            $event->cancel();
            return;
        }
        if($completed == $target) {
            $player->sendTitle("§l§aSelesai", "§l§aKamu telah menyelesaikan quest hari ini");
            $session = DailyQuest::getInstance()->getSessionManager()->getSession($player->getName());
            $session?->setCompleted($completed + 1);
            $reward = DailyQuest::getInstance()->getYamlProvider()->getReward();
            $player->sendMessage("§aKamu telah menyelesaikan misi hari ini dan mendapatkan§e " . $reward . " §akoin");
            if($player->hasPermission("vip.buff")) {
                $bonusVip = (int)round($reward * (mt_rand(1, 5) / 10));
                $player->sendMessage("§aKamu mendapatkan §e" . $bonusVip . " §akoin tambahan dari efek§e VIP");
                $reward += $bonusVip;
            }
            \phuongaz\core\utils\Utils::firework($player->getPosition(), Fireworks::TYPE_STAR, Fireworks::COLOR_RED, Fireworks::COLOR_YELLOW);
            Main::getInstance()->getBalance($player)->addValue(Balance::MONEY, $reward, "Daily quest");
        }
    }

}
