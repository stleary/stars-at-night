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

include ('class-iss-data.php');
include ('class-iridium-data.php');

/**
 * This class is just a helper, it holds the heavens-above.com satellite functionality.
 * Currently it holds no state, so the methods could be declared static, but it may hold state in
 * the future.
 */
class NGC2244_Satellite_Passes {
    
    /**
     * Get an array of ISS visible passes from cache or server.
     * Each row of the array will be an NGC2244_ISS_Data instance. If the user makes multiple ISS
     * satellite requests with different location params, there may be more than one transient ISS
     * array in the cache.
     *
     * @param string $finalSegment
     *            final segment of the URL request
     * @param int $satId
     *            satellite id
     * @param int $lat
     *            local latitude
     * @param int $long
     *            local longitude
     * @param DateTime $startDate
     *            start of date range requested
     * @param DateTime $endDate
     *            end of date range requested
     * @param $refresh boolean
     *            if true, get from server instead of cache
     * @param $suppressDegrees boolean
     *            if true, omit degree symbol from table
     * @return array of matching rows, or null if no rows are forthcoming
     */
    public function getSatelliteData($finalSegment, $satId, $lat, $long, $startDate, $endDate, 
            $refresh, $suppressDegrees) {
        /**
         * Check transient data for cached satellite data.
         * The key to the cache is the $url, which uniquely captures
         * the location and satellite type. The value is an array of satellite objects that was
         * parsed from the 'body' tag content of the server response. Starting in v3.0,
         * only date field is populated in the first array element; it contains the
         * end date of the query, to ensure transient cache holds the required range of days.
         *
         * In v1.2, transient cache did not contain the initial record with the
         * query end date. Sometimes a satellite is not visible for many
         * days, causing the rows to be empty or sparse, and making it appear
         * that the cache was empty or stale.
         *
         * On upgrade, the first query may retrieve the v1.2 cache. If so, this
         * will most likely flush the cache. It will appear to be stale, due
         * to the first row date field being interpreted as the query end date.
         * If it does not flush the cache, that will simply mean the user requested
         * date range ended before the date of the first matched row. Either way
         * the code should work correctly.
         */
        
        // Uncomment when you want to clear the cache
        // error_log ( "delete cache for " . $url );
        // delete_transient ( $url );
        
        // just take a wild guess as to the location altitude, in meters
        $url = "http://www.heavens-above.com/?lat=" . $lat;
        
        $locationAlt = 300;
        $url = "http://www.heavens-above.com/' . $finalSegment . '?' . $satId . 'lat=" . $lat;
        $url = $url . "&lng=" . $long . "&loc=Unspecified&alt=" . $locationAlt;
        
        error_log ( "getting transient for " . $url );
        if (! $refresh && false !== ($data = get_transient ( $url ))) {
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
                    
                    $row = $data [0];
                    $rowDate = $row->date;
                    error_log ( "row date = " . $rowDate );
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
            error_log ( 'refresh [' . $refresh . '] cache not used, get from the server' );
        }
        /**
         * If we got this far, there was no match in the transient cache.
         * Need to send a server request and parse the response according to the satellite type,
         * adding it to the cache.
         */
        error_log ( 'GET request for satellite data: ' . $url );
        $data = NULL;
        if (strpos ( $url, 'satid=25544' ) !== false) {
            $data = $this->getISSDataFromServer ( $url );
        } else if (strpos ( $url, 'Iridium' ) !== false) {
            $data = $this->getIridiumDataFromServer ( $url );
        }
        if (! is_null ( $data )) {
            error_log ( 'cache a new transient for ' . $url );
            $count = 0;
            foreach ( $data as $row ) {
                error_log ( 'row ' . $count ++ );
                error_log ( $row->toString () );
            }
            set_transient ( $url, $data, DAY_IN_SECONDS * 10 );
            // filter by date and return matching rows
            $rows = $this->filterRowsByDate ( $data, $startDate, $endDate );
            return $rows;
        }
    }
    
    /**
     * Checks the dates on an array of rows, returning those that fall between the start and end
     * dates.
     * Source may be the transient cache or the parsed server response.
     *
     * @param object $data
     *            an array of rows of satellite data.
     * @param DateTime $startDate
     *            starting date for filter
     * @param DateTime $endDate
     *            ending date for filter
     * @return array of matching rows, or NULL if none match
     */
    private function filterRowsByDate($data, $startDate, $endDate) {
        /**
         * Cached data found, make sure it has rows.
         * The first row just contains the query end date, so don't count it.
         */
        $count = count ( $data );
        if ($count > 1) {
            /**
             * the server returns dates in m/d format so we have to infer the year.
             * Usually that will be the present year, but during the last 10 days of the year
             * some rows will be in the next year, so build the row datetimes accordingly.
             */
            $yearChange = false;
            $today = new DateTime ();
            $curYearStr = $today->format ( ' Y' );
            $nextYearStr = $curYearStr;
            $startCutoffDate = new DateTime ( "Dec 23" );
            $endCutoffDate = new DateTime ( "Dec 31" );
            if ($startDate >= $startCutoffDate && $startDate <= $endCutoffDate) {
                // error_log ( 'detected change of year' );
                $today->add ( new DateInterval ( 'P1Y' ) );
                $nextYearStr = $today->format ( ' Y' );
            }
            // build an array of the requested date range
            $rows = array ();
            $rowCount = 0;
            // skip first row, which contains only the query end date
            for($i = 1; $i < $count; $i ++) {
                $rowDateStr = $data [$i]->date;
                if (strpos ( $rowDateStr, 'Jan' ) !== false) {
                    $rowDateStr .= $nextYearStr;
                } else {
                    $rowDateStr .= $curYearStr;
                }
                
                $rowDate = new DateTime ( $rowDateStr );
                if ($startDate <= $rowDate && $endDate >= $rowDate) {
                    // error_log ( 'row ' . $i . ' found ' . $data [$i]->date );
                    $rows [$rowCount ++] = $data [$i];
                } else {
                    // error_log ( 'row ' . $i . ' skipped ' . $data [$i]->date );
                }
            }
            if ($rowCount > 0) {
                return $rows;
            } else {
                // error_log ( 'no rows matched' );
            }
        } else {
            // error_log ( 'No rows to filter' );
        }
        return NULL;
    }
    
    /**
     * Sends a request to the remote heavens-above server for ISS data for the next 10 days.
     * Response might be empty of row content if there are no ISS passes or if the server is unable
     * to respond.
     *
     * @param string $url
     *            The fully formed URL string for an HTTP GET request to the server
     * @return NGC2244_ISS_Data[]. May not have any rows other than the end date.
     */
    private function getISSDataFromServer($url) {
        // error_log ( 'getting iss data' );
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
        $rows = $domXPath->query ( "//*[@class='clickableRow']" );
        /**
         * Record the end query date unconditionally, which by convention
         * is 10 days from today.
         * Insert it into the first array position.
         */
        $issTable = array ();
        $item = new NGC2244_ISS_Data ();
        $tenday = new DateTime ();
        $tenday->add ( new DateInterval ( 'P10D' ) );
        $item->date = $tenday->format ( 'm/d/Y' );
        $issTable [0] = $item;
        
        if (! is_null ( $rows )) {
            // error_log ( 'iss rows found' );
            $issTableCount = 1;
            foreach ( $rows as $row ) {
                // error_log ( 'process iss row ' . $issTableCount );
                $cols = $row->childNodes;
                if ($cols->length == 12) {
                    // error_log ( 'iss 12 cols found' );
                    $data = new NGC2244_ISS_Data ();
                    $data->date = $cols->item ( 0 )->nodeValue;
                    $data->magnitude = $cols->item ( 1 )->nodeValue;
                    $data->startTime = $cols->item ( 2 )->nodeValue;
                    $data->startAltitude = htmlentities ( $cols->item ( 3 )->nodeValue );
                    $data->startAzimuth = $cols->item ( 4 )->nodeValue;
                    $data->highTime = $cols->item ( 5 )->nodeValue;
                    $data->highAltitude = htmlentities ( $cols->item ( 6 )->nodeValue );
                    $data->highAzimuth = $cols->item ( 7 )->nodeValue;
                    $data->endTime = $cols->item ( 8 )->nodeValue;
                    $data->endAltitude = htmlentities ( $cols->item ( 9 )->nodeValue );
                    $data->endAzimuth = $cols->item ( 10 )->nodeValue;
                    // skip pass type which is always 'visible'
                    $issTable [$issTableCount ++] = $data;
                } else {
                    error_log ( 'iss expect number of columns NOT found' );
                }
            }
        }
        return $issTable;
    }
    
    /**
     * Sends a request to the remote heavens-above server for ISS data for the next 10 days.
     * Response might be empty of row content if there are no ISS passes or if the server is unable
     * to respond.
     *
     * @param string $url
     *            The fully formed URL string for an HTTP GET request to the server
     * @return NGC2244_Iridium_Data[]. May not have any rows other than the end date.
     */
    private function getIridiumDataFromServer($url) {
        // error_log ( 'getting iridium data' );
        /**
         * Can't rely on using file_get_contents() since a php.ini server
         * config may dissallow use of this method: allow_url_fopen=0
         * Instead, use wp_remote_get(), which returns an array or a WP_Error.
         */
        $response = wp_remote_get ( $url );
        $response = wp_remote_retrieve_body ( $response );
        $doc = new DOMDocument ();
        // set error level
        $internalErrors = libxml_use_internal_errors ( true );
        $doc->loadHTML ( $response );
        // Restore error level
        libxml_use_internal_errors ( $internalErrors );
        $doc->preserveWhiteSpace = false;
        $domXPath = new DOMXpath ( $doc );
        $rows = $domXPath->query ( "//*[@class='clickableRow']" );
        /**
         * Record the end query dates unconditionally, which by convention
         * is 7 days from today.
         * Insert it into the first array position.
         */
        $iridiumTable = array ();
        $item = new NGC2244_Iridium_Data ();
        $sevenday = new DateTime ();
        $sevenday->add ( new DateInterval ( 'P7D' ) );
        $item->date = $sevenday->format ( 'm/d/Y' );
        $iridiumTable [0] = $item;
        if (! is_null ( $rows )) {
            $iridiumTableCount = 1;
            foreach ( $rows as $row ) {
                $cols = $row->childNodes;
                if ($cols->length == 8) {
                    $data = new NGC2244_Iridium_Data ();
                    $dayTime = $cols->item ( 0 )->nodeValue;
                    $dateTimeArray = explode ( ", ", $dayTime );
                    $data->date = $dateTimeArray [0];
                    $data->time = $dateTimeArray [1];
                    $data->magnitude = $cols->item ( 1 )->nodeValue;
                    $data->altitude = htmlentities ( $cols->item ( 2 )->nodeValue );
                    $data->azimuth = htmlentities ( $cols->item ( 3 )->nodeValue );
                    $data->satellite = $cols->item ( 4 )->nodeValue;
                    $iridiumTable [$iridiumTableCount ++] = $data;
                } else {
                    error_log ( 'iridium expected number of columns NOT found' );
                }
            }
        }
        return $iridiumTable;
    }
    
    /**
     * Convenience method to fix degree markup for heavens-above column values
     * that contain degree values, like altitude or azimuth, without losing
     * actual numeric or parenthesized chars.
     * Anything else will be stripped out.
     * An HTML escape for degree will be inserted after the numeric part
     * of the string.
     * Examples:
     * 45° -> 45&deg;
     * 358&Acirc; (N) -> 358&deg; (N)
     *
     * @param $value string
     *            $the value to be fixed
     *            #param $suppressDegrees boolean if true, omit degree symbol
     * @return the modified string.
     */
    private function getDegreeMarkup($value, $suppressDegrees) {
        $match = array ();
        // just in case, make sure something gets returned
        $newValue = $value;
        /**
         * Performs a Perl-style regex match for the first contiguous
         * set of numeric chars (we only expect 1 set).
         */
        if (preg_match ( '/[0-9]+/', $value, $match )) {
            if (! $suppressDegrees) {
                $newValue = $match [0] . '&deg; ';
            } else {
                $newValue = $match [0] . ' ';
            }
        }
        /**
         * Performs a regex match for a value captured in parens.
         */
        if (preg_match ( '/(\(.*\))/', $value, $match )) {
            $newValue .= $match [1];
        }
        return $newValue;
    }
}
