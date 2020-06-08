<?php

/* Credits: xXKHaLeD098Xx
* Discord: кнαℓє∂#7787
*/

namespace xXKHaLeD098Xx\WorldPlayerCount;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use slapper\events\SlapperCreationEvent;
use slapper\events\SlapperDeletionEvent;
use xXKHaLeD098Xx\WorldPlayerCount\Task\RefreshCount;
use slapper\events\SlapperHitEvent;
use slapper\Main as SlapperMain;
use pocketmine\Player;

class WorldPlayerCount extends PluginBase implements Listener{

	public function getSlapper() : SlapperMain{
		/** @var SlapperMain $api */
		$api = $this->getServer()->getPluginManager()->getPlugin("Slapper");
		return $api;
	}

	public function onEnable()
	{
		$map = $this->getDescription()->getMap();
		$ver = $this->getDescription()->getVersion();
		if(isset($map["author"])){
			if($map["author"] !== "xXKHaLeD098Xx" or $ver !== "2.0-beta"){
				$this->getLogger()->emergency("§cPlugin info has been changed, please give the author the proper credits, set the author to \"xXKHaLeD098Xx\" and setting the version to \"1.0 by xXKHaLeD098Xx\" if required, or else the server will shutdown on every start-up");
				$this->getServer()->shutdown();
				return;
			}
		} else {
			$this->getLogger()->emergency("§cPlugin info has been changed, please give the author the proper credits, set the author to \"xXKHaLeD098Xx\" and setting the version to \"1.0 by xXKHaLeD098Xx\" if required, or else the server will shutdown on every start-up");
			$this->getServer()->shutdown();
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
		$this->saveResource("config.yml");
		$this->getScheduler()->scheduleRepeatingTask(new RefreshCount($this), (int) $this->getConfig()->get("count-interval") * 10);
		$worlds = $this->getConfig()->get("worlds");
		foreach ($worlds as $key => $world){
			if (file_exists($this->getServer()->getDataPath()."/worlds/".$world)){
				$this->getServer()->loadLevel($world);
			} else {
				unset($worlds[$key]);
				$this->getConfig()->set("worlds", $worlds);
				$this->getConfig()->save();
			}
		}
	}

	public function slapperCreation(SlapperCreationEvent $ev){
		$entity = $ev->getEntity();
		$name = $entity->getNameTag();
		if(strpos($name, "\n") !== false){
			$allines = explode("\n", $name);
			$pos = strpos($allines[1], "count ");
			if($pos !== false){
				//Single world
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
			//Multi-world
			$combinedPos = strpos($allines[1], "combinedcounts ");
			if($combinedPos !== false){
				$symbolPos = strpos($allines[1], "&");
				if($symbolPos !== false){
					$levelnameS = str_replace("combinedcounts ", "", $allines[1]);
					$levelnamesInArray = explode("&", $levelnameS);
					if(in_array("", $levelnamesInArray)) return;
					$entity->namedtag->setString("combinedPlayerCounts", $levelnameS);
					$this->combinedPlayerCounts();
				}
			}
		}
	}

	public function onSlapperDeletion(SlapperDeletionEvent $event){

		// single world
		if($event->getEntity()->namedtag->hasTag("playerCount")){
			$tag = $event->getEntity()->namedtag->getString("playerCount");
			$event->getEntity()->namedtag->removeTag("playerCount");
			$worlds = $this->getConfig()->get("worlds");
			unset($worlds[array_search($tag, $worlds)]);
			$this->getConfig()->set("worlds", $worlds);
			$this->getConfig()->save();
		}
		// combined
		if($event->getEntity()->namedtag->hasTag("combinedPlayerCounts")){
			$tag = $event->getEntity()->namedtag->getString("combinedPlayerCounts");
			$event->getEntity()->namedtag->removeTag("combinedPlayerCounts");
			$worlds = $this->getConfig()->get("worlds");
			$arrayOfNames = explode("&", $tag);
			foreach ($arrayOfNames as $name){
				if(in_array($name, $worlds)){
					unset($worlds[array_search($name, $worlds)]);
			        $this->getConfig()->set("worlds", $worlds);
			        $this->getConfig()->save();
				}
			}
		}
	}

	public function onSlapperHit(SlapperHitEvent $event){ // jojoe77777 didn't call SlapperDeletionEvent on EntityDamageEvent so i will do it instead
		$slapper = $this->getSlapper();
		$damager = $event->getDamager();
		if($damager instanceof Player){
			if(isset($slapper->hitSessions[$damager->getName()])){
				$slapperDelete = new SlapperDeletionEvent($event->getEntity());
				$slapperDelete->call();
			}
		}
	}

	// here no single world allowed, only combined ones

	public function combinedPlayerCounts(){
		$levels = $this->getServer()->getLevels();
		foreach ($levels as $level){
			foreach($level->getEntities() as $entity){
				$nbt = $entity->namedtag;
				if($nbt->hasTag("combinedPlayerCounts") && !$nbt->hasTag("playerCount")){
					$worldsNames = explode("&", $nbt->getString("combinedPlayerCounts"));
					foreach ($worldsNames as $name){
						if(!file_exists($this->getServer()->getDataPath()."/worlds/".$name)){
							unset($worldsNames[array_search($name, $worldsNames)]);
							$slapperDelete = new SlapperDeletionEvent($entity);
							$slapperDelete->call();
							$entity->close();
						}
					}
					// extra checks just in case
					if(count($worldsNames) > 1){
						$counts = 0;
						foreach ($worldsNames as $name){
							if($name === ""){
								continue;
							}
							if($this->getServer()->isLevelLoaded($name)){
								$worlds = $this->getConfig()->get("worlds");
								if(!in_array($name, $worlds)){
									$worlds[] = $name;
									$this->getConfig()->set("worlds", $worlds);
									$this->getConfig()->save();
								}
								$pmLevel = $this->getServer()->getLevelByName($name);
								$countOfLevel = count($pmLevel->getPlayers());
								$counts += $countOfLevel;
							} else {
								$worlds = $this->getConfig()->get("worlds");
								if(!in_array($name, $worlds)){
									$worlds[] = $name;
									$this->getConfig()->set("worlds", $worlds);
									$this->getConfig()->save();
								}
								$this->getServer()->loadLevel($name);
							}
						}
						$count = $this->getConfig()->get("count");
						$str = str_replace("{number}", $counts, $count);
						$allines = explode("\n", $entity->getNameTag());
						$entity->setNameTag($allines[0]."\n".$str);
					}
				}
			}
		}
	}

	public function playerCount(){
		$levels = $this->getServer()->getLevels();
		foreach ($levels as $level){
			$entities = $level->getEntities();
			foreach ($entities as $entity){
				$nbt = $entity->namedtag;
				if($nbt->hasTag("playerCount") && !$nbt->hasTag("combinedPlayerCounts")){
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

