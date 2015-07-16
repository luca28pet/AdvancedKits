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
                    if(!isset($this->ak->kits[strtolower($text[1])])){
                        $event->getPlayer()->sendMessage("Kit ".$text[1]." does not exist");
                        return;
                    }
                    if(isset($this->ak->hasKit[$event->getPlayer()->getId()])){
                        $event->getPlayer()->sendMessage("You already have a kit");
                        return;
                    }
                    if(isset($this->ak->coolDown[strtolower($event->getPlayer()->getName())]) and in_array(strtolower($text[1]), $this->ak->coolDown[strtolower($event->getPlayer()->getName())])){
                        $event->getPlayer()->sendMessage("Kit ".$text[1]." is in coolDown at the moment");
                        return;
                    }
                    if(!$this->ak->checkPermission($event->getPlayer(), strtolower($text[1]))){
                        $event->getPlayer()->sendMessage("You haven't the permission to use kit ".$text[1]);
                        return;
                    }
                    if(isset($this->ak->kits[strtolower($text[1])]["money"])){
                        if($this->ak->economy->grantKit($event->getPlayer(), (int) $this->ak->kits[strtolower($text[1])]["money"])){
                            $this->ak->addKit(strtolower($text[1]), $event->getPlayer());
                            $event->getPlayer()->sendMessage("Selected kit: ".$text[1].". Taken ".$this->ak->kits[strtolower($text[1])]["money"]." money");
                        }else{
                            $event->getPlayer()->sendMessage("You can not afford this kit");
                        }
                    }else{
                        $this->ak->addKit(strtolower($text[1]), $event->getPlayer());
                        $event->getPlayer()->sendMessage("Selected kit: ".$text[1]);
                    }
                }
            }
        }
    }

    public function onSignChange(SignChangeEvent $event){
        if(strtolower(TextFormat::clean($event->getLine(0))) === "[advancedkits]" and !$event->getPlayer()->hasPermission("advancedkits.createsign")){
            $event->getPlayer()->sendMessage("You don't have permission to create a sign kit");
            $event->setCancelled();
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        if(isset($this->ak->hasKit[$event->getEntity()->getId()])){
            unset($this->ak->hasKit[$event->getEntity()->getId()]);
        }
    }

    public function onLogOut(PlayerQuitEvent $event){
        if($this->ak->getConfig()->get("reset-on-logout") == true and isset($this->ak->hasKit[$event->getPlayer()->getId()])){
            unset($this->ak->hasKit[$event->getPlayer()->getId()]);
        }
    }

}