<?php

declare(strict_types=1);

namespace WolfDen133\FormCreator;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\utils\TextFormat;

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
     * @var FormBase[]
     */
    private $forms;


    /**
     * Default messages to be loaded if not found
     *
     * @var array[]
     */
    public $default_messages = array(
        "errors" => array(
            "invalid-sender" => 'Error: This command can only be executed by players',
            "no-forms" => 'No valid form data section was found, disabling plugin.',
            "no-messages" => 'No messages where, rewriting default messages.'
        ),
        "warnings" => array(
            "form" => array(
                "title" => 'The form \'{NAME}\' has no valid title set.',
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
     * @var array
     */
    private $wildcards = [];

    /**
     * Message list
     *
     * @var array[]
     */
    private $messages;


    public function onEnable()
    {
        $this->saveDefaultConfig();

        $this->loadMessages();
        $this->loadForms();
        $this->loadWildCards();
    }


    /**
     * Load the wild cards
     *
     * @return void
     */
    private function loadWildCards () : void
    {
        $this->wildcards["{LINE}"] = "\n";
        $this->wildcards["{MAX_PLAYERS}"] = (string) $this->getServer()->getMaxPlayers();
        $this->wildcards["{ONLINE_PLAYERS}"] = (string) count($this->getServer()->getOnlinePlayers());
    }


    /**
     * Add a new wild card to the array
     *
     * @param string $wildcard
     * @param string $value
     * @return void
     */
    public function addWildCard (string $wildcard, string $value) : void
    {
        $this->wildcards["$wildcard"] = $value;
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


            /* Required data checks */

            if (!isset($form["title"])) {
                $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["title"]) ? str_replace("{NAME}", (string)$name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["title"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["title"]));
                continue;
            }

            if (!isset($form["command"])) {
                $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["command"]) ? str_replace("{NAME}", (string)$name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["command"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["command"]));
                continue;
            }

            $command = $form["command"];

            if (!isset($command["label"])) {
                $this->logMessage(isset(($this->getMessages())["warnings"]["form"]["command-label"]) ? str_replace("{NAME}", (string)$name, TextFormat::colorize(($this->getMessages())["warnings"]["form"]["command-label"])) : str_replace("{NAME}", $name, ($this->default_messages)["warnings"]["form"]["command-label"]));
                continue;
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

            $this->forms[$name] = new FormBase($name, $title, $content, $elements);
            $this->getServer()->getCommandMap()->register("FormCreator", new OpenFormCommand($this, $name, $label, $permission, $description));

        }
    }

    /**
     * Opens a form to a player, called in the command class
     *
     * @param Player $player
     * @param string $name
     * @return void
     */
    public function openForm (Player $player, string $name) : void
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


        /* Form data inputs */
        $title = ($this->forms[$name])->getTitle();
        foreach ($this->wildcards as $wildcard=>$value) {
            $title = str_replace($wildcard, $value, $title);
        }
        $form->setTitle(TextFormat::colorize(($this->forms[$name])->getTitle()));

        $content = ($this->forms[$name])->getContent();
        foreach ($this->wildcards as $wildcard=>$value) {
            $content = str_replace($wildcard, $value, $content);
        }
        $form->setContent(TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $content)));


        /* Button registration */

        $buttons = ($this->forms[$name])->getElements();

        foreach ($buttons as $name=>$data) {

            foreach ($this->wildcards as $wildcard=>$value) {
                $name = str_replace($wildcard, $value, $name);
            }

            $form->addButton(TextFormat::colorize(str_replace("{PLAYER}", $player->getName(), $name)), $data["image-type"] ?? -1, $data["image-path"] ?? "", $name);
        }


        /* Send the form */

        $player->sendForm($form);
    }

    public function logMessage (string $message, int $type = self::WARN) : void
    {
        switch ($type) {
            case self::WARN:
                echo "\x1b[38;5;227m[" . date("H:i:s") . "] [FormCreator/WARNING]: " . $message . "\x1b[m\n";
                break;
            case self::ERROR:
                echo "\x1b[38;5;203m[" . date("H:i:s") . "] [FormCreator/ERROR]: " . $message . "\x1b[m\n";
                break;

        }
    }

}
