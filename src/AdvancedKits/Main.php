<?php

namespace AdvancedKits;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

	public $hasKit = [];

	/** @var Config*/
	private $kits;

	/** @var Config*/
	public $vipPlayers;

	/** @var Config*/
	public $vipPlayersPlus;

	public function onEnable(){
  		@mkdir($this->getDataFolder());
  		$this->kits = new Config($this->getDataFolder()."kits.yml", Config::YAML, [
			"basicpvp" => ["Rank" => "Player", "Armor" => [1, 2, 3, 4], "Content" => [
					[272, 0, 1],
					[260, 0, 5],
					[260, 0, 5]
				]
			],
			"basicbuilder" => ["Rank" => "Player", "Armor" => [], "Content" => [
                   	[4, 0, 25],
                   	[275, 0, 1],
                   	[297, 0, 3]
                ]
            ],
			"darkgodpvp" => ["Rank" => "Vip", "Armor" => [], "Content" => [
					[276, 0, 2],
                  	[311, 0, 1],
                   	[366, 0, 20]
               	]
           	]
		]);
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
					if(!($sender instanceof Player)){
						$sender->sendMessage("Run this command in game.");
						return true;
					}
					if(in_array($sender->getName(), $this->hasKit)){
						$sender->sendMessage("[AdvancedKits] You already got a kit.");
						return true;
					}
					if($this->kits->exists($args[1])){
						$readconfig = $this->kits->get($args[1]);
						$kit = new Kit($readconfig["Armor"], $readconfig["Content"], $readconfig["Rank"], $args[1], $this);
						$kit->give($sender);
						return true;
					}else{
						$sender->sendMessage("[AdvancedKits] Kit ".$args[1]." does not exist.");
						return true;
					}
				}elseif($args[0] == "addvip"){
					if($sender->isOp() or !($sender instanceof Player)){
						if(!(isset($args[1]))){
							return false;
						}
						if(isset($args[2]) and $args[2] == "plus"){
							$this->vipPlayersPlus->set(strtolower($args[1]));
							$this->vipPlayersPlus->save();
							$sender->sendMessage("[AdvancedKits] ".$args[1]." has been added to vips +.");
							return true;
						}else{
							$this->vipPlayers->set(strtolower($args[1]));
							$this->vipPlayers->save();
							$sender->sendMessage("[AdvancedKits] ".$args[1]." has been added to vips.");
							return true;
						}
					}else{
						$sender->sendMessage("[AdvancedKits] You need to be an OP in order to run this command.");
						return true;
					}
				}elseif($args[0] == "unvip"){
					if($sender->isOp() or !($sender instanceof Player)){
						if(!(isset($args[1]))){
							return false;
						}
						if(isset($args[2]) and $args[2] == "plus"){
							$this->vipPlayersPlus->remove(strtolower($args[1]));
							$this->vipPlayersPlus->save();
							$sender->sendMessage("[AdvancedKits] ".$args[1]." has been removed from vips +.");
							return true;
						}else{
							$this->vipPlayers->remove(strtolower($args[1]));
							$this->vipPlayers->save();
							$sender->sendMessage("[AdvancedKits] ".$args[1]." has been removed from vips.");
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
	
	public function onDeath(PlayerDeathEvent $event){
		if(($key = array_search($event->getEntity()->getName(), $this->hasKit)) !== false) {
			unset($this->hasKit[$key]);
		}
	}
	
	public function onQuit(PlayerQuitEvent $event){
		if(($key = array_search($event->getPlayer()->getName(), $this->hasKit)) !== false) {
			unset($this->hasKit[$key]);
		}
	}

}
