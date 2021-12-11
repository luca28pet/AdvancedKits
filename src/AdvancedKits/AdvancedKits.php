<?php
declare(strict_types=1);

namespace AdvancedKits;

use pocketmine\plugin\PluginBase;
use AdvancedKits\kit\KitsManager;
use AdvancedKits\kit\Kit;
use pocketmine\item\StringToItemParser;
use pocketmine\item\Item;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use AdvancedKits\perm\DefaultPermissionChecker;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use AdvancedKits\lang\LangManager;

final class AdvancedKits extends PluginBase {
	private KitsManager $kitsManager;
	private LangManager $langManager;

	protected function onEnable() : void {
		$this->saveResource('kits.yml');
		$contents = file_get_contents($this->getDataFolder().'kits.yml');
		if ($contents === false) {
			$this->getLogger()->error('Unable to open kits.yml');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$data = yaml_parse($contents);
		try {
			$kits = ConfigParser::getList($data, [ConfigParser::class, 'getKit']);
		} catch (\InvalidArgumentException $e) {
			$this->getLogger()->error('Configuration error in kits.yml, details:');
			do {
				$this->getLogger()->error($e->getMessage());
			} while (($e = $e->getPrevious()) !== null);
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->langManager = new LangManager($this->getDataFolder().'lang.properties', $this->getLogger());
		$this->kitsManager = new KitsManager(
			array_combine(array_map(fn(Kit $kit) => $kit->getName(), $kits), $kits),
			new DefaultPermissionChecker($kits),
			$this->langManager
		);
	}

	public function getKitsManager() : KitsManager {
		return $this->kitsManager;
	}

	public function getLangManager() : LangManager {
		return $this->langManager;
	}

	public function onCommand(CommandSender $sn, Command $cmd, string $lbl, array $args) : bool {
		switch($cmd->getName()) {
		case 'kit':
			switch (count($args)) {
			case 0:
				//todo
				return true;
			case 1:
				if (!($sn instanceof Player)) {
					return true;
				}
				$kitName =& $args[0];
				$kit = $this->kitsManager->getKitByName($kitName);
				if ($kit !== null) {
					$this->kitsManager->kitRequest($sn, $kit, function(bool $reqAccepted, ?string $reason) use ($sn, $kit) : void {
						if (!$sn->isOnline()) {
							return;
						}
						if ($reqAccepted) {
							$kit->giveTo($sn);
							$sn->sendMessage($this->langManager->getTranslation('sel-kit', [$kit->getName()]));
						} else {
							$sn->sendMessage($reason ?? '');
						}
					});
				} else {
					$sn->sendMessage($this->langManager->getTranslation('no-kit', [$kitName]));
				}
				return true;
			default:
				return false;
			}
		case 'givekit':
			if (count($args) !== 2) {
				return false;
			}
			$kitName =& $args[0];
			$playerName =& $args[1];
			$kit = $this->kitsManager->getKitByName($kitName);
			if ($kit === null) {
				$sn->sendMessage($this->langManager->getTranslation('no-kit', [$kitName]));
				return true;
			}
			$player = $this->getServer()->getPlayerByPrefix($playerName);
			if ($player === null) {
				$sn->sendMessage($this->langManager->getTranslation('player-offline', [$playerName]));
				return true;
			}
			$kit->giveTo($player);
			return true;
		case 'akreload':
			//todo
			return true;
		}
		return true;
	}
}

