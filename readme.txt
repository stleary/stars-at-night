=== stars-at-night ===
Contributors: stleary
Donate link: N/A
Tags: astronomy
Requires at least: 4.6
Requires PHP: 7.0
Tested up to: 5.8
Stable tag: 1.8
License: MIT
License URI: https://opensource.org/licenses/MIT

Stars-at-night displays astronomical data for a specified location and date.

== Description ==

This plugin displays data of interest to amateur astronomers. It is calculated from user-provided location and date information. 
The plugin is called from a WordPress shortcode, and the parameters are specified in the shortcode. The following parameters are
required, in any order:

* **lat** : Lattitude of location in fractional degrees (e.g. 30.8910). Positive is north, negative is south of equator
* **long** : Longitude of location in fractional degrees (e.g.-98.4265). Positive is east, negative is west of the UTC line
* **timezone** : Timezone name, must be value recognized by PHP. See [http://php.net/manual/en/timezones.php](http://php.net/manual/en/timezones.php)

These parameters are optional:

* **name** : The name of the location to be calculated
* **days** : The number of days of data to display. Must be a value from 1 to 10. Defaults to 3 if not specified. 

**Shortcode Examples:**

* stars-at-night name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata days=1
* stars-at-night name="COE Observing Field" lat=30.891 long=-97.4265 timezone=America/Chicago days=10

The output consists of simple HTML tables:

* Sun/Moon table: The times for sunrise, sunset, moonrise, moonset, morning astronomical twilight, and evening astronomical twilight for the specified days (max 10 days).
* Planets table: The times and visibility for the planets, for the current day.
* (DISABLED - will be restored later) ISS table: The times and directions for visible ISS passes over the specified days (max 10 days).
* (REMOVED) Iridium flares table: The times and directions for visible Iridium flares over the specified days (max 7 days).

You can view the plugin in action here: [http://johnjleary.com/notforlong](http://johnjleary.com/notforlong)

**Credits and Acknowledgements:**

* Lunar images by [Dan Morgan](mailto://Dan@danmorgan.org). Used with permission. [http://DanMorgan.org](http://DanMorgan.org).
* WordPress framework and sunrise/sunset algorithm: https://github.com/bengreeley/sunrisesunset
* Moonrise and Moonset class (with a correction for timezone): http://dxprog.com/entry/calculate-moon-rise-and-set-in-php
* Moon phase class: https://github.com/solarissmoke/php-moon-phase
* Planetary, ISS and Iridium Flare tables are obtained by sending GET requests to: http://heavens-above.com (HTTP API used with permission)

== Installation ==

1. Login to your WordPress site as admin.
2. From the Dashboard menu, select Plugins, then click the Add New button. 
3. In the Search text area, enter "stars at night", then press Enter
4. Find the stars-at-night plugin on the results page and select the Install Now button.

== Frequently Asked Questions ==

= What does twilight mean? =

The sky is fully dark starting about 90 minutes after sunset. This is the evening astronomical twilight time.
The sky stays fully dark until about 90 minutes before sunrise. This is the morning astronomical twilight time.
These times are important for observers because they indicate when faint deep sky objects can be seen

= Why is the reported sunrise/sunset a little different from (my favorite website) =

Many PHP-based Sun calculators use an incorrect value for the zenith. This plugin uses the correct value.
Search for 'zenith' in class-stars-at-night-manager.php for more information.

= I would like a change made to the input, output format, or anything else =

This plugin is driven by user requests. Just ask.

= Where did you get the planet and satellite data? =
The Planet, ISS, and Iridium flare data is obtained by parsing the response to a GET request to http://www.heavens-above.com. 

= Can you provide more astronomical data and images? =

More is coming. Stay tuned!  If you have a specific request, let us know.

== Screenshots ==

1. Sunrise and sunset table
2. Planet table


== Changelog ==

= 1.8 =

Use correct version number, fix readme

= 1.7.3 =

Fix tested version and tag (not released)

= 1.7.2 =

Disable ISS passes (heavens-above API has changed), remove Iridium flares display. (not released)

= 1.7.1 =

Fix version, tag. No code changes.

= 1.7 =

Removed planetary visibility row - it was too buggy

= 1.6 =

Fix planet and satellite requests to heavens-above.com, since the website does not accept daylight savings timezones.

= 1.5.1 =

Fix array initialization for PHP versions earlier than 5.4

= 1.5 =

* Update lunar images for accuracy and fixed image select algorithm
* Add table images for ISS, Iridium, and planet tables
* Table rendering cleanup

= 1.4 =

* Add planet table
* More consistent look for table titles
* Clarify required vs optional params in readme
* Fixed potential Lunar image alignment
* Include larger Lunar clickable images
* Fixed satellite de-caching for fewer server hits

= 1.3 =

* Add Lunar images to Sun/Moon table. 1 image per 3 rows.
* Fixed Sun/Moon day increment.
* Adapt to sparse or empty satellite cache.
* Handle empty cache - maybe there are no visible satellites during that period.

= 1.2 =
* Iridium flare visible passes table. Yay!
* Cache satellite data locally to reduce load on remote server - other apps are QOS-filtered because of too many requests.
* Replace start date with number of days. This was done because the satellite data is reported starting with the current day. Retrieving from an arbitrary start date is not practical.

= 1.1 =
* ISS visible passes table for the next 10 days, starting on the specified day.

= 1.0.1 =
* Readme.txt update. No functional changes.

= 1.0 =
* Initial version. 
* Sunrise, sunset, moonrise, moonset, morning astronomical twilight, evening astronomical twilight

== Upgrade Notice ==

= 1.0 =
Minimum required version.

== Contributors ==
stleary
