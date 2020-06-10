# WorldPlayerCount [![Poggit-CI](https://poggit.pmmp.io/ci.badge/xXKHaLeD098Xx/WorldPlayerCount/WorldPlayerCount)](https://poggit.pmmp.io/ci/xXKHaLeD098Xx/WorldPlayerCount/WorldPlayerCount)

[![](https://poggit.pmmp.io/shield.dl.total/WorldPlayerCount)](https://poggit.pmmp.io/p/WorldPlayerCount)
[![](https://poggit.pmmp.io/shield.state/WorldPlayerCount)](https://poggit.pmmp.io/p/WorldPlayerCount)
[![Poggit](https://poggit.pmmp.io/ci.shield/xXKHaLeD098Xx/WorldPlayerCount/WorldPlayerCount?style=flat-square)](https://poggit.pmmp.io/ci/xXKHaLeD098Xx/WorldPlayerCount/WorldPlayerCount)
<br>
A simple PocketMine addon plugin for Slapper which allows you to create a slapper counting the players of a world(s) on its name tag.<br>
## Versions
- v1.0-beta
  - First version

- v2.0-beta
  - Added combined world player count support!
  - Added customizable count-check interval, see the config
## Usage for single world
__Note: This is used when creating a slapper counting the players of 1 world only__
- First thing we add after the entity type (human) is the nametag we want, followed by a {line} tag then adding "count WORLDNAME"<br>
- __Example : /slapper spawn human BedWars{line}count Hub<br>__
- Congrats!, you spawned an entity counting the players of the world "Hub"!
## Usage for combined worlds
__Note: This is used when creating a slapper counting the players of more than 1 world__
- First thing we add after the entity type (human) is the nametag we want, followed by a {line} tag then adding "combinedcounts World1&World2" and so on with the "&" symbol<br>
- __Example : /slapper spawn human SkyWars{line}combinedcounts SK-1&SK-2&SK-3__
- Congrats!, you spawned an entity counting the players of the worlds "SK-1", "SK-2" and "SK-3" at the same time
## Contacts
In case you are confused about the usage or found a bug please contact me on my discord __@кнαℓє∂#7787__
