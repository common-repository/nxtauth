=== nxtAuth ===
Contributors: scor2k
Dotate link: http://nxtauth.tk
Tags: NXT, Token
Requires at least: 3.0.0
Tested up to: 4.0
Stable tag: 0.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

nxtAuth allow you to log into Wordpress using NXT-token

== Description ==

This plugin allow you to LogIn on Wordpress by NXT Token

* What is NXT: http://NxtCrypto.org/  [EN]
* NXT WiKi: http://wiki.nxtcrypto.org/ [EN][RU]

How it's work:

* Click on the button with NXT caption in the logon screen and enter valid NXT token for this site.
* If NXT server worked fine and token valid - you enter site with login name as your RS account name (NXT-.....)
* NXT servers and default post select on Options page. By default - localhost

== Installation ==

How to Install:

* Upload nxtAuth to wp-content/plugins or search on wordpress plugins and install it 
* Activate the plugin through 'Plugins' menu in WordPress
* Configure hostname and port in settings page for NXT server

== Changelog ==

= 0.6 =
* Changed the API to access the NXT-node 
* Change the style of the buttons on a standard for authentication. 
* You can specify any number of servers NXT for authorization. Plugin check their availability before use.

= 0.5 =
* Remove additional field. Change it for prompt message. 
* Add button with NXT logo (https://nextcoin.org/index.php/topic,3540.0.html)

= 0.3.1 =
* Fix readme.txt file :) 

= 0.3 =
* Add additional field on LogIn screen for NXT token. 
* Fix a little bugs
* Now you don't need to fix wp-login.php page :) 

= 0.2 = 
* Add Option page with setting for NXT server hostname

= 0.1 =
* Initial Build

