AdvancedKits
============

PocketMine-MP plugin that adds kits to your PocketMine server. Report bugs and errors to https://github.com/luca28pet/AdvancedKits/issues

This is a simple yet useful PocketMine-MP kit plugin. For who doesn't know what kits are, they are groups of items that you can get simply by typing a command or touching a sign.

**Features:**

- Highly configurable
- Custom permission support: give a player permission advancedkits.kitname to let him use the kit named "kitname"
- Built in perms system for non-PurePerms users (read the documentation)
- Economy support: pay to get a kit (to set up read the documentation tab). Support for EconomyS, PocketMoney and MassiveEconomy
- Sign support: write a sign to let users get a kit
- Unlimited kits with unlimited items, and armor support
- Time limit (cooldown) for kits
- Option for one kit per life (see config.yml)
- Execute commands with kits
- Easy translation system

**Commands:**
The main command: /kit
Alias for /kit: /ak, /advancedkits .

- /kit
- /akreload - reload kits.yml (when edited while the server is running)

 

**Signs:**
To let users get a kit through a sign, you can create one like this: (capitals don't matter)

Line 1: [AdvancedKits]

Line 2: kitname

Line 3 & 4: Whatever you like


The default kit is: testkit.
You can add kits editing kits.yml (read carefully the documentation).
If plugin has bugs, please report them to the GitHub issue tracker.
You can find development versions in the GitHub repository
Documentation

**Kit Settings:**

In order to add kit you will need to edit the config kits.yml .
If you open that file with bloc notes, you will be not able to edit because it will be all in one line, so open it with WordPad, Notepad ++, ...
You can add lots of kits, but remember to keep this file format:

```
---
testkit:
  #items. name and enchantments are optional.
  #if damage is not specified, 0 will be used
  #if count is not specified, 1 will be used
  items:
  - id: 272
    damage: 0
    count: 1
    name: "SwordName"
    enchantment:
      #name: level
      weapon_sharpness: 5
      weapon_knockback: 2
  - id: 160
    damage: 0
    count: 5
    name: "AppleName"
  #helmet
  #optional. you can remove this if you don't want a helmet
  helmet:
    id: 298
    name: "HelmetName"
    enchantment:
      armor_protection: 1
  #chestplate
  #optional. you can remove this if you don't want a chestplate
  chestplate:
    id: 299
  #leggings
  #optional. you can remove this if you don't want leggings
  leggings:
    id: 300
  #boots
  #optional. you can remove this if you don't want boots
  boots:
    id: 301
  #cool down time.
  #when a player gets this kit, he will not be able to get this again until the cool down ends
  #this is optional. you can remove this if you don't want a cooldown
  cooldown:
    hours: 24
    minutes: 30
  #commands to execute when a player gets a kit
  #use {player} to specify the player name
  #optional. you can remove this if you don't want any command to be executed
  commands:
  - "tell {player} you got an awesome kit thanks to AdvancedKits plugin!"
  #effects to give to the player
  #optional. you can remove this if you don't want effects to come with this kit
  effects:
  - name: "speed"
    seconds: 120
    amplifier: 2
  #if you do not use pureperms, use 'worlds' to specify in which worlds you want this kit to be used
  worlds:
  - "kitpvp"
  #if you do not use pureperms, use 'users' to specify which players will be able to get this kit
  users:
  - "luca28pet"
  - "dioconsole"
...
```

**Permissions:**

With PurePerms or a permissions manager: you must give players the permission to get a kit: advancedkits.kitname

Without PurePerms or a permissions manager: this plugin has a permission system that lets the server owner choose what players in what worlds can get a certain kit.
To use it, add the parameters "users" and "worlds" like this in the kits.yml file:

```
---
testkit:
  #stuff...
  worlds:
  - "kitpvp"
  users:
  - "luca28pet"
  - "dioconsole"
...
```


If you don't specify users or world, then the kit will be available to all users or in all worlds.
If you have PurePerms, these parameters will be ignored unless you edit the config.yml


**Translations:**

You can easily translate plugin messages by editing the lang.properties file inside the plugin folder. Remember to don't change the "lang-version" parameter, as it is used for internal proposes.

**Config:**
```
---
# Users are able to get only one kit per life
one-kit-per-life: true

# Users are able to get a kit if they log out event if they did not die (only works if one-kit-per-life: true)
reset-on-logout: true

# Use built in permission system even if using PurePerms
force-builtin-permissions: false

# Sign text (capitals don't matter)
sign-text: "[AdvancedKits]"
...
```
