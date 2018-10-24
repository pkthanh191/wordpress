<?php
/*
Plugin Name: Simple:Press V5 Importer Framework
Plugin URI: http://simple-press.com
Description: A framework to build Data Importers for Simple:Press V5
Version: 3.0
Author: Andy Staines & Steve Klasen
Author URI: http://simple-press.com
WordPress Version 3.3 and above
Simple-Press Version 5.0.0 and above
*/

/*  Copyright 2012  Andy Staines & Steve Klasen

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    For a copy of the GNU General Public License, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

# ------------------------------------------------------------------
# Define Constants
# ------------------------------------------------------------------
	define('SPI_URL',			WP_PLUGIN_URL.'/sp-import/');	# Plugin folder url
	define('SPI_DIR',			WP_PLUGIN_DIR.'/sp-import/');	# Plugin path

# ------------------------------------------------------------------
# Action/Filter Hooks
# Create new menu item in the WP Simple:Press menu
# ------------------------------------------------------------------
add_action('admin_menu', 				'spi_menu');

# ------------------------------------------------------------------------------------------
# Determine quickly if admin and then if importer admin page load

if(is_admin() && isset($_GET['page']) && stristr($_GET['page'], 'sp-import/')) {
	add_action('admin_print_styles', 	'spi_css');
	add_action('admin_enqueue_scripts', 'spi_scripts');
}

# ------------------------------------------------------------------
# spi_menu()
# Add the importer to SP menu item into th SP menu
# ------------------------------------------------------------------
function spi_menu() {
	$url = 'sp-import/admin/spimport-setup.php';
	add_submenu_page('simple-press/admin/panel-forums/spa-forums.php', esc_attr('Importer'), esc_attr('Importer'), 'read', $url);
}

# ------------------------------------------------------------------
# spa_load_admin_css()
# Loads up the forum admin CSS
# ------------------------------------------------------------------
function spi_css() {
	wp_register_style('spImportStyle', SPI_URL.'css/spimport.css');
	wp_enqueue_style( 'spImportStyle');
}

# ------------------------------------------------------------------
# spi_scripts()
# Load up the javascript we need
# ------------------------------------------------------------------
function spi_scripts() {
	wp_register_script('jquery', WPINC.'/js/jquery/jquery.js', false, false, false);
	wp_enqueue_script('jquery');
	wp_enqueue_script('spi', SPI_URL.'jscript/spimport.js', array('jquery'), false);
}

?>