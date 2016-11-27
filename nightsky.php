<?php
/**
 * Plugin Name:       nightsky 
 * Plugin URI:        http://www.github.com/stleary/Night-Sky
 * Description:       A WordPress plugin to display astronomical information, given latitude, longitude, timezone, and date.
 * Version:           0.1
 * Author:            Sean Leary
 * Author URI:        http://www.johnjleary.com
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
 * This code provides the hooks and actions needed by a WordPress plugin
 */

// If this file is called directly, e.g. via REST API, then abort. 
// Should only be called from the WordPress framework.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// WordPress activation 
function activateNightSky() {
    require_once plugin_dir_path( __FILE__ ) . 'src/NightSkyActivator.php';
    nightSkyActivator::activate();
}
register_activation_hook( __FILE__, 'activateNightSky' );

// WordPress deactivation
function deactivateNightSky() {
    require_once plugin_dir_path( __FILE__ ) . 'src/nightSkyDeactivator.php';
    NightSkyDeactivator::deactivate('nightsky');
}
register_deactivation_hook( __FILE__, 'deactivateNightSky' );

// WordPress plugin invocation
require plugin_dir_path( __FILE__) . 'src/NightSkyManager.php';
new NightSkyManager();

?>
