<?php

namespace AdvancedKits;

use AdvancedKits\economy\EconomyManager;
use AdvancedKits\lang\LangManager;
use AdvancedKits\tasks\CoolDownTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase{

    /** @var Kit[] */
    public $kits = [];
    /** @var string[] */
    public $hasKit = [];
    /** @var EconomyManager */
    public $economy;
    /** @var bool  */
    public $permManager = false;
    /** @var LangManager */
    public $langManager;
    /** @var  null|\PiggyCustomEnchants\Main */
    public $piggyCustomEnchantsInstance;

    public function onEnable() : void{
        if(!is_dir($this->getDataFolder())){
            if(!mkdir($this->getDataFolder()) && !is_dir($this->getDataFolder())){
                $this->getLogger()->error('Unable to create data folder');
            }
        }
        if(!is_dir($this->getDataFolder().'cooldowns/')){
            if(!mkdir($this->getDataFolder().'cooldowns/') && !is_dir($this->getDataFolder().'cooldowns/')){
                $this->getLogger()->error('Unable to create cooldowns folder');
            }
        }
        $this->saveDefaultConfig();
        $this->loadKits();
        $this->economy = new EconomyManager($this);
        $this->langManager = new LangManager($this);
        if(!$this->getConfig()->get('force-builtin-permissions') && $this->getServer()->getPluginManager()->getPlugin('PurePerms') !== null){
            $this->permManager = true;
            $this->getLogger()->notice('PurePerms mode enabled');
        }
        if(($plugin = $this->getServer()->getPluginManager()->getPlugin('PiggyCustomEnchants')) !== null){
            $this->piggyCustomEnchantsInstance = $plugin;
            $this->getLogger()->notice('PiggyCustomEnchants detected. Activated custom enchants support');
        }
        $this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CoolDownTask($this), 1200, 1200);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable() : void{
        foreach($this->kits as $kit){
            $kit->save();
        }
        $this->kits = [];
        $this->piggyCustomEnchantsInstance = null;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch(strtolower($command->getName())){
            case 'kit':
                if(!($sender instanceof Player)){
                    $sender->sendMessage($this->langManager->getTranslation('in-game'));
                    return true;
                }
                if(!isset($args[0])){
                    $sender->sendMessage($this->langManager->getTranslation('av-kits', implode(', ', array_keys($this->kits))));
                    return true;
                }
                $kit = $this->getKit($args[0]);
                if($kit === null){
                    $sender->sendMessage($this->langManager->getTranslation('no-kit', $args[0]));
                    return true;
                }
                $kit->handleRequest($sender);
                return true;
            case 'akreload':
                foreach($this->kits as $kit){
                    $kit->save();
                }
                $this->kits = [];
                $this->loadKits();
                $sender->sendMessage($this->langManager->getTranslation('reload'));
                return true;
        }
        return true;
    }

    private function loadKits() : void{
        $this->saveResource('kits.yml');
        $kitsData = yaml_parse_file($this->getDataFolder().'kits.yml');
        $this->fixConfig($kitsData);
        foreach($kitsData as $kitName => $kitData){
            $this->kits[$kitName] = new Kit($this, $kitData, $kitName);
        }
    }

    private function fixConfig(array &$config) : void{
        foreach($config as $name => $kit){
            if(isset($kit['users'])){
                $users = array_map('strtolower', $kit['users']);
                $config[$name]['users'] = $users;
            }
            if(isset($kit['worlds'])){
                $worlds = array_map('strtolower', $kit['worlds']);
                $config[$name]['worlds'] = $worlds;
            }
        }
    }

    /**
     * @param string $kitName
     * @return Kit|null
     */
    public function getKit(string $kitName) : ?Kit{
        /**@var Kit[] $lowerKeys*/
        $lowerKeys = array_change_key_case($this->kits, CASE_LOWER);
        if(isset($lowerKeys[strtolower($kitName)])){
            return $lowerKeys[strtolower($kitName)];
        }
        return null;
    }

    /**
     * @param $player
     * @return string|null
     */
    public function getPlayerKit($player) : ?string{
        if($player instanceof Player){
            $player = $player->getLowerCaseName();
        }else{
            $player = strtolower($player);
        }
        return $this->hasKit[$player] ?? null;
    }

}