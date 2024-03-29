<?php


namespace WolfDen133\FormCreator;


use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use WolfDen133\BetterBedrock\Form\Form;
use WolfDen133\FormCreator\Bases\FormBase;

class OpenFormCommand extends Command implements PluginOwned
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
            $this->getOwningPlugin()->logMessage(isset(($this->getOwningPlugin()->getMessages())["errors"]["invalid-sender"]) ? TextFormat::colorize(($this->getOwningPlugin()->getMessages())["errors"]["invalid-sender"]) : ($this->getOwningPlugin()->default_messages)["errors"]["invalid-sender"], 1);
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
        switch ($this->plugin->getForm($this->form)->getType()){
            case FormBase::SIMPLE:
                $this->plugin->openSimpleForm($player, $this->form);
                break;
            case FormBase::MODAL:
                $this->plugin->openModalForm($player, $this->form);
                break;
        }
    }

    /**
     * @return FormCreator
     */
    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }
}
