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

/**
 * This class calculates and emits astronomical values in an HTML table.
 * The WordPress-defined var 'WPINC' is used to facilitate command line testing.
 * To test from the command line, create a file test.php in this directory with the
 * following content:
 * <?php
 * include('NightSkyManager.php');
 * $nightSkyManager = new NightSkyManager();
 * $nightSkyManager->runNightSky(null);
 * ?>
 *
 * Then execute this command: php -f test.php
 */
class NightSkyManager {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        if (defined('WPINC')) {
            $this->plugin_name = 'nightsky';
            $this->version = '.1';

            $this->define_admin_hooks();
            $this->define_public_hooks();
        }
    }

    private function define_admin_hooks() {
        // Any admin hooks...
    }

    private function define_public_hooks() {        
        // Hook shortcodes, etc.
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        add_action( 'init', array( $this, 'enqueuestyles' ));
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
    
    public function register_shortcodes() {
        add_shortcode('nightsky', array($this, 'runNightSky') );
    }
    
    public function enqueuestyles() {
        wp_enqueue_style( 'nightskycss', plugins_url('../css/NightSky.css',__FILE__), array(), $this->version  );
    }

    /**
     * This method can be used to calculate early morning or late evening
     * astronomical twilight. By definition this is 90 minutes before sunrise
     * or 90 minutes after sunset.
     * $today - day for which the calculation is being made
     * $tzOffset - timezone offset in seconds
     * $sunTime - either sunrise or sunset, string hh:mm 24 hr format
     * $delta - use -90 for morning, +90 for evening
     * return: twilight string in hh:mm format
     */
    public function calculateTwilight($today, $tzOffset, $sunTime, $delta) {
        $todayStr = $today . " " . $sunTime;
        $todayTimestamp = strtotime($todayStr);
        $twilight = $todayTimestamp + $delta + tzOffset;
        $dateTime = new DateTime();
        $dateTime = $dateTime->setTimestamp($twilight);
        $twilightStr = $dateTime->format('H:i');
        return $twilightStr;
    }
    
    public function runNightSky($atts) {
    
        // our default location is the dark sky observing site for the Austin Astronomical Society            
        if (defined('WPINC')) {
            extract( shortcode_atts( array(
<<<<<<< HEAD
                'name' => 'COE',                  // name of location
=======
>>>>>>> f916c12832f0c6a77cbd1590b1ad7a14f3984188
                'lat' => '30.8910',               // Latitude value
                'long' => '-98.4265',             // Longitude value
                'timezone' => 'America/Chicago',  // timezone
                'date' => 'now',                  // date
                'graphical' => 'false'            // Display moon images?
                
            ), $atts, 'nightsky' ) );
        } else {
<<<<<<< HEAD
            /**
             * If you are executing from the command line, use something like this: 
             * php -f NightSkyManager.php name=test lat=33
             * order of params does not matter, but make sure you get the syntax right
             */
            $name = 'COE';                  // name of location
=======
>>>>>>> f916c12832f0c6a77cbd1590b1ad7a14f3984188
            $lat = '30.8910';               // Latitude value
            $long = '-98.4265';             // Longitude value
            $timezone = 'America/Chicago';  // timezone
            $date = 'now';                  // date
            $graphical = 'false';           // Display moon images?
<<<<<<< HEAD
            $size = sizeof($atts);
            if ($size > 1) {
                for ($i = 1; $i < $size; $i++) {
                    $e=explode("=",$atts[$i]);
                    ${$e[0]}=$e[1];
                }
            }
=======
>>>>>>> f916c12832f0c6a77cbd1590b1ad7a14f3984188
        }
       
        /**
         * The builtin php lib requires the zenith position, but has a flawed default value. 
         * So we use our own instead. See http://grokbase.com/t/php/php-bugs/09932wqn2a/49448-new-sunset-sunrise-zenith-default-values-wrong
         */
        $zenith = 90+(50/60);
        $remote_dtz = new DateTimeZone($timezone);
        $remote_dt = new DateTime($date, $remote_dtz);
        $tzOffset = $remote_dtz->getOffset($remote_dt) / 3600;
                
        // returns a string like this: 07:10
        $sunRise = date_sunrise(strtotime('now'), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $tzOffset);
        $sunSet = date_sunset(strtotime('now'), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $tzOffset);

        $year = $remote_dt->format('Y');
        $month = $remote_dt->format('m');
        $day = $remote_dt->format('d');
        $today = $remote_dt->format('D m/d/Y');

        $morningTwilight = $this->calculateTwilight($today, $tzOffset, $sunRise, (-90 * 60));
        $eveningTwilight = $this->calculateTwilight($today, $tzOffset, $sunSet, (90 * 60));

        include('moon.php');
        $moonData = Moon::calculateMoonTimes($month, $day, $year, $lat, $long);

        $moonRise = $moonData->moonrise;
        $moonSet = $moonData->moonset;
        $dtmr = new DateTime("@$moonRise");
        $moonRise = $dtmr->format('H:i');
        $dtms = new DateTime("@$moonSet");
        $moonSet = $dtms->format('H:i');
                
        if ($moonRise ==  "00:00") {
            $moonRise = "None";
        }
        if ($moonSet ==  "00:00") {
            $moonSet = "None";
        }

<<<<<<< HEAD
        $this->display( $name, $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight );
=======
        $this->display( $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight );
>>>>>>> f916c12832f0c6a77cbd1590b1ad7a14f3984188
        return;
    }

    /*
        Displays night sky data:
<<<<<<< HEAD
        $name, $lat, $long - name and location 
        $today - date of calculation 
=======
        $lat, $long - location
        $today - time
>>>>>>> f916c12832f0c6a77cbd1590b1ad7a14f3984188
        $sunRise - time value for sunrise (eg. 6:00)
        $sunSet - time value for sunset (eg. 15:00)
        $moonRise - time value for Moonrise (eg. 6:00)
        $moonSet - time value for Moonset (eg. 15:00)
        $morningTwilight - morning astronomical twilight
        $eveningTwilight - evening astronomical twilight
    */    
<<<<<<< HEAD
    public function display($name, $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight) {
    ?>
        <div class="nightsky">
           <bold><?php echo $name ?> (<?php echo $lat ?>, <?php echo $long ?>) astronomical times for today (<?php echo $today ?>)</bold>
=======
    public function display($lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight) {
    ?>
        <div class="nightsky">
           <bold>COE (<?php echo $lat ?>, <?php echo $long ?>) astronomical times for today (<?php echo $today ?>)</bold>
>>>>>>> f916c12832f0c6a77cbd1590b1ad7a14f3984188
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
