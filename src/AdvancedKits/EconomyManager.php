<?php

namespace AdvancedKits;

use pocketmine\Player;

class EconomyManager{

    private $plugin;
    private $economy = null;
    private $api;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
        foreach(["EconomyAPI", "PocketMoney", "MassiveEconomy"] as $plugin){
            if($p = $this->plugin->getServer()->getPluginManager()->getPlugin($plugin) !== null){
                $this->economy = $plugin;
                $this->api = $p;
                break;
            }
        }
    }

    public function grantKit(Player $player, $money){
        if(is_null($this->economy)){
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