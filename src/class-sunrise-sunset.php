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
    public $sunRise;
    public $sunSet;
    public $morningTwilight;
    public $eveningTwilight;
    
    /**
     *
     * @param float $lat
     *            latitude of location
     * @param float $long
     *            longitude of location
     * @param int $sunTzOffset
     *            local timezone offset of location
     * @param DateTime $date
     *            date of calculation
     */
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
        $this->sunRise = date_sunrise ( strtotime ( $date->format('m/d/Y') ), SUNFUNCS_RET_STRING, $lat, $long, 
                $zenith, $sunTzOffset );
        $this->sunSet = date_sunset ( strtotime ( $date->format('m/d/Y') ), SUNFUNCS_RET_STRING, $lat, $long, 
                $zenith, $sunTzOffset );
        
        // get the twilight times, which we define as 90 minutes before sunrise, and after sunset
        $this->morningTwilight = $this->calculateTwilight ( $date, $sunTzOffset, $this->sunRise, 
                (- 90 * 60) );
        $this->eveningTwilight = $this->calculateTwilight ( $date, $sunTzOffset, $this->sunSet, 
                (90 * 60) );
    }
    
    /**
     * This method can be used to calculate early morning or late evening
     * astronomical twilight.
     * By definition this is 90 minutes before sunrise
     * or 90 minutes after sunset.
     *
     * @param DateTime $date
     *            day for which the calculation is being made
     * @param int $tzOffset
     *            timezone offset in seconds
     * @param string $sunTime
     *            either sunrise or sunset, string hh:mm 24 hr format
     * @param int $delta
     *            use -90 for morning, +90 for evening
     * @return twilight string in hh:mm format
     */
    private function calculateTwilight($date, $tzOffset, $sunTime, $delta) {
        $todayStr = $date->format('m/d/Y') . " " . $sunTime;
        $todayTimestamp = strtotime ( $todayStr );
        $twilight = $todayTimestamp + $delta + $tzOffset;
        $dateTime = new DateTime ();
        $dateTime = $dateTime->setTimestamp ( $twilight );
        $twilightStr = $dateTime->format ( 'H:i' );
        return $twilightStr;
    }
}
