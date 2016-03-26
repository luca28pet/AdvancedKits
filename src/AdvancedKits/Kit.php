<?php

namespace AdvancedKits;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Effect;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\Player;

class Kit{

    private $ak;
    private $data;
    private $name;
    private $cost = 0;
    private $coolDown;
    private $coolDowns = [];
    /** @var  Item[] */
    private $items = [];
    /** @var Effect[] */
    private $effects = [];

    public function __construct(Main $ak, array $data, string $name){
        $this->ak = $ak;
        $this->data = $data;
        $this->name = $name;
        $this->coolDown = $this->getCoolDownMinutes();
        if(isset($this->data["money"]) and $this->data["money"] != 0){
            $this->cost = (int) $this->data["money"];
        }
        $this->loadItems();
        $this->loadEffects();
        if(file_exists($this->ak->getDataFolder()."cooldowns/".strtolower($this->name).".sl")){
            $this->coolDowns = unserialize(file_get_contents($this->ak->getDataFolder()."cooldowns/".strtolower($this->name).".sl"));
        }
    }

    public function getName() : string{
        return $this->name;
    }

    public function handleRequest(Player $player) : bool{
        if($this->testPermission($player)){
            if(!isset($this->coolDowns[strtolower($player->getName())])){
                if(!($this->ak->getConfig()->get("one-kit-per-life") and isset($this->ak->hasKit[strtolower($player->getName())]))){
                    if($this->cost){
                        if($this->ak->economy->grantKit($player, $this->cost)){
                            $this->addTo($player);
                            $player->sendMessage($this->ak->langManager->getTranslation("sel-kit", $this->name));
                            return true;
                        }else{
                            $player->sendMessage($this->ak->langManager->getTranslation("cant-afford", $this->name));
                        }
                    }else{
                        $this->addTo($player);
                        $player->sendMessage($this->ak->langManager->getTranslation("sel-kit", $this->name));
                        return true;
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
        return false;
    }

    public function addTo(Player $player){
        $inv = $player->getInventory();
        foreach($this->items as $type => $item){
            if((int) $type === $type) $inv->addItem($item);
            elseif($type === "helmet")  $inv->setHelmet($item);
            elseif($type === "chestplate") $inv->setChestplate($item);
            elseif($type === "leggings") $inv->setLeggings($item);
            elseif($type === "boots") $inv->setBoots($item);
        }
        if(isset($this->data["commands"]) and is_array($this->data["commands"])){
            foreach($this->data["commands"] as $cmd){
                $this->ak->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $player->getName(), $cmd));
            }
        }
        foreach($this->effects as $effect){
            $player->addEffect($effect);
        }
        if($this->coolDown){
            $this->coolDowns[strtolower($player->getName())] = $this->coolDown;
        }
        $this->ak->hasKit[strtolower($player->getName())] = $this;
    }

    private function loadItems(){
        foreach($this->data["items"] as $values){
            if(!isset($values["id"])){
                continue;
            }
            $item = Item::get($values["id"], $values["damage"] ?? 0, $values["count"] ?? 1);
            isset($values["name"]) and $item->setCustomName($values["name"]);
            if(isset($values["enchantment"]) and is_array($values["enchantment"])){
                $class = Enchantment::getEnchantment(Enchantment::TYPE_INVALID);
                $method = (new \ReflectionClass($class))->hasMethod("getEnchantmentByName");
                foreach($values["enchantment"] as $name => $level){
                    $enchantment = ($method ? Enchantment::getEnchantmentByName($name) : Enchantment::getEffectByName($name));
                    if($enchantment !== null){
                        $enchantment->setLevel($level);
                        $item->addEnchantment($enchantment);
                    }
                }
            }
            $this->items[] = $item;
        }
        foreach(["helmet", "chestplate", "leggings", "boots"] as $armor){
            if(isset($this->data[$armor]) and isset($this->data[$armor]["id"])){
                $item = Item::get($this->data[$armor]["id"]);
                isset($this->data[$armor]["name"]) and $item->setCustomName($this->data[$armor]["name"]);
                if(isset($this->data[$armor]["enchantment"]) and is_array($this->data[$armor]["enchantment"])){
                    $class = Enchantment::getEnchantment(Enchantment::TYPE_INVALID);
                    $method = (new \ReflectionClass($class))->hasMethod("getEnchantmentByName");
                    foreach($this->data[$armor]["enchantment"] as $name => $level){
                        $enchantment = ($method ? Enchantment::getEnchantmentByName($name) : Enchantment::getEffectByName($name));
                        if($enchantment !== null){
                            $enchantment->setLevel($level);
                            $item->addEnchantment($enchantment);
                        }
                    }
                }
                $this->items[$armor] = $item;
            }
        }
    }

    private function loadEffects(){
        if(!isset($this->data["effects"]) or !is_array($this->data["effects"])){
            return;
        }
        foreach($this->data["effects"] as $eff){
            if(!isset($eff["name"])){
                continue;
            }
            $effect = Effect::getEffectByName($eff["name"]);
            if($effect !== null){
                $effect->setAmplifier($eff["amplifier"] ?? 1);
                $effect->setDuration(isset($eff["seconds"]) ? $eff["seconds"] * 20 : 20 * 60);
                $effect->setVisible($eff["visible"] ?? false);
                $this->effects[] = $effect;
            }
        }
    }

    private function getCoolDownMinutes() : int{
        $min = 0;
        if(isset($this->data["cooldown"]["minutes"])){
            $min += (int) $this->data["cooldown"]["minutes"];
        }
        if(isset($this->data["cooldown"]["hours"])){
            $min += (int) $this->data["cooldown"]["hours"] * 60;
        }
        return $min;
    }

    private function getCoolDownLeft(Player $player) : string{
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
            if($this->coolDowns[$player] <= 0){
                unset($this->coolDowns[$player]);
            }
        }
    }

    private function testPermission(Player $player) : bool{
        return $this->ak->permManager ? $player->hasPermission("advancedkits.".strtolower($this->name)) : (
            (isset($this->data["users"]) ? in_array(strtolower($player->getName()), $this->data["users"]) : true)
            and
            (isset($this->data["worlds"]) ? in_array(strtolower($player->getLevel()->getName()), $this->data["worlds"]) : true)
        );
    }

    public function save(){
        if(count($this->coolDowns) > 0){
            file_put_contents($this->ak->getDataFolder()."cooldowns/".strtolower($this->name).".sl", serialize($this->coolDowns));
        }
    }

}