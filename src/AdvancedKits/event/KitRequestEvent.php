<?php
declare(strict_types=1);

namespace AdvancedKits\event;

use pocketmine\event\Event;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use AdvancedKits\kit\Kit;
use pocketmine\player\Player;

class KitRequestEvent extends Event implements Cancellable {
	public const REQUEST_DENIAL_REASON_CUSTOM = 0;
	public const REQUEST_DENIAL_REASON_PERMISSION = 1;
	public const REQUEST_DENIAL_REASON_NO_MONEY = 2;
	public const REQUEST_DENIAL_REASON_COOLDOWN = 3;
	public const REQUEST_DENIAL_REASON_FULL_INVENTORY = 4;

	public const REQUEST_DENIAL_REASONS_PRIORITY = [
		self::REQUEST_DENIAL_REASON_CUSTOM,
		self::REQUEST_DENIAL_REASON_PERMISSION,
		self::REQUEST_DENIAL_REASON_NO_MONEY,
		self::REQUEST_DENIAL_REASON_COOLDOWN,
		self::REQUEST_DENIAL_REASON_FULL_INVENTORY
	];

	/** @var array<int, string> */
	private array $denialReasons = [];

	private Player $player;
	private Kit $kit;

	public function __construct(Player $player, Kit $kit) {
		$this->player = $player;
		$this->kit = $kit;
	}

	public function getPlayer() : Player {
		return $this->player;
	}

	public function getKit() : Kit {
		return $this->kit;
	}

	public function deny(int $flag, string $reason) : void {
		if (!in_array($flag, self::REQUEST_DENIAL_REASONS_PRIORITY, true)) {
			throw new \DomainException('invalid flag');
		}
		$this->denialReasons[$flag] = $reason;
	}

	public function allow() : void {
		$this->denialReasons = [];
	}

	/**
	 * @return array<int, string>
	 */
	public function getDenialReasons() : array {
		return $this->denialReasons;
	}

	public function isAllowed() : bool {
		return count($this->denialReasons) === 0;
	}

	public function getFinalReason() : ?string {
		foreach (self::REQUEST_DENIAL_REASONS_PRIORITY as $flag) {
			if (isset($this->denialReasons[$flag])) {
				return $this->denialReasons[$flag];
			}
		}
		return null;
	}

	public function isCancelled() : bool {
		return !$this->isAllowed();
	}
}

