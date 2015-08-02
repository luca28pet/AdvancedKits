<?php

namespace AdvancedKits;

use pocketmine\scheduler\PluginTask;

class CoolDownTask extends PluginTask{

    private $plugin;

    public function __construct(Main $plugin){
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun($tick){
        foreach($this->plugin->coolDown as $player => $coolDownKits){
            foreach($coolDownKits as $kit => $minutes){
                $this->plugin->coolDown[$player][$kit] -= 1;
                if($this->plugin->coolDown[$player][$kit] <= 0){
                    unset($this->plugin->coolDown[$player][$kit]);
                }
            }
        }
    }

}