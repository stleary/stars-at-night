# The Stars At Night
The Stars at Night is a WordPress plugin. It is intended for use by amateur astronomers and others interested in tracking astronomical data for their location. For now, that is sunrise/sunset, moonrise/moonset, and twilight times. 

The plugin is activated by the shortcode “stars-at-night”. Here is an example:

\[stars-at-night name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata date=1/2/2000\]

* **name** : (location name: can be anything)
* **lat** : latitude. North of the equator is positive, South is negative. Ex: 30.9810
* **long** : longitude. East of the Prime Meridian is positive, West is negative. Ex: -98.4265
* **timezone** : A recognizable timezeone. For a list of valid values, see http://php.net/manual/en/timezones.php. Ex: America/Chicago
* **date** : A recognizable date. For the current day, use ‘now’

# Credits
* The WordPress framework code and sunrise/sunset algorithm uses code from this project: https://github.com/bengreeley/sunrisesunset
* The Moonrise and Moonset class uses this code (with a correction for timezone): http://dxprog.com/entry/calculate-moon-rise-and-set-in-php

This is a WordPress plugin, so it is written in PHP. Since it is available on the WordPress plugin directory, it is also stored in a SVN repository. However, this project is the single source of truth for the plugin.

To use this plug-in:
Preferred method: Install from the WordPress Plugin Repository. Search for "Stars At Night". It carries the Astronomy tag.

Manual installation is also supported. <br>
Unix-ish:
*       Create a "stars-at-night" directory in your WordPress installation, under wp-content/plugins
*       cd to the stars-at-night directory
*       git clone https://github.com/stleary/stars-at-night.git .
*           (enter your GitHub username and password when prompted)
 
The official project page for stars-at-night is located in http://notforlong.org/the-stars-at-night/


If you have questions or feature requests, open an issue in this project or leave a comment on the notforlong.org page.
