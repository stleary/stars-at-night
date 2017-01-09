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
 * This struct-like class represents Iridium satellite cached data, as received from
 * heavens-above.com
 */
class NGC2244_Iridium_Data {
    public $date;
    public $time;
    public $magnitude;
    public $altitude;
    public $azimuth;
    public $satellite;
    // required for debugging
    public function toString() {
        $str = "NGC2244_Iridium_DATA:" . "\n   date: " . $this->date . "\n   time: " . $this->time .
                 "\n   magnitude: " . $this->magnitude . "\n   altitude: " . $this->altitude .
                 "\n   azimuth: " . $this->azimuth . "\n   satellite: " . $this->satellite . "\n";
        return $str;
    }
}