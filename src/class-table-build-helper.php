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

/**
 * This class provides helper methods to build mobile and fullsize tables
 * for Stars_At_Night_Manager.
 * All of the tedious, error-prone, and poorly
 * designed table code is gathered here in one place. The main benefit is that
 * responsive tables can be approximated here. Only 2 sizes are returned:
 * mobile and fullsize. It is expected that the user selects a theme that
 * can handle a reasonable amount of responsiveness, e.g. Shapely or
 * Twenty Sixteen.
 */
class NGC2244_Table_Build_Helper {
    private $manager;
    
    /**
     * create and initialize a class instance
     */
    public function __construct($manager) {
        $this->manager = $manager;
    }
    
    /**
     * Returns a string containing the HTML to render a table of
     * night sky data inside a div.
     * This table is suitable for rendering
     * on a smart phone display.
     * A leading table description is included as well.
     *
     * @return html table of event times
     */
    public function getSunAndMoonTableMobile() {
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
        $days = $this->manager->getEndDate ()->diff ( $this->manager->getStartDate () )->days + 1;
        $xdate = new DateTime ( $this->manager->getStartDate ()->format ( 'm/d/Y' ) );
        for($i = 0; $i < $days; $i = $i + 3) {
            $moonPhase = new NGC2244_Moon_Phase ( $xdate->getTimestamp () );
            $xdate->add ( new DateInterval ( 'P3D' ) );
        }
        
        $sunMoonTable = '';
        $dayCount = 0;
        for($date = new DateTime ( $this->manager->getStartDate ()->format ( 'm/d/Y' ) ); $date <=
                 $this->manager->getEndDate (); $date->add ( new DateInterval ( 'P1D' ) )) {
            $sunMoonTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
            $sunMoonTable .= '<thead><tr><th colspan="2">Sun and Moon: ' .
                     $dateStr = $date->format ( 'd M Y' ) . '</th></tr></thead>';
            // both sun and moon require a timezone offset, although they use different units
            $remote_dtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
            $remote_dt = new DateTime ( $date->format ( 'm/d/Y' ), $remote_dtz );
            $sunTzOffset = $remote_dtz->getOffset ( $remote_dt ) / 3600;
            $moonTzOffset = $remote_dtz->getOffset ( $remote_dt ) / 60;
            
            // get the Sun times
            $this->manager->getSunriseSunset ()->calculate_sun_times ( 
                    $this->manager->get_sanitized_lat (), $this->manager->get_sanitized_long (), 
                    $sunTzOffset, $date );
            
            // get the Moon times
            $moonriseMoonset = new NGC2244_Moonrise_Moonset ();
            $moonriseMoonset->calculate_moon_times ( $this->manager->get_sanitized_lat (), 
                    $this->manager->get_sanitized_long (), $moonTzOffset, 
                    $this->manager->get_sanitized_timezone (), $date );
            
            // convert date for table rendering
            $dateStr = $date->format ( 'd M Y' );
            // get the tables
            $sunMoonTable .= '<tr><td>' . 'AM Twilight </td><td>' .
                     $this->manager->getSunriseSunset ()->morningTwilight . '</td></tr>';
            $sunMoonTable .= '<tr><td>' . 'Sunrise </td><td>' .
                     $this->manager->getSunriseSunset ()->sunRise . '</td></tr>';
            $sunMoonTable .= '<tr><td>' . 'Sunset </td><td>' .
                     $this->manager->getSunriseSunset ()->sunSet . '</td></tr>';
            $sunMoonTable .= '<tr><td>' . 'PM Twilight </td><td>' .
                     $this->manager->getSunriseSunset ()->eveningTwilight . '</td></tr>';
            $sunMoonTable .= '<tr><td>' . 'Moonrise </td><td>' . $moonriseMoonset->getMoonRise () .
                     '</td></tr>';
            $sunMoonTable .= '<tr><td>' . 'Moonset </td><td>' . $moonriseMoonset->getMoonSet () .
                     '</td></tr>';
            $moonPhase = new NGC2244_Moon_Phase ( $date->getTimestamp () );
            $age = round ( $moonPhase->age () );
            $imageCount = $phaseArray [$age];
            $imageFile = plugin_dir_url ( __FILE__ ) . "../images/Moon-" . $imageCount . ".jpg";
            error_log ( 'image file:' . $imageFile );
            error_log ( 
                    'dayCount ' . $dayCount . ' age: ' . $age . ' calendar: ' .
                             $date->format ( 'Y M d' ) );
            $sunMoonTable .= '<td rowspan="3">' . 'Lunar day ' . $age .
                     '</td><td rowspan="3"><a href="' . plugin_dir_url ( __FILE__ ) .
                     '../images/Moon-' . $imageCount .
                     '-large.jpg"><img class="ngc2244_stars_at_night_lunar" src="' . $imageFile .
                     '" alt="day ' . $age . ' of Moon"></img></a></td>';
            
            $dayCount ++;
            $sunMoonTable .= '</table></div>';
        }
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
     * Returns a string containing the HTML to render a table of
     * night sky data inside a div.
     * This table is suitable for rendering
     * on a full sized display.
     * A leading table description is included as well.
     *
     * @return html table of event times
     */
    public function getSunAndMoonTableFull() {
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
        $days = $this->manager->getEndDate ()->diff ( $this->manager->getStartDate () )->days + 1;
        $xdate = new DateTime ( $this->manager->getStartDate ()->format ( 'm/d/Y' ) );
        for($i = 0; $i < $days; $i = $i + 3) {
            $moonPhase = new NGC2244_Moon_Phase ( $xdate->getTimestamp () );
            $xdate->add ( new DateInterval ( 'P3D' ) );
        }
        
        $sunMoonTable = '<div class="is-default"><table class="ngc2244_stars_at_night_standardTable">';
        if ($this->manager->get_sanitized_days () == 1) {
            $days = " day";
        } else {
            $days = " days";
        }
        $sunMoonTable .= '<thead><tr><td align="center" valign="middle" colspan="8" >Astronomical Times for ' .
                 $this->manager->get_sanitized_name () . ' (' . $this->manager->get_sanitized_lat () .
                 ', ' . $this->manager->get_sanitized_long () . ')<br>' . 'Starting ' .
                 $this->manager->getStartDate ()->format ( 'd M Y' ) . ', for the next ' .
                 $this->manager->get_sanitized_days () . $days . '</td></tr>';
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
        for($date = new DateTime ( $this->manager->getStartDate ()->format ( 'm/d/Y' ) ); $date <=
                 $this->manager->getEndDate (); $date->add ( new DateInterval ( 'P1D' ) )) {
            // both sun and moon require a timezone offset, although they use different units
            $remote_dtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
            $remote_dt = new DateTime ( $date->format ( 'm/d/Y' ), $remote_dtz );
            $sunTzOffset = $remote_dtz->getOffset ( $remote_dt ) / 3600;
            $moonTzOffset = $remote_dtz->getOffset ( $remote_dt ) / 60;
            
            // get the Sun times
            $this->manager->getSunriseSunset ()->calculate_sun_times ( 
                    $this->manager->get_sanitized_lat (), $this->manager->get_sanitized_long (), 
                    $sunTzOffset, $date );
            
            // get the Moon times
            $moonriseMoonset = new NGC2244_Moonrise_Moonset ();
            $moonriseMoonset->calculate_moon_times ( $this->manager->get_sanitized_lat (), 
                    $this->manager->get_sanitized_long (), $moonTzOffset, 
                    $this->manager->get_sanitized_timezone (), $date );
            
            // convert date for table rendering
            $dateStr = $date->format ( 'd M Y' );
            // get the tables
            $sunMoonTable .= '<tr><td>' . $dateStr . '</td><td>' .
                     $this->manager->getSunriseSunset ()->morningTwilight . '</td><td>';
            $sunMoonTable .= $this->manager->getSunriseSunset ()->sunRise . '</td><td>' .
                     $this->manager->getSunriseSunset ()->sunSet . '</td><td>';
            $sunMoonTable .= $this->manager->getSunriseSunset ()->eveningTwilight . '</td><td>' .
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
     * Returns a string containing the HTML to render a table of
     * planet data inside a div, suitable for mobile displays.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     *
     * @return table HTML
     */
    public function getPlanetTableMobile() {
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        // convert the php-compatible timezone name to heavens-above format
        $dateTime->setTimeZone ( new DateTimeZone ( $this->manager->get_sanitized_timezone () ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        $rows = $this->manager->getPlanetPasses ()->getPlanetData ( 
                $this->manager->getSunriseSunset (), $this->manager->get_sanitized_lat (), 
                $this->manager->get_sanitized_long () );
        
        $mercury = 0;
        $venus = 1;
        $mars = 2;
        $jupiter = 3;
        $saturn = 4;
        $uranus = 5;
        $neptune = 6;
        $pluto = 7;
        
        // table and column headers
        $planetTable = '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Mercury</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/mercury-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$mercury]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$mercury]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$mercury]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$mercury]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Venus</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/venus-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$venus]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$venus]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$venus]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$venus]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Mars</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/mars-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$mars]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$mars]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$mars]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$mars]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Jupiter</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/jupiter-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$jupiter]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$jupiter]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$jupiter]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$jupiter]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Saturn</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/saturn-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$saturn]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$saturn]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$saturn]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$saturn]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Uranus</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/uranus-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$uranus]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$uranus]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$uranus]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$uranus]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Neptune</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/neptune-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$neptune]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$neptune]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$neptune]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$neptune]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        
        // table and column headers
        $planetTable .= '<div class="is-mobile"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><th align="center" valign="middle" colspan="2">Pluto</th></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><th colspan="2" style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/pluto-sm.jpg"</th></tr></thead>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$pluto]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$pluto]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$pluto]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$pluto]->constellation . '</td></tr>';
            $planetTable .= '</table></div>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="2">No planetary data is available</td></tr></table></div>';
        }
        return $planetTable;
    }
    
    /**
     * Returns a string containing the HTML to render a table of
     * planet data inside a div, suitable for full sized displays.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     *
     * @return table HTML
     */
    public function getPlanetTableFull() {
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        // convert the php-compatible timezone name to heavens-above format
        $dateTime->setTimeZone ( new DateTimeZone ( $this->manager->get_sanitized_timezone () ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        $rows = $this->manager->getPlanetPasses ()->getPlanetData ( 
                $this->manager->getSunriseSunset (), $this->manager->get_sanitized_lat (), 
                $this->manager->get_sanitized_long () );
        
        $mercury = 0;
        $venus = 1;
        $mars = 2;
        $jupiter = 3;
        $saturn = 4;
        $uranus = 5;
        $neptune = 6;
        $pluto = 7;
        
        // table and column headers
        $planetTable = '<div class="is-default"><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><td align="center" valign="middle" colspan="11">Planetary Data for today</td></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><td style="background-color: #000000;"></td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/mercury-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/venus-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/mars-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/jupiter-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/saturn-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/uranus-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/neptune-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/pluto-sm.jpg"</td>';
        $planetTable .= '</tr>';
        $planetTable .= '<tr><td></td>';
        $planetTable .= '<td align="center">Mercury</td>';
        $planetTable .= '<td align="center">Venus</td>';
        $planetTable .= '<td align="center" >Mars</td>';
        $planetTable .= '<td align="center" >Jupiter</td>';
        $planetTable .= '<td align="center">Saturn</td>';
        $planetTable .= '<td align="center">Uranus</td>';
        $planetTable .= '<td align="center">Neptune</td>';
        $planetTable .= '<td align="center">Pluto</td>';
        $planetTable .= '</tr></thead><tbody>';
        $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime ( $rows [$mercury]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$venus]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$mars]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$jupiter]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$saturn]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$uranus]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$neptune]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$pluto]->rise );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime ( $rows [$mercury]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$venus]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$mars]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$jupiter]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$saturn]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$uranus]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$neptune]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$pluto]->meridian );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime ( $rows [$mercury]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$venus]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$mars]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$jupiter]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$saturn]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$uranus]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$neptune]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td>';
            $wkTime = new DateTime ( $rows [$pluto]->set );
            $wkTime->setTimeZone ( $wkDtz );
            $planetTable .= '<td>' . $wkTime->format ( "H:i" ) . '</td></tr>';
            
            $planetTable .= '<tr><td><b>Constellation</b></td>';
            $planetTable .= '<td>' . $rows [$mercury]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$venus]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$mars]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$jupiter]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$saturn]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$uranus]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$neptune]->constellation . '</td>';
            $planetTable .= '<td>' . $rows [$pluto]->constellation . '</td></tr>';
        } else {
            // no matching days were found
            $planetTable .= '<tr><td colspan="11">No planetary data is available at this time</td></tr>';
        }
        $planetTable = $planetTable . '</tbody></table></div>';
        return $planetTable;
    }
    
    /*
     * Returns a string containing the HTML to render a table of
     * ISS data inside a div, suitable for mobile displays.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     *
     * @return table HTML
     */
    public function getISSTableMobile() {
        $timezone = $this->manager->get_sanitized_timezone ();
        $lat = $this->manager->get_sanitized_lat ();
        $long = $this->manager->get_sanitized_long ();
        $startDate = $this->manager->getStartDate ();
        $endDate = $this->manager->getEndDate ();
        
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        // convert the php-compatible timezone name to heavens-above format
        $dateTime->setTimeZone ( new DateTimeZone ( $timezone ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        // just take a wild guess as to the location altitude, in meters
        $locationAlt = 300;
        $url = "http://www.heavens-above.com/PassSummary.aspx?satid=25544&lat=" . $lat;
        $url = $url . "&lng=" . $long . "&loc=Unspecified&alt=" . $locationAlt;
        // $url = $url . "&tz=" . $heavensAboveTZ;
        $rows = $this->manager->getSatellitePasses ()->getSatelliteData ( $url, $startDate, 
                $endDate );
        
        // table and column headers
        $issTable = '<div class="is-mobile">';
        if (is_null ( $rows )) {
            // no matching days were found
            $issTable .= '<table class="ngc2244_stars_at_night_standardTable"><thead><tr><th align="center" valign="middle" colspan="2">ISS Passes</th></tr>';
            $issTable .= '<tr><td colspan="2">No ISS passes during this period</td></tr></table></div>';
        } else {
            $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
            $wkDT = new DateTime ( "now" );
            foreach ( $rows as $row ) {
                $wkTime = new DateTime ( $row->date . $wkDT->format ( " Y " ) . $row->startTime );
                $wkTime->setTimeZone ( $wkDtz );
                $currentDate = $wkTime->format ( "d M" );
                $issTable .= '<table class="ngc2244_stars_at_night_standardTable"><thead><tr><th align="center" valign="middle" colspan="2">ISS Pass: ' .
                         $currentDate . '</th></tr></thead>';
                $issTable .= '<tr><td>Magnitude</td><td>' . $row->magnitude . '</td></tr>';
                $wkTime = new DateTime ( $row->startTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<tr><td>Start Time</td><td>' . $wkTime->format ( "H:i:s" ) .
                         '</td></tr>';
                $issTable .= '<tr><td>Start Altitude</td><td>' . $row->startAltitude . '</td></tr>';
                $issTable .= '<tr><td>Start Azimuth</td><td>' . $row->startAzimuth . '</td></tr>';
                $wkTime = new DateTime ( $row->highTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<tr><td>High Time</td><td>' . $wkTime->format ( "H:i:s" ) . '</td></tr>';
                $issTable .= '<tr><td>High Altitude</td><td>' . $row->highAltitude . '</td></tr>';
                $issTable .= '<tr><td>High Azimuth</td><td>' . $row->highAzimuth . '</td></tr>';
                $wkTime = new DateTime ( $row->endTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<tr><td>End Time</td><td>' . $wkTime->format ( "H:i:s" ) . '</td></tr>';
                $issTable .= '<tr><td>End Altitude</td><td>' . $row->endAltitude . '</td></tr>';
                $issTable .= '<tr><td>End Azimuth</td><td>' . $row->endAzimuth . '</td></tr>';
                $issTable .= '</table>';
            }
            $issTable .= '</div>';
        }
        return $issTable;
    }
    
    /**
     * Returns a string containing the HTML to render a table of
     * ISS pass data inside a div.
     * Suitable for full sized displays.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     *
     * @return table HTML
     */
    public function getISSTableFull() {
        $timezone = $this->manager->get_sanitized_timezone ();
        $lat = $this->manager->get_sanitized_lat ();
        $long = $this->manager->get_sanitized_long ();
        $startDate = $this->manager->getStartDate ();
        $endDate = $this->manager->getEndDate ();
        
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        // convert the php-compatible timezone name to heavens-above format
        $dateTime->setTimeZone ( new DateTimeZone ( $timezone ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        // just take a wild guess as to the location altitude, in meters
        $locationAlt = 300;
        $url = "http://www.heavens-above.com/PassSummary.aspx?satid=25544&lat=" . $lat;
        $url = $url . "&lng=" . $long . "&loc=Unspecified&alt=" . $locationAlt;
        // $url = $url . "&tz=" . $heavensAboveTZ;
        $rows = $this->manager->getSatellitePasses ()->getSatelliteData ( $url, $startDate, 
                $endDate );
        // table and column headers
        $imageFileIssSmall = plugin_dir_url ( __FILE__ ) . "../images/iss-small.jpg";
        $imageFileIssLarge = plugin_dir_url ( __FILE__ ) . "../images/iss-large.jpg";
        $issTable = '<div class="is-default"><table class="ngc2244_stars_at_night_standardTable">';
        $issTable .= '<thead>';
        $issTable .= '<tr><td align="center" valign="middle" colspan="11">Visible ISS Passes</td></tr>';
        $issTable .= '<tr><td align="center" valign="middle" colspan="11">';
        $issTable .= '<a href="' . $imageFileIssLarge . '">';
        $issTable .= '<img class="ngc2244_stars_at_night_satellite" ';
        $issTable .= 'src="' . $imageFileIssSmall .
                 '" alt="ISS image. Credit: NASA" /></a></td></tr>';
        $issTable .= '<tr><td align="center" rowspan="2" valign="middle">Date</td>';
        $issTable .= '<td align="center">Brightness</td>';
        $issTable .= '<td align="center" valign="top" colspan="3">Start</td>';
        $issTable .= '<td align="center" colspan="3">Highest point</td>';
        $issTable .= '<td align="center" colspan="3">End</td></tr>';
        $issTable .= '<tr><td align="center">(mag)</td>';
        $issTable .= '<td align="center">Time</td><td>Alt.</td><td>Az.</td>';
        $issTable .= '<td align="center">Time</td><td>Alt.</td><td>Az.</td>';
        $issTable .= '<td align="center">Time</td><td>Alt.</td><td>Az.</td></tr></thead>';
        if (! is_null ( $rows )) {
            $wkDtz = new DateTimeZone ( $timezone );
            $wkDT = new DateTime ( "now" );
            foreach ( $rows as $row ) {
                // this could be inaccurate at the new year
                $wkTime = new DateTime ( $row->date . $wkDT->format ( " Y " ) . $row->startTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<tr><td>' . $wkTime->format ( "d M" ) . '</td>';
                $issTable .= '<td>' . $row->magnitude . '</td>';
                $wkTime = new DateTime ( $row->startTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<td>' . $wkTime->format ( "H:i:s" ) . '</td>';
                $issTable .= '<td>' . $row->startAltitude . '</td>';
                $issTable .= '<td>' . $row->startAzimuth . '</td>';
                $wkTime = new DateTime ( $row->highTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<td>' . $wkTime->format ( "H:i:s" ) . '</td>';
                $issTable .= '<td>' . $row->highAltitude . '</td>';
                $issTable .= '<td>' . $row->highAzimuth . '</td>';
                $wkTime = new DateTime ( $row->endTime );
                $wkTime->setTimeZone ( $wkDtz );
                $issTable .= '<td>' . $wkTime->format ( "H:i:s" ) . '</td>';
                $issTable .= '<td>' . $row->endAltitude . '</td>';
                $issTable .= '<td>' . $row->endAzimuth . '</td></tr>';
            }
        } else {
            // no matching days were found
            $issTable .= '<tr><td colspan="11">No visible ISS passes during this time period</td></tr>';
        }
        $issTable = $issTable . '</table></div>';
        return $issTable;
    }
    public function getIridiumTableMobile() {
        return '';
    }
    
    /**
     * Returns a string containing the HTML to render a table of
     * Iridium satellite pass data inside a div.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     * Suitable for a full sized display
     *
     * @return table HTML
     */
    public function getIridiumTableFull() {
        $iridiumDays = (($this->manager->get_sanitized_days () > 7) ? 7 : $this->manager->get_sanitized_days ());
        $iridiumEndDate = new DateTime ( $this->manager->getStartDate ()->format ( 'm/d/Y' ) );
        $iridiumEndDate->add ( new DateInterval ( 'P' . ($iridiumDays - 1) . 'D' ) );
        // error_log ( 'enddate ' . $this->endDate->format ( 'm/d/Y' ) );
        
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        // convert the php-compatible timezone name to heavens-above format
        $dateTime->setTimeZone ( new DateTimeZone ( $this->manager->get_sanitized_timezone () ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        $rows = $this->manager->getSatellitePasses ()->getSatelliteData ( 'IridiumFlares.aspx', '', 
                $this->manager->get_sanitized_lat (), $this->manager->get_sanitized_long (), 
                $this->manager->getStartDate (), $this->manager->getEndDate (), 
                $this->manager->get_sanitized_refresh (), 
                $this->manager->get_sanitized_suppressDegrees () );
        $daysStr = "";
        if ($this->manager->get_sanitized_days () > 7) {
            $daysStr = " for the next 7 days";
        }
        // table and column headers
        $iridiumTable = '<div class="is-default"><table class="ngc2244_stars_at_night_standardTable"><thead>';
        $iridiumTable .= '<tr><td align="center" valign="middle" colspan="6">Visible Iridium Flares' .
                 $daysStr . '</td></tr>';
        $iridiumTable .= '<tr><td colspan="6"><img class="ngc2244_stars_at_night_satellite" src="' .
                 plugin_dir_url ( __FILE__ ) . '../images/iridium-flare.jpg"</td></tr>';
        $iridiumTable .= '<tr><td align="center" valign="middle">Date</td>';
        $iridiumTable .= '<td align="center">Time</td>';
        $iridiumTable .= '<td align="center">Magnitude</td>';
        $iridiumTable .= '<td align="center">Altitude</td>';
        $iridiumTable .= '<td align="center">Azimuth</td>';
        $iridiumTable .= '<td align="center">Satellite</td></tr></thead>';
        if (! is_null ( $rows )) {
            $wkDtz = new DateTimeZone ( $this->manager->get_sanitized_timezone () );
            $wkDT = new DateTime ( "now" );
            foreach ( $rows as $row ) {
                // table row
                // this could be inaccurate at the new year
                $wkTime = new DateTime ( $row->date . $wkDT->format ( " Y " ) . $row->time );
                $wkTime->setTimeZone ( $wkDtz );
                $iridiumTable .= '<tr><td>' . $wkTime->format ( "d M" ) . '</td>';
                $wkTime = new DateTime ( $row->time );
                $wkTime->setTimeZone ( $wkDtz );
                $iridiumTable .= '<td>' . $wkTime->format ( "H:i:s" ) . '</td>';
                $iridiumTable .= '<td>' . $row->magnitude . '</td>';
                $iridiumTable .= '<td>' . $this->getDegreeMarkup ( $row->altitude, 
                        $suppressDegrees ) . '</td>';
                $iridiumTable .= '<td>' . $this->getDegreeMarkup ( $row->azimuth, $suppressDegrees ) .
                         '</td>';
                $iridiumTable .= '<td>' . $row->satellite . '</td></tr>';
            }
        } else {
            // no matching days were found
            $iridiumTable .= '<tr><td colspan="6">No visible Iridium Flares during this time period</td></tr>';
        }
        $iridiumTable = $iridiumTable . '</table></div>';
        return $iridiumTable;
    }
}
