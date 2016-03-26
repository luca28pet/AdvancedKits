<?php

namespace AdvancedKits\economy;

use AdvancedKits\Main;
use pocketmine\Player;

class EconomyManager{

    private $plugin;
    private $economy = null;
    private $api;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        foreach(["EconomyAPI", "PocketMoney", "MassiveEconomy"] as $plugin){
            if(($p = $this->plugin->getServer()->getPluginManager()->getPlugin($plugin)) !== null){
                $this->economy = $plugin;
                $this->api = $p;
                break;
            }
        }
    }

    public function grantKit(Player $player, int $money) : bool{
        if($this->economy === null){
            return false;
        }
        switch($this->economy){
            case "EconomyAPI":
                if($this->api->reduceMoney($player, $money) === 1){
                    return true;
                }
            break;
            case "PocketMoney":
                if($this->api->getMoney($player->getName()) < $money){
                    return false;
                }
                if($this->api->setMoney($player->getName(), $this->api->getMoney($player->getName()) - $money)){
                    return true;
                }
            break;
            case "MassiveEconomy":
                if($this->api->takeMoney($player->getName(), $money) === 2){
                    return true;
                }
            break;
        }
        return false;
    }

}