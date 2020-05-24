<?php

/* DISCORD: кнαℓє∂#7787 */
/* Credits: by xXKHaLeD098Xx */

namespace Khaled;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use slapper\events\SlapperCreationEvent;
use slapper\events\SlapperDeletionEvent;

class WorldPlayerCount extends PluginBase implements Listener{

	public function onEnable()
	{
		if(!$this->getServer()->getPluginManager()->getPlugin("Slapper")){
			$this->getServer()->getPluginManager()->disablePlugin($this);
			$this->getLogger()->emergency("You need slapper installed, disabled plugin...");
			return;
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$this->saveResource("config.yml");
		$this->getScheduler()->scheduleRepeatingTask(new RefreshCount($this), 10);
		$worlds = $this->getConfig()->get("worlds");
		foreach ($worlds as $world){
			if (file_exists($this->getServer()->getDataPath()."/worlds/".$world)){
				$this->getServer()->loadLevel($world);
			}
		}
	}

	public function slapperCreation(SlapperCreationEvent $ev){
		$entity = $ev->getEntity();
		$name = $entity->getNameTag();
		$allines = explode("\n", $name);
		$pos = strpos($allines[1], "count ");
		if($pos !== false){
			$levelname = str_replace("count ", "", $allines[1]);
			if(file_exists($this->getServer()->getDataPath()."/worlds/".$levelname)){
				if (!$this->getServer()->isLevelLoaded($levelname)) $this->getServer()->loadLevel($levelname);
				$entity->namedtag->setString("playerCount", $levelname);
				$worlds = $this->getConfig()->get("worlds");
				if(!in_array($levelname, $worlds)){
					array_push($worlds, $levelname);
					$this->getConfig()->set("worlds", $worlds);
					$this->getConfig()->save();
					return;
				}
			}
		}
	}

	public function onSlapperDeletion(SlapperDeletionEvent $event){
		if($event->getEntity()->namedtag->hasTag("playerCount")){
			$tag = $event->getEntity()->namedtag->getString("playerCount");
			$event->getEntity()->namedtag->removeTag("playerCount");
			unset($this->getConfig()->get("worlds")[array_search($tag, $this->getConfig()->get("worlds"))]);
			$this->getConfig()->save();
		}
	}

	public function playerCount(){
		$levels = $this->getServer()->getLevels();
		foreach ($levels as $level){
			$entities = $level->getEntities();
			foreach ($entities as $entity){
				$nbt = $entity->namedtag;
				if($nbt->hasTag("playerCount")){
					$world = $nbt->getString("playerCount");
					$allines = explode("\n", $entity->getNameTag());
					$countPlayers = count($this->getServer()->getLevelByName($world)->getPlayers());
					$count = $this->getConfig()->get("count");
					$str = str_replace("{number}", $countPlayers, $count);
					$entity->setNameTag($allines[0]."\n".$str);
				}
			}
		}
	}
}

