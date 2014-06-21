=== Guild Wars 2 - WvW Matchups ===
Contributors: klaufel
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=WC2WU4PKNCV2N&lc=ES&item_name=Guild%20Wars%202%20-%20WvW%20Matchups
Tags: guild, guildwars, guildwars2, gw2, wvw, matchups, guild wars 2, world vs world, mundo contra mundo, guild wars plugin, enfrentamientos, guild wars, puntuaciones, world, mundo, juegos, videojuegos, gaming, gamer
Requires at least: 3.0
Tested up to: 3.9
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display the live scores of WvW matchups of Guild Wars 2.

== Description ==

Guild Wars 2 - WvW Matchups, is a simple plugin to display the live scores of WvW matchups of Guild Wars 2

The names of the worlds and scores can be displayed in 4 languages (en, fr, de, es).

Very simple to use, simply create a WIDGETS and drag it to your sidebars and select your favourite world!

If you have any issues or feature requests please visit the plugin [Plugin Page](http://www.klaufel.com/guild-wars-2-wvw-matchups/).

The plugin refreshes its data by ArenaNet API (beta)
https://forum-en.guildwars2.com/forum/community/api/API-Documentation


= Requirements =

* PHP 5.3+
* cURL enabled


= Guild Wars 2 - Copyright =

©2010–2013 ArenaNet, LLC. and NC Interactive, LLC. All rights reserved. Guild Wars, Guild Wars 2, ArenaNet, NCSOFT, the Interlocking NC Logo, and all associated logos and designs are trademarks or registered trademarks of NCSOFT Corporation. All other trademarks are the property of their respective owners.

"Guild Wars 2 - Content Terms of Use"
https://www.guildwars2.com/en/legal/guild-wars-2-content-terms-of-use/


== Installation ==

1. Upload the `guild-wars-2-wvw-matchups` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin using the 'Plugins' menu in your WordPress admin panel.
1. You will find 'GW2 - WvW Matchups' in your Widgets page on WordPress admin panel.
1. Drag the widget to where you want to have and set the world who want to appear to show the match with scores.

* If the load times on your website increases, you should look at the permissions on the folder 'cache' in the plugin directory `/wp-content/plugins/guild-wars-2-wvw-matchups`. You have to give write permissions (777).

= Requeriments =

* PHP 5.3+
* cURL enabled

== Frequently Asked Questions ==

= Increases load times slightly on its website after installing the plugin? =

If the load times on your website increases, you should look at the permissions on the folder 'cache' in the plugin directory `/wp-content/plugins/guild-wars-2-wvw-matchups/cache`. You have to give write permissions (777).


== Changelog ==

= 1.2.1 - 07.06.2014 =
* Fixed problem to caching. UPDATE IMPORTANT

= 1.2 - 07.06.2014 =
* Updating SDK.
* Critical fixed for problem the plugin (UPDATE IMPORTANT).
* world_names.json is disabled in the ArenaNet´s API.
* Getting server names manually (temporarily)


= 1.1 - 27.12.2013 =
* Fixed a bug for caching.
* Update SDK (PhpGw2Api) to latest version.
* Cleaning the code base and elimination of useless code elements.
* The image size (home-world.png) is reduced to 276 bytes.

= 1.0.2 - 29.07.2013 =
* Now the results of the matches are sorted by score, from highest to lowest.

= 1.0.1 - 22.06.2013 =
* Added selection of language (en, fr, de, es).
* Implemented style sheet instead of displaying it in the code (show style.css).
* Fixes permissions for caching.
* Update SDK (PhpGw2Api) to latest version.

= 1.0.0 - 22.06.2013 =
* First release.