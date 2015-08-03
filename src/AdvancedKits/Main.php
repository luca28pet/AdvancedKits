<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    public $kits;
    public $hasKit = [];
    /**@var EconomyManager*/
    public $economy;
    public $coolDown = [];
    private $permManager = false;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder()."kits.yml")){
            $r = $this->getResource("kits.yml");
            $o = stream_get_contents($r);
            fclose($r);
            file_put_contents($this->getDataFolder()."kits.yml", $o);
        }
        $this->kits = yaml_parse(file_get_contents($this->getDataFolder()."kits.yml"));
        $this->saveDefaultConfig();
        $this->economy = new EconomyManager($this);
        if($this->getServer()->getPluginManager()->getPlugin("PurePerms") !== null and $this->getConfig()->get("force-builtin-permissions") == false){
            $this->permManager = true;
        }
        if(file_exists($this->getDataFolder()."cooldowns.sl")){
            $this->coolDown = unserialize(file_get_contents($this->getDataFolder()."cooldowns.sl"));
        }
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CoolDownTask($this), 1200, 1200);
        $this->fixConfig();
    }

    public function onDisable(){
        file_put_contents($this->getDataFolder()."cooldowns.sl", serialize($this->coolDown));
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch(strtolower($command->getName())){
            case "kit":
                if(!($sender instanceof Player)){
                    $sender->sendMessage("Please run this command in game");
                    return true;
                }
                if(!isset($args[0])){
                    $sender->sendMessage("Available kits: ".implode(", ", array_keys($this->kits)));
                    return true;
                }
                if(!isset($this->kits[strtolower($args[0])])){
                    $sender->sendMessage("Kit ".$args[0]." does not exist");
                    return true;
                }
                if(!$this->checkPermission($sender, strtolower($args[0]))){
                    $sender->sendMessage("You haven't the permission to use kit ".$args[0]);
                    return true;
                }
                if(isset($this->coolDown[strtolower($sender->getName())][strtolower($args[0])])){
                    $sender->sendMessage("Kit ".$args[0]." is in coolDown at the moment");
                    $sender->sendMessage("You will be able to get it in ".$this->getTimeLeftString($this->coolDown[strtolower($sender->getName())][strtolower($args[0])]));
                    return true;
                }
                if(isset($this->hasKit[$sender->getId()])){
                    $sender->sendMessage("You already have a kit");
                    return true;
                }
                if(isset($this->kits[strtolower($args[0])]["money"])){
                    if($this->economy->grantKit($sender, (int) $this->kits[strtolower($args[0])]["money"])){
                        $this->addKit(strtolower($args[0]), $sender);
                        $sender->sendMessage("Selected kit: ".$args[0].". Taken ".$this->kits[strtolower($args[0])]["money"]." money");
                    }else{
                        $sender->sendMessage("You can not afford this kit");
                    }
                }else{
                    $this->addKit(strtolower($args[0]), $sender);
                    $sender->sendMessage("Selected kit: ".$args[0]);
                }
                return true;
            break;
            case "akreload":
                $this->kits = yaml_parse(file_get_contents($this->getDataFolder()."kits.yml"));
                $this->fixConfig();
                $sender->sendMessage("Reloaded kits settings");
                return true;
            break;
        }
        return true;
    }

    public function checkPermission(Player $player, $kitName){
        return $this->permManager ? $player->hasPermission("advancedkits.".$kitName) : (
            (isset($this->kits[$kitName]["users"]) ? in_array(strtolower($player->getName()), $this->kits[$kitName]["users"]) : true)
            and
            (isset($this->kits[$kitName]["worlds"]) ? in_array(strtolower($player->getLevel()->getName()), $this->kits[$kitName]["worlds"]) : true)
        );
    }

    public function addKit($kitName, Player $player){
        $kit = $this->kits[$kitName];
        $inv = $player->getInventory();
        foreach($kit["items"] as $item){
            $itemData = array_map("intval", explode(":", $item));
            $inv->setItem($inv->firstEmpty(), Item::get($itemData[0], $itemData[1], $itemData[2]));
        }
        foreach(["helmet", "chestplate", "leggings", "boots"] as $armor){
            if(isset($kit[$armor])){
                $armorItem = Item::get((int) $kit[$armor]);
                switch($armor){
                    case "helmet":
                        $inv->setHelmet($armorItem);
                        break;
                    case "chestplate":
                        $inv->setChestplate($armorItem);
                        break;
                    case "leggings":
                        $inv->setLeggings($armorItem);
                        break;
                    case "boots":
                        $inv->setBoots($armorItem);
                        break;
                }
            }
        }
        if(isset($kit["cooldown"]["minutes"])){
            $this->coolDown[strtolower($player->getName())][$kitName] = $kit["cooldown"]["minutes"];
        }
        if(isset($kit["cooldown"]["hours"])){
            $this->coolDown[strtolower($player->getName())][$kitName] += $kit["cooldown"]["hours"] * 60;
        }
        if($this->getConfig()->get("one-kit-per-life") == true){
            $this->hasKit[$player->getId()] = true;
        }
    }

    public function getTimeLeftString($minutes){
        if($minutes < 60){
            return $minutes." minutes";
        }
        if(($modulo = $minutes % 60) !== 0){
            return floor($minutes / 60)." hours and ".$modulo." minutes";
        }
        return ($minutes / 60)." hours";
    }

    private function fixConfig(){
        foreach($this->kits as $name => $kit){
            if(isset($kit["users"])){
                $users = array_map("strtolower", $kit["users"]);
                $this->kits[$name]["users"] = $users;
            }
            if(isset($kit["worlds"])){
                $worlds = array_map("strtolower", $kit["worlds"]);
                $this->kits[$name]["worlds"] = $worlds;
            }
        }
    }

}