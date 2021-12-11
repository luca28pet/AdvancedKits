<?php
declare(strict_types=1);

namespace AdvancedKits\perm;

use AdvancedKits\perm\PermissionChecker;
use pocketmine\player\Player;
use AdvancedKits\kit\Kit;
use pocketmine\permission\PermissionManager;
use pocketmine\permission\Permission;

class DefaultPermissionChecker implements PermissionChecker {
	/**
	 * @param array<Kit> $kits
	 */
	public function __construct(array $kits) {
		foreach ($kits as $kit) {
			PermissionManager::getInstance()->addPermission(new Permission('advancedkits.'.strtolower($kit->getName())));
		}
	}

	public function canPlayerGetKit(Player $p, Kit $kit) : bool {
		return $p->hasPermission('advancedkits.'.strtolower($kit->getName()));
	}
}

