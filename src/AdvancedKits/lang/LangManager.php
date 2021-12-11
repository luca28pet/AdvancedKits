<?php

namespace AdvancedKits\lang;

use AdvancedKits\AdvancedKits;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class LangManager{
	public const LANG_VERSION = 1;
	public const DEFAULTS = [
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
		'form-title' => self::PREFIX.'Available Kits',
		'full-inv' => self::PREFIX.'Your inventory is full, kit {%0} was not given',
		'player-offline' => self::PREFIX.'Player {%0} is not online'
	];
	private const PREFIX = TextFormat::AQUA.'['.TextFormat::RED.'AdvancedKits'.TextFormat::AQUA.'] '.TextFormat::WHITE;

	/** @var array<mixed> */
	private array $data;

	public function __construct(string $configPath, ?\Logger $logger){
		$this->data = (new Config($configPath, Config::PROPERTIES, self::DEFAULTS))->getAll();
		if(!isset($this->data['lang-version']) || $this->data['lang-version'] != self::LANG_VERSION){
			if ($logger !== null) {
				$logger->warning('Translation file is outdated. The old file has been renamed and a new one has been created');
			}
			@rename($configPath, $configPath.'.old');
			$this->data = (new Config($configPath, Config::PROPERTIES, self::DEFAULTS))->getAll();
		}
	}

	/**
	 * @param list<string> $args
	 */
	public function getTranslation(string $dataKey, array $args) : string{
		if(!isset(self::DEFAULTS[$dataKey])){
			throw new \InvalidArgumentException('Invalid datakey '.$dataKey.' passed to method LangManager::getTranslation()');
		}
		$str = $this->data[$dataKey] ?? self::DEFAULTS[$dataKey];
		foreach($args as $key => $arg){
			$str = str_replace('{%'.$key.'}', $arg, strval($str));
		}
		return TextFormat::colorize(strval($str));
	}
}

