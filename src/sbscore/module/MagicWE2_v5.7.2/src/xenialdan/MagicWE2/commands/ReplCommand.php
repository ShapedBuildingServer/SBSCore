<?php

declare(strict_types=1);

namespace xenialdan\MagicWE2\commands;

use pocketmine\command\CommandSender;
use pocketmine\lang\TranslationContainer;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use xenialdan\MagicWE2\Loader;
use xenialdan\MagicWE2\QuickReplace;
use xenialdan\MagicWE2\API;

class ReplCommand extends WECommand{
	public function __construct(Plugin $plugin){
		parent::__construct("/repl", $plugin);
		$this->setPermission("we.command.repl");
		$this->setDescription("Easily replace blocks.");
		$this->setUsage("//repl <block ID>");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/** @var Player $sender */
		$return = $sender->hasPermission($this->getPermission());
		if (!$return){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
			return true;
		}
		$lang = Loader::getInstance()->getLanguage();
		try{
			$item = ItemFactory::get(ItemIds::BLAZE_ROD);
			$item->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PROTECTION)));
			$item->setCustomName(Loader::$prefix . TextFormat::BOLD . TextFormat::DARK_PURPLE . 'Quick Replace Tool');
			$item->setLore([
				'Left click a block to pick it for replace',
				'Right click a block to replace it',
				'Use //repl to toggle its functionality'
			]);
			$item->setNamedTagEntry(new CompoundTag("MagicWE", []));
			$sender->getInventory()->addItem($item);

			$messages = [];
			$error = false;
			if ($args[0] == "hand") {
				$blocks = API::blockParser((string)$sender->getInventory()->getItemInHand()->getId(), $messages, $error);
			} else {
				$blocks = API::blockParser(array_shift($args), $messages, $error);
			}
			foreach ($messages as $message){
				$sender->sendMessage($message);
			}
			$return = !$error;
			if ($return){
				$session = API::getSession($sender);
				if (is_null($session)){
					throw new \Exception("No session was created - probably no permission to use " . $this->getPlugin()->getName());
				}

				$session->addQuickReplace(new QuickReplace($blocks[0]));
				$sender->sendMessage(Loader::$prefix . "QuickReplace Tool set.");
			} else{
				throw new \InvalidArgumentException("Could not replace with the selected blocks");
			}
		} catch (\Exception $error){
			$sender->sendMessage(Loader::$prefix . TextFormat::RED . "Looks like you are missing an argument or used the command wrong!");
			$sender->sendMessage(Loader::$prefix . TextFormat::RED . $error->getMessage());
			$return = false;
		} catch (\ArgumentCountError $error){
			$sender->sendMessage(Loader::$prefix . TextFormat::RED . "Looks like you are missing an argument or used the command wrong!");
			$sender->sendMessage(Loader::$prefix . TextFormat::RED . $error->getMessage());
			$return = false;
		} catch (\Error $error){
			$this->getPlugin()->getLogger()->error($error->getMessage());
			$sender->sendMessage(Loader::$prefix . TextFormat::RED . $error->getMessage());
			$return = false;
		} finally{
			return $return;
		}
	}
}
