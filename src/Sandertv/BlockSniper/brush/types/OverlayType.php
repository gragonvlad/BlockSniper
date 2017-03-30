<?php

namespace Sandertv\BlockSniper\brush\types;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;
use Sandertv\BlockSniper\brush\BaseType;
use Sandertv\BlockSniper\brush\BrushManager;
use Sandertv\BlockSniper\Loader;

class OverlayType extends BaseType {
	
	/*
	 * Lays a layer of blocks over every block within the brush radius.
	 */
	public function __construct(Loader $main, Player $player, Level $level, array $blocks = []) {
		parent::__construct($main);
		$this->level = $level;
		$this->player = $player;
		$this->blocks = $blocks;
	}
	
	/**
	 * @return bool
	 */
	public function fillShape(): bool {
		$undoBlocks = [];
		foreach($this->blocks as $block) {
			if($block->getId() !== Item::AIR) {
				$directions = [
					$block->getSide(Block::SIDE_DOWN),
					$block->getSide(Block::SIDE_UP),
					$block->getSide(Block::SIDE_NORTH),
					$block->getSide(Block::SIDE_SOUTH),
					$block->getSide(Block::SIDE_WEST),
					$block->getSide(Block::SIDE_EAST)
				];
				$valid = true;
				foreach(BrushManager::get($this->player)->getBlocks() as $possibleBlock) {
					if(is_numeric($possibleBlock)) {
						if($block->getId() === $possibleBlock) {
							$valid = false;
						}
					} else {
						if($block->getId() === Item::fromString($possibleBlock)->getId()) {
							$valid = false;
						}
					}
				}
				foreach($directions as $direction) {
					if($this->level->getBlock($direction)->getId() === Item::AIR && $valid) {
						$randomBlock = BrushManager::get($this->player)->getBlocks()[array_rand(BrushManager::get($this->player)->getBlocks())];
						if($block->getId() !== $randomBlock->getId()) {
							$undoBlocks[] = $direction;
							$this->level->setBlock($direction, $randomBlock, false, false);
						}
					}
				}
			}
		}
		$this->getMain()->getUndoStore()->saveUndo($undoBlocks, $this->player);
		return true;
	}
	
	public function getName(): string {
		return "Overlay";
	}
	
	public function getPermission(): string {
		return "blocksniper.type.overlay";
	}
}
