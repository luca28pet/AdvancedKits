<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

class MainClass extends PluginBase implements Listener{

	public function onLoad(){
		$this->getLogger()->info("AdvancedKits loaded.");
	}

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->configFile = new Config($this->getDataFolder()."kits.yml", Config::YAML, array(
            "basicpvp" => array(
                "Vip" => false,
                "Content" => array(
                    array(
                        272,
                        0,
                        1
                    ),
                    array(
                        260,
                        0,
                        5
                    ),
                )
            ),
            "basicbuilder" => array(
                "Vip" => false,
                "Content" => array(
                    array(
                        11,
                        0,
                        7
                    ),
                    array(
                        275,
                        0,
                        1
                    ),
                    array(
                        297,
                        0,
                        3
                    ),
                )
            ),
            "darkgodpvp" => array(
                "Vip" => true,
                "Content" => array(
                    array(
                        276,
                        0,
                        2
                    ),
                    array(
                        311,
                        0,
                        1
                    ),
                    array(
                        366,
                        0,
                        20
                    ),
                )
            ),
        )
	);
		$this->configFile->save();
		$this->vipPlayers = new Config($this->getDataFolder()."vips.txt", Config::ENUM);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("AdvancedKits enabled!");
    }

	public function onDisable(){
		$this->getLogger()->info("AdvancedKits disabled!");
		$this->configFile->save();
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case "advancedkits":
			if ($issuer instanceof Player){
			if($args[0] == "get"){
			$player = $sender->getName();
                if(isset($this->configFile->get("kits")[strtolower($args[1])])){
                    $kitgot = $this->configFile->get(strtolower($args[1]));
                    if($kitgot["Vip"] == true and !$this->vipPlayers->exists($sender->getName()){
                        $sender->sendMessage("You cannot get this kit, buy vip!!");
                    }else{
                        foreach ($kitgot as $content){
							$sender->addItem($content[0], $content[1], $content[2]);
							}
                        $sender->sendMessage("[AdvancedKits] Here is your kit!");
                    }
                }else{
						$sender->sendMessage("This kit does not exist");
				}
				return true;
			}
			if($args[0] == "addvip"){
				$playerName = $args[1];
				$this->vips->set($playerName));
				$this->vips->save();
				return true;
			}
			if($args[0] == "unvip"){
				$playerName = $args[1];
				$this->vipPlayers->remove($playerName));
				$this->vipPlayers->save();
				return true;
			}
		}else{
			$sender->sendMessage("Run this command in-game");
			return true;
		}
		break;
			
			default:
			return false;
		}
	}
}
