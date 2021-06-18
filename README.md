[![](https://poggit.pmmp.io/shield.state/FormCreator)](https://poggit.pmmp.io/p/FormCreator)
# FormCreator (NEW)
A simple plugin for creating simple forms through config

## Info

This is a simple plugin to create simple forms using config, if you want to create forms easly then this is the plugin for you.

The way you use it is, you open config (You can also edit the system messages in there) and under `forms:` you can create your forms, there is already an example form in there.

If you find any bugs report them [here](https://github.com/WolfDen133/WFT/issues).

## Example
### Simple form 
```yaml 
  simple-example:

    type: "simple"
    
    command:
      label: "opensimpleform"
      description: "Opens the example ui"
      permission: "formcreator.simpleeg.use"
      
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

### Modal
```yaml
  modal-example:

    type: "modal"

    title: "&aModal Form Example"
    content: "&aHello {PLAYER}! :) {LINE} &bOnline players&7: &l&3{ONLINE_PLAYERS}&8/&3{MAX_PLAYERS} {LINE}{LINE} This plugin is cool :)"

    button1:
      label: "&aYes"
      commands :
        - label: "title {PLAYER} title Welcome!"
          sender: "console"
        - label: "say &bHello {PLAYER}!"
          sender: "player"

    button2:
      label: "&cNo"
      commands:
        - label: "title {PLAYER} title Awesome!"
          sender: "console"

        - label: "say &bThis plugin is cool!"
          sender: "player"


    command:
      label: "openmodalform"
      description: "Opens the example modal form"
      permission: "formcreator.modaleg.use"
```
 
## Wild-Cards

| WildCard | Description |
| --- | --- |
| `{LINE}` | Line break |
| `{NAME}` | The Players name |
| `{REAL_NAME}` | The Players name |
| `{DISPLAY_NAME}` | The Players display name |
| `{PING}` | Players ping |
| `{ONLINE_PLAYERS}` | Online player count |
| `{MAX_PLAYERS}` | Max players |
| `{X}` | Player x pos |
| `{Y}` | Player y pos |
| `{Z}` | Player \ pos |
| `{REAL_TPS}` | Servers current tps |
| `{TPS}` | Servers average tps |
| `{REAL_LOAD}` | Servers current load |
| `{LOAD}` | Servers average load |
| `{LEVEL_NAME}` | Players level name |
| `{LEVEL_FOLDER_NAME}` | Players level folder name |
| `{LEVEL_PLAYERS}` | Players level player count |
| `{CONNECTION_IP}` | Players connection ip |
| `{SERVER_IP}` | Servers ip |
| `{TIME}` | Time |
| `{DATE}` | Date |

All of these values can be changed in the FormCreator class

## TODO 

- Custom forms
- Advanced output cases
- Code cleanup

## Thanks

This plugin was fully hand coded by WolfDen133, so come to me for help, my discord is [here](https://dsc.bio/WolfDen133).

All of the code is commented so feel free to look at the code.
