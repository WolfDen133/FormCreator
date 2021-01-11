# FormCreator
A pocket mine plugin to simply and easily create simple forms.

You can create as many forms as you want
(more info in config)

## Form example
```
Example:                                            # Internal unique identifier (can be anything)
  Command: testform                                 # The command that opens the form (can be anything)
  CommandDescription: Test form command description # The command description (can be anything)
  CommandPermission: form.perm                      # The command permission (can be anything)
  FormTitle: Form Title                             # The form title (can be anything)
  FormDescription: test description                 # The form description (can be anything)
  Buttons:                                          # The buttons category for defining buttons
    Button1:                                        # The buttons names (can be anything)
      Name: "&l&aTestButton&b1"                     # The button display names (Must be in 'quote marks')
      ImageType: 0                                  # The button image type (0 for internal texture/1 for url)
      ImagePath: textures/items/stone_sword         # The texture path / url
      CommandSender: Player                         # The command sender for the command (Player or Console)
      Command: say test 1                           # The command executed by the sender (you can use {player} for the player and "&" for color
    Button2:
      Name: "&l&aTestButton&b2"
      ImageType: 0
      ImagePath: textures/items/iron_sword
      CommandSender: Console
      Command: say This is also a test```
