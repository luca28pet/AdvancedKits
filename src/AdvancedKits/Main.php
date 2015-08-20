<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    /**@var kit[]*/
    public $kits;
    public $hasKit = [];
    /**@var EconomyManager*/
    public $economy;
    public $permManager = false;

    public function onEnable(){
        $this->saveDefaultConfig();
        @mkdir($this->getDataFolder()."cooldowns/");
        $this->loadKits();
        $this->economy = new EconomyManager($this);
        if($this->getServer()->getPluginManager()->getPlugin("PurePerms") !== null and $this->getConfig()->get("force-builtin-permissions") == false){
            $this->permManager = true;
        }
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CoolDownTask($this), 1200, 1200);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable(){
        foreach($this->kits as $kit){
            $kit->close();
        }
    }

    private function loadKits(){
        if(!file_exists($this->getDataFolder()."kits.yml")){
            $r = $this->getResource("kits.yml");
            $o = stream_get_contents($r);
            fclose($r);
            file_put_contents($this->getDataFolder()."kits.yml", $o);
        }
        $kitsData = yaml_parse(file_get_contents($this->getDataFolder()."kits.yml"));
        $this->fixConfig($kitsData);
        foreach($kitsData as $kitName => $kitData){
            $this->kits[$kitName] = new Kit($this, $kitData, $kitName);
        }
    }

    private function fixConfig(&$config){
        foreach($config as $name => $kit){
            if(isset($kit["users"])){
                $users = array_map("strtolower", $kit["users"]);
                $config[$name]["users"] = $users;
            }
            if(isset($kit["worlds"])){
                $worlds = array_map("strtolower", $kit["worlds"]);
                $config[$name]["worlds"] = $worlds;
            }
        }
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
                /**@var Kit[] $lowerKeys*/
                $lowerKeys = array_change_key_case($this->kits, CASE_LOWER);
                if(!isset($lowerKeys[strtolower($args[0])])){
                    $sender->sendMessage("Kit ".$args[0]." does not exist");
                    return true;
                }
                $lowerKeys[strtolower($args[0])]->handleRequest($sender);
                return true;
            break;
            case "akreload":
                $this->loadKits();
                $sender->sendMessage("Reloaded kits settings");
                return true;
            break;
        }
        return true;
    }

}