=== stars-at-night ===
Contributors: stleary
Donate link: N/A
Tags: astronomy
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 1.2
License: MIT
License URI: https://opensource.org/licenses/MIT

Stars-at-night displays astronomical data for a specified location and date.

== Description ==

This plugin displays data of interest to amateur astronomers. It is calculated from user-provided location and date information. 
The plugin is called from a WordPress shortcode, and the parameters are specified in the shortcode. The following parameters are
required, in any order:

* **name** : The name of the location to be calculated
* **lat** : Lattitude of location in fractional degrees (e.g. 30.8910). Positive is north, negative is south of equator
* **long** : Longitude of location in fractional degrees (e.g.-98.4265). Positive is east, negative is west of the UTC line
* **timezone** : Timezone name, must be value recognized by PHP. See [http://php.net/manual/en/timezones.php]
* **days** : The number of days of data to display. Must be a value from 1 to 10. Defaults to 3 if not specified. 

**Shortcode Examples:**

* stars-at-night name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata days=1
* stars-at-night name="COE Observing Field" lat=30.891 long=-97.4265 timezone=America/Chicago 

The output consists of simple HTML tables:

* Sun/Moon table: The times for sunrise, sunset, moonrise, moonset, morning astronomical twilight, and evening astronomical twilight for the specified days.
* ISS table: The times and directions for visible ISS passes over the specified days.
* Iridium flares table: The times and directions for visible Iridium flares over the specified days.

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

= Where did you get the satellite data? =
The ISS and Iridium flare data is obtained by parsing the response to a GET request to http://www.heavens-above.com. 

= Can you provide more astronomical data, like what planets are visible, etc? =

More is coming. Stay tuned!  If you have a specific request, let us know.

== Screenshots ==

1. Sample tables generated for a specified location and time

== Changelog ==

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
