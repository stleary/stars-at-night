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
     */
    public static function get_iss_data($lat, $long, $timezone) {
        // convert php tz to heavens above expected format
        $dateTime = new DateTime ();
        $dateTime->setTimeZone ( new DateTimeZone ( $timezone ) );
        $heavensAboveTZ = $dateTime->format ( 'T' );
        
        // just take a wild guess as to the location altitude, in meters
        $locationAlt = 300;
        $url = "http://www.heavens-above.com/PassSummary.aspx?satid=25544&lat=" . 
            $lat . "&lng=" . $long . "&loc=Unspecified&alt=" . $locationAlt . 
            "&tz=" . $heavensAboveTZ;
        // we are not changing the http args
        $response = file_get_contents ( $url );
        // $htmlBody = htmlentities($body);
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
            $issTable = "<div><table class='ngc2244_stars_at_night_standardTable'>" . 
                '<thead><tr><td align="center" rowspan="2" valign="middle">Date</td><td align="center">Brightness</td><td align="center" valign="top" colspan="3">Start</td><td align="center" colspan="3">Highest point</td>     <td align="center" colspan="3">End</td></tr>' .
                       '<tr>                                                        <td align="center">(mag)</td>     <td align="center">Time</td><td>Alt.</td><td>Az.</td> <td align="center">Time</td><td>Alt.</td><td>Az.</td> <td align="center">Time</td><td>Alt.</td><td>Az.</td></tr></thead>';
            foreach ( $rows as $row ) {
                $cols = $row->childNodes;
                if ($cols->length == 12) {
                    $day = $cols->item ( 0 )->nodeValue;
                    $magnitude = $cols->item ( 1 )->nodeValue;
                    $startTime = $cols->item ( 2 )->nodeValue;
                    $startAlt = htmlentities($cols->item ( 3 )->nodeValue);
                    $startAlt = str_replace("&Acirc;", "", $startAlt);
                    $startAz = $cols->item ( 4 )->nodeValue;
                    $highTime = $cols->item ( 5 )->nodeValue;
                    $highAlt = htmlentities($cols->item ( 6 )->nodeValue);
                    $highAlt = str_replace("&Acirc;", "", $highAlt);
                    $highAz = $cols->item ( 7 )->nodeValue;
                    $endTime = $cols->item ( 8 )->nodeValue;
                    $endAlt = htmlentities($cols->item ( 9 )->nodeValue);
                    $endAlt = str_replace("&Acirc;", "", $endAlt);
                    $endAz = $cols->item ( 10 )->nodeValue;
                    // skip pass type which is always 'visible'
                }
                $issTable = $issTable . "<tr><td>" . $day . "</td><td>" . $magnitude . "</td><td>" . $startTime . 
                    "</td><td>" . $startAlt . "</td><td>" . $startAz . "</td><td>" . $highTime . 
                    "</td><td>" . $highAlt . "</td><td>" . $highAz . "</td><td>" . $endTime . 
                    "</td><td>" . $endAlt . "</td><td>" . $endAz . "</td></tr>";
            }
            $issTable = $issTable . '</table></div>';
            return $issTable;
        } else {
            return "body not found";
        }
    }
}
