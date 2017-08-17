AdvancedKits
============

PocketMine-MP plugin that adds kits to your PocketMine server. Report bugs and errors to https://github.com/luca28pet/AdvancedKits/issues

This is a simple yet useful PocketMine-MP kit plugin. For who doesn't know what kits are, they are groups of items that you can get simply by typing a command or touching a sign.

*Find the latest PHAR at: https://poggit.pmmp.io/ci/luca28pet/AdvancedKits*

This plugin only supports PocketMine-MP. It might work on other forks as well, but please Do NOT open issues if you have problems with AdvancedKits and you are using a fork.

**Features:**

- Highly configurable
- Custom permission support: give a player permission advancedkits.kitname to let him use the kit named "kitname"
- Built in perms system for non-PurePerms users (read the documentation)
- Economy support: pay to get a kit. Support for EconomyS, PocketMoney and MassiveEconomy
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
You can add kits editing kits.yml (see "Kit settings").

**Kit Settings:**

In order to add kit you will need to edit the config kits.yml .
If you open that file with bloc notes, you will be not able to edit because it will be all in one line, so open it with WordPad, Notepad ++, ...
You can add lots of kits, but remember to keep this file format:

```
---
testkit:
  # Format: "id:damage:count:name:ench_name:ench_level"

  # If you want ONLY custom name (no enchantments): "id:damage:count:name"

  # If you don't want enchantments or custom name: "id:damage:count"

  # If you want ONLY enchantments (no custom name): "id:damage:count:DEFAULT:ench_name:ench_level" -- you have to put DEFAULT in the name field

  # If you want more than one enchantment just do: "id:damage:count:name:ench1_name:ench1_level:ench2_name:ench2_level"
  # or "id:damage:count:DEFAULT:ench1_name:ench1_level:ench2_name:ench2_level" if you don't want a custom item name

  # Please note: You have to write numeric IDs
  items:
  - "260:0:10"
  - "267:0:1:Sword Name:sharpness:3:knockback:1"
  helmet: "302:0:1"
  chestplate: "303:0:1:DEFAULT:protection:1"
  leggings: "304:0:1:Leggings Name"
  boots: "305:0:1"

  commands:
  - "tell {player} you got an awesome kit thanks to AdvancedKits plugin!"

  cooldown:
    hours: 24
    minutes: 30

  # Format: "name:time:amplifier"
  # Time is in seconds
  effects:
  - "speed:120:2"
  
  # Add a cost for the kit. Compatible with EconomyAPI, PocketMoney and MassiveEconomy
  # Put 0 if you want the kit to be free
  money: 50

  # If you do not use pureperms, use 'worlds' to specify in which worlds you want this kit to be used
  worlds:
  - "kitpvp"
  # If you do not use pureperms, use 'users' to specify which players will be able to get this kit
  users:
  - "luca28pet"
  - "dioconsole"
...
```

You can find a list of available enchantments in the file Enchantment.php in the pmmp source code: https://github.com/pmmp/PocketMine-MP/blob/master/src/pocketmine/item/enchantment/Enchantment.php

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
