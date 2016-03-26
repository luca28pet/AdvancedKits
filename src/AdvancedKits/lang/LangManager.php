<?php

namespace AdvancedKits\lang;

use AdvancedKits\Main;
use pocketmine\utils\Config;

class LangManager{

    const LANG_VERSION = 0;

    private $ak;
    private $defaults;
    private $data;

    public function __construct(Main $ak){
        $this->ak = $ak;
        $this->defaults = [
            "lang-version" => 0,
            "in-game" => "Please run this command in game",
            "av-kits" => "Available kits: {%0}",
            "no-kit" => "Kit {%0} does not exist",
            "reload" => "Reloaded kits settings",
            "sel-kit" => "Selected kit: {%0}",
            "cant-afford" => "You cannot afford kit: {%0}",
            "one-per-life" => "You can only get one kit per life",
            "cooldown1" => "Kit {%0} is in coolDown at the moment",
            "cooldown2" => "You will be able to get it in {%0}",
            "no-perm" => "You haven't the permission to use kit {%0}",
            "cooldown-format1" => "{%0} minutes",
            "cooldown-format2" => "{%0} hours and {%1} minutes",
            "cooldown-format3" => "{%0} hours",
            "no-sign-on-kit" => "On this sign, the kit is not specified",
            "no-perm-sign" => "You don't have permission to create a sign kit"
        ];
        $this->data = new Config($this->ak->getDataFolder()."lang.properties", Config::PROPERTIES, $this->defaults);
        if($this->data->get("lang-version") != self::LANG_VERSION){
            $this->ak->getLogger()->alert("Translation file is outdated. The old file has been renamed and a new one has been created");
            @rename($this->ak->getDataFolder()."lang.properties", $this->ak->getDataFolder()."lang.properties.old");
            $this->data = new Config($this->ak->getDataFolder()."lang.properties", Config::PROPERTIES, $this->defaults);
        }
    }

    public function getTranslation(string $dataKey, ...$args) : string{
        if(!isset($this->defaults[$dataKey])){
            $this->ak->getLogger()->error("Invalid datakey $dataKey passed to method LangManager::getTranslation()");
            return "";
        }
        $str = $this->data->get($dataKey, $this->defaults[$dataKey]);
        foreach($args as $key => $arg){
            $str = str_replace("{%".$key."}", $arg, $str);
        }
        return $str;
    }

}