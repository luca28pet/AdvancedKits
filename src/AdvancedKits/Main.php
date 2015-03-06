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

	public $hasKit = array();
	/** @var Config::YAML*/
	private $kits;
	/** @var Config::ENUM*/
	public $vipPlayers;
	/** @var Config::ENUM*/
	public $vipPlayersPlus;

	public function onEnable(){
  		@mkdir($this->getDataFolder());
  		$this->kits = new Config($this->getDataFolder()."kits.yml", Config::YAML, array(
			"basicpvp" => array("Rank" => "Player", "Armor" => array(1, 2, 3, 4), "Content" => array(
					array(272, 0, 1),
					array(260, 0, 5),
					array(260, 0, 5)
				)
			),
			"basicbuilder" => array("Rank" => "Player", "Armor" => array(), "Content" => array(
                   	array(4, 0, 25),
                   	array(275, 0, 1),
                   	array(297, 0, 3),
                )
            ),
			"darkgodpvp" => array("Rank" => "Vip", "Armor" => array(), "Content" => array(
					array(276, 0, 2),
                  	array(311, 0, 1),
                   	array(366, 0, 20)
               	)
           	)
		));
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
						if(in_array($sender->getName(), $this->hasKit)){
							$sender->sendMessage("[AdvancedKits] You already got a kit.");
							return true;
						}
						$kitname = $args[1];
						if($this->kits->exists($kitname)){
							$readconfig = $this->kits->get($kitname);
							$kit = new Kit($readconfig["Armor"], $readconfig["Content"], $readconfig["Rank"], $kitname, $this);
							$kit->give($sender);
							return true;
						}else{
							$sender->sendMessage("[AdvancedKits] This kit does not exist.");
							return true;
						}
					}else{
						$sender->sendMessage("Run this command in game.");
						return true;
					}
				}elseif($args[0] == "addvip"){
					if($sender->isOp() or !$sender instanceof Player){
						if(!(isset($args[1]))){
							return false;
						}
						$playerName = $args[1];
						if(isset($args[2]) and $args[2] == "plus"){
							$this->vipPlayersPlus->set(strtolower($playerName));
							$this->vipPlayersPlus->save();
							$sender->sendMessage("[AdvancedKits] ".$playerName." has been added to vips +.");
							return true;
						}else{
							$this->vipPlayers->set(strtolower($playerName));
							$this->vipPlayers->save();
							$sender->sendMessage("[AdvancedKits] ".$playerName." has been added to vips.");
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
						if(isset($args[2]) and $args[2] == "plus"){
							$this->vipPlayersPlus->remove(strtolower($playerName));
							$this->vipPlayersPlus->save();
							$sender->sendMessage("[AdvancedKits] ".$playerName." has been removed from vips +.");
							return true;
						}else{
							$this->vipPlayers->remove(strtolower($playerName));
							$this->vipPlayers->save();
							$sender->sendMessage("[AdvancedKits] ".$playerName." has been removed from vips.");
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
		if(in_array($event->getEntity()->getName(), $this->hasKit)){
			if(($key = array_search($event->getEntity()->getName(), $this->hasKit)) !== false) {
				unset($this->hasKit[$key]);
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

}
