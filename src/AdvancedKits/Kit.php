<?php

namespace AdvancedKits;

use pocketmine\item\Item;
use pocketmine\Player;

class Kit{

    private $armor;
    private $items;
    private $rank;
    private $name;

    public function __Construct($armor, $items, $rank, $name, Main $plugin){
        $this->armor = $armor;
        $this->items = $items;
        $this->rank = $rank;
        $this->name = $name;
        $this->plugin = $plugin;
    }

    public function give(Player $player){
        if(!$this->testRank($player)){
            $player->sendMessage("[AdvancedKits] You don't have permission to use this kit.");
        }
        $armorItems = array();
        foreach($this->armor as $armor){
            $armorItems[] = Item::get($armor);
        }
        $player->getInventory()->setArmorContents($this->armor);
        foreach($this->items as $item){
            $player->getInventory()->addItem(Item::get($item[0], $item[1], $item[2]));
        }
        $player->sendMessage("[AdvancedKits] Kit ".$this->name." given.");
        $this->plugin->hasKit[] = $player->getName();
    }

    private function testRank(Player $player){
        switch(strtolower($this->rank)){
            case "vip+":
            case "vipplus":
                return($this->plugin->vipPlayersPlus->exists(strtolower($player->getName())));
            break;
            case "vip":
                return($this->plugin->vipPlayers->exists(strtolower($player->getName())) or $this->plugin->vipPlayersPlus->exists(strtolower($player->getName())));
            break;
            case "player":
            case "default":
                return true;
            break;
            default:
                return false;
        }
    }

}