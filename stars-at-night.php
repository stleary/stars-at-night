<?php
/**
 * Plugin Name:       The Stars at Night
 * Plugin URI:        http://notforlong.org/the-stars-at-night/
 * Description:       A WordPress plugin to display astronomical information
 *                    for a specified location and date.
 * Version:           1.3
 * Author:            Sean Leary
 * Author URI:        http://notforlong.org
 */
 
/*
MIT License

Copyright (c) 2016 Sean Leary

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.*/

/**
 * If this file is called directly, e.g. via REST API, then abort.
 * Should only be called from the WordPress framework.
 */
defined( 'WPINC' ) or die;
// Disallow direct access (TODO: is this redundant?)
defined( 'ABSPATH' ) or die;

// WordPress activation 
function activate_stars_at_night() {
    require_once plugin_dir_path( __FILE__ ) . 'src/class-stars-at-night-activator.php';
    Stars_At_Night_Activator::activate();
}
register_activation_hook( __FILE__, 'activate_stars_at_night' );

// WordPress deactivation
function deactivate_stars_at_night() {
    require_once plugin_dir_path( __FILE__ ) . 'src/class-stars-at-night-deactivator.php';
    Stars_At_Night_Deactivator::deactivate('stars-at-night');
}
register_deactivation_hook( __FILE__, 'deactivate_stars_at_night' );

// WordPress plugin invocation
require plugin_dir_path( __FILE__) . 'src/class-stars-at-night-manager.php';
new Stars_At_Night_Manager();

?>
