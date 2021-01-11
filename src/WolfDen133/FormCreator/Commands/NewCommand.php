<?php

namespace WolfDen133\FormCreator\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use WolfDen133\FormCreator\Main;
use jojoe77777\FormAPI\SimpleForm;

class NewCommand extends Command {

    private $plugin;
    private $form;

    public function __construct(Main $plugin, String $name, string $description, string $permission, array $form)
    {
        $this->plugin = $plugin;
        $this->form = $form;

        parent::__construct($name, $description);

        $this->setPermission($permission);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if ($sender instanceof Player){


            $thisform = $this->form;

            $form = new SimpleForm(function (Player $sender, $data = null){
                if ($data === null){
                    return;
                }
                $thisForm = $this->form;
                $buttons = $thisForm["Buttons"];
                foreach ($buttons as $button) {
                    if ($button["Name"] === $data){
                        if ($button["Command"] !== null || $button["CommandSender"] !== null) {

                            $cmd = str_replace(["&", "{player}"], ["§", $sender], $button["Command"]);

                            if (strtolower($button["CommandSender"]) === "player") {
                                $this->plugin->getServer()->dispatchCommand($sender, $cmd);
                            } elseif (strtolower($button["CommandSender"]) === "console") {
                                $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
                            }
                        }
                    }
                }

            });

            $form->setTitle((string)$this->form["FormTitle"]);
            $form->setContent((string)$this->form["FormDescription"]);

            foreach ($thisform["Buttons"] as $button){

                if ($button["ImagePath"] !== null || $button["ImageType"] !== null){

                    $buttonname = str_replace("&", "§", $button["Name"]);

                    if ($button["ImageType"] === 0){
                        $form->addButton($buttonname, 0, $button["ImagePath"], $button["Name"]);
                    } elseif ($button["ImageType"] === 1) {
                        $form->addButton($buttonname, 1, $button["ImagePath"], $button["Name"]);
                    }
                } else {
                    $form->addButton((string)$button, -1, "", $button);
                }

            }

            $form->sendToPlayer($sender);
            return $form;
        } else {
            $sender->sendMessage("This command is for players only");
        }
    }
}
