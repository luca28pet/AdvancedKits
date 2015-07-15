<?php

namespace AdvancedKits;

use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\tile\Sign;

class Main extends PluginBase implements Listener{

    private $kits;
    private $hasKit = [];
    /**@var EconomyManager*/
    private $economy;

    public function onEnable(){
        @mkdir($this->getDataFolder());
        if(!file_exists($this->getDataFolder()."kits.yml")){
            $r = $this->getResource("kits.yml");
            $o = stream_get_contents($r);
            fclose($r);
            file_put_contents($this->getDataFolder()."kits.yml", $o);
        }
        $this->kits = yaml_parse(file_get_contents($this->getDataFolder()."kits.yml"));
        $this->economy = new EconomyManager($this);
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
                if(isset($this->hasKit[$sender->getId()])){
                    $sender->sendMessage("You already have a kit");
                    return true;
                }
                if(!isset($this->kits[strtolower($args[0])])){
                    $sender->sendMessage("Kit ".$args[0]." does not exist");
                    return true;
                }
                if(!$sender->hasPermission("advancedkits.".strtolower($args[0]))){
                    $sender->sendMessage("You haven't the permission to use kit ".$args[0]);
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
        }
        return true;
    }

    public function onSign(PlayerInteractEvent $event){
        $id = $event->getBlock()->getId();
        if($id === Block::SIGN_POST or $id === Block::WALL_SIGN){
            $tile = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if($tile instanceof Sign){
                $text = $tile->getText();
                if(trim($text[0]) === "[AdvancedKits]"){
                    if(empty($text[1])){
                        $event->getPlayer()->sendMessage("On this sign, the kit is not specified");
                        return;
                    }
                    if(!isset($this->kits[strtolower($text[1])])){
                        $event->getPlayer()->sendMessage("Kit ".$text[1]." does not exist");
                        return;
                    }
                    if(!$event->getPlayer()->hasPermission("advancedkits.".strtolower($text[1]))){
                        $event->getPlayer()->sendMessage("You haven't the permission to use kit ".$text[1]);
                        return;
                    }
                    $this->addKit(strtolower($text[1]), $event->getPlayer());
                }
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event){
        if(isset($this->hasKit[$event->getEntity()->getId()])){
            unset($this->hasKit[$event->getEntity()->getId()]);
        }
    }

    public function onLogOut(PlayerQuitEvent $event){
        if(isset($this->hasKit[$event->getPlayer()->getId()])){
            unset($this->hasKit[$event->getPlayer()->getId()]);
        }
    }

    private function addKit($name, Player $player){
        $kit = $this->kits[$name];
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
        $this->hasKit[$player->getId()] = true;
    }

}