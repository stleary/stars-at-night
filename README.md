# The Stars At Night
The Stars at Night is a WordPress plugin. It is intended for use by amateur astronomers and others interested in tracking astronomical data for their location. For now, that includes tables for the Moon, planets, ISS, and Iridium flares visible from your local area. 

The plugin is activated by the shortcode “stars-at-night”. Here is an example:

\[stars-at-night name=”COE Observing Field” lat=30.891 long=-97.4265 timezone=America/Chicago days=10\]

* **name** : (location name: can be anything)
* **lat** : latitude. North of the equator is positive, South is negative. 
* **long** : longitude. East of the Prime Meridian is positive, West is negative. 
* **timezone** : A recognizable timezeone. For a list of valid values, see http://php.net/manual/en/timezones.php. 
* **days** : The number of days to predict. Must be a value from 1-10. Default is 3 days.

# Credits

* Lunar images by Dan Morgan (dan@danmorgan.org). Used with permission. http://DanMorgan.org.
* The WordPress framework and sunrise/sunset algorithm:
https://github.com/bengreeley/sunrisesunset
* The Moonrise and Moonset class (with a correction for timezone): <br>
http://dxprog.com/entry/calculate-moon-rise-and-set-in-php
* The Moon phase class:<br>
https://github.com/solarissmoke/php-moon-phase 
* The ISS and Iridium Flare tables are obtained by sending GET requests to:<br>
http://heavens-above.com (HTTP API used with permission)

This is a WordPress plugin, so it is written in PHP. Since it is available on the WordPress plugin directory, it is also stored in a SVN repository. However, this GitHub project is the single source of truth for the plugin.

# Installation
Preferred method: 
* Install from the WordPress Plugin Repository. Search for "Stars At Night". It carries the Astronomy tag.
* https://wordpress.org/plugins/stars-at-night

Manual installation is also supported:
*       Create a "stars-at-night" directory in your WordPress installation, under wp-content/plugins
*       cd to the stars-at-night directory
*       git clone https://github.com/stleary/stars-at-night.git .
*           (enter your GitHub username and password when prompted)
 
