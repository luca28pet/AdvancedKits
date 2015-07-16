<?php

namespace AdvancedKits;

use pocketmine\scheduler\PluginTask;

class CoolDownTask extends PluginTask{

    private $kitName;
    private $playerName;
    private $plugin;

    public function __construct($kitName, $playerName, Main $plugin){
        $this->kitName = $kitName;
        $this->playerName = $playerName;
        $this->plugin = $plugin;
    }

    public function onRun($tick){
        if(isset($this->plugin->coolDown[$this->playerName])){
            if(($key = array_search($this->kitName, $this->plugin->coolDown[$this->playerName])) !== false){
                unset($this->plugin->coolDown[$this->playerName][$key]);
            }
        }
    }

}