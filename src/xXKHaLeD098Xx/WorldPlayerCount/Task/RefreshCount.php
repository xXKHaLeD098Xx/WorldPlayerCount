<?php
namespace xXKHaLeD098Xx\WorldPlayerCount\Task;

use pocketmine\scheduler\Task;
use xXKHaLeD098Xx\WorldPlayerCount\WorldPlayerCount;

class RefreshCount extends Task {
	private $plugin;
	public function __construct(WorldPlayerCount $plugin)
	{
		$this->plugin = $plugin;
	}

	public function onRun(int $currentTick)
	{
		$this->plugin->playerCount();
	}
}