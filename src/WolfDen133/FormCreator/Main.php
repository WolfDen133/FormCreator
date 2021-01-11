<?php

namespace WolfDen133\FormCreator;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use WolfDen133\FormCreator\Commands\NewCommand;

class Main extends PluginBase{

    public function onEnable()
    {
        $config = $this->getConfig();
        $this->saveDefaultConfig();

        $commandMap = $this->getServer()->getCommandMap();

        $internalConfig = $config->getAll();

        foreach ($internalConfig as $value){
            if (isset($value["Command"])){
                $commandMap->register(strtolower((string)$value["Command"]), new NewCommand($this, strtolower((string)$value["Command"]), $value["CommandDescription"], $value["CommandPermission"], $value));
                $this->getLogger()->info("Registed " . $value["Command"]);
            } else {
                $this->getLogger()->notice("No commands found in config.yml, disabled");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }
        }
    }
}
