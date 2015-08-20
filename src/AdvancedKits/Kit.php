<?php

namespace AdvancedKits;

use pocketmine\item\Item;
use pocketmine\Player;

class Kit{

    private $ak;
    private $data;
    private $name;
    private $coolDowns = [];

    public function __construct(Main $ak, array $data, $name){
        $this->ak = $ak;
        $this->data = $data;
        $this->name = $name;
        if(file_exists($this->ak->getDataFolder()."cooldowns/".strtolower($this->name).".sl")){
            $this->coolDowns = unserialize(file_get_contents($this->ak->getDataFolder()."cooldowns/".strtolower($this->name).".sl"));
        }
    }

    public function handleRequest(Player $player){
        if($this->testPermission($player)){
            if(!isset($this->coolDowns[strtolower($player->getName())])){
                if(!($this->ak->getConfig()->get("one-kit-per-life") and isset($this->ak->hasKit[strtolower($player->getName())]))){
                    if($this->isPaid()){
                        if($this->ak->economy->grantKit($player, $this->getCost())){
                            $this->addTo($player);
                            $player->sendMessage($this->ak->langManager->getTranslation("sel-kit", $this->name));
                        }else{
                            $player->sendMessage($this->ak->langManager->getTranslation("cant-afford", $this->name));
                        }
                    }else{
                        $this->addTo($player);
                        $player->sendMessage($this->ak->langManager->getTranslation("sel-kit", $this->name));
                    }
                }else{
                    $player->sendMessage($this->ak->langManager->getTranslation("one-per-life"));
                }
            }else{
                $player->sendMessage($this->ak->langManager->getTranslation("cooldown1", $this->name));
                $player->sendMessage($this->ak->langManager->getTranslation("cooldown2", $this->getCoolDownLeft($player)));
            }
        }else{
            $player->sendMessage($this->ak->langManager->getTranslation("no-perm", $this->name));
        }
    }

    private function addTo(Player $player){
        $items = $this->getItems();
        $inv = $player->getInventory();
        foreach($items as $type => $item){
            if(is_int($type)) $inv->addItem($item);
            elseif($type === "helmet")  $inv->setHelmet($item);
            elseif($type === "chestplate") $inv->setChestplate($item);
            elseif($type === "leggings") $inv->setLeggings($item);
            elseif($type === "boots") $inv->setBoots($item);
        }
        $this->coolDowns[strtolower($player)] = $this->getCoolDownMinutes();
        $this->ak->hasKit[strtolower($player->getName())] = true;
    }

    /**
     * @return Item[]
     */
    private function getItems(){
        $items = [];
        foreach($this->data["items"] as $itemString){
            $itemData = array_map("intval", explode(":", $itemString));
            $items[] = Item::get($itemData[0], $itemData[1], $itemData[2]);
        }
        foreach(["helmet", "chestplate", "leggings", "boots"] as $armor){
            if(isset($this->data[$armor])){
                $armorItem = Item::get((int) $this->data[$armor]);
                $items[$armor] = $armorItem;
            }
        }
        return $items;
    }

    private function isPaid(){
        return isset($this->data["money"]) and $this->data["money"] !== 0;
    }

    //Call isPaid() before !!!
    private function getCost(){
        return (int) $this->data["money"];
    }

    private function getCoolDownMinutes(){
        $min = 0;
        if(isset($this->data["cooldown"]["minutes"])){
            $min += (int) $this->data["cooldown"]["minutes"];
        }
        if(isset($this->data["cooldown"]["hours"])){
            $min += (int) $this->data["cooldown"]["hours"] * 60;
        }
        return $min;
    }

    private function getCoolDownLeft(Player $player){
        if(($minutes = $this->coolDowns[strtolower($player->getName())]) < 60){
            return $this->ak->langManager->getTranslation("cooldown-format1", $minutes);
        }
        if(($modulo = $minutes % 60) !== 0){
            return $this->ak->langManager->getTranslation("cooldown-format2", floor($minutes / 60), $modulo);
        }
        return $this->ak->langManager->getTranslation("cooldown-format3", $minutes / 60);
    }

    public function processCoolDown(){
        foreach($this->coolDowns as $player => $min){
            $this->coolDowns[$player] -= 1;
            if($this->coolDowns[$player] === 0){
                unset($this->coolDowns[$player]);
            }
        }
    }

    private function testPermission(Player $player){
        return $this->ak->permManager ? $player->hasPermission("advancedkits.".strtolower($this->name)) : (
            (isset($this->data["users"]) ? in_array(strtolower($player->getName()), $this->data["users"]) : true)
            and
            (isset($this->data["worlds"]) ? in_array(strtolower($player->getLevel()->getName()), $this->data["worlds"]) : true)
        );
    }

    public function close(){
        if(count($this->coolDowns) > 0){
            file_put_contents($this->ak->getDataFolder()."cooldowns/".strtolower($this->name).".sl", serialize($this->coolDowns));
        }
    }

}