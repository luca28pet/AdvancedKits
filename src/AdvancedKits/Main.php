<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
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
                    )
                )
            )
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
			if($args[0] == "get"){
			if ($issuer instanceof Player){
			$player = $sender->getName();
                if(isset($this->configFile->get("kits")[strtolower($args[1])])){
                    $selectedkit = $this->configFile->get(strtolower($args[1]));
                    if($selectedkit["Vip"] == true and !$this->vipPlayers->exists($sender->getName()){
                        $sender->sendMessage("You cannot get this kit, buy vip!!");
                    }else{
                        foreach ($selectedkit as $kit){
							$sender->addItem($kit[0], $kit[1], $kit[2]);
							}
                        $sender->sendMessage("[AdvancedKits] Here is your kit!");
                    }
                }else{
						$sender->sendMessage("This kit does not exist");
				}
				return true;
				}else{
				$sender->sendMessage("Run this command in game.");
				return true;
				}
			}
			if($args[0] == "addvip"){
				if($sender->isOP){
				$playerName = $args[1];
				$this->vipPlayers->set($playerName));
				$this->vipPlayers->save();
				return true;
				}else{
				$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command");
				return true;
				}
			}
			if($args[0] == "unvip"){
				if($sender->isOP){
				$playerName = $args[1];
				$this->vipPlayers->remove($playerName));
				$this->vipPlayers->save();
				return true;
				}else{
				$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command");
				return true;
				}
			}
		break;
			
			default:
			return false;
		}
	}
}
