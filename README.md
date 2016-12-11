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
Preferred method: Install from the WordPress Plugin Repository. Search for "Stars At Night". It carries the Astronomy tag.

Manual installation <br>
Unix-ish:
*       Create a "stars-at-night" directory on your computer.
*       cd stars-at-night
*       git clone https://github.com/stleary/stars-at-night.git .
*           (enter your GitHub username and password when prompted)
*       cd ..
*       tar the files in the 'stars-at-night' directory, excluding the GitHub items: .git, .buildpath, .project, .settings. For example:<br>
```
# -o: OR operator, finds both filename patterns
# -T : get filenames from a specified file
# - : stdin is the specified file
find stars-at-night -name "\*.php" -o -name "\*.css" -o "README.md" -o "LICENSE" | tar -cf starsAtNight.tar -T -
```
*       Upload to your WordPress installation and untar in the wp-content/plugins directory
*       From the Wordpress wp-admin login, navigate to the dashboard, select plug-ins, find "The Stars At Night", and select Activate.
 
Windows:
*       From explorer, create a zipfile called "stars-at-night" at the sibling level of your local repository. Copy over and insert the entire "stars-at-night" directory, then remove those items you don't need: .git, .buildpath, .project, .settings.<br> 
*       From your WordPress admin login, navigate to Plugins, click Add New, click Upload  Plugins, browse to your zip file.<br>
*       When prompted, activate the plugin.
* To include the plugin on a page, insert the shortcode "[stars-at-night]".

To test from the command line, search the PHP files for "DEBUG ONLY" and follow the directions on commenting/uncommenting code. Execute something like this in the src directory of your repository:
```
php  -f class-stars-at-night-manager.php mode=test name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata date=now
```
