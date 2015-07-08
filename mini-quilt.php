<?php
/*
Plugin Name: Mini Quilt
Plugin URI: http://www.ikirudesign.com/plugins/mini-quilt/
Description: A unique way to show recent or random posts in your sidebar using a visually interesting quilt of your posts with colors derived by the <a href="http://www.ikirudesign.com/themes/kaleidoscope/">Kaleidoscope theme</a>'s color algorithm.
Author: david (b) hayes
Version: 0.9.0
Author URI: http://www.davidbhayes.com/
License: GPL 2.0 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
*/

include 'Kaleidoscope_Color_Calculator.php';
include 'Mini_Quilt_Widget.php';

add_action( 'wp_print_styles', 'add_mq_stylesheet' );
function add_mq_stylesheet() {
	$myStyleUrl = WP_PLUGIN_URL . '/mini-quilt/mqstyle.css';
	$myStyleFile = WP_PLUGIN_DIR . '/mini-quilt/mqstyle.css';
	if ( file_exists( $myStyleFile ) ) {
		wp_register_style( 'myStyleSheets', $myStyleUrl );
		wp_enqueue_style( 'myStyleSheets' );
	}
}

// -=- Add our function to the widgets_init hook.
add_action( 'widgets_init', 'mq_load_widgets' );
function mq_load_widgets() {
	register_widget( 'Mini_Quilt_Widget' );
}

// -=- The Class Extension to make the Widget

// -=- The Kaleidoscope Functions -- These make the colors
function mq_date_to_color( $day, $year ) {
	return Kaleidoscope_Color_Calculator::date_to_color( $day, $year );
}
