<?php
declare(strict_types=1);

namespace AdvancedKits\kit;

use AdvancedKits\perm\PermissionChecker;
use pocketmine\player\Player;
use AdvancedKits\event\KitRequestEvent;
use pocketmine\inventory\SimpleInventory;
use pocketmine\inventory\Inventory;
use AdvancedKits\lang\LangManager;

class KitsManager {
	/** @var array<string, Kit> */
	private array $kits;
	private PermissionChecker $permChecker;
	private LangManager $langManager;

	/**
	 * @param array<string, Kit> $kits
	 */
	public function __construct(array $kits, PermissionChecker $permChecker, LangManager $langManager) {
		$this->kits = array_change_key_case($kits, CASE_LOWER);
		if (count($this->kits) !== count($kits)) {
			throw new \InvalidArgumentException('Kit names are case insensitive');
		}
		$this->permChecker = $permChecker;
		$this->langManager = $langManager;
	}

	/**
	 * @return array<string, Kit>
	 */
	public function getKits() : array {
		return $this->kits;
	}

	public function getKitByName(string $name) : ?Kit {
		return $this->kits[strtolower($name)] ?? null;
	}

	/**
	 * @param callable(bool $accepted, ?string $reason) : void $callback
	 */
	public function kitRequest(Player $p, Kit $kit, callable $callback) : void {
		$ev = new KitRequestEvent($p, $kit);

		if (!$this->permChecker->canPlayerGetKit($p, $kit)) {
			$ev->deny(KitRequestEvent::REQUEST_DENIAL_REASON_PERMISSION, $this->langManager->getTranslation('no-perm', [$kit->getName()]));
		}

		if (!self::inventoryCheck($p->getInventory(), $kit)) {
			$ev->deny(KitRequestEvent::REQUEST_DENIAL_REASON_FULL_INVENTORY, $this->langManager->getTranslation('full-inv', [$kit->getName()]));
		}

		//todo check economy, cooldown
		$ev->call();
		$callback($ev->isAllowed(), $ev->getFinalReason());
	}

	private static function inventoryCheck(Inventory $inv, Kit $kit) : bool {
		$tmpInv = new SimpleInventory($inv->getSize());
		$tmpInv->setContents($inv->getContents());
		foreach ($kit->getSlotItems() as $slot => $item) {
			$tmpInv->setItem($slot, $item);
		}
		foreach ($kit->getSlotFreeItems() as $item) {
			if (!$tmpInv->canAddItem($item)) {
				return false;
			}
			$tmpInv->addItem($item);
		}
		if ($kit->getHelmet()?->isNull() === false && !$kit->getOverWriteArmor()) {
			if (!$tmpInv->canAddItem($kit->getHelmet())) {
				return false;
			}
			$tmpInv->addItem($kit->getHelmet());
		}
		if ($kit->getChestplate()?->isNull() === false && !$kit->getOverWriteArmor()) {
			if (!$tmpInv->canAddItem($kit->getChestplate())) {
				return false;
			}
			$tmpInv->addItem($kit->getChestplate());
		}
		if ($kit->getLeggigns()?->isNull() === false && !$kit->getOverWriteArmor()) {
			if (!$tmpInv->canAddItem($kit->getLeggigns())) {
				return false;
			}
			$tmpInv->addItem($kit->getLeggigns());
		}
		if ($kit->getBoots()?->isNull() === false && !$kit->getOverWriteArmor()) {
			if (!$tmpInv->canAddItem($kit->getBoots())) {
				return false;
			}
			$tmpInv->addItem($kit->getBoots());
		}
		return true;
	}
}

