<?php

namespace luca28pet\AdvancedKits\tasks;

use luca28pet\AdvancedKits\Main;
use pocketmine\scheduler\Task;

class CoolDownTask extends Task{

    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(int $tick) : void{
        foreach($this->plugin->kits as $kit){
            $kit->processCoolDown();
        }
    }

}