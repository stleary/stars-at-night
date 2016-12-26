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

/**
 * This class is just a helper, it holds the iss functionality
 */
class ISS_Passes {
    /**
     * Returns a string containing the HTML to render a table of
     * ISS pass data inside a div.
     * A leading table description is included as well.
     * The data is obtained from an external website. This may affect rendering time.
     *
     * @param $lat latitude
     *            of the viewer
     * @param $long longitude
     *            of the viewer
     * @param $timezone timezone
     *            of the viewer
     * @return table HTML
     */
    public static function get_iss_table($lat, $long, $timezone) {
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        $dateTime->setTimeZone ( new DateTimeZone ( $timezone ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        // just take a wild guess as to the location altitude, in meters
        $locationAlt = 300;
        $url = "http://www.heavens-above.com/PassSummary.aspx?satid=25544&lat=" . $lat;
        $url = $url . "&lng=" . $long . "&loc=Unspecified&alt=" . $locationAlt;
        $url = $url . "&tz=" . $heavensAboveTZ;
        /**
         * Can't rely on using file_get_contents() since a php.ini server
         * config may dissallow use of this method: allow_url_fopen=0
         * Instead, use wp_remote_get(), which returns an array or a WP_Error.
         */
        $response = wp_remote_get($url);
        $response = wp_remote_retrieve_body($response);
        $doc = new DOMDocument ();
        // set error level
        $internalErrors = libxml_use_internal_errors ( true );
        $doc->loadHTML ( $response );
        // Restore error level
        libxml_use_internal_errors ( $internalErrors );
        $doc->preserveWhiteSpace = false;
        $domXPath = new DOMXpath ( $doc );
        $rows = $domXPath->query ( "//*[@class='clickableRow']" );
        if (! is_null ( $rows )) {
            // table and column headers
            $issTable = '<div>Visible ISS Passes for the Next 10 Days';
            $issTable .= '<table class="ngc2244_stars_at_night_standardTable">';
            $issTable .= '<thead><tr><td align="center" rowspan="2" valign="middle">Date</td>';
            $issTable .= '<td align="center">Brightness</td>';
            $issTable .= '<td align="center" valign="top" colspan="3">Start</td>';
            $issTable .= '<td align="center" colspan="3">Highest point</td>';
            $issTable .= '<td align="center" colspan="3">End</td></tr>';
            $issTable .= '<tr><td align="center">(mag)</td>';
            $issTable .= '<td align="center">Time</td><td>Alt.</td><td>Az.</td>';
            $issTable .= '<td align="center">Time</td><td>Alt.</td><td>Az.</td>';
            $issTable .= '<td align="center">Time</td><td>Alt.</td><td>Az.</td></tr></thead>';
            // column data
            foreach ( $rows as $row ) {
                $cols = $row->childNodes;
                if ($cols->length == 12) {
                    $day = $cols->item ( 0 )->nodeValue;
                    $magnitude = $cols->item ( 1 )->nodeValue;
                    $startTime = $cols->item ( 2 )->nodeValue;
                    $startAlt = htmlentities ( $cols->item ( 3 )->nodeValue );
                    $startAlt = str_replace ( "&Acirc;", "", $startAlt );
                    $startAz = $cols->item ( 4 )->nodeValue;
                    $highTime = $cols->item ( 5 )->nodeValue;
                    $highAlt = htmlentities ( $cols->item ( 6 )->nodeValue );
                    $highAlt = str_replace ( "&Acirc;", "", $highAlt );
                    $highAz = $cols->item ( 7 )->nodeValue;
                    $endTime = $cols->item ( 8 )->nodeValue;
                    $endAlt = htmlentities ( $cols->item ( 9 )->nodeValue );
                    $endAlt = str_replace ( "&Acirc;", "", $endAlt );
                    $endAz = $cols->item ( 10 )->nodeValue;
                    // skip pass type which is always 'visible'
                }
                // table row
                $issTable .= '<tr><td>' . $day . '</td>';
                $issTable .= '<td>' . $magnitude . '</td>';
                $issTable .= '<td>' . $startTime . '</td>';
                $issTable .= '<td>' . $startAlt . '</td>';
                $issTable .= '<td>' . $startAz . '</td>';
                $issTable .= '<td>' . $highTime . '</td>';
                $issTable .= '<td>' . $highAlt . '</td>';
                $issTable .= '<td>' . $highAz . '</td>';
                $issTable .= '<td>' . $endTime . '</td>';
                $issTable .= '<td>' . $endAlt . '</td>';
                $issTable .= '<td>' . $endAz . '</td></tr>';
            }
            // end of table
            $issTable = $issTable . '</table></div>';
            return $issTable;
        } else {
            return "body not found";
        }
    }
}
