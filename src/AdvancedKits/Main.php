<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\event\player\PlayerQuitevent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\inventory\BaseInventory;
use pocketmine\utils\Config;
use pocketmine\item\Item;

class Main extends PluginBase implements Listener{

private $hasKit = array();

	public function onEnable(){
  		@mkdir($this->getDataFolder());
  		$this->kits = new Config($this->getDataFolder()."kits.yml", Config::YAML, array(
            		"basicpvp" => array(
                		"Rank" => "Player",
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
                		"Rank" => "Player",
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
                		"Rank" => "Vip",
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
  		$this->kits->save();
  		$this->vipPlayers = new Config($this->getDataFolder()."vips.txt", Config::ENUM);
  		$this->vipPlayersPlus = new Config($this->getDataFolder()."vips+.txt", Config::ENUM);
  		$this->getServer()->getPluginManager()->registerEvents($this, $this);
 	}

 	public function onDisable(){
 	 	$this->kits->save();
 	 	$this->vipPlayers->save();
 	 	$this->vipPlayersPlus->save();
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
      					$kitname = $args[1];
      					$readconfig  = $this->kits->get($kitname);
       					if(isset($readconfig)){
						switch($readconfig['Rank']){
	 						case "Vip+":
	 							if($this->vipPlayersPlus->exists($sender->getName())){
	 								if(!in_array($sender->getName(), $this->hasKit)){
	  									$this->addKit($sender, $kitname);
	  									$sender->sendMessage("[AdvancedKits] Kit added to inventory.");
	  									return true;
	 								}else{
	 									$sender->sendMessage("[AdvancedKits] You already got a kit.");
	 								}
	 							}else{
	  								$sender->sendMessage("[AdvancedKits] This is a Vip++ kit!");
	  								return true;
	 							}
	 						break;
	 						case "Vip":
	 							if($this->vipPlayersPlus->exists($sender->getName()) || $this->vipPlayers->exists($sender->getName())){
	 								if(!in_array($sender->getName(), $this->hasKit)){
	  									$this->addKit($sender, $kitname);
	  									$sender->sendMessage("[AdvancedKits] Kit added to iventory.");
	  									return true;
	 								}else{
	 									$sender->sendMessage("[AdvancedKits] You already got a kit.");
	 								}
	 							}else{
	  								$sender->sendMessage("[AdvancedKits] This is a vip kit!");
	  								return true;
	 							}
	 						break;
							case "Player":
								if(!in_array($sender->getName(), $this->hasKit)){
	 								$this->addKit($sender, $kitname);
	 								$sender->sendMessage("[AdvancedKits] Kit added to inventory.");
	 								return true;
								}else{
									$sender->sendMessage("[AdvancedKits] You already got a kit.");
								}
	 						break;
	 						default:
	 							$sender->sendMessage("[AdvancedKits] Kit rank is invalid.");
	 						return true;
						}
       					}else{
						$sender->sendMessage("[AdvancedKits] This kit does not exist.");
					}
      				}else{
       					$sender->sendMessage("Run this command in game.");
      				}
				return true;
     			}elseif($args[0] == "addvip"){
				if($sender->isOp() or !$sender instanceof Player){
					if(!(isset($args[1]))){
						return false;
					}
					$playerName = $args[1];
					if($args[2] == "plus"){
						$this->vipPlayersPlus->set($playerName);
						$this->vipPlayersPlus->save();
						$sender->sendMessage($playerName." has been added to vips +.");
						return true;
					}else{
						$this->vipPlayers->set($playerName);
						$this->vipPlayers->save();
						$sender->sendMessage($playerName." has been added to vips.");
						return true;
					}
				}else{
					$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command.");
					return true;
				}
			}elseif($args[0] == "unvip"){
				if($sender->isOp() or !$sender instanceof Player){
					if(!(isset($args[1]))){
						return false;
					}
					$playerName = $args[1];
					if($args[2] == "plus"){
						$this->vipPlayersPlus->remove($playerName);
						$this->vipPlayersPlus->save();
						$sender->sendMessage($playerName." has been removed from vips +.");
						return true;
					}else{
						$this->vipPlayers->remove($playerName);
						$this->vipPlayers->save();
						$sender->sendMessage($playerName." has been removed from vips.");
						return true;
					}
				}else{
					$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command.");
					return true;
				}
			}else{
				return false;
			}
			break;
			
			default:
			return false;
		}
	}
	
	public function onDeath(EntityDeathEvent $event){
		if($event->getEntity() instanceof Player){
			if(in_array($event->getEntity()->getName(), $this->hasKit)){
				if(($key = array_search($event->getEntity()->getName(), $this->hasKit)) !== false) {
    					unset($this->hasKit[$key]);
				}
			}
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
		if(in_array($event->getPlayer()->getName(), $this->hasKit)){
			if(($key = array_search($event->getPlayer()->getName(), $this->hasKit)) !== false) {
				unset($this->hasKit[$key]);
			}
		}
	}
	
	private function AddKit(Player $player, $kitname){
		$selectedkit = $this->kits->get($kitname);
		foreach($selectedkit['Content'] as $k){
			$items = new Item($k[0], $k[1], $k[2]);
			$player->getInventory()->addItem($items);
		}
		array_push($this->hasKit, $player->getName());
		return true;
	}
}
