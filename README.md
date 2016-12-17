# The Stars At Night
This project is for a WordPress plug-in that provides astronomical information based on location and date.

The WordPress framework code and sunrise/sunset algorithm uses code from this project:
https://github.com/bengreeley/sunrisesunset

The Moonrise and Moonset class uses this code (with a correction for timezone):
http://dxprog.com/entry/calculate-moon-rise-and-set-in-php

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
