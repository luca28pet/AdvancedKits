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
                if(strtolower(TextFormat::clean($text[0])) === "[advancedkits]"){
                    $event->setCancelled();
                    if(empty($text[1])){
                        $event->getPlayer()->sendMessage("On this sign, the kit is not specified");
                        return;
                    }
                    /**@var Kit[] $lowerKeys*/
                    $lowerKeys = array_change_key_case($this->ak->kits, CASE_LOWER);
                    if(!isset($lowerKeys[strtolower($text[1])])){
                        $event->getPlayer()->sendMessage("Kit ".$text[1]." does not exist");
                        return;
                    }
                    $lowerKeys[strtolower($text[1])]->handleRequest($event->getPlayer());
                }
            }
        }
    }

    public function onSignChange(SignChangeEvent $event){
        if(strtolower(TextFormat::clean($event->getLine(0))) === "[advancedkits]" and !$event->getPlayer()->hasPermission("advancedkits.admin")){
            $event->getPlayer()->sendMessage("You don't have permission to create a sign kit");
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