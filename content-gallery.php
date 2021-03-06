<?php
/*
Plugin Name: Optimized Content Gallery
Plugin URI: http://github.com/alxconn/optimized-content-gallery
Description: Used to create a customizable rotating image gallery anywhere within your WordPress site.
Version: 0.1
Author: alxconn
Author URI: http://www.indygeek.net
*/

/*  Updated by IndyGeek.net Dev Team

	Original Plugin
	Copyright 2009  iePlexus  (email : info@ieplexus.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* options page */
$options_page = get_option('siteurl') . '/wp-admin/admin.php?page=optimized-content-gallery/options.php';
/* Adds our admin options under "Options" */
function gallery_options_page() {
	add_options_page('Optimized Content Gallery Options', 'Optimized Content Gallery', 10, 'Optimized-content-gallery/options.php');
}

function add_frontend_dependencies() {
	$gallery_path =  get_bloginfo('wpurl')."/wp-content/plugins/optimized-content-gallery/";
	
	wp_enqueue_style( jd_gallery_dyn, $gallery_path."css/jd.gallery.css.php", null ,1, screen );
	wp_enqueue_style( jd_gallery, $gallery_path."css/jd.gallery.css", jd_gallery_dyn ,1, screen );
}
function gallery_styles() {
    /* The next lines figures out where the javascripts and images and CSS are installed,
    relative to your wordpress server's root: */
    $gallery_path =  get_bloginfo('wpurl')."/wp-content/plugins/optimized-content-gallery/";


	
    /* This method of adding scripts is depreciated and unreliable, transitioning to wp_enqueue_scripts and wp_enqueue_styles: */
	$galleryscript = "
	<!-- begin gallery scripts -->
	<script type=\"text/javascript\" src=\"".$gallery_path."scripts/mootools.v1.11.js\"></script>
	<script type=\"text/javascript\" src=\"".$gallery_path."scripts/jd.gallery.js.php\"></script>
	<script type=\"text/javascript\" src=\"".$gallery_path."scripts/jd.gallery.transitions.js\"></script>
	<!-- end gallery scripts -->\n";
	
	/* Output $galleryscript as text for our web pages: */
	echo($galleryscript);
}
/* TODO:  Lots of optimization required in this function.  Remove
	backwards compatability if/else's and employ a switch statement
	to reduce processing time.	
*/
function get_a_post($id='GETPOST') {
	global $post, $tableposts, $tablepostmeta, $wp_version, $wpdb;

	if($wp_version < 1.5)
		$table = $tableposts;
	else
		$table = $wpdb->posts;

	$now = current_time('mysql');
	$name_or_id = '';
	$orderby = 'post_date';

	if( !$id || 'GETPOST' == $id || 'GETRANDOM' == $id ) {
		if( $wp_version < 2.1 )
			$query_suffix = "post_status = 'publish'";
		else
			$query_suffix = "post_type = 'post' AND post_status = 'publish'";
	} elseif('GETPAGE' == $id) {
		if($wp_version < 2.1)
			$query_suffix = "post_status = 'static'";
		else
			$query_suffix = "post_type = 'page' AND post_status = 'publish'";
	} elseif('GETSTICKY' == $id) {
		if($wp_version < 1.5)
			$table .= ', ' . $tablepostmeta;
		else
			$table .= ', ' . $wpdb->postmeta;
		$query_suffix = "ID = post_id AND meta_key = 'sticky' AND meta_value = 1";
	} else {
		$query_suffix = "(post_status = 'publish' OR post_status = 'static')";

		if(is_numeric($id)) {
			$name_or_id = "ID = '$id' AND";
		} else {
			$name_or_id = "post_name = '$id' AND";
		}
	}

	if('GETRANDOM' == $id)
		$orderby = 'RAND()';

	$post = $wpdb->get_row("SELECT * FROM $table WHERE $name_or_id post_date <= '$now' AND $query_suffix ORDER BY $orderby DESC LIMIT 1");
	get_post_custom($post->ID);

	if($wp_version < 1.5)
		start_wp();
	else
		setup_postdata($post);
}
/* Grab some variables for below */

$width = get_option('gallery-width');
$height = get_option('gallery-height');


/* Add required hooks: */
add_action('wp_head', 'gallery_styles');
add_action('admin_menu', 'gallery_options_page');
add_action('wp_print_styles', 'add_frontend_dependencies');
add_theme_support( 'post-thumbnails' );
add_image_size( 'gallery-image-file', $width, $height);
?>