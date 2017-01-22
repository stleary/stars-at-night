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
 * This struct-like class represents ISS satellite cached data, as received from heavens-above.com.
 */
class NGC2244_Planet_Data {
    public $date; // date only matter on the first record
    public $name;
    public $rise;
    public $set;
    public $meridian;
    public $constellation;
    public $visibility;
    // required for debugging
    public function toString() {
        $str = "NGC2244_PLANET_DATA:" . "\n   date: " . $this->date . "\n   name: " . $this->name .
                 "\n   rise:" . $this->rise . "\n   set: " . $this->set . "\n   meridian: " .
                 $this->meridian . "\n   constellation: " . $this->constellation .
                 "\n   visibility: " .  $this->visibility . "\n";
        return $str;
    }
}