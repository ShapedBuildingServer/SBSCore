<?php

declare(strict_types=1);

namespace xenialdan\MagicWE2;

use pocketmine\block\Block;
use pocketmine\utils\UUID;

class QuickReplace{
	/** @var Block */
	private $block;
	/** @var UUID */
	private $uuid;

	public function __construct(Block $block){
		$this->setUUID(UUID::fromRandom());
		$this->setBlock($block);
	}

	private function setBlock(Block $block){
		$this->block = $block;
	}

	/**
	 * @return Block
	 */
	public function getBlock() :Block{
		return $this->block;
	}

	private function setUUID(UUID $uuid){
		$this->uuid = $uuid;
	}

	/**
	 * @return UUID
	 */
	public function getUUID(){
		return $this->uuid;
	}
}
