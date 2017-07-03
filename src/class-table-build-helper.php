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
 * for Stars_At_Night_Manager
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
}
