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
        $url = $url . "&tz=" . $heavensAboveTZ;
        $rows = $this->getPlanetData ( $url, $sunriseSunset );
        // table and column headers
        $planetTable = '<div><table class="ngc2244_stars_at_night_standardTable">';
        $planetTable .= '<thead><tr><td align="center" valign="middle" colspan="11">Planetary Data</td></tr>';
        $planetTable .= '<tr><td></td>';
        $planetTable .= '<td align="center">Mercury</td>';
        $planetTable .= '<td align="center">Venus</td>';
        $planetTable .= '<td align="center" >Mars</td>';
        $planetTable .= '<td align="center" >Jupiter</td></tr>';
        $planetTable .= '<td align="center">Saturn</td>';
        $planetTable .= '<td align="center">Uranus</td>';
        $planetTable .= '<td align="center">Neptune</td>';
        $planetTable .= '<td align="center">Pluto</td>';
        $planetTable .= '</tr></thead><tbody>';
        if (! is_null ( $rows )) {
            $planetTable .= '<tr><td>Meridian</td>';
            $planetTable .= '<td>' . $rows ['Mercury']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Venus']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Mars']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Jupiter']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Saturn']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Uranus']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Neptune']->meridian . '</td>';
            $planetTable .= '<td>' . $rows ['Pluto']->meridian . '</td></tr>';
            
            $planetTable .= '<tr><td>Rises</td>';
            $planetTable .= '<td>' . $rows ['Mercury']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Venus']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Mars']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Jupiter']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Saturn']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Uranus']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Neptune']->rise . '</td>';
            $planetTable .= '<td>' . $rows ['Pluto']->rise . '</td></tr>';
            
            $planetTable .= '<tr><td>Sets</td>';
            $planetTable .= '<td>' . $rows ['Mercury']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Venus']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Mars']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Jupiter']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Saturn']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Uranus']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Neptune']->set . '</td>';
            $planetTable .= '<td>' . $rows ['Pluto']->set . '</td></tr>';
            
            $planetTable .= '<tr><td>Constellation</td>';
            $planetTable .= '<td>' . $rows ['Mercury']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Venus']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Mars']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Jupiter']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Saturn']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Uranus']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Neptune']->constellation . '</td>';
            $planetTable .= '<td>' . $rows ['Pluto']->constellation . '</td></tr>';
            
            $planetTable .= '<tr><td>Visibility</td>';
            $planetTable .= '<td>' . $rows ['Mercury']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Venus']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Mars']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Jupiter']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Saturn']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Uranus']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Neptune']->visibility . '</td>';
            $planetTable .= '<td>' . $rows ['Pluto']->visibility . '</td></tr>';
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
            error_log ( 'is array: ' . is_array ( $data ) );
            if (is_array ( $data )) {
                /**
                 * Must check the date range before filtering by rows, in case
                 * the existing cache is empty or sparse
                 */
                error_log ( 'is empty: ' . empty ( $data ) );
                error_log ( 'count: ' . count ( $data ) );
                if (! empty ( $data )) {
                    error_log ( 'retrieved a transient for ' . $url );
                    $x1 = 0;
                    foreach ( $data as $x ) {
                        error_log ( 'row ' . $x1 ++ );
                        error_log ( $x->toString () );
                    }
                    
                    $x = $data [0];
                    $x = $x->date;
                    error_log ( "x = " . $x );
                    $endQueryDay = new DateTime ( $data [0]->date );
                    if ($endDate <= $endQueryDay) {
                        // error_log ( 'transient data found for ' . $url );
                        $rows = $this->filterRowsByDate ( $data, $startDate, $endDate );
                        if (! is_null ( $rows )) {
                            error_log ( 'returning rows for ' . $url );
                            return $rows;
                        }
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
            $x1 = 0;
            foreach ( $data as $x ) {
                error_log ( 'row ' . $x1 ++ );
                error_log ( $x->toString () );
            }
            set_transient ( $url, $data, DAY_IN_SECONDS );
            return $data;
        }
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
     * @return NGC2244_Planet_Data[]. May not have any rows other than the date.
     */
    private function getPlanetDataFromServer($url, $sunriseSunset) {
        // error_log ( 'getting planet data' );
        /**
         * Can't rely on using file_get_contents() since a php.ini server
         * config may dissallow use of this method: allow_url_fopen=0
         * Instead, use wp_remote_get(), which returns an array or a WP_Error.
         */
        $response = wp_remote_get ( $url );
        $response = wp_remote_retrieve_body ( $response );
        // error_log ( 'iss response received' );
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
        $data = array ();
        $planetCount = count ( $planetNames );
        // The first element is empty
        for($i = 1; $i < $planetCount; ++ $i) {
            $data [$planetNames [i]] = new NGC2244_Planet_Data ();
            $data [$planetNames [i]]->date = todayStr;
        }
        
        // fill in the planet data detail
        if (! is_null ( $constellations )) {
            for($i = 1; $i < $planetCount; ++ $i) {
                $data [$planetNames [i]]->constellation = $constellations [$i]->textContent;
            }
        }
        if (! is_null ( $meridians )) {
            for($i = 1; $i < $planetCount; ++ $i) {
                $data [$planetNames [i]]->meridian = $meridians [$i]->textContent;
            }
        }
        if (! is_null ( $rises )) {
            for($i = 1; $i < $planetCount; ++ $i) {
                $data [$planetNames [i]]->rise = $rises [$i]->textContent;
            }
        }
        if (! is_null ( $sets )) {
            for($i = 1; $i < $planetCount; ++ $i) {
                $data [$planetNames [i]]->set = $sets [$i]->textContent;
            }
        }
        for($i = 1; $i < $planetCount; ++ $i) {
            $this->getVisibility ( $planetNames [i] );
        }
        return $data;
    }
    
    /**
     * Calculate visibility for this planet on this date.
     * Result is a string with one of these values:
     * Not visible
     * Visible
     * Good
     * Prime
     *
     * @param NGC2244_Planet_Data $planetData
     *            update with visibility, already contains date
     */
    private function getVisibility(NGC2244_Planet_Data $planetData) {
    }
}
