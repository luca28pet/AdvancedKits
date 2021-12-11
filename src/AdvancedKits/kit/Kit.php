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
		private ?Item $helmet,
		private ?Item $chestplate,
		private ?Item $leggings,
		private ?Item $boots,
		private int $cost,
		/** @var list<string> */
		private array $commands,
		private bool $clearInventory,
		private bool $overWriteArmor
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

	public function getHelmet() : ?Item {
		return $this->helmet;
	}

	public function getChestplate() : ?Item {
		return $this->chestplate;
	}

	public function getLeggigns() : ?Item {
		return $this->leggings;
	}

	public function getBoots() : ?Item {
		return $this->boots;
	}

	/**
	 * @return array<string>
	 */
	public function getCommands() : array {
		return $this->commands;
	}

	public function getClearInventory() : bool {
		return $this->clearInventory;
	}

	public function getOverWriteArmor() : bool {
		return $this->overWriteArmor;
	}

	/**
	 * Give the kit to a player without performing any additional checks
	 */
	public function giveTo(Player $p) : void {
		if ($this->clearInventory) {
			$p->getInventory()->clearAll();
			$p->getArmorInventory()->clearAll();
		}
		foreach ($this->slotItems as $slot => $item) {
			$p->getInventory()->setItem($slot, $item);
		}
		$p->getInventory()->addItem(...$this->slotFreeItems);
		if ($this->helmet !== null) {
			if ($p->getArmorInventory()->getHelmet()->isNull() || $this->overWriteArmor) {
				$p->getArmorInventory()->setHelmet($this->helmet);
			} else {
				$p->getInventory()->addItem($this->helmet);
			}
		}
		if ($this->chestplate !== null) {
			if ($p->getArmorInventory()->getChestplate()->isNull() || $this->overWriteArmor) {
				$p->getArmorInventory()->setChestplate($this->chestplate);
			} else {
				$p->getInventory()->addItem($this->chestplate);
			}
		}
		if ($this->leggings !== null) {
			if ($p->getArmorInventory()->getLeggings()->isNull() || $this->overWriteArmor) {
				$p->getArmorInventory()->setLeggings($this->leggings);
			} else {
				$p->getInventory()->addItem($this->leggings);
			}
		}
		if ($this->boots !== null) {
			if ($p->getArmorInventory()->getBoots()->isNull() || $this->overWriteArmor) {
				$p->getArmorInventory()->setBoots($this->boots);
			} else {
				$p->getInventory()->addItem($this->boots);
			}
		}
		foreach ($this->commands as $command) {
			$p->getServer()->dispatchCommand(new ConsoleCommandSender($p->getServer(), $p->getServer()->getLanguage()), $command);
		}
	}
}

