<?php
/**
 * Plugin Name:       The Stars at Night
 * Plugin URI:        http://notforlong.org/StarsAtNight
 * Description:       A WordPress plugin to display astronomical information,
 *                    given the latitude, longitude, timezone, and date.
 * Version:           1.0
 * Author:            Sean Leary
 * Author URI:        http://notforlong.org
 */
 
/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * If this file is called directly, e.g. via REST API, then abort.
 * Should only be called from the WordPress framework.
 */
if ( !defined( 'WPINC' ) ) {
    die;
}

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
