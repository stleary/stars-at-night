<?php   

/*
MIT License

Copyright (c) 2016 Sean Leary

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/
/**
 * TODO: FOR DEBUG ONLY
 * Not to be used in production
 * Comment this out to allow command line testing
 */
defined( 'ABSPATH' ) or die;

include('class-moon.php');

/** 
 * TODO: FOR DEBUG ONLY
 * Not to be used in production.
 * This is just for testing from the command line. 
 * To test from the command line, execute something like this:
 *     php  class-stars-at-night-manager.php mode=test name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata date=now
 * To verify the results, use an online calculator such as http://www.rsimons.org/sunmoon/
 */
/*
if ( !defined( 'WPINC' ) ) {
    // var_dump($argv);
    $ngc2244_stars_at_night_manager = new Stars_At_Night_Manager();
    $ngc2244_stars_at_night_manager->run_stars_at_night( $argv );
}
*/

    
/**
 * This class calculates and emits astronomical values in an HTML table.
 * For testing, the values are just written to stdout
http://www.heavens-above.com/PassSummary.aspx?satid=25544&lat=30.891&lng=-98.4265&loc=Unspecified&alt=300&tz=CST
http://stackoverflow.com/questions/4979836/domdocument-in-php/4983721#4983721
 */
class Stars_At_Night_Manager {
    protected $loader;
    protected $plugin_name;
    protected $version;
    // sanitized user input
    private $sanitized_name;
    private $sanitized_lat;
    private $sanitized_long;
    private $sanitized_timezone;
    private $sanitized_date;
    private $sanitized_graphical;

    /**
     * create and initialize a class instance
     */
    public function __construct() {
        if (defined('WPINC')) {
            $this->plugin_name = 'stars-at-night';
            $this->version = '1.0';

            $this->define_admin_hooks();
            $this->define_public_hooks();
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
        // Hook shortcodes, etc.
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        add_action( 'init', array( $this, 'enqueuestyles' ));
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
        add_shortcode('stars-at-night', array($this, 'run_stars_at_night') );
    }

    /**
     * CSS for the plugin
     */
    public function enqueuestyles() {
        wp_enqueue_style( 'ngc2244_stars_at_night_css', plugins_url('../css/stars-at-night.css',__FILE__), array(), $this->version  );
    }

    /**
     * Here is where all of the work is done. 
     * param: atts - an array of parameter values with '=' delimiters inside each array element,
     * except for the first param which is the program name, and is ignored.
     * Remaining params (order is unimportant):
     *   name=the name of the location to be calculated
     *   lat=lattitude of location in fractional degrees (e.g. 30.8910). Positive is north, negative is south of equator
     *   long=longitude of location in fractional degrees (e.g.-98.4265). Positive is east, negative is west of the UTC line
     *   timezone=timezone name, must be value recognized by php. See http://php.net/manual/en/timezones.php
     *   date=a date that php can parse. For the current day, use "now" 
     *   graphical=not used at present. Will cause an image of the Moon phase to be displayed. 
     */ 
    public function run_stars_at_night( $atts ) {
        // raw user input
        $name = "";
        $lat = "";
        $long = "";
        $timezone = "";
        $date = "";
        $graphical = "";
         
        if ( defined( 'WPINC' ) ) {
            /**
             * WordPress mode. Default location is the dark sky observing site
             * for the Austin Astronomical Society
             */
            extract( shortcode_atts( array(
                'name' => '',        // name of location
                'lat' => '',        // Latitude value
                'long' => '',       // Longitude value
                'timezone' => '',   // timezone
                'date' => '',       // date
                'graphical' => ''   // Display moon images? not in use yet
                
            ), $atts, 'stars-at-night' ), EXTR_IF_EXISTS );
        } else {
            /**
             * TODO: FOR DEBUG ONLY
             * Not to be used in production.
             * Comment out 'die' and Uncomment this code if you want to try command line
             * mode for testing. Input is unprotected since the user could create any number
             * of random local vars.
             */
            die;
            /*
            $size = sizeof( $atts );
            if ($size > 1) {
                for ($i = 1; $i < $size; $i++) {
                    $e = explode( "=", $atts[ $i ] );
                    ${ $e[0]} = $e[1];
                }
            }
            */
        }

        /**
         * Make sure the incoming data is valid.
         * If not, errors will be reported in the return string
         * and the method stops here
         */
        $validator_result = $this->data_validator(
                $name, $lat, $long, $timezone, $date, $graphical );
        if ( !empty( $validator_result ) ) {
           return $validator_result;
        }

        /**
         * The builtin php lib uses the Solar zenith position, but has a flawed default value. 
         * So we use our own instead. See 
         * http://grokbase.com/t/php/php-bugs/09932wqn2a/49448-new-sunset-sunrise-zenith-default-values-wrong
         */
        $zenith = 90 + ( 50/60 );
        // times have to be calculated in the specified timezone
        $remote_dtz = new DateTimeZone( $this->sanitized_timezone );
        $remote_dt = new DateTime( $this->sanitized_date, $remote_dtz );
        $tzOffset = $remote_dtz->getOffset( $remote_dt ) / 3600;

        /**
         * get Sun times. returns a string like this: 07:10
         */
        $sunRise = date_sunrise( strtotime( 'now' ),
                SUNFUNCS_RET_STRING, $this->sanitized_lat, $this->sanitized_long, $zenith, $tzOffset );
        $sunSet = date_sunset( strtotime( 'now' ),
                SUNFUNCS_RET_STRING, $this->sanitized_lat, $this->sanitized_long, $zenith, $tzOffset );

        // get the Moon times
        $year = $remote_dt->format( 'Y' );
        $month = $remote_dt->format( 'm' );
        $day = $remote_dt->format( 'd' );
        $today = $remote_dt->format( 'D m/d/Y' );
        // we use a different timezone offset unit for the Moon. Just how moon.php is written.
        $moonTzOffset = $tzOffset * 60;
        /**
         * the original flawed object did not calculate timezone correctly. Our version does.
         */
        $moonData = Moon::calculateMoonTimes(
                $month, $day, $year, $this->sanitized_lat, $this->sanitized_long, $moonTzOffset );

        $moonRise = $moonData->moonrise;
        $moonSet = $moonData->moonset;
        $dtmr = new DateTime( "@$moonRise" );
        $moonRise = $dtmr->format( 'H:i' );
        $dtms = new DateTime( "@$moonSet" );
        $moonSet = $dtms->format( 'H:i' );
               
        // some days might not have a moonRise or moonSet 
        if ( $moonRise ==  "00:00" ) {
            $moonRise = "None";
        }
        if ( $moonSet ==  "00:00" ) {
            $moonSet = "None";
        }

        // get the twilight times, which we define as 90 minutes before sunrise, and after sunset
        $morningTwilight = $this->calculateTwilight( $today, $tzOffset, $sunRise, (-90 * 60) );
        $eveningTwilight = $this->calculateTwilight( $today, $tzOffset, $sunSet, (90 * 60) );

        if ( defined( 'WPINC' ) ) {
            // WordPress mode
            return $this->display(
                    $this->sanitized_name,
                    $this->sanitized_lat,
                    $this->sanitized_long,
                    $today,
                    $sunRise,
                    $sunSet,
                    $moonRise,
                    $moonSet,
                    $morningTwilight,
                    $eveningTwilight );
        } else {
            // test mode
            print "name: $this->sanitized_name\n";
            print "local day: $today\n";
            print "sunRise: $sunRise\n";
            print "sunSet: $sunSet\n";
            print "moonRise: $moonRise\n";
            print "moonSet: $moonSet\n";
            print "morningTwilight: $morningTwilight\n";
            print "eveningTwilight: $eveningTwilight\n";
        }
        return;
    }

    /**
     * Validates the parameters sent by the user. 
     *   @param $name the name of the location to be calculated
     *   @param $lat lattitude of location in fractional degrees 
     *   @param $long longitude of location in fractional degrees 
     *   @param $timezone timezone name, must be value recognized by php
     *   @param $date a date that php can parse 
     *   @param $graphical not used at present 
     * @return string containing error messages, or empty if no errors found
     */
    private function data_validator( $name, $lat, $long, $timezone, $date, $graphical ) {
        $result = "";
        /**
         * Name must be safe, but can be any value, up to 32 chars
         */
        if ( strlen( $name ) > 32 ) {
            $name = substr( $name, 32 );
        }
        $this->sanitized_name = filter_var( $name, FILTER_SANITIZE_STRING, 
                FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_ENCODE_AMP );

        /**
         * lat must be valid fractional decimals +- 0-90
         */
        if ( !is_numeric( $lat ) ) {
            $result .= " Latitude must be numeric.";
        } else if ( $lat < (-90) || $lat > 90 ) {
            $result .= " Latitude must be in the range -90 to 90.";
        } else {
            $this->sanitized_lat = $lat;
        }

        /**
         * long must be valid fractional decimal, +- 0-90
         */
        if ( !is_numeric( $long ) ) {
            $result .= " Longitude must be numeric.";
        } else if ( $long < (-180) || $long > 180 ) {
            $result .= " Longitude must be in the range -180 to 180.";
        } else {
            $this->sanitized_long = $long;
        }

        /**
         * timezone must be recognized by php
         */
        if ( !in_array( $timezone, DateTimeZone::listIdentifiers() ) ) {
            $result .= " Timezone contains an unrecognized value.";
        } else {
            $this->sanitized_timezone = $timezone;
        }

        /**
         * Date must be recognized by php
         */
        try {
            new DateTime( $date );
        } catch ( Exception $e ) {
            $result .= "Date contains an unrecognized value.";
        }
        $this->sanitized_date = $date;

        // for now, graphical is ignored

        if ( !empty( $result ) ) {
            $result = "Errors: " . $result;
        }
        return $result;
    }

    /**
     * This method can be used to calculate early morning or late evening
     * astronomical twilight. By definition this is 90 minutes before sunrise
     * or 90 minutes after sunset.
     * Parameters:
     *     $today - day for which the calculation is being made
     *     $tzOffset - timezone offset in seconds
     *     $sunTime - either sunrise or sunset, string hh:mm 24 hr format
     *     $delta - use -90 for morning, +90 for evening
     * Returns: twilight string in hh:mm format
     */
    private function calculateTwilight( $today, $tzOffset, $sunTime, $delta ) {
        $todayStr = $today . " " . $sunTime;
        $todayTimestamp = strtotime( $todayStr );
        $twilight = $todayTimestamp + $delta + $tzOffset;
        $dateTime = new DateTime();
        $dateTime = $dateTime->setTimestamp( $twilight );
        $twilightStr = $dateTime->format( 'H:i' );
        return $twilightStr;
    }

    /**
     * Returns a string containing the HTML to render a table of
     * night sky data.
     * Parameters:
     *     $name, $lat, $long - name and location 
     *     $today - date of calculation 
     *     $sunRise - time value for sunrise (eg. 6:00)
     *     $sunSet - time value for sunset (eg. 15:00)
     *     $moonRise - time value for Moonrise (eg. 6:00)
     *     $moonSet - time value for Moonset (eg. 15:00)
     *     $morningTwilight - morning astronomical twilight
     *     $eveningTwilight - evening astronomical twilight
     */
    private function display($name, $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight) {
        $string = 
        '<div class="nightsky">' .
           '<bold>' . $name . ' ('  .$lat . ' '  . $long . ') astronomical times for ' .
           $today . '</bold>' .
           '<table>' .
                '<tr>' .
                '<td>Astronomical twilight</td>' .
                '<td>'  . $morningTwilight . '</td>' .
                '</tr>' .
                '<tr>' .
                '<td>Sunrise</td>' .
                '<td>'  . $sunRise . '</td>' .
                '</tr>' .
                '<tr>' .
                '<td>Sunset</td>' .
                '<td>'  . $sunSet . '</td>' .
                '</tr>' .
                '<tr>' .
                '<td>Astronomical twilight</td>' .
                '<td>'  . $eveningTwilight . '</td>' .
                '</tr>' .
                '<tr>' .
                '<td>Moonrise</td>' .
                '<td>'  . $moonRise . '</td>' .
                '</tr>' .
                '<tr>' .
                '<td>Moonset</td>' .
                '<td>'  . $moonSet . '</td>' .
                '</tr>' .
            '</table>' .
        '</div>';
        return $string;
    }
} 
