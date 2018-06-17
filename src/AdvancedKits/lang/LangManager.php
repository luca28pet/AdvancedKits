<?php

namespace AdvancedKits\lang;

use AdvancedKits\Main;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class LangManager{

    public const LANG_VERSION = 1;
    private const PREFIX = TextFormat::AQUA.'['.TextFormat::RED.'AdvancedKits'.TextFormat::AQUA.'] '.TextFormat::WHITE;

    private $ak;
    private $defaults;
    private $data;

    public function __construct(Main $ak){
        $this->ak = $ak;
        $this->defaults = [
            'lang-version' => self::LANG_VERSION,
            'in-game' => self::PREFIX.'Please run this command in game',
            'av-kits' => self::PREFIX.'Available kits: {%0}',
            'no-kit' => self::PREFIX.'Kit {%0} does not exist',
            'reload' => self::PREFIX.'Reloaded kits settings',
            'sel-kit' => self::PREFIX.'Selected kit: {%0}',
            'cant-afford' => self::PREFIX.'You cannot afford kit: {%0}',
            'one-per-life' => self::PREFIX.'You can only get one kit per life',
            'cooldown1' => self::PREFIX.'Kit {%0} is in coolDown at the moment',
            'cooldown2' => self::PREFIX.'You will be able to get it in {%0}',
            'no-perm' => self::PREFIX.'You haven\'t the permission to use kit {%0}',
            'cooldown-format1' => self::PREFIX.'{%0} minutes',
            'cooldown-format2' => self::PREFIX.'{%0} hours and {%1} minutes',
            'cooldown-format3' => self::PREFIX.'{%0} hours',
            'no-sign-on-kit' => self::PREFIX.'On this sign, the kit is not specified',
            'no-perm-sign' => self::PREFIX.'You don\'t have permission to create a sign kit',
            'form-title' => self::PREFIX.'Available Kits'
        ];
        $this->data = new Config($this->ak->getDataFolder().'lang.properties', Config::PROPERTIES, $this->defaults);
        if($this->data->get('lang-version') != self::LANG_VERSION){
            $this->ak->getLogger()->warning('Translation file is outdated. The old file has been renamed and a new one has been created');
            @rename($this->ak->getDataFolder().'lang.properties', $this->ak->getDataFolder().'lang.properties.old');
            $this->data = new Config($this->ak->getDataFolder().'lang.properties', Config::PROPERTIES, $this->defaults);
        }
    }

    public function getTranslation(string $dataKey, ...$args) : string{
        if(!isset($this->defaults[$dataKey])){
            $this->ak->getLogger()->error('Invalid datakey '.$dataKey.' passed to method LangManager::getTranslation()');
            return '';
        }
        $str = $this->data->get($dataKey, $this->defaults[$dataKey]);
        foreach($args as $key => $arg){
            $str = str_replace('{%'.$key.'}', $arg, $str);
        }
        return str_replace('&', TextFormat::ESCAPE, $str);
    }

}