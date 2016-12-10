# The Stars At Night
WordPress plug-in that provides astronomical information based on location and date

WordPress framework and sunrise/sunset calculation credit:
https://github.com/bengreeley/sunrisesunset

Moonrise and Moonset credit:
http://dxprog.com/entry/calculate-moon-rise-and-set-in-php

This is a WordPress plugin, so it is written in PHP. Core functionality is
separated from the framework, so that it can be incorporated into non-WordPress
sites, or executed from the command line. 

To use this plug-in:
* 1. Preferred method: Install from the WordPress Plugin Repository. Search for
* "Stars At Night".
* 2. Manual installation
* Create a "stars-at-night" directory on your computer.
* cd stars-at-night
* git clone https://github.com/stleary/stars-at-night.git .
* (enter your GitHub username and password when prompted)
* cd ..
* \# Notes:
* \# on Windows, this requires Cygwin or similar linux command line tools
* \# -o: OR operator, finds both filename patterns<br>
* \# -T : get filenames from a specified file<br>
* \# - : stdin is the specified file<br>
* find stars-at-night -name "\*.php" -o -name "\*.css" | tar -cf starsAtNight.tar -T -<br>
* Untar the file in your WordPress plugins directory.<br> 
* From the Wordpress wp-admin login, navigate to the dashboard, select plug-ins, find "The Stars At Night", and select Activate.
* To include the plugin on a page, insert the shortcode "[stars-at-night]".

To test from the command line, execute something like this in the src directory of your repository:
```
php  class-stars-at-night-manager.php mode=test name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata date=now
```    
