<?php

namespace AdvancedKits;

use AdvancedKits\economy\EconomyManager;
use AdvancedKits\lang\LangManager;
use AdvancedKits\tasks\CoolDownTask;
use jojoe77777\FormAPI\SimpleForm;
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
    public $permManager;
    /** @var LangManager */
    public $langManager;
    /** @var  null|\DaPigGuy\PiggyCustomEnchants\Main */
    public $piggyCustomEnchantsInstance;
    /** @var  null|\jojoe77777\FormAPI\FormAPI */
    public $formAPIInstance;

    public function onEnable() : void{
        if(!is_dir($this->getDataFolder().'cooldowns/')){
            if(!mkdir($this->getDataFolder().'cooldowns/', 0777, true) && !is_dir($this->getDataFolder().'cooldowns/')){
                $this->getLogger()->error('Unable to create cooldowns folder');
            }
        }
        if(($plugin = $this->getServer()->getPluginManager()->getPlugin('PiggyCustomEnchants')) !== null){
            $this->piggyCustomEnchantsInstance = $plugin;
            $this->getLogger()->notice('PiggyCustomEnchants detected. Activated custom enchants support');
        }
        if(($plugin = $this->getServer()->getPluginManager()->getPlugin('FormAPI')) !== null){
            $this->formAPIInstance = $plugin;
            $this->getLogger()->notice('FormAPI detected. Activated kit selection UI support');
        }
        $this->saveDefaultConfig();
        $this->loadKits();
        $this->economy = new EconomyManager($this);
        $this->langManager = new LangManager($this);
        if(!$this->getConfig()->get('force-builtin-permissions') && $this->getServer()->getPluginManager()->getPlugin('PurePerms') !== null){
            $this->permManager = true;
            $this->getLogger()->notice('PurePerms mode enabled');
        }else{
            $this->permManager = false;
        }
        $this->getScheduler()->scheduleDelayedRepeatingTask(new CoolDownTask($this), 1200, 1200);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable() : void{
        foreach($this->kits as $kit){
            $kit->save();
        }
        $this->kits = [];
        $this->piggyCustomEnchantsInstance = null;
        $this->formAPIInstance = null;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch(strtolower($command->getName())){
            case 'kit':
                if(!($sender instanceof Player)){
                    $sender->sendMessage($this->langManager->getTranslation('in-game'));
                    return true;
                }
                if(!isset($args[0])){
                    $kits = ((bool) $this->getConfig()->get('hide-no-perm-kits', false)) ? array_filter($this->kits, function (Kit $kit) use($sender){
                        return $kit->testPermission($sender);
                    }) : $this->kits;
                    if($this->formAPIInstance === null){
                        $sender->sendMessage($this->langManager->getTranslation('av-kits', implode(', ', array_keys($kits))));
                    }else{
                        $this->openKitUI($sender, $kits);
                    }
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

    /**
     * @param Player $player
     * @param Kit[] $kits
     */
    public function openKitUI(Player $player, array $kits) : void{
        if($this->formAPIInstance === null){
            return;
        }
        $form = new SimpleForm([$this, 'onPlayerSelection']);
        $form->setTitle($this->langManager->getTranslation('form-title'));
        foreach($kits as $kit){
            $form->addButton($kit->getFormName() ?? $kit->getName(), $kit->hasValidImage() ? $kit->getImageType() : -1, $kit->hasValidImage() ? $kit->getImageData() : '', $kit->getName());
        }
        $player->sendForm($form);
    }

    public function onPlayerSelection(Player $player, ?string $data) : void{
        if($data === null){
            return;
        }
        $kit = $this->getKit($data);
        if($kit === null){
            return;
        }
        $kit->handleRequest($player);
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