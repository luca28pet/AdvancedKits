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
        foreach($this->plugin->kits as $kit){
            $kit->processCoolDown();
        }
    }

}