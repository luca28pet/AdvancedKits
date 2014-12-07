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
use pocketmine\item\Item;

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
                        4,
                        0,
                        25
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
				if(!(isset($args[1]))){
					return false;
				}
     				if($sender instanceof Player){
      					$player = $sender->getName();
      					$kitname = $args[1];
      					$readconfig = $this->configFile->get($args[1]);
       					if(isset($readconfig)){
						if($this->isVipPlus($kitname)){
	 						if($this->vipPlayersPlus->exists($sender->getName())){
	  							$this->addKit($sender, $kitname);
	  							$sender->sendMessage("[AdvancedKits] Kit added to inventory");
	 						}else{
	  							$sender->sendMessage("[AdvancedKits] This is a Vip++ kit!");
	 						}
						}elseif($this->isVip($kitname)){
	 						if($this->vipPlayersPlus->exists($sender->getName()) || $this->vipPlayers->exists($sender->getName())){
	  							$this->addKit($sender, $kitname);
	  							$sender->sendMessage("[AdvancedKits] Kit added to iventory");
	 						}else{
	  							$sender->sendMessage("[AdvancedKits] This is a vip kit!");
	 						}
						}else{
	 						$this->addKit($sender, $kitname);
	 						$sender->sendMessage("[AdvancedKits] Kit added to inventory");
						}
       					}else{
						$sender->sendMessage("[AdvancedKits] This kit does not exist.");
					}
      				}else{
       					$sender->sendMessage("Run this command in game.");
      				}
				return true;
     			}
			if($args[0] == "addvip"){
				if($sender instanceof Player){
					if(!(isset($args[1]))){
						return false;
					}
					if($sender->isOP()){
						$playerName = $args[1];
						if($args[2] == "plus"){
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
					if(!(isset($args[1]))){
						return false;
					}
					$playerName = $args[2];
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
					if(!(isset($args[1]))){
						return false;
					}
					if($sender->isOP()){
						$playerName = $args[1];
						if($args[2] == "plus"){
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
					if(!(isset($args[1]))){
						return false;
					}
					$playerName = $args[1];
					if($args[2] == "plus"){
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
	public function AddKit(Player $player, $kitname){
		$readconfig = $this->configFile->get($kitname[2]); //get the 'Content'
		$selectedkit = $this->configFile->get($kitname);
		foreach($selectedkit['Content'] as $k){
			$kit = new Item($k[0], $k[1], $k[2]);
			$player->getInventory()->addItem($kit);
			}
		}
		return true;
	}

	private function isVipPlus($kit){
		$readconfig = $this->configFile->get($kit);
		if($readconfig['Vip+'] == true){
			return true;
		}else{
			return false;
		}	
	}
	
	private function isVip($kit){
		$readconfig = $this->configFile->get($kit);
		if($readconfig['Vip'] == true){
			return true;
		}else{
			return false;
		}	
	}
}
