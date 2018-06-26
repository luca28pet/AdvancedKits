AdvancedKits
============

A PocketMine-MP plugin that adds kits to your server with many features and support for UIs (FormAPI plugin) and custom enchantments (PiggyCustomEnchants)

*Latest release: https://poggit.pmmp.io/p/AdvancedKits/*

*Latest development phars: https://poggit.pmmp.io/ci/luca28pet/AdvancedKits/AdvancedKits*

This plugin only supports PocketMine-MP. It might work on other forks as well, but please Do NOT open issues if you have problems with AdvancedKits and you are using a fork.

**Features overview:**

- Highly configurable
- UI (user interface) support using FormAPI plugin
- Custom enchantments support using PiggyCustomEnchants plugin
- Custom permissions support
- Built in permissions system for non-PurePerms users (see kits.yml)
- Economy support: pay to get a kit. Support for EconomyS, PocketMoney and MassiveEconomy
- Sign support: write a sign to let users get a kit
- Time limit (cooldown) for kits
- Option for one kit per life (see config.yml)
- Execute commands with kits
- Easy translation system

**Commands:**
The main command: /kit
Alias for /kit: /ak, /advancedkits .

- /kit [kitname] - Selects a kit. If no argument is kit name, opens the UI if possible or display a list of available kits.
- /akreload - reloads kits.yml (when edited while the server is running)



**Signs:**
To let users get a kit through a sign, you can create one like this: (capitals don't matter)

Line 1: [AdvancedKits]

Line 2: kitname

Line 3 & 4: Whatever you like


The default kit is: testkit.
You can add kits editing kits.yml (see "Kit settings").

**UI support:**
To let users select a kit using a UI, install the plugin FormAPI.
Then, to open the UI, use /kit

**Permissions:**
If you have PurePerms: with the permission advancedkits.kitname, a player will be able to get the kit named "kitname".
Note: in the permission, the kit name HAS to be ALL in lowercase letters.
If you do not use PurePerms, you can specify which users (and in which worlds) can get a kit. (see kit settings)

**Kit Settings:**

In order to add a kit you will need to edit the config kits.yml .
If you open that file with bloc notes, you will be not able to edit because it will be all in one line, so open it with WordPad, Notepad ++, ...
You can add lots of kits, but remember to keep this format:

```
testkit:
  # ITEM FORMAT: "id:damage:count:name:ench_name:ench_level"
  # NO enchantments and NO custom name: "id:damage:count"

  # ONLY custom name: "id:damage:count:custom name"

  # ONLY enchantments: "id:damage:count:DEFAULT:enchantment1:level"
  # (Put DEFAULT in the name field if you do not want a custom name)
  # You can put as many enchantments as you want like this: "id:damage:count:DEFAULT:enchantment1:level:enchantment2:level" etc.

  # Enchantments AND custom name: "id:damage:count:custom name:enchantment1:level"
  # You can put as many enchantments as you want like this: "id:damage:count:custom name:enchantment1:level:enchantment2:level" etc.

  # You can write both numeric or string IDs
  items:
  - "260:0:10"
  - "267:0:1:Sword Name"
  helmet: "diamond_helmet:0:1"
  chestplate: "diamond_chestplate:0:1:DEFAULT:protection:1"
  leggings: "diamond_leggings:0:1:Leggings Name"
  boots: "diamond_boots:0:1"

  # Set items for specific slots, will override existing items. Only use if you want to assign items to specific slots, otherwise delete this.
  slots:
    9: "golden_apple:0:5"
    12: "bow:0:1"

  commands:
  - "tell {player} you got an awesome kit thanks to AdvancedKits plugin!"

  cooldown:
    hours: 24
    minutes: 30

  # EFFECT FORMAT: "name:seconds:amplifier"
  effects:
  - "speed:120:2"

  # Add a cost for the kit. Compatible with EconomyAPI, PocketMoney and MassiveEconomy
  # Put 0 if you want the kit to be free
  money: 50

  # If you do not use pureperms, use 'worlds' to specify in which worlds you want this kit to be used
  # Leave blank to let use the kit in all worlds
  worlds:
  - "kitpvp"

  # If you do not use pureperms, use 'users' to specify which players will be able to get this kit
  # Leave blank to let all the players use this kit
  users:
  - "luca28pet"
  - "dioconsole"

  # FormAPI users:
  # Img type: 'url' or 'path'
  img-type: ''
  # Put here the image URL/path
  img-data: ''
  # How the kit is displayed on the form
  form-name: 'Test Kit: 50$'
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

# Users are able to get a kit if they log out even if they did not die (only works if one-kit-per-life: true)
reset-on-logout: true

# Use built in permission system even if using PurePerms
force-builtin-permissions: false

# Sign text (capitals and color codes don't matter)
sign-text: "[AdvancedKits]"
...
```
