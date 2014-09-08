<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\inventory\BaseInventory;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->configFile = new Config($this->getDataFolder()."kits.yml", Config::YAML, array(
            "basicpvp" => array(
                "Vip" => false,
                "Vip+" => false,
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
					 array(
                        260,
                        0,
                        5
                    )
                )
            ),
            "basicbuilder" => array(
                "Vip" => false,
                "Vip+" => false,
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
                "Vip+" => false,
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
		$this->vipPlayersPlus = new Config($this->getDataFolder()."vips+.txt", Config::ENUM);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("AdvancedKits enabled!");
    }

	public function onDisable(){
		$this->configFile->save();
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		switch($command->getName()){
			case "advancedkits":
			if(!(isset($args[0]))){
				return false;
			}
			if($args[0] == "get"){
				if($sender instanceof Player){
					$player = $sender->getName();
					$selectedkit = $this->configFile->get(strtolower($args[1]));
                			if(isset($selectedkit)){
                				 if($selectedkit["Vip"] == true){
							if(!($this->vipPlayers->exists($player)) and (!($this->vipPlayersPlus->exist($player)))){			//and !$this->vipPlayers->exists($sender->getName())){
                        					$sender->sendMessage("[AdvancedKits] You cannot get this kit, buy vip!!");
							}elseif($this->vipPlayers->exists($player) or $this->vipPlayersPlus->exist($player)){
								$this->AddKit($selectedkit, $sender, $args);
                        					$sender->sendMessage("[AdvancedKits] Here is your kit!");
							}
                    				}elseif($selectedkit["Vip+"] == true){
                    					if($this->vipPlayersPlus->exist($player)){
                    						$this->AddKit($selectedkit, $sender, $args);
                        					$sender->sendMessage("[AdvancedKits] Here is your kit!");
                    					}else{
                    						$sender->sendMessage("[AdvancedKits] You cannot get this kit, buy vip!!");
                    					}
                    				}else{
							$this->AddKit($selectedkit, $sender, $args);
                        				$sender->sendMessage("[AdvancedKits] Here is your kit!");
                    				}
        				 }else{
						$sender->sendMessage("[AdvancedKits] That kit does not exist");
					}
					return true;
				}else{
					$sender->sendMessage("Run this command in game.");
					return true;
				}
			}
			if($args[0] == "addvip"){
				if($sender instanceof Player){
					if($sender->isOP){
						$playerName = $args[1];
						if($args[1] == "plus"){
							$this->vipPlayersPlus->set($playerName);
							$this->vipPlayersPlus->save();
							$sender->sendMessage($playerName." has been added to vips +.");
						}else{
							$this->vipPlayers->set($playerName);
							$this->vipPlayers->save();
							$sender->sendMessage($playerName." has been added to vips.");
							return true;
						}
					}else{
						$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command");
						return true;
					}
				}elseif(!($sender instanceof Player)){
					$playerName = $args[1];
					if($args[1] == "plus"){
						$this->vipPlayersPlus->set($playerName);
						$this->vipPlayersPlus->save();
						$sender->sendMessage($playerName." has been added to vips +.");
					}else{
						$this->vipPlayers->set($playerName);
						$this->vipPlayers->save();
						$sender->sendMessage($playerName." has been added to vips.");
						return true;
					}
				}
			}
			if($args[0] == "unvip"){
				if($sender instanceof Player){
					if($sender->isOP){
						$playerName = $args[1];
						if($args[1] == "plus"){
							$this->vipPlayersPlus->remove($playerName);
							$this->vipPlayersPlus->save();
							$sender->sendMessage($playerName." has been removed from vips +.");
						}else{
							$this->vipPlayers->remove($playerName);
							$this->vipPlayers->save();
							$sender->sendMessage($playerName." has been removed from vips.");
							return true;
						}
					}else{
						$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command");
						return true;
					}
				}elseif(!($sender instanceof Player)){
					$playerName = $args[1];
					if($args[1] == "plus"){
						$this->vipPlayersPlus->remove($playerName);
						$this->vipPlayersPlus->save();
						$sender->sendMessage($playerName." has been removed from vips +.");
					}else{
						$this->vipPlayers->remove($playerName);
						$this->vipPlayers->save();
						$sender->sendMessage($playerName." has been removed from vips.");
						return true;
					}
				}
			}
		break;
			
			default:
			return false;
		}
	}
	public function AddKit($selectedkit, $player, $params){
		$selectedkit = $this->configFile->get(strtolower($params[1]));
		$readconfig = $this->configFile->get(strtolower($params[1])['Content']);
		if(!(isset($readconfig[1]))){
			foreach ($selectedkit['Content'] as $kit){
				$player->addItem($kit[0]);
				}
			}
		if(!(isset($readconfig[2]))){
			foreach ($selectedkit['Content'] as $kit){
				$player->addItem($kit[0], $kit[1]);
				}
			}
		if(!(isset($readconfig[3]))){
			foreach ($selectedkit['Content'] as $kit){
				$player->addItem($kit[0], $kit[1], $kit[2]);
				}
			}
		if(!(isset($readconfig[4]))){
			foreach ($selectedkit['Content'] as $kit){
				$player->addItem($kit[0], $kit[1], $kit[2], $kit[3]);
				}
			}
		if(!(isset($readconfig[5]))){
			foreach ($selectedkit['Content'] as $kit){
				$player->addItem($kit[0], $kit[1], $kit[2], $kit[3], $kit[4]);
				}
			}
		if(!(isset($readconfig[6]))){
			foreach ($selectedkit['Content'] as $kit){
				$player->addItem($kit[0], $kit[1], $kit[2], $kit[3], $kit[4], $kit[5]);
				}
			}
	}
}
