<?php
declare(strict_types=1);

namespace AdvancedKits;

use AdvancedKits\kit\Kit;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Armor;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\NbtDataException;

final class ConfigParser {
	private function __construct() {
	}

	public static function getKit(mixed $node) : Kit {
		if (!is_array($node)) {
			throw new \InvalidArgumentException('Kit node must be an array, '.gettype($node).' found');
		}
		
		if (!isset($node['name'])) {
			throw new \InvalidArgumentException('Kit node must have a \'name\' field');
		}
		try {
			$name = self::getString($node['name']);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException('Kit node: error loading name', previous: $e);
		}

		$slotFreeItems = [];
		if (isset($node['items'])) {
			try {
				$slotFreeItems = self::getList($node['items'], [self::class, 'getItem']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Kit node: error loading items', previous: $e);
			}
		}

		$slottedItems = [];
		if (isset($node['slots'])) {
			try {
				$slottedItems = self::getMap($node['slots'], [self::class, 'getInt'], [self::class, 'getItem']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Kit node: error loading slots', previous: $e);
			}
		}

		$armor = ['helmet' => null, 'chestplate' => null, 'leggings' => null, 'boots' => null];
		foreach ($armor as $k => $_) {
			if (isset($node[$k])) {
				try {
					$tmp = self::getItem($node[$k]);
					if (!($tmp instanceof Armor) && !($tmp->equals(ItemFactory::air()))) {
						throw new \InvalidArgumentException('Armor item is not an armor');
					}
					$armor[$k] = $tmp;
				} catch (\InvalidArgumentException $e) {
					throw new \InvalidArgumentException('Kit node: error loading '.$k, previous: $e);
				}
			}
		}

		$cost = 0;
		if (isset($node['cost'])) {
			try {
				$cost = self::getInt($node['cost']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Kit node: error loading cost', previous: $e);
			}
		}

		$commands = [];
		if (isset($node['commands'])) {
			try {
				$commands = self::getList($node['commands'], [self::class, 'getString']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Kit node: error loading commands', previous: $e);
			}
		}

		if (!isset($node['clear-inventory'])) {
			throw new \InvalidArgumentException('Kiit node must have a \'clear-inventory\' field');
		}
		try {
			$clearInventory = self::getBool($node['clear-inventory']);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException('Kit node: error loading clear-inventory', previous: $e);
		}

		if (!isset($node['overwrite-armor'])) {
			throw new \InvalidArgumentException('Kiit node must have a \'overwrite-armor\' field');
		}
		try {
			$overWriteArmor = self::getBool($node['overwrite-armor']);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException('Kit node: error loading overwrite-armor', previous: $e);
		}

		return new Kit(
			$name,
			$slotFreeItems,
			$slottedItems,
			$armor['helmet'], $armor['chestplate'], $armor['leggings'], $armor['boots'],
			$cost,
			$commands,
			$clearInventory,
			$overWriteArmor
		);
	}

	public static function getItem(mixed $node) : Item {
		if (!is_array($node)) {
			throw new \InvalidArgumentException('Item node must be an array, '.gettype($node).' found');
		}

		if (!isset($node['alias'])) {
			throw new \InvalidArgumentException('Item node must have an \'alias\' field');
		}
		try {
			$alias = self::getString($node['alias']);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException('Item node: error loading alias', previous: $e);
		}
		$item = StringToItemParser::getInstance()->parse($alias);
		if ($item === null) {
			throw new \InvalidArgumentException('Item node has unknown alias '.$alias);
		}

		$item->setCount(1);
		if(isset($node['count'])) {
			try {
				$count = self::getInt($node['count']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Item node: error loading count', previous: $e);
			}
			$item->setCount($count);
		}

		if (isset($node['custom-name'])) {
			try {
				$customName = self::getString($node['custom-name']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Item node: error loading custom-name', previous: $e);
			}
			$item->setCustomName($customName);
		}

		if (isset($node['enchantments'])) {
			try {
				$enchs = self::getList($node['enchantments'], [self::class, 'getEnchantmentInstance']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Item node: error loading enchantments', previous: $e);
			}
			foreach ($enchs as $ench) {
				$item->addEnchantment($ench);
			}
		}

		if (isset($node['custom-nbt'])) {
			try {
				$nbtData = self::getString($node['custom-nbt']);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Item node: error loading custom-nbt', previous: $e);
			}
			try {
				$nbt = JsonNbtParser::parseJson($nbtData);
				$item->setNamedTag($nbt);
			} catch (NbtDataException $e) {
				throw new \InvalidArgumentException('Item node: error loading custom-nbt', previous: $e);
			}
		}

		return $item;
	}

	public static function getString(mixed $node) : string {
		if (!is_string($node)) {
			throw new \InvalidArgumentException('String node contains wrong type '.gettype($node));
		}
		return $node;
	}

	public static function getInt(mixed $node) : int {
		if (!is_int($node)) {
			throw new \InvalidArgumentException('Int node contains wrong type '.gettype($node));
		}
		return $node;
	}

	public static function getBool(mixed $node) : bool {
		if (!is_bool($node)) {
			throw new \InvalidArgumentException('Bool node contains wrong type '.gettype($node));
		}
		return $node;
	}

	/**
	 * @param array<string> $alternatives
	 */
	public static function getStringAlternative(mixed $node, array $alternatives) : string {
		if (!is_string($node)) {
			throw new \InvalidArgumentException('String alternative node contains wrong type '.gettype($node));
		}
		foreach ($alternatives as $alt) {
			if ($node === $alt) {
				return $alt;
			}
		}
		throw new \InvalidArgumentException(
			'String alternative node contains unknown option '.$node.', possible values: '.implode(', ', $alternatives));
	}

	public static function getEnchantmentInstance(mixed $node) : EnchantmentInstance {
		if (!is_array($node)) {
			throw new \InvalidArgumentException('Enchantment node must be an array, '.gettype($node).' found');
		}
		
		if (!isset($node['name'])) {
			throw new \InvalidArgumentException('Enchantment node must have a \'name\' field');
		}
		$ench = StringToEnchantmentParser::getInstance()->parse($node['name']);
		if ($ench === null) {
			throw new \InvalidArgumentException('Enchantment node has unknown name '.$node['name']);
		}

		if (!isset($node['level'])) {
			throw new \InvalidArgumentException('Enchantment node must have a \'level\' field');
		}
		try {
			$level = self::getInt($node['level']);
		} catch (\InvalidArgumentException $e) {
			throw new \InvalidArgumentException('Enchantment node: error loading level', previous: $e);
		}

		return new EnchantmentInstance($ench, $level);
	}

	/**
	 * @template T
	 * @param callable(mixed) : T $getter a function that returns T or throws an InvalidArgumentException if not possible
	 * @return list<T>
	 */
	public static function getList(mixed $node, callable $getter) : array {
		if (!is_array($node)) {
			throw new \InvalidArgumentException('List node must be of type array, '.gettype($node).' found');
		}
		$list = [];
		foreach ($node as $n) {
			try {
				$list[] = $getter($n);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('List node: error loading element', previous: $e);
			}
		}
		return $list;
	}

	/**
	 * @template K of int|string
	 * @template V
	 * @param callable(mixed) : K $keyGetter a function that returns K or throws an InvalidArgumentException if not possible
	 * @param callable(mixed) : V $valueGetter a function that returns V or throws an InvalidArgumentException if not possible
	 * @return array<K, V>
	 */
	public static function getMap(mixed $node, callable $keyGetter, callable $valueGetter) : array {
		if (!is_array($node)) {
			throw new \InvalidArgumentException('Map node must be of type array, '.gettype($node).' found');
		}
		$map = [];
		foreach ($node as $nk => $nv) {
			try {
				$map[$keyGetter($nk)] = $valueGetter($nv);
			} catch (\InvalidArgumentException $e) {
				throw new \InvalidArgumentException('Map node: error loading pair', previous: $e);
			}
		}
		return $map;
	}
}

