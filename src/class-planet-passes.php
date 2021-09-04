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

// Disallow direct access
defined ( 'ABSPATH' ) or die ();

include ('class-planet-data.php');

/**
 * This class is just a helper, it holds the heavens-above.com planet functionality.
 * Currently it holds no state, so the methods could be declared static, but it may hold state in
 * the future.
 */
class NGC2244_Planet_Passes {
    public $sunriseSunset;
    
    /**
     * Returns a string containing the HTML to render a table of
     * planet data inside a div.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     *
     * @param float $lat
     *            latitude of the viewer
     * @param float $long
     *            longitude of the viewer
     * @param string $timezone
     *            timezone of the viewer
     * @param NGC2244_Sunrise_Sunset $sunriseSunset
     *            current day sun data
     * @return table HTML
     */
    public function get_planet_table($lat, $long, $timezone, $sunriseSunset) {
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        // convert the php-compatible timezone name to heavens-above format
        $dateTime->setTimeZone ( new DateTimeZone ( $timezone ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        // just take a wild guess as to the location altitude, in meters
        $locationAlt = 300;
        $url = "http://www.heavens-above.com/PlanetSummary.aspx?lat=" . $lat;
        $url = $url . "&lng=" . $long . "&loc=Unspecified&alt=" . $locationAlt;
        // $url = $url . "&tz=" . $heavensAboveTZ;
        $rows = $this->getPlanetData ( $url, $sunriseSunset );
        
        $mercury = 0;
        $venus = 1;
        $mars = 2;
        $jupiter = 3;
        $saturn = 4;
        $uranus = 5;
        $neptune = 6;
        $pluto = 7;
        
        // table and column headers
        $planetTable = '<div><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><td align="center" valign="middle" colspan="11">Planetary Data for today</td></tr>';
        // most sure way of overriding the standardTable thead td style is with inline style
        $planetTable .= '<tr><td style="background-color: #000000;"></td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/mercury-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/venus-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/mars-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/jupiter-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/saturn-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/uranus-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/neptune-sm.jpg"</td>';
        $planetTable .= '<td style="background-color: #000000;"><img class="ngc2244_stars_at_night_planet" src="' . plugin_dir_url ( __FILE__ ) . '../images/pluto-sm.jpg"</td>';
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
        $wkDtz = new DateTimeZone($timezone);
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td><b>Rises</b></td>';
            $wkTime = new DateTime($rows [$mercury]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$venus]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$mars]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$jupiter]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$saturn]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$uranus]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$neptune]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$pluto]->rise);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td></tr>';
            
            $planetTable .= '<tr><td><b>Meridian</b></td>';
            $wkTime = new DateTime($rows [$mercury]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$venus]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$mars]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$jupiter]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$saturn]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$uranus]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$neptune]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$pluto]->meridian);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td></tr>';
            
            $planetTable .= '<tr><td><b>Sets</b></td>';
            $wkTime = new DateTime($rows [$mercury]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$venus]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$mars]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$jupiter]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$saturn]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$uranus]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$neptune]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td>';
            $wkTime = new DateTime($rows [$pluto]->set);
            $wkTime->setTimeZone($wkDtz);
            $planetTable .= '<td>' . $wkTime->format("H:i") . '</td></tr>';
            
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
    
    /**
     * Get an array of planet data from cache or server.
     * Each row of the array will be an NGC2244_Planet_Data instance. If the user makes multiple
     * requests with different location params, there may be more than one transient
     * array in the cache.
     *
     * @param string $url
     *            URL request for this location. For example,
     *            http://www.heavens-above.com/PlanetSummary.aspx?lat=30.891&lng=-98.4265&loc=Unspecified&alt=300&tz=CST
     * @param DateTime $sunriseSunset
     *            sun data for today
     * @return array of matching rows, or null if no rows are forthcoming
     */
    public function getPlanetData($url, $sunriseSunset) {
        /**
         * Check transient data for cached planetary data.
         * The key to the cache is the $url, which uniquely captures
         * the location and request type. The value is an array of planet objects that was
         * parsed from the 'body' tag content of the server response. Starting in v3.0,
         * only date field is populated in the first array element; it contains the
         * date of the query, to ensure transient cache holds the required day.
         */
        
        // Uncomment when you want to clear the cache
        // error_log ( "delete cache for " . $url );
        // delete_transient ( $url );
        error_log ( "getting transient for " . $url );
        if (false !== ($data = get_transient ( $url ))) {
            if (is_array ( $data )) {
                /**
                 * Must check the date range before filtering by rows, in case
                 * the existing cache is empty or sparse
                 */
                if (! empty ( $data )) {
                    error_log ( 'retrieved a transient for ' . $url );
                    $count = 0;
                    foreach ( $data as $row ) {
                        error_log ( 'row ' . $count ++ );
                        error_log ( $row->toString () );
                    }
                    
                    // all the dates are the same, just grab first one
                    $firstRow = $data [0];
                    $cacheDate = $firstRow->date;
                    error_log ( "cacheDate = " . $cacheDate );
                    $thisDay = new DateTime ( "today" );
                    $thisDayStr = $thisDay->format ( "m/d/Y" );
                    if (strcmp ( $cacheDate, $thisDayStr ) == 0) {
                        error_log ( 'transient data found for ' . $url );
                        error_log ( 'returning rows for ' . $url );
                        return $data;
                    } else {
                        error_log ( 'cache is stale, refresh from the server' );
                        delete_transient ( $url );
                    }
                } else {
                    error_log ( 'cache is empty, refresh from the server' );
                    delete_transient ( $url );
                }
            } else {
                // should never happen
                error_log ( 'Unexpected transient item, flushing the cache' );
                delete_transient ( $url );
            }
        } else {
            error_log ( 'cache is empty, refresh from the server' );
        }
        /**
         * If we got this far, there was no match in the transient cache.
         * Need to send a server request and parse the response according to the satellite type,
         * adding it to the cache.
         */
        error_log ( 'GET request for planet data: ' . $url );
        $data = $this->getPlanetDataFromServer ( $url, $sunriseSunset );
        if (! is_null ( $data )) {
            error_log ( 'cache a new transient for ' . $url );
            $count = 0;
            foreach ( $data as $row ) {
                error_log ( 'row ' . $count ++ );
                error_log ( $row->toString () );
            }
            set_transient ( $url, $data, DAY_IN_SECONDS );
        } else {
            error_log ( "no planet data available" );
        }
        return $data;
    }
    
    /**
     * Sends a request to the remote heavens-above server for ISS data for the next 10 days.
     * Response might be empty of row content if there are no ISS passes or if the server is unable
     * to respond.
     *
     * @param string $url
     *            The fully formed URL string for an HTTP GET request to the server
     * @param NGC2244_Sunrise_Sunset $sunriseSunset
     *            sun data for today
     * @return NGC2244_Planet_Data[]
     */
    private function getPlanetDataFromServer($url, $sunriseSunset) {
        // error_log ( 'getting planet data' )
        $this->sunriseSunset = $sunriseSunset;
        
        /**
         * Can't rely on using file_get_contents() since a php.ini server
         * config may dissallow use of this method: allow_url_fopen=0
         * Instead, use wp_remote_get(), which returns an array or a WP_Error.
         */
        $response = wp_remote_get ( $url );
        $response = wp_remote_retrieve_body ( $response );
        // error_log ( 'planet response received: ' . $response );
        $doc = new DOMDocument ();
        // set error level
        $internalErrors = libxml_use_internal_errors ( true );
        $doc->loadHTML ( $response );
        // Restore error level
        libxml_use_internal_errors ( $internalErrors );
        $doc->preserveWhiteSpace = false;
        $domXPath = new DOMXpath ( $doc );
        
        /**
         * our anchor is going to be the table that has a Mercury column header
         * table > thead > tr > td > Mercury
         */
        
        $table = $domXPath->query ( "//td[.='Mercury']/../../.." )->item ( 0 );
        $planetNames = $domXPath->query ( "//td[.='Mercury']/../*/text()", $table );
        $constellations = $domXPath->query ( "//td[.='Constellation']/../td/a/text()", $table );
        $meridians = $domXPath->query ( "//td[.='Meridian transit']/../td/text()", $table );
        $rises = $domXPath->query ( "//td[.='Rises']/../td/text()", $table );
        $sets = $domXPath->query ( "//td[.='Sets']/../td/text()", $table );
        
        $today = new DateTime ();
        $todayStr = $today->format ( 'm/d/Y' );
        
        /**
         * In this case data is an associate array key=planetname value=planet_data
         * For this table, every item has today's date, which is the transient expiration date.
         */
        $planetTable = array ();
        for($i = 0; $i < 8; ++ $i) {
            $data = new NGC2244_Planet_Data ();
            $data->date = $todayStr;
            $planetTable [$i] = $data;
        }
        
        if (! is_null ( $planetNames )) {
            $count = 0;
            // there is an empty td on far left, but it does not seem to be returned in the query
            foreach ( $planetNames as $planetName ) {
                $planetTable [$count]->name = $planetName->textContent;
                ++ $count;
            }
        }
        
        // fill in the planet data detail
        if (! is_null ( $constellations )) {
            $count = 0;
            foreach ( $constellations as $constellation ) {
                // only text nodes have a element, so don't skip first column
                $planetTable [$count]->constellation = $constellation->textContent;
                ++ $count;
            }
        }
        if (! is_null ( $meridians )) {
            $count = 0;
            foreach ( $meridians as $meridian ) {
                // first column is a label, so skip it
                if ($count == 0) {
                    ++ $count;
                } else {
                    $planetTable [$count - 1]->meridian = $meridian->textContent;
                    ++ $count;
                }
            }
        }
        if (! is_null ( $rises )) {
            $count = 0;
            foreach ( $rises as $rise ) {
                // first column is a label, so skip it
                if ($count == 0) {
                    ++ $count;
                } else {
                    $planetTable [$count - 1]->rise = $rise->textContent;
                    ++ $count;
                }
            }
        }
        if (! is_null ( $sets )) {
            $count = 0;
            foreach ( $sets as $set ) {
                // first column is a label, so skip it
                if ($count == 0) {
                    ++ $count;
                } else {
                    $planetTable [$count - 1]->set = $set->textContent;
                    ++ $count;
                }
            }
        }
        return $planetTable;
    }
    
    /**
     * Calculate visibility for this planet on this date.
     * Not used at the present time - this method is buggy
     * Result is a string with one of these values:
     * Not visible
     * Visible
     * Good
     * Prime
     *
     * @param NGC2244_Planet_Data $planetData
     *            populated planet values
     * @return visibility string
     */
    private function getVisibility($planetData) {
        $sunsetDate = new DateTime ( 'today ' . $this->sunriseSunset->sunSet );
        $darkDate = new DateTime ( 'today ' . $this->sunriseSunset->eveningTwilight );
        $sunset2Date = new DateTime ( $sunsetDate->format ( "m/d/Y H:i:s" ) );
        $sunset2Date->add ( new DateInterval ( 'PT2H' ) );
        $endDate = new DateTime ( 'tomorrow ' . "00:00:00" );
        $end2Date = new DateTime ( 'today ' . "22:00:00" );
        // error_log ( 'sunset: ' . $sunsetDate->format ( "m/d/Y H:i:s" ) );
        // error_log ( 'dark: ' . $darkDate->format ( "m/d/Y H:i:s" ) );
        // error_log ( 'sunset2: ' . $sunset2Date->format ( "m/d/Y H:i:s" ) );
        // error_log ( 'end: ' . $endDate->format ( "m/d/Y H:i:s" ) );
        // error_log ( 'end2: ' . $end2Date->format ( "m/d/Y H:i:s" ) );
        
        // resets all timestamps to today 00:00:00
        $riseDate = new DateTime ( 'today ' . $planetData->rise );
        $setDate = new DateTime ( 'today ' . $planetData->set );
        $meridianDate = new DateTime ( 'today ' . $planetData->meridian );
        
        if ($meridianDate < $riseDate) {
            $meridianDate->add ( new DateInterval ( 'P1D' ) );
            $setDate->add ( new DateInterval ( 'P1D' ) );
        } else if ($setDate < $meridianDate) {
            $setDate->add ( new DateInterval ( 'P1D' ) );
        }
        
        // error_log (
        // 'planet: ' . $planetData->name . ' rise: ' . $riseDate->format ( 'd H:i' ) .
        // ' merid: ' . $meridianDate->format ( 'd H:i' ) . ' set: ' .
        // $setDate->format ( 'd H:i' ) );
        
        $visibility = '';
        if ($setDate >= $sunsetDate && $setDate < $sunset2Date) {
            $visibility = 'Visible';
        } else if (($setDate >= $sunset2Date) && ($meridianDate < $sunsetDate)) {
            $visibility = 'Good';
        } else if (($meridianDate >= $sunsetDate) && ($meridianDate < $endDate)) {
            $visibility = 'Prime';
        } else if (($meridianDate >= $endDate) && ($riseDate < $endDate)) {
            $visibility = 'Good';
        } else if (($riseDate >= $endDate) && ($riseDate < $end2Date)) {
            $visibility = 'Visible';
        } else {
            $visibility = 'Not visible';
        }
        return $visibility;
    }
}
