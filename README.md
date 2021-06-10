[![](https://poggit.pmmp.io/shield.state/FormCreator)](https://poggit.pmmp.io/p/FormCreator)
# FormCreator (NEW)
A simple plugin for creating simple forms through config

## Info

This is a simple plugin to create simple forms using config, if you want to create forms easly then this is the plugin for you.

The way you use it is, you open config (You can also edit the system messages in there) and under `forms:` you can create your forms, there is already an example form in there.

If you find any bugs report them [here](https://github.com/WolfDen133/WFT/issues).

## Example

```yaml 
  example:
  
    command:
      label: "openexample"
      description: "Opens the example ui"
      permission: "example.command.use"

    title: "&eHello world"
    content: "&aHello {PLAYER}! :) {LINE} &bOnline players&7: &l&3{ONLINE_PLAYERS}&8/&3{MAX_PLAYERS} {LINE}{LINE} This plugin is cool :)"

    buttons:

      "&eButton 1{LINE}&eClick me!!":

        image-type: 0
        image-path: "textures/ui/MashupIcon"

        commands:
          - label: "title {player} title Welcome!"
            sender: "console"
            
          - label: "say &bHello {player}!"
            sender: "player"


      "&cButton 2":
        commands:
          - label: "title {player} title Awesome!"
            sender: "console"

          - label: "say &bThis plugin is cool!"
            sender: "player" 
 ```
 
## Wild-Cards

| WildCard | Description |
| --- | --- |
| `{PLAYER}` | Opening player name |
| `{LINE}` | Line break |
| `{ONLINE_PLAYERS}` | Current online player count |
| `{MAX_PLAYERS}` | Maximum online player count |

## API 

> This will open the form with the name `$formname` to the player `$player`
 ```php
 $this->getServer()->getPluginManager("FormCreator")->openForm($player, $formname);
 ```
 
> If you wish to add any more wild cards (string replacements e.g. "{PLAYER}" will replace to the player name)
```php
$this->getServer()->getPluginManager("FormCreator")->addWildCard("{FIND}", "{REPLACE}");
```

## TODO 

- Custom forms
- Advanced output cases
- Code cleanup

## Thanks

This plugin was fully hand coded by WolfDen133, so come to me for help, my discord is [here](https://dsc.bio/WolfDen133).

All of the code is commented so feel free to look at the code.
