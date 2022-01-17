<?php

declare(strict_types=1);

namespace WolfDen133\FormCreator;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\player\PlayerChunkLoader;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\ModalForm;

use WolfDen133\FormCreator\Bases\FormBase;
use WolfDen133\FormCreator\Bases\ModalFormBase;
use WolfDen133\FormCreator\Bases\SimpleFormBase;

class FormCreator extends PluginBase
{

    /**
     * Message types
     */
    public const WARN = 0;
    public const ERROR = 1;

    /**
     * @name FormCreator
     * @author WolfDen133
     */

    /**
     * Form DataBase array
     *
     * @var SimpleFormBase[]|ModalFormBase[]
     */
    private array $forms;


    /**
     * Default messages to be loaded if not found
     *
     * @var array[]
     */
    public array $default_messages = array(
        "errors" => array(
            "invalid-sender" => 'Error: This command can only be executed by players',
            "no-forms" => 'No valid form data section was found, disabling plugin.',
            "no-messages" => 'No messages where, rewriting default messages.',
            "invalid_form" => 'No form data has showed up for the form \'{NAME}\'.'
        ),
        "warnings" => array(
            "form" => array(
                "title" => 'The form \'{NAME}\' has no valid title set.',
                "type" => 'The form \'{NAME}\' has no valid type set.',
                "command" => 'The form \'{NAME}\' has no valid command field set.',
                "command-label" => 'The form \'{NAME}\' has no valid command label set.'
            ),
            "commands" => array(
                "sender" => 'The command {KEY} in the form \'{NAME}\' has no valid sender set."',
                "label" => 'The command {KEY} in the form \'{NAME}\' has no valid label set.',
                "invalid-sender" => 'The command {KEY} in the form \'{NAME}\' has an invalid sender.'
            )
        )
    );

    /**
     * Message list
     *
     * @var array[]
     */
    private array $messages;


    public function onEnable() : void
    {
        $this->saveDefaultConfig();

        $this->loadMessages();
        $this->loadForms();
    }


    /**
     * Load the wild cards
     *
     * @param Player $player
     * @return array
     */
    private function getWildCards (Player $player) : array
    {
        return [
            "{LINE}" => "\n",
            "{NAME}" => $player->getName(),
            "{REAL_NAME}" => $player->getName(),
            "{DISPLAY_NAME}" => $player->getDisplayName(),
            "{PING}" => $player->getPing(),
            "{ONLINE_PLAYERS}" => count($this->getServer()->getOnlinePlayers()),
            "{MAX_PLAYERS}" => $this->getServer()->getMaxPlayers(),
            "{X}" => $this->currentLocation->getFloorX(),
            "{Y}" => round($player->getY()),
            "{Z}" => $this->currentLocation->getFloorZ(),
            "{REAL_TPS}" => $this->getServer()->getTicksPerSecond(),
            "{TPS}" => $this->getServer()->getTicksPerSecondAverage(),
            "{REAL_LOAD}" => $this->getServer()->getTickUsage(),
            "{LOAD}" => $this->getServer()->getTickUsageAverage(),
            "{LEVEL_NAME}" => $player->getLevel()->getName(),
            "{LEVEL_FOLDER_NAME}" => $player->getLevel()->getFolderName(),
            "{LEVEL_PLAYERS}" => count($player->getLevel()->getPlayers()),
            "{CONNECTION_IP}" => $player->getAddress(),
            "{SERVER_IP}" => $this->getServer()->getIP(),
            "{TIME}" => date("H:i:s"),
            "{DATE}" => date("d-m-Y")
        ];
    }



    /**
     * Returns the message array
     *
     * @return array[]
     */
    public function getMessages () : array
    {
        return $this->messages;
    }


    /**
     * Loads all the messages from config
     *
     * @return void
     */
    private function loadMessages () : void
    {
        while (true){

            $config = $this->getConfig()->getAll();

            /* Messages exists check */
            if (isset($config["messages"])) {
                $messages = $config["messages"];
                break;
            }


            /* Messages fix */
            $this->logMessage(($this->default_messages)["errors"]["no-messages"], self::ERROR);

            $this->getConfig()->set("messages", $this->default_messages);
            $this->getConfig()->save();
        }

        /* Messages registration */
        $this->messages = $messages;
    }


    /**
     * Loads all the forms into a simple array database
     *
     * @return void
     */
    private function loadForms () : void
    {
        $config = $this->getConfig()->getAll();

        if (isset($config["forms"])) $forms = $config["forms"];
        else {
            $this->logMessage(isset(($this->getMessages())["errors"]["no-forms"]) ? TextFormat::colorize(($this->getMessages())["errors"]["no-forms"]) : ($this->default_messages)["errors"]["no-forms"], self::ERROR);
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        foreach ($forms as $name=>$form) {

            $type = $form["type"] ?? "";

            switch (strtolower($type)) {

                case "simple":
                    $this->loadSimpleForm($name, $form);
                    break;
                case "modal":
                    $this->loadModalForm($name, $form);
                    break;
                //TODO Custom forms
                default:
                    $this->logMessage(isset(($this->getMessages())["warnings"]["forms"]["type"]) ? str_replace("{NAME}", (string)$name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["type"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["type"]));

            }
        }
    }

    /**
     * Loads a simple form to the database.
     *
     * @param string $name
     * @param array $form
     * @return void
     */
    private function loadSimpleForm (string $name, array $form) : void
    {
        /* Required data checks */

        if (!isset($form["title"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["title"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["title"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["title"]));
            return;
        }

        if (!isset($form["command"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["command"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["command"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["command"]));
            return;
        }

        $command = $form["command"];

        if (!isset($command["label"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["command-label"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["command-label"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["command-label"]));
            return;
        }


        /* Optional data checks*/

        if (isset($command["description"])) $description = $command["description"];
        else $description = "";

        if (isset($command["permission"])) $permission = $command["permission"];
        else $permission = "";

        if (isset($form["content"])) $content = $form["content"];
        else $content = "";

        if (isset($form["buttons"])) $elements = $form["buttons"];
        else $elements = [];


        /* Data register */

        $title = $form["title"];

        $label = $command["label"];


        /* Form/command register */

        $this->forms[$name] = new SimpleFormBase($name, $title, $content, $elements);
        $this->getServer()->getCommandMap()->register("FormCreator", new OpenFormCommand($this, $name, $label, $permission, $description));
    }

    private function loadModalForm (string $name, array $form) : void
    {
        /* Required data checks */

        if (!isset($form["title"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["title"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["title"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["title"]));
            return;
        }

        if (!isset($form["command"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["command"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["command"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["command"]));
            return;
        }

        $command = $form["command"];

        if (!isset($command["label"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["command-label"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["command-label"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["command-label"]));
            return;
        }


        if (!isset($form["button1"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["button1"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["button1"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["button1"]));
            return;
        }

        if (!isset($form["button2"])) {
            $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["button2"]) ? str_replace("{NAME}", $name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["button2"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["button2"]));
            return;
        }

        /* Optional data checks*/

        if (isset($command["description"])) $description = $command["description"];
        else $description = "";

        if (isset($command["permission"])) $permission = $command["permission"];
        else $permission = "";

        if (isset($form["content"])) $content = $form["content"];
        else $content = "";


        /* Data register */

        $title = $form["title"];

        $label = $command["label"];

        $button1 = $form["button1"];
        $button2 = $form["button2"];


        /* Form/command register */

        $this->forms[$name] = new ModalFormBase($name, $title, $content, $button1, $button2);
        $this->getServer()->getCommandMap()->register("FormCreator", new OpenFormCommand($this, $name, $label, $permission, $description));
    }

    /**
     * Opens a form to a player, called in the command class
     *
     * @param Player $player
     * @param string $name
     * @return void
     */
    public function openSimpleForm (Player $player, string $name) : void
    {
        $form = new SimpleForm(function (Player $player, string $data = null) use ($name){

            /* Data check, required or will result in an internal server error if no data is selected */
            if ($data === null) return;


            /* Button check */

            if (is_null((($this->forms[$name])->getElements())[$data]["commands"])) {
                return;
            }

            $button = (($this->forms[$name])->getElements())[$data]["commands"];

            foreach ($button as $key=>$command) {


                /* Required data checks*/

                if (!isset($command["sender"])) {
                    $this->logMessage(isset(($this->getMessages())["warnings"]["commands"]["sender"]) ? str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], TextFormat::colorize(($this->getMessages())["warnings"]["commands"]["sender"])) : str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], ($this->default_messages)["warnings"]["commands"]["sender"]));
                    continue;
                }

                if (!isset($command["label"])) {
                    $this->logMessage(isset(($this->getMessages())["warnings"]["commands"]["label"]) ? str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], TextFormat::colorize(($this->getMessages())["warnings"]["commands"]["label"])) : str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], ($this->default_messages)["warnings"]["commands"]["label"]));
                    continue;
                }


                /* Data registration */

                $sender = $command["sender"];
                $label = $command["label"];


                /* Sender selector */

                switch (strtolower($sender)) {
                    case "console":

                        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $label)));
                        continue 2;

                    case "player":

                        $this->getServer()->dispatchCommand($player, TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $label)));
                        continue 2;

                    default:

                        $this->logMessage(isset(($this->getMessages())["warnings"]["commands"]["invalid-sender"]) ? str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], TextFormat::colorize(($this->getMessages())["warnings"]["commands"]["invalid-sender"])) : str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], ($this->default_messages)["warnings"]["commands"]["invalid-sender"]));
                        continue 2;
                }
            }

        });

        /* Call the wildcards for efficency (Thanks Andreas) */
        $wildcards = $this->getWildCards($player);


        /* Form data inputs */
        $title = ($this->forms[$name])->getTitle();
        foreach ($wildcards as $wildcard=>$value) {
            $title = str_replace($wildcard, (string) $value, $title);
        }
        $form->setTitle(TextFormat::colorize(($this->forms[$name])->getTitle()));

        $content = ($this->forms[$name])->getContent();
        foreach ($wildcards as $wildcard=>$value) {
            $content = str_replace($wildcard, (string) $value, $content);
        }
        $form->setContent(TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $content)));


        /* Button registration */

        $buttons = ($this->forms[$name])->getElements();

        foreach ($buttons as $name=>$data) {
            $b_name = $name;

            foreach ($wildcards as $wildcard=>$value) {
                $name = str_replace($wildcard, (string) $value, $name);
            }

            $form->addButton(TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $name)), $data["image-type"] ?? -1, $data["image-path"] ?? "", $b_name);
        }


        /* Send the form */

        $player->sendForm($form);
    }

    public function openModalForm (Player $player, string $name) : void
    {
        $form = new ModalForm(function (Player $player, bool $data = null) use ($name){

            /* Data check, required or will result in an internal server error if no data is selected */
            if ($data === null) return;


            /* Button check */

            if ($data) $button = $this->forms[$name]->getButton1();
            if (!$data) $button = $this->forms[$name]->getButton2();

            foreach ($button["commands"] as $key=>$command) {


                /* Required data checks*/

                if (!isset($command["sender"])) {
                    $this->logMessage(isset(($this->getMessages())["warnings"]["commands"]["sender"]) ? str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], TextFormat::colorize(($this->getMessages())["warnings"]["commands"]["sender"])) : str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], ($this->default_messages)["warnings"]["commands"]["sender"]));
                    continue;
                }

                if (!isset($command["label"])) {
                    $this->logMessage(isset(($this->getMessages())["warnings"]["commands"]["label"]) ? str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], TextFormat::colorize(($this->getMessages())["warnings"]["commands"]["label"])) : str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], ($this->default_messages)["warnings"]["commands"]["label"]));
                    continue;
                }


                /* Data registration */

                $sender = $command["sender"];
                $label = $command["label"];


                /* Sender selector */

                switch (strtolower($sender)) {
                    case "console":

                        $this->getServer()->dispatchCommand(new ConsoleCommandSender(), TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $label)));
                        continue 2;

                    case "player":

                        $this->getServer()->dispatchCommand($player, TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $label)));
                        continue 2;

                    default:

                        $this->logMessage(isset(($this->getMessages())["warnings"]["commands"]["invalid-sender"]) ? str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], TextFormat::colorize(($this->getMessages())["warnings"]["commands"]["invalid-sender"])) : str_replace(["{KEY}", "{NAME}"], [$key + 1, $name], ($this->default_messages)["warnings"]["commands"]["invalid-sender"]));
                        continue 2;
                }
            }

        });

        /* Call the wildcards for efficency (Thanks Andreas) */
        $wildcards = $this->getWildCards($player);


        /* Form data inputs */
        $title = ($this->forms[$name])->getTitle();
        foreach ($wildcards as $wildcard=>$value) {
            $title = str_replace($wildcard, (string) $value, $title);
        }
        $form->setTitle(TextFormat::colorize(($this->forms[$name])->getTitle()));


        $content = ($this->forms[$name])->getContent();
        foreach ($wildcards as $wildcard=>$value) {
            $content = str_replace($wildcard, (string) $value, $content);
        }
        $form->setContent(TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $content)));


        /* Button registration */

        $b1 = $this->forms[$name]->getButton1()["label"] ?? "";
        foreach ($wildcards as $wildcard=>$value) {
            $b1 = str_replace($wildcard, (string) $value, $b1);
        }
        $form->setButton1(TextFormat::colorize($b1));

        $b2 = $this->forms[$name]->getButton2()["label"] ?? "";
        foreach ($wildcards as $wildcard=>$value) {
            $b2 = str_replace($wildcard, (string) $value, $b2);
        }
        $form->setButton2(TextFormat::colorize($b2));


        /* Send the form */

        $player->sendForm($form);
    }



    /**
     * @param string $name
     * @return FormBase|null
     */
    public function getForm(string $name): ?FormBase
    {
        if (!isset($this->forms[$name])){
            $this->logMessage(isset(($this->getMessages())["errors"]["invalid-form"]) ? TextFormat::colorize(($this->getMessages())["errors"]["invalid-form"]) : ($this->default_messages)["errors"]["invalid-form"], self::ERROR);
            return null;
        }
        return $this->forms[$name];
    }


    /**
     * @param string $message
     * @param int $type
     */
    public function logMessage (string $message, int $type = self::WARN) : void
    {
        switch ($type) {
            case self::WARN:
                $this->getLogger->info("\x1b[38;5;227m[" . date("H:i:s") . "] [FormCreator/WARNING]: " . $message . "\x1b[m\n");
                break;
            case self::ERROR:
                $this->getLogger->info("\x1b[38;5;203m[" . date("H:i:s") . "] [FormCreator/ERROR]: " . $message . "\x1b[m\n");
                break;

        }
    }


    /* System Command */

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (strtolower($command->getName()) !== "formcreator"){
            return false;
        }

        if (count($args) !== 1){
            return false;
        }

        switch (strtolower($args[0])) {
            case "help":
            case "?":
            case "stuck":
                $sender->sendMessage('
Usage: /formcreator < help | info | list>
  - help: shows help about this command,
  - info: shows information about this plugin,
  - list: shows all the current forms loaded
                ');
                break;

            case "info":
            case "information":
                $sender->sendMessage('
<====== FormCreator ======>
 Version: 1.2.0
 Forms: ' . count($this->forms) . '
 Author: WolfDen133
 API Version: 3.21.0+
<=======================>
                ');
                break;

            case "list":
            case "forms":

                $sender->sendMessage("Forms:\n");
                $sender->sendMessage("<=======================>");

                foreach ($this->forms as $name=>$data) {

                    if ($data->getType() === FormBase::SIMPLE) $type = "Simple";
                    elseif ($data->getType() === FormBase::MODAL) $type = "Modal";
                    else $type = "Unknown";

                    $sender->sendMessage(" - $name: $type");

                }

                $sender->sendMessage("<=======================>");


                break;

            default:
                $sender->sendMessage("Execute: /fc help");

            }
        return true;
    }

}
