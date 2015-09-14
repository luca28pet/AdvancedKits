<?php

namespace AdvancedKits;

use pocketmine\block\Block;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

class EventListener implements Listener{

    /**@var Main*/
    private $ak;

    public function __construct(Main $ak){
        $this->ak = $ak;
    }

    public function onSign(PlayerInteractEvent $event){
        $id = $event->getBlock()->getId();
        if($id === Block::SIGN_POST or $id === Block::WALL_SIGN){
            $tile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if($tile instanceof Sign){
                $text = $tile->getText();
                if(strtolower(TextFormat::clean($text[0])) === strtolower($this->ak->getConfig()->get("sign-text"))){
                    $event->setCancelled();
                    if(empty($text[1])){
                        $event->getPlayer()->sendMessage($this->ak->langManager->getTranslation("no-sign-on-kit"));
                        return;
                    }
                    $kit = $this->ak->getKit($text[1]);
                    if($kit === null){
                        $event->getPlayer()->sendMessage($this->ak->langManager->getTranslation("no-kit", $text[1]));
                        return;
                    }
                    $kit->handleRequest($event->getPlayer());
                }
            }
        }
    }

    public function onSignChange(SignChangeEvent $event){
        if(strtolower(TextFormat::clean($event->getLine(0))) === strtolower($this->ak->getConfig()->get("sign-text")) and !$event->getPlayer()->hasPermission("advancedkits.admin")){
            $event->getPlayer()->sendMessage($this->ak->langManager->getTranslation("no-perm-sign"));
            $event->setCancelled();
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        if(isset($this->ak->hasKit[strtolower($event->getEntity()->getName())])){
            unset($this->ak->hasKit[strtolower($event->getEntity()->getName())]);
        }
    }

    public function onLogOut(PlayerQuitEvent $event){
        if($this->ak->getConfig()->get("reset-on-logout") and isset($this->ak->hasKit[strtolower($event->getPlayer()->getName())])){
            unset($this->ak->hasKit[strtolower($event->getPlayer()->getName())]);
        }
    }

}