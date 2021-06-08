<?php


namespace WolfDen133\FormCreator;


use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class OpenFormCommand extends Command implements PluginIdentifiableCommand
{
    /** @var FormCreator */
    private $plugin;

    /** @var string */
    private $form;

    /**
     * OpenFormCommand constructor.
     * @param FormCreator $main
     * @param string $form
     * @param string $name
     * @param string $permission
     * @param string $description
     */
    public function __construct(FormCreator $main, string $form, string $name, string $permission = "", string $description = "")
    {
        parent::__construct($name, $description, "Usage: /$name");

        $this->setPermission($permission);
        $this->setPermissionMessage(TextFormat::RED . "Unknown command. Try /help for a list of commands");

        $this->plugin = $main;
        $this->form = $form;
    }


    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player){
            $this->getPlugin()->logMessage(isset(($this->getPlugin()->getMessages())["errors"]["invalid-sender"]) ? TextFormat::colorize(($this->getPlugin()->getMessages())["errors"]["invalid-sender"]) : ($this->getPlugin()->default_messages)["errors"]["invalid-sender"], 1);
            return;
        }

        if ($this->getPermission() === "") {
            $this->openForm($sender);
        }

        if (!$sender->hasPermission($this->getPermission())) {

            $sender->sendMessage($this->getPermissionMessage());
            return;
        }

        $this->openForm($sender);

    }

    /**
     * Calls the open function in the main class.
     *
     * @param Player $player
     * @return void
     */
    private function openForm(Player $player) : void
    {
        $this->plugin->openForm($player, $this->form);
    }

    /**
     * @return FormCreator
     */
    public function getPlugin(): Plugin
    {
        return $this->plugin;
    }
}