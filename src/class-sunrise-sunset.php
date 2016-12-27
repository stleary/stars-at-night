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
class NGC2244_Sunrise_Sunset {
    private $sunRise;
    private $sunSet;
    private $morningTwilight;
    private $eveningTwilight;

    public function calculate_sun_times($lat, $long, $sunTzOffset, $date) {
        /**
         * The builtin php lib uses the Solar zenith position, but has a flawed default value.
         * So we use our own instead. See
         * http://grokbase.com/t/php/php-bugs/09932wqn2a/49448-new-sunset-sunrise-zenith-default-values-wrong
         */
        $zenith = 90 + (50 / 60);

        
        /**
         * get Sun times.
         * returns a string like this: 07:10
         */
        $this->sunRise = date_sunrise ( strtotime ( 'now' ), SUNFUNCS_RET_STRING, $lat, $long, $zenith, 
                $sunTzOffset );
        $this->sunSet = date_sunset ( strtotime ( 'now' ), SUNFUNCS_RET_STRING, $lat, $long, $zenith, 
                $sunTzOffset );

        // get the twilight times, which we define as 90 minutes before sunrise, and after sunset
        $this->morningTwilight = $this->calculateTwilight ( $date, $sunTzOffset, $this->sunRise, (- 90 * 60) );
        $this->eveningTwilight = $this->calculateTwilight ( $date, $sunTzOffset, $this->sunSet, (90 * 60) );
        
    }
    
    /**
     * Returns a string containing the HTML to render a table of
     * night sky data inside a div.
     * A leading table description is included as well.
     * Parameters:
     *
     * @param $name name
     *            of location
     * @param $lat latitude
     *            of viewer
     * @param $long -
     *            longitude of viewer
     * @param $today -
     *            date of calculation
     * @param $moonriseMoonset -
     *            container for moon data
     * @return html table of event times
     */
    public function get_sun_moon_table($name, $lat, $long, $today, $moonriseMoonset) {
        $sunMoonTable = '<div ">' . $name . ' (' . $lat . ', ' . $long;
        $sunMoonTable .= ') Astronomical Times for ' . $today;
        $sunMoonTable .= '<table class="ngc2244_stars_at_night_standardTable">';

        $sunMoonTable .= '<thead><tr><td align="center" rowspan="2" valign="middle">Date</td>';
        $sunMoonTable .= '<td align="center">Morning</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Sunrise</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Sunset</td>';
        $sunMoonTable .= '<td align="center">Evening</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Moonrise</td>';
        $sunMoonTable .= '<td align="center" rowspan="2" valign="middle">Moonset</td></tr>';
        $sunMoonTable .= '<tr><td>Twilight</td><td>Twilight</td></tr></thead>';
        // column data
        $sunMoonTable .= '<tr><td>' . $today . '</td><td>' . $this->morningTwilight . '</td><td>';
        $sunMoonTable .= $this->sunRise . '</td><td>' . $this->sunSet  . '</td><td>';
        $sunMoonTable .= $this->eveningTwilight . '</td><td>' . $moonriseMoonset->getMoonRise();
        $sunMoonTable .= '</td><td>' . $moonriseMoonset->getMoonSet() . '</td></tr>';
        $sunMoonTable .= '</table></div>';
        return $sunMoonTable;
    }
    
    /**
     * This method can be used to calculate early morning or late evening
     * astronomical twilight.
     * By definition this is 90 minutes before sunrise
     * or 90 minutes after sunset.
     *
     * @param $today -
     *            day for which the calculation is being made
     * @param $tzOffset -
     *            timezone offset in seconds
     * @param $sunTime -
     *            either sunrise or sunset, string hh:mm 24 hr format
     * @param $delta -
     *            use -90 for morning, +90 for evening
     * @return twilight string in hh:mm format
     */
    private function calculateTwilight($today, $tzOffset, $sunTime, $delta) {
        $todayStr = $today . " " . $sunTime;
        $todayTimestamp = strtotime ( $todayStr );
        $twilight = $todayTimestamp + $delta + $tzOffset;
        $dateTime = new DateTime ();
        $dateTime = $dateTime->setTimestamp ( $twilight );
        $twilightStr = $dateTime->format ( 'H:i' );
        return $twilightStr;
    }
}
