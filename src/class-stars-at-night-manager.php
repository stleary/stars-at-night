<?php

/*
 * MIT License
 *
 * Copyright (c) 2016 Sean Leary
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
defined ( 'ABSPATH' ) or die ();

include ('class-sunrise-sunset.php');
include ('class-moonrise-moonset.php');
include ('class-moon-phase.php');
include ('class-satellite-passes.php');
include ('class-planet-passes.php');

/**
 * This class calculates and emits astronomical values in an HTML table.
 * For testing, the values are just written to stdout
 */
class Stars_At_Night_Manager {
    // WordPress required properties
    protected $loader;
    protected $plugin_name;
    protected $version;

    // sanitized user input
    private $sanitized_name;
    private $sanitized_lat;
    private $sanitized_long;
    private $sanitized_timezone;
    private $sanitized_days;
    private $sanitized_graphical;
    // calculated values
    private $startDate;
    private $endDate;
    private $satellitePasses;
    private $planetPasses;
    private $sunriseSunset;

    /**
     * create and initialize a class instance
     */
    public function __construct() {
        if (defined ( 'WPINC' )) {
            $this->plugin_name = 'stars-at-night';
            $this->version = '1.0';

            $this->define_admin_hooks ();
            $this->define_public_hooks ();
        }
    }

    /**
     * This class does perform WordPress Admin functionality
     */
    private function define_admin_hooks() {
        // Any admin hooks...
    }

    /**
     * These are how the plugin interacts with WordPress
     */
    private function define_public_hooks() {
        add_action ( 'init', array ($this,'register_shortcodes' 
        ) );
        add_action ( 'init', array ($this,'enqueuestyles' 
        ) );
    }

    /**
     * This is how the plugin is known to WordPress
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Report plugin version to WordPress
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * WordPress shortcodes for this plugin
     */
    public function register_shortcodes() {
        add_shortcode ( 'stars-at-night',          array ($this,'run_stars_at_night') );
        add_shortcode ( 'stars-at-night-sun-moon', array ($this,'run_stars_at_night') );
        add_shortcode ( 'stars-at-night-planets',  array ($this,'run_stars_at_night') );
        add_shortcode ( 'stars-at-night-iss',      array ($this,'run_stars_at_night') );
    }

    /**
     * CSS for the plugin
     */
    public function enqueuestyles() {
        wp_enqueue_style ( 'ngc2244_stars_at_night_css', 
                plugins_url ( '../css/stars-at-night.css', __FILE__ ), array (), $this->version );
    }

    /**
     * Here is where all of the work is done.
     *
     * @param $atts array
     *            An array of parameter values with '=' delimiters inside each array element,
     *            except for the first param which is the program name, and is ignored.
     *            Remaining params (order is unimportant):
     *            name: the name of the location to be calculated
     *            lat: latitude of location in fractional degrees (e.g. 30.8910).
     *            Positive is north, negative is south of equator
     *            long: longitude of location in fractional degrees (e.g.-98.4265).
     *            Positive is east, negative is west of the UTC line
     *            timezone: timezone name, must be value recognized by php.
     *            See http://php.net/manual/en/timezones.php
     *            days: number of days to report, starting from today
     *
     *            graphical=not used at present. Will cause an image of the Moon phase to be
     *            displayed.
     *
     * @param content String
     *            The shortcode content or null.
     * @param shortcode_tag String
     *            The shortcode tag.
     *
     */
    public function run_stars_at_night($atts, $content, $shortcode_tag) {
        if (! defined ( 'WPINC' )) {
            die ();
        }
        
        $this->satellitePasses = new NGC2244_Satellite_Passes ();
        $this->planetPasses = new NGC2244_Planet_Passes ();
        $this->sunriseSunset = new NGC2244_Sunrise_Sunset ();
        
        /**
         * these are the supported fields of raw user input
         */
        $name = '';
        $lat = '';
        $long = '';
        $timezone = '';
        $days = '';
        $graphical = '';

        extract ( 
                shortcode_atts ( 
                        array ('name' => '','lat' => '','long' => '','timezone' => '','days' => '3',
                                'graphical' => '' 
                        ), $atts, 'stars-at-night' ), EXTR_IF_EXISTS );

        /**
         * Make sure the incoming data is valid.
         * If not, errors will be reported in the return string
         * and the method stops here
         */
        $validator_result = $this->data_validator ( $name, $lat, $long, $timezone, $days, 
                $graphical );
        if (! empty ( $validator_result )) {
            return $validator_result;
        }
        $today = new DateTime ( 'now', new DateTimeZone ( $timezone ) );
        $this->startDate = new DateTime ( $today->format ( 'm/d/Y' ) );
        // error_log ( 'startdate ' . $this->startDate->format ( 'm/d/Y' ) );
        $this->endDate = new DateTime ( $today->format ( 'm/d/Y' ) );
        $this->endDate->add ( new DateInterval ( 'P' . ($this->sanitized_days - 1) . 'D' ) );

        // Create an output string.
        $output = '';

        // Populate the output depending on the shortcode tag.
        switch ($shortcode_tag) {
          case 'stars-at-night-sun-moon':
            $output .= '<p>' . $this->getSunAndMoonTable ();
            break;
          case 'stars-at-night-planets':
            $output .= '<p>' . $this->getPlanetTable ();
            break;
          case 'stars-at-night-iss':
            $output .= '<p>'.  $this->getISSTable ();
            break;
          default:
            $output .= '<p>' . $this->getSunAndMoonTable ();
            $output .= '<p>' . $this->getPlanetTable ();
            $output .= '<p>'.  $this->getISSTable ();
            break;
        }

        return $output;
    }

    /**
     * Planettable is just for today
     *
     * @return table of planets for today
     */
    private function getPlanetTable() {
        $planetTable = $this->planetPasses->get_planet_table ( $this->sanitized_lat, 
                $this->sanitized_long, $this->sanitized_timezone, $this->sunriseSunset );
        return $planetTable;
    }

    /**
     * Iridium table can only look ahead 7 days, so calculate end date to at most 7 days, but pass
     * in the actual days in case we need to call this out in the table header.
     *
     * @return table of iridium flares for the request time period, starting today
     */
    private function getIridiumTable() {
        $iridiumDays = (($this->sanitized_days > 7) ? 7 : $this->sanitized_days);
        $iridiumEndDate = new DateTime ( $this->startDate->format ( 'm/d/Y' ) );
        $iridiumEndDate->add ( new DateInterval ( 'P' . ($iridiumDays - 1) . 'D' ) );
        // error_log ( 'enddate ' . $this->endDate->format ( 'm/d/Y' ) );
        $iridiumTable = $this->satellitePasses->get_iridium_table ( $this->sanitized_lat, 
                $this->sanitized_long, $this->sanitized_timezone, $this->startDate, $iridiumEndDate, 
                $this->sanitized_days );
        return $iridiumTable;
    }

    /**
     * ISS table can look ahead 10 days, same as the max days user can request, so no modification
     * of the end date is needed
     *
     * @return table of ISS passes for the request timer period, starting today
     */
    private function getISSTable() {
        $issTable = $this->satellitePasses->get_iss_table ( $this->sanitized_lat, 
                $this->sanitized_long, $this->sanitized_timezone, $this->startDate, $this->endDate );
        return $issTable;
    }

    /**
     * Returns a string containing the HTML to render a table of
     * night sky data inside a div.
     * A leading table description is included as well.
     *
     * @return html table of event times
     */
    private function getSunAndMoonTable() {
        /**
         * table for day to image mapping.
         * It's problematic because
         * there are only 26 lunar images (28 if you count the blank images),
         * whereas the lunar cycle goes from 0 to 29.52 days. Complicating
         * things is that a couple of the images do not seem to be a day
         * apart, and other images may be missing. I also wanted the quarter
         * images to match up with the real phase days. Here is the result,
         * where key=day of moon, value=image number
         */
        $phaseArray = array (1 => 1,2 => 2,3 => 3,4 => 4,5 => 5,6 => 6,7 => 8,8 => 8,9 => 9,
                10 => 10,11 => 11,12 => 11,13 => 12,14 => 13,15 => 13,16 => 14,17 => 15,18 => 16,
                19 => 17,20 => 18,21 => 19,22 => 20,23 => 21,24 => 22,25 => 23,26 => 24,27 => 25,
                28 => 26,29 => 27,30 => 28 
        );

        /**
         * Get the Moon phase.
         */
        $days = $this->endDate->diff ( $this->startDate )->days + 1;
        $xdate = new DateTime ( $this->startDate->format ( 'm/d/Y' ) );
        for($i = 0; $i < $days; $i = $i + 3) {
            $moonPhase = new NGC2244_Moon_Phase ( $xdate->getTimestamp () );
            $xdate->add ( new DateInterval ( 'P3D' ) );
        }

        $sunMoonTable = '<div><table class="ngc2244_stars_at_night_standardTable">';
        if ($this->sanitized_days == 1) {
            $days = " day";
        } else {
            $days = " days";
        }
        $sunMoonTable .= '<thead><tr><td align="center" valign="middle" colspan="8" >Astronomical Times for ' .
                 $this->sanitized_name . ' (' . $this->sanitized_lat . ', ' . $this->sanitized_long .
                 ')<br>' . 'Starting ' . $this->startDate->format ( 'd M Y' ) . ', for the next ' .
                 $this->sanitized_days . $days . '</td></tr>';
        $sunMoonTable .= '<tr><td align="center" rowspan="2" valign="middle">Date</td>';
        $sunMoonTable .= '<td align="center">Morning</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Sunrise</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Sunset</td>';
        $sunMoonTable .= '<td align="center">Evening</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Moonrise</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Moonset</td>';
        $sunMoonTable .= '<td aligh="center" rowspan="2" valign="middle">Moon Phase</td></tr>';
        $sunMoonTable .= '<tr><td>Twilight</td><td>Twilight</td></tr></thead>';

        $dayCount = 0;
        for($date = new DateTime ( $this->startDate->format ( 'm/d/Y' ) ); $date <= $this->endDate; $date->add ( 
                new DateInterval ( 'P1D' ) )) {
            // both sun and moon require a timezone offset, although they use different units
            $remote_dtz = new DateTimeZone ( $this->sanitized_timezone );
            $remote_dt = new DateTime ( $date->format ( 'm/d/Y' ), $remote_dtz );
            $sunTzOffset = $remote_dtz->getOffset ( $remote_dt ) / 3600;
            $moonTzOffset = $remote_dtz->getOffset ( $remote_dt ) / 60;
            
            // get the Sun times
            $this->sunriseSunset->calculate_sun_times ( $this->sanitized_lat, 
                    $this->sanitized_long, $sunTzOffset, $date );
            
            // get the Moon times
            $moonriseMoonset = new NGC2244_Moonrise_Moonset ();
            $moonriseMoonset->calculate_moon_times ( $this->sanitized_lat, $this->sanitized_long, 
                    $moonTzOffset, $this->sanitized_timezone, $date );
            // convert date for table rendering
            $dateStr = $date->format ( 'd M Y' );
            // get the tables
            $sunMoonTable .= '<tr><td>' . $dateStr . '</td><td>' .
                     $this->sunriseSunset->morningTwilight . '</td><td>';
            $sunMoonTable .= $this->sunriseSunset->sunRise . '</td><td>' .
                     $this->sunriseSunset->sunSet . '</td><td>';
            $sunMoonTable .= $this->sunriseSunset->eveningTwilight . '</td><td>' .
                     $moonriseMoonset->getMoonRise ();
            $sunMoonTable .= '</td><td>' . $moonriseMoonset->getMoonSet () . '</td>';
            if ($dayCount % 3 === 0) {
                $moonPhase = new NGC2244_Moon_Phase ( $date->getTimestamp () );
                $age = round ( $moonPhase->age () );
                $imageCount = $phaseArray [$age];
                $imageFile = plugin_dir_url ( __FILE__ ) . "../images/Moon-" . $imageCount . ".jpg";
                error_log ( 'image file:' . $imageFile );
                error_log ( 
                        'dayCount ' . $dayCount . ' age: ' . $age . ' calendar: ' .
                                 $date->format ( 'Y M d' ) );
                $sunMoonTable .= '<td rowspan="3"><a href="' . plugin_dir_url ( __FILE__ ) .
                         '../images/Moon-' . $imageCount .
                         '-large.jpg"><img class="ngc2244_stars_at_night_lunar" src="' . $imageFile .
                         '" alt="day ' . $age . ' of Moon"></img></a></td>';
            }
            $sunMoonTable .= '</tr>';
            $dayCount ++;
        }
        $sunMoonTable .= '</table></div>';
        // for debugging the phase array
        // for($i = 0; $i < 200; ++ $i) {
        // $moonPhase = new NGC2244_Moon_Phase ( $date->getTimestamp () );
        // $age = $moonPhase->age ();
        // $roundAge = round($age) + 1;
        // error_log ( "test " . $i . " age " . $roundAge . " image " .
        // $phaseArray[$roundAge] );
        // $date->add ( new DateInterval ( 'P1D' ) );
        // }
        
        return $sunMoonTable;
    }
    
    /**
     * Validates the parameters sent by the user.
     *
     * @param $name string
     *            name of the location to be calculated
     * @param $lat float
     *            latitude of location in fractional degrees
     * @param $long float
     *            longitude of location in fractional degrees
     * @param $timezone string
     *            timezone name, must be value recognized by php
     * @param $days int
     *            number of days to report
     * @param $graphical bool
     *            not used at present
     * @return string containing error messages, or empty if no errors found
     */
    private function data_validator($name, $lat, $long, $timezone, $days, $graphical) {
        $result = "";
        /**
         * Name must be safe, but can be any value, up to 32 chars
         */
        if (strlen ( $name ) > 32) {
            $name = substr ( $name, 32 );
        }
        $filterFlags = FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_AMP;
        $this->sanitized_name = filter_var ( $name, FILTER_SANITIZE_STRING, $filterFlags );

        /**
         * lat must be valid fractional decimal [-90:90]
         */
        if (! is_numeric ( $lat )) {
            $result .= " Latitude must be numeric.";
        } else if ($lat < (- 90) || $lat > 90) {
            $result .= " Latitude must be in the range -90 to 90.";
        } else {
            $this->sanitized_lat = $lat;
        }

        /**
         * long must be valid fractional decimal [-180:180]
         */
        if (! is_numeric ( $long )) {
            $result .= " Longitude must be numeric.";
        } else if ($long < (- 180) || $long > 180) {
            $result .= " Longitude must be in the range -180 to 180.";
        } else {
            $this->sanitized_long = $long;
        }

        /**
         * timezone must be recognized by php
         */
        if (! in_array ( $timezone, DateTimeZone::listIdentifiers () )) {
            $result .= " Timezone contains an unrecognized value.";
        } else {
            $this->sanitized_timezone = $timezone;
        }

        /**
         * days must be valid int [1:10].
         * Total of date+days must not exceed 10.
         */
        if (! is_numeric ( $days )) {
            $result .= " Days must be numeric.";
        } else if ($days < 1 || $days > 10) {
            $result .= " Days must be in the range 1 to 10.";
        } else {
            $this->sanitized_days = $days;
        }

        // for now, graphical is ignored

        if (! empty ( $result )) {
            $result = "Errors: " . $result;
        }
        return $result;
    }
} 
