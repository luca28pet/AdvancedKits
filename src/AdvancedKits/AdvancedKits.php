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
use AdvancedKits\perm\PermissionChecker;
use dktapps\pmforms\{MenuForm, MenuOption};


final class AdvancedKits extends PluginBase {
	private KitsManager $kitsManager;
	private LangManager $langManager;
	private PermissionChecker $permChecker;
	private string $kitListType;
	private bool $hideNoPermKits;

	protected function onEnable() : void {
		$kits = $this->loadKitsSettings();
		if ($kits === null) {
			return;
		}
		$this->loadOtherSettings();
		$this->langManager = new LangManager($this->getDataFolder().'lang.properties', $this->getLogger());
		$this->permChecker = new DefaultPermissionChecker($kits);
		$this->kitsManager = new KitsManager(
			array_combine(array_map(fn(Kit $kit) => $kit->getName(), $kits), $kits),
			$this->permChecker,
			$this->langManager
		);
	}

	private function loadOtherSettings() : void {
		$this->saveResource('config.yml');
		$contents = file_get_contents($this->getDataFolder().'config.yml');
		if ($contents === false) {
			$this->getLogger()->error('Unable to open config.yml');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$data = yaml_parse($contents);
		if (!is_array($data)) {
			$this->getLogger()->error('Configuration error in config.yml');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		try {
			$this->kitListType = ConfigParser::getStringAlternative($data['kit-list-type'] ?? null, ['chat', 'form']);
			$this->hideNoPermKits = ConfigParser::getBool($data['hide-no-perm-kits'] ?? null);
		} catch (\InvalidArgumentException $e) {
			$this->getLogger()->error('Configuration error in config.yml, details:');
			do {
				$this->getLogger()->error($e->getMessage());
			} while (($e = $e->getPrevious()) !== null);
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
	}

	/**
	 * @return array<Kit>
	 */
	private function loadKitsSettings() : ?array {
		$this->saveResource('kits.yml');
		$contents = file_get_contents($this->getDataFolder().'kits.yml');
		if ($contents === false) {
			$this->getLogger()->error('Unable to open kits.yml');
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return null;
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
			return null;
		}
		return $kits;
	}

	public function getKitsManager() : KitsManager {
		return $this->kitsManager;
	}

	public function getLangManager() : LangManager {
		return $this->langManager;
	}

	private function doKitRequest(Player $p, Kit $kit) : void {
		$this->kitsManager->kitRequest($p, $kit, function(bool $reqAccepted, ?string $reason) use ($p, $kit) : void {
			if (!$p->isOnline()) {
				return;
			}
			if ($reqAccepted) {
				$kit->giveTo($p);
				$p->sendMessage($this->langManager->getTranslation('sel-kit', [$kit->getName()]));
			} else {
				$p->sendMessage($reason ?? '');
			}
		});
	}

	public function onCommand(CommandSender $sn, Command $cmd, string $lbl, array $args) : bool {
		switch($cmd->getName()) {
		case 'kit':
			switch (count($args)) {
			case 0:
				if ($sn instanceof Player && $this->hideNoPermKits) {
					$kits = array_filter($this->kitsManager->getKits(),
						fn(Kit $kit) => $this->permChecker->canPlayerGetKit($sn, $kit));
				} else {
					$kits = $this->kitsManager->getKits();
				}
				switch ($this->kitListType) {
				case 'chat':
					$sn->sendMessage($this->langManager->getTranslation(
						'av-kits', [implode(', ', array_map(fn(Kit $kit) => $kit->getName(), $kits))]));
					break;
				case 'form':
					$options = array_map(fn(Kit $kit) => new MenuOption($kit->getName()), $kits);
					$form = new MenuForm($this->langManager->getTranslation('form-title', []), '', $options, function(Player $p, int $opt) use (&$form) : void {
						$option = $form?->getOption($opt);
						if ($option === null) {
							return;
						}
						$kit = $this->kitsManager->getKitByName($option->getText());
						if ($kit === null) {
							return;
						}
						$this->doKitRequest($p, $kit);
					});
					break;
				default:
					throw new \LogicException('This should never happen');
				}
				return true;
			case 1:
				if (!($sn instanceof Player)) {
					return true;
				}
				$kitName =& $args[0];
				$kit = $this->kitsManager->getKitByName($kitName);
				if ($kit !== null) {
					$this->doKitRequest($sn, $kit);
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

