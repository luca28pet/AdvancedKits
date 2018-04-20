<?php

namespace AdvancedKits\economy;

use AdvancedKits\Main;
use pocketmine\Player;

class EconomyManager{

    private $economy;
    private $api;

    public function __construct(Main $ak){
        foreach(['EconomyAPI', 'PocketMoney', 'MassiveEconomy'] as $ecoPluginName){
            if(($ecoPlugin = $ak->getServer()->getPluginManager()->getPlugin($ecoPluginName)) !== null){
                $this->economy = $ecoPluginName;
                $this->api = $ecoPlugin;
                break;
            }
        }
    }

    public function grantKit(Player $player, int $money) : bool{
        if($this->economy === null){
            return false;
        }
        switch($this->economy){
            case 'EconomyAPI':
                if($this->api->reduceMoney($player, $money) === 1){
                    return true;
                }
            break;
            case 'PocketMoney':
                if($this->api->getMoney($player->getName()) < $money){
                    return false;
                }
                if($this->api->setMoney($player->getName(), $this->api->getMoney($player->getName()) - $money)){
                    return true;
                }
            break;
            case 'MassiveEconomy':
                if($this->api->takeMoney($player->getName(), $money) === 2){
                    return true;
                }
            break;
        }
        return false;
    }

}