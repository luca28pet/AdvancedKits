<?php

namespace AdvancedKits\kit;

use pocketmine\item\Item;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pocketmine\console\ConsoleCommandSender;

final class Kit {
	public function __construct(
		private string $name,
		/** @var list<Item> */
		private array $slotFreeItems,
		/** @var array<int, Item> */
		private array $slotItems,
		private ?Armor $helmet,
		private ?Armor $chestplate,
		private ?Armor $leggings,
		private ?Armor $boots,
		private int $cost,
		/** @var list<string> */
		private array $commands
	) {
		if ($this->cost < 0) {
			throw new \DomainException('$cost must be non negative');
		}
	}

	public function getName() : string {
		return $this->name;
	}

	/**
	 * @return array<Item>
	 */
	public function getSlotFreeItems() : array {
		return $this->slotFreeItems;
	}

	/**
	 * @return array<int, Item>
	 */
	public function getSlotItems() : array {
		return $this->slotItems;
	}

	/**
	 * Give the kit to a player without performing any additional checks
	 */
	public function giveTo(Player $p) : void {
		foreach ($this->slotItems as $slot => $item) {
			$p->getInventory()->setItem($slot, $item);
		}
		$p->getInventory()->addItem(...$this->slotFreeItems);
		if ($this->helmet !== null) {
			$p->getArmorInventory()->setHelmet($this->helmet);
		}
		if ($this->chestplate !== null) {
			$p->getArmorInventory()->setChestplate($this->chestplate);
		}
		if ($this->leggings !== null) {
			$p->getArmorInventory()->setLeggings($this->leggings);
		}
		if ($this->boots !== null) {
			$p->getArmorInventory()->setBoots($this->boots);
		}
		foreach ($this->commands as $command) {
			$p->getServer()->dispatchCommand(new ConsoleCommandSender($p->getServer(), $p->getServer()->getLanguage()), $command);
		}
	}
}

