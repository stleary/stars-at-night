<?php   

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include('class-moon.php');

/** 
 * This is just for testing from the command line
 * The WordPress-defined var 'WPINC' is used to facilitate command line testing.
 * To test from the command line, execute something like this:
 *     php  class-stars-at-night-manager.php mode=test name=Chennai lat=13.08 long=80.26 timezone=Asia/Kolkata date=now
 * To verify the results, browse to the 10 day weather forecast for that city in https://weather.com/weather/tenday
 * which shows sun rise/set and moon rise/set in that city's local time
 */
if ( !defined( 'WPINC' ) ) {
    // var_dump($argv);
    $ngc2244_stars_at_night_manager = new Stars_At_Night_Manager();
    $ngc2244_stars_at_night_manager->run_stars_at_night( $argv );
}

    
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
    
        if ( defined( 'WPINC' ) ) {
            /**
             * WordPress mode. Default location is the dark sky observing site
             * for the Austin Astronomical Society
             */
            extract( shortcode_atts( array(
                'name' => 'COE',                  // name of location
                'lat' => '30.8910',               // Latitude value
                'long' => '-98.4265',             // Longitude value
                'timezone' => 'America/Chicago',  // timezone
                'date' => 'now',                  // date
                'graphical' => 'false'            // Display moon images? not in use yet
                
            ), $atts, 'nightsky' ) );
        } else {
            // command line mode for testing
            $size = sizeof( $atts );
            if ($size > 1) {
                for ($i = 1; $i < $size; $i++) {
                    $e = explode( "=", $atts[ $i ] );
                    ${ $e[ 0 ]} = $e[ 1 ];
                }
            }
        }

        /**
         * The builtin php lib uses the Solar zenith position, but has a flawed default value. 
         * So we use our own instead. See http://grokbase.com/t/php/php-bugs/09932wqn2a/49448-new-sunset-sunrise-zenith-default-values-wrong
         */
        $zenith = 90 + ( 50/60 );
        // times have to be calculated in the specified timezone
        $remote_dtz = new DateTimeZone( $timezone );
        $remote_dt = new DateTime( $date, $remote_dtz );
        $tzOffset = $remote_dtz->getOffset( $remote_dt ) / 3600;

        /**
         * get Sun times. returns a string like this: 07:10
         */
        $sunRise = date_sunrise( strtotime( 'now' ), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $tzOffset );
        $sunSet = date_sunset( strtotime( 'now' ), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $tzOffset );

        // get the twilight times, which we define as 90 minutes before sunrise, and after sunset
        $morningTwilight = $this->calculateTwilight( $today, $tzOffset, $sunRise, (-90 * 60) );
        $eveningTwilight = $this->calculateTwilight( $today, $tzOffset, $sunSet, (90 * 60) );

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
        $moonData = Moon::calculateMoonTimes( $month, $day, $year, $lat, $long, $moonTzOffset );

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

        if ( defined( 'WPINC' ) ) {
            // WordPress mode
            $this->display( $name, $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight );
        } else {
            // test mode
            print "location: $name\n";
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
    public function calculateTwilight( $today, $tzOffset, $sunTime, $delta ) {
        $todayStr = $today . " " . $sunTime;
        $todayTimestamp = strtotime( $todayStr );
        $twilight = $todayTimestamp + $delta + tzOffset;
        $dateTime = new DateTime();
        $dateTime = $dateTime->setTimestamp( $twilight );
        $twilightStr = $dateTime->format( 'H:i' );
        return $twilightStr;
    }

    /**
     * Displays night sky data as a simple HTML table.
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
    public function display($name, $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight) {
    ?>
        <div class="nightsky">
           <bold><?php echo $name ?> (<?php echo $lat ?>, <?php echo $long ?>) astronomical times for today (<?php echo $today ?>)</bold>
           <table>
                <tr>
                <td>Astronomical twilight</td>
                <td><?php echo $morningTwilight ?></td>
                </tr>
                <tr>
                <td>Sunrise</td>
                <td><?php echo $sunRise ?></td>
                </tr>
                <tr>
                <td>Sunset</td>
                <td><?php echo $sunSet ?></td>
                </tr>
                <tr>
                <td>Astronomical twilight</td>
                <td><?php echo $eveningTwilight ?></td>
                </tr>
                <tr>
                <td>Moonrise</td>
                <td><?php echo $moonRise ?></td>
                </tr>
                <tr>
                <td>Moonset</td>
                <td><?php echo $moonSet ?></td>
                </tr>
            </table>    
        </div>
        <?php
            
        return;

    }
} 
