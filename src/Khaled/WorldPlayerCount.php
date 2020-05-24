<?php

/* DISCORD: кнαℓє∂#7787 */
/* Credits: by xXKHaLeD098Xx */

namespace Khaled;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use slapper\events\SlapperCreationEvent;
use slapper\events\SlapperDeletionEvent;
use slapper\events\SlapperHitEvent;
use pocketmine\Player;

class WorldPlayerCount extends PluginBase implements Listener{

	public function onEnable()
	{
		$map = $this->getDescription()->getMap();
		$ver = $this->getDescription()->getVersion();
		if(isset($map["author"])){
			if($map["author"] !== "xXKHaLeD098Xx" or $ver !== "1.0 by xXKHaLeD098Xx"){
				$this->getLogger()->emergency("§cPlugin info has been changed, please give the author the proper credits, set the author to xXKHaLeD098Xx, or else the serve will shutdown on every start-up");
				$this->getServer()->shutdown();
				return;
			}
		} else {
			$this->getLogger()->emergency("§cPlugin info has been changed, please give the author the proper credits, set the author to xXKHaLeD098Xx");
			$this->getServer()->shutdown();
		}
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
			$worlds = $this->getConfig()->get("worlds");
			unset($worlds[array_search($tag, $worlds)]);
			$this->getConfig()->set("worlds", $worlds);
			$this->getConfig()->save();
		}
	}

	public function onSlapperHit(SlapperHitEvent $event){ // jojoe77777 didn't call SlapperDeletionEvent on EntityDamageEvent so i will do it instead
		$slapper = $this->getServer()->getPluginManager()->getPlugin("Slapper");
		$damager = $event->getDamager();
		if($damager instanceof Player){
			if(isset($slapper->hitSessions[$damager->getName()])){
				$slapperDelete = new SlapperDeletionEvent($event->getEntity(), $damager);
				$slapperDelete->call();
			}
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
					if(file_exists($this->getServer()->getDataPath()."/worlds/".$world)){
						if($this->getServer()->isLevelLoaded($world)){
							$worlds = $this->getConfig()->get("worlds");
							if(!in_array($world, $worlds)){
								$worlds[] = $world;
								$this->getConfig()->set("worlds", $worlds);
								$this->getConfig()->save();
							}
							$countPlayers = count($this->getServer()->getLevelByName($world)->getPlayers());
							$count = $this->getConfig()->get("count");
							$str = str_replace("{number}", $countPlayers, $count);
							$entity->setNameTag($allines[0]."\n".$str);
						} else {
							$worlds = $this->getConfig()->get("worlds");
							if(!in_array($world, $worlds)){
								$worlds[] = $world;
								$this->getConfig()->set("worlds", $worlds);
								$this->getConfig()->save();
							}
							$this->getServer()->loadLevel($world);
						}
					} else {
						$slapperDelete = new SlapperDeletionEvent($entity);
						$slapperDelete->call();
						$entity->close();
					}
				}
			}
		}
	}
}

