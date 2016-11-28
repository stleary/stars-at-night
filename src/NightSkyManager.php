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

class NightSkyManager {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'nightsky';
        $this->version = '.1';

        $this->define_admin_hooks();
        $this->define_public_hooks();
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
        extract( shortcode_atts( array(
                'lat' => '30.8910',               // Latitude value
                'long' => '-98.4265',             // Longitude value
                'timezone' => 'America/Chicago',  // timezone
                'date' => 'now',                  // date
                'graphical' => 'false'            // Display moon images?
                
            ), $atts, 'nightsky' ) );
       
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

        $this->display( $lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight );
        return;
    }

    /*
        Displays night sky data:
        $lat, $long - location
        $today - time
        $sunRise - time value for sunrise (eg. 6:00)
        $sunSet - time value for sunset (eg. 15:00)
        $moonRise - time value for Moonrise (eg. 6:00)
        $moonSet - time value for Moonset (eg. 15:00)
        $morningTwilight - morning astronomical twilight
        $eveningTwilight - evening astronomical twilight
    */    
    public function display($lat, $long, $today, $sunRise, $sunSet, $moonRise, $moonSet, $morningTwilight, $eveningTwilight) {
    ?>
        <div class="nightsky">
           <bold>COE (<?php echo $lat ?>, <?php echo $long ?>) astronomical times for today (<?php echo $today ?>)</bold>
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
