<?php
namespace Khaled;

use pocketmine\scheduler\Task;

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