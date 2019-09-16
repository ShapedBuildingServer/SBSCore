<?php

namespace xenialdan\MagicWE2;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\item\ItemIds;
use pocketmine\level\Position;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class EventListener implements Listener{
	public $owner;

	public function __construct(Plugin $plugin){
		$this->owner = $plugin;
	}

	public function onLogin(PlayerLoginEvent $event){
		if ($event->getPlayer()->hasPermission("we.session")){
			if (is_null(($session = API::getSession($event->getPlayer())))){
				$session = API::addSession(new Session($event->getPlayer()));
				Loader::getInstance()->getLogger()->debug("Created new session with UUID {" . $session->getUUID() . "} for player {" . $session->getPlayer()->getName() . "}");
			} else{
				$session->setPlayer($event->getPlayer());
				Loader::getInstance()->getLogger()->debug("Restored session with UUID {" . $session->getUUID() . "} for player {" . $session->getPlayer()->getName() . "}");
			}
		}
	}

	public function onBlockBreak(BlockBreakEvent $event){
		if (!is_null($event->getItem()->getNamedTagEntry("MagicWE"))){
			$event->setCancelled();

			if (!($event->getPlayer()->hasPermission("worlds." . strtolower($event->getPlayer()->getLevel()->getFolderName()) . ".build")
				|| $event->getPlayer()->hasPermission("worlds.admin.build"))) {
				$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "You do not have permission to use WorldEdit here!");
				return;
			}

			if (!$event->getPlayer()->hasPermission("we.myplot-allow-outside") && !API::isPointInPlayerPlot($event->getBlock()->asVector3(), $event->getPlayer())) {
				$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "You cannot use WorldEdit outside of your plot!");
				return;
			}

			/** @var Session $session */
			$session = API::getSession($event->getPlayer());
			if (is_null($session)){
				throw new \Exception("No session was created - probably no permission to use " . $this->owner->getName());
			}
			switch ($event->getItem()->getId()){
				case ItemIds::WOODEN_AXE: {
					/** @var Session $session */
					if (!$session->isWandEnabled()){
						$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "The wand tool is disabled. Use //togglewand to re-enable it");//TODO #translation
						break;
					}
					$selection = $session->getLatestSelection() ?? $session->addSelection(new Selection($event->getBlock()->getLevel())); // TODO check if the selection inside of the session updates
					if (is_null($selection)){
						throw new \Error("No selection created - Check the console for errors");
					}
					$event->getPlayer()->sendMessage($selection->setPos1(new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())));
					break;
				}
				case ItemIds::STICK: {
					/** @var Session $session */
					if (!$session->isDebugStickEnabled()){
						$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "The debug stick is disabled. Use //toggledebug to re-enable it");//TODO #translation
						break;
					}
					$event->getPlayer()->sendMessage($event->getBlock()->__toString() . ', variant: ' . $event->getBlock()->getVariant());
					break;
				}
				case ItemIds::BLAZE_ROD: {
					/** @var Session $session */
					// if (!$session->isReplSession()){
					// 	$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "The repl tool is disabled. Use //repl <ID> to re-enable it");
					// 	break;
					// }

					$session->addQuickReplace(new QuickReplace($event->getBlock()));
					$event->getPlayer()->sendMessage(Loader::$prefix . "QuickReplace Tool updated.");
					break;
				}
			}
		}
	}

	public function onRightClickBlock(PlayerInteractEvent $event){
		if ($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;

		if (!is_null($event->getItem()->getNamedTagEntry("MagicWE"))){
			$event->setCancelled();

			if (!($event->getPlayer()->hasPermission("worlds." . strtolower($event->getPlayer()->getLevel()->getFolderName()) . ".build")
				|| $event->getPlayer()->hasPermission("worlds.admin.build"))) {
				$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "You do not have permission to use WorldEdit here!");
				return;
			}

			if (!$event->getPlayer()->hasPermission("we.myplot-allow-outside") && !API::isPointInPlayerPlot($event->getBlock()->asVector3(), $event->getPlayer())) {
				$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "You cannot use WorldEdit outside of your plot!");
				return;
			}

			/** @var Session $session */
			$session = API::getSession($event->getPlayer());
			if (is_null($session)){
				throw new \Exception("No session was created - probably no permission to use " . $this->owner->getName());
			}
			switch ($event->getItem()->getId()){
				/*case ItemIds::WOODEN_SHOVEL: { //TODO Open issue on pmmp, RIGHT_CLICK_BLOCK + RIGHT_CLICK_AIR are BOTH called when right clicking a block - Turns out to be a client bug
					$target = $event->getBlock();
					if (!is_null($target)){// && has perms
						API::createBrush($target, $event->getItem()->getNamedTagEntry("MagicWE"));
					}
					break;
				}*/
				case ItemIds::WOODEN_AXE: {
					/** @var Session $session */
					if (!$session->isWandEnabled()){
						$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "The wand tool is disabled. Use //togglewand to re-enable it");//TODO #translation
						break;
					}
					$selection = $session->getLatestSelection() ?? $session->addSelection(new Selection($event->getBlock()->getLevel())); // TODO check if the selection inside of the session updates
					if (is_null($selection)){
						throw new \Error("No selection created - Check the console for errors");
					}
					$event->getPlayer()->sendMessage($selection->setPos2(new Position($event->getBlock()->x, $event->getBlock()->y, $event->getBlock()->z, $event->getBlock()->getLevel())));
					break;
				}
				case ItemIds::STICK: {
					/** @var Session $session */
					if (!$session->isDebugStickEnabled()){
						$event->getPlayer()->sendMessage(Loader::$prefix . TextFormat::RED . "The debug stick is disabled. Use //toggledebug to re-enable it");//TODO #translation
						break;
					}
					$event->getPlayer()->sendMessage($event->getBlock()->__toString() . ', variant: ' . $event->getBlock()->getVariant());
					break;
				}
				case ItemIds::BLAZE_ROD: {
					$replace = $session->getLatestQuickReplace();
					if (is_null($replace)){
						return;
					}
					API::setSingle($replace, $session, $event->getBlock());
					break;
				}
			}
		}
	}
}
