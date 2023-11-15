<?php
declare(strict_types=1);

namespace AdvancedKits\perm;

use pocketmine\player\Player;
use AdvancedKits\kit\Kit;

interface PermissionChecker {
	/**
	 * This function must return true if the provided player
	 * is allowed to obtain the provided kit, false otherwise
	 */
	public function canPlayerGetKit(Player $player, Kit $kit) : bool;
}

