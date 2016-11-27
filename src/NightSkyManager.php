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
        // wp_enqueue_style( 'sunrisesunsetcss', plugins_url('../css/sunrisesunset.css',__FILE__), array(), $this->version  );
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
        $sunrisedata = date_sunrise(strtotime('now'), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $tzOffset);
        $sunsetdata = date_sunset(strtotime('now'), SUNFUNCS_RET_STRING, $lat, $long, $zenith, $tzOffset);

        $year = $remote_dt->format('Y');
        $month = $remote_dt->format('m');
        $day = $remote_dt->format('d');
        $today = $remote_dt->format('D m/d/Y');

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

        // $this->display_sunrisesunset( $sunrisedata, $sunsetdata, $moonRise, $moonSet, $graphical == 'true' );
        print "COE (30.8910, -98.4265) astronomical times for today ($today)<br>";
        print "Sunrise: $sunrisedata<br>";    
        print "Sunset: $sunsetdata<br>";    
        print "Moonrise: $moonRise<br>";
        print "Moonset: $moonSet<br><p>";
        return;
    }

    /*
        Displays sunrise/sunset data:
        $sunrise - time value for sunrise (eg. 6:00)
        $sunset - time value for sunset (eg. 15:00)
        $graphical - bool true to display sun/moon with output.
    */    
    public function display_sunrisesunset($sunrise, $sunset, $moonRise, $moonSet, $graphical = true) {
    ?>
        <div class="sunrise-sunset<?php echo ( $graphical == true? ' sun-graphical':'');?>">
            <span class="time-sunrise"><strong>Sunrise</strong><?php echo date('g:i a', strtotime( date('n/j/Y', strtotime('now')) . $sunrise )); ?></span>
            <span class="time-sunset"><strong>Sunset</strong><?php echo date('g:i a', strtotime( date('n/j/Y', strtotime('now')) . $sunset )); ?></span>
            <span class="time-moonrise"><strong>Moonrise</strong><?php echo date('g:i a', strtotime( date('n/j/Y', strtotime('now')) . $moonRise )); ?></span>
            <span class="time-moonset"><strong>Moonset</strong><?php echo date('g:i a', strtotime( date('n/j/Y', strtotime('now')) . $moonSet )); ?></span>
        </div>
        <?php
            
        return;

    }
}
