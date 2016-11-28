# Night-Sky
WordPress plug-in that provides astronomical information based on location and date

WordPress framework and sunrise/sunset calculation credit:
https://github.com/bengreeley/sunrisesunset
Moonrise and Moonset credit:
http://dxprog.com/entry/calculate-moon-rise-and-set-in-php

This is a WordPress plugin, so it is written in PHP. Core functionality is separated from the framework, so that it can be incorporated into non-WordPress sites or executed from the command line. 

To use this plug-in:
* Create a "nightsky" directory on your computer.
* cd nightsky
* git clone https://github.com/stleary/Night-Sky.git .
* (enter your GitHub username and password when prompted)
* cd ..
* \# -o: OR operator, finds both filename patterns<br>
* \# -T : get filenames from a specified file<br>
* \# - : stdin is the specified file<br>
* find nightsky -name "*.php" -o -name "*.css" | tar -cf nightsky.tar -T -<br>
* Untar the file in your WordPress plugins directory. 
* From the Wordpress wp-admin login, navigate to the dashboard, select plug-ins, find "nightsky", and select Activate.
* To include the nightsky plugin on a page, insert the shortcode "[nightsky]".
