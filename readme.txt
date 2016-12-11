=== stars-at-night ===
Contributors: stleary
Donate link: N/A
Tags: astronomy
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 1.0
License: MIT
License URI: 

Stars-at-night displays astronomical data for a specified location and date.

== Description ==

This plugin displays data of interest to amateur astronomers. It is calculated from user-provided location and date information. 
The plugin is called from a WordPress shortcode, and the parameters are specified in the shortcode. The following parameters are
required, in any order:
* name: The name of the location to be calculated
* lat: Lattitude of location in fractional degrees (e.g. 30.8910). Positive is north, negative is south of equator
* long: Longitude of location in fractional degrees (e.g.-98.4265). Positive is east, negative is west of the UTC line
* timezone: Timezone name, must be value recognized by PHP. See [http://php.net/manual/en/timezones.php]
* date: A date that PHP can parse. For the current day, use "now" 

Examples:
[stars-at-night name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata date=now]
[name="COE Observing Field" lat=30.891 long=-97.4265 timezone=America/Chicago date=1/29/2017

The output consists of a simple HTML table containing the times for sunrise, sunset, moonrise, moonset,
morning astronomical twilight, and evening astronomical twilight.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/stars-at-night` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress

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

= Can you provide more astronomical data, like visible ISS passes, what planets are visible, etc? =

More is coming. Stay tuned!  If you have a specific request, let us know.

== Screenshots ==


== Changelog ==

= 1.0 =

Initial version. Sunrise, sunset, moonrise, moonset, morning astronomical twilight, evening astronomical twilight

== Upgrade Notice ==

