<?php
/**
 * Plugin Name: Dick Broadcasting Company
 * Description: Radio Group Website Extensions
 * Version:     1.0.0
 * Author:      DBC Next
 * Author URI:  https://dbcnext.com
 * Text Domain: elementor-addon
 */

function register_dbc_radio_widgets() {
    require_once(__DIR__ . '/widgets/podcast-player-widget.php');
    require_once(__DIR__ . '/widgets/podcast-player-widget-advanced.php');

    $widgets_manager = \Elementor\Plugin::instance()->widgets_manager;

    $widgets_manager->register_widget_type(new \Elementor_Podcast_Player_Widget());
    $widgets_manager->register_widget_type(new \Elementor_Podcast_Player_Widget_Advanced());
}
add_action('elementor/widgets/widgets_registered', 'register_dbc_radio_widgets');

include_once (plugin_dir_path( __FILE__ ) . '/bios-post-type.php' );
include_once (plugin_dir_path( __FILE__ ) . '/syndicated-stations-post-type.php' );
include_once (plugin_dir_path( __FILE__ ) . '/station-settings.php' );


function podcast_enqueue_mediaelement() {
    wp_enqueue_style('wp-mediaelement');
    wp_enqueue_script('wp-mediaelement');

    wp_enqueue_script('audio_player_script', plugin_dir_url(__FILE__) . 'javascript/audio_player.js', array('jquery'), '', true);
    wp_enqueue_style('audio_player_style', plugin_dir_url(__FILE__) . 'css/audio_player.css');
}
add_action('wp_enqueue_scripts', 'podcast_enqueue_mediaelement');


function sgplayer_html() {
	echo '<div class="sgplayer-website-footer">';
	echo '<div class="sgplayer-embed" style="width:100%;height:120px;"></div>';
	echo '</div>';
	echo '</div>';
}
add_action( 'wp_footer', 'sgplayer_html' );

function sgplayer_enqueue_livestream() {
	// Get the value of the Call Letters option
	$call_letters = get_option('dbc_station_call_letters');
	$frequency = get_option('dbc_station_frequency');

	// Check if the value exists and display it in lowercase
	$call_letters_lower = !empty($call_letters) ? strtolower($call_letters) : 'NaN';
	$frequency_lower = !empty($frequency) ? strtolower($frequency) : 'NaN';
	
	wp_enqueue_script('sgplayer_script', plugin_dir_url(__FILE__) . '/sgplayer/javascript/player.js', array('jquery'), '', true);
	wp_enqueue_style('sgplayer_style', plugin_dir_url(__FILE__) . '/sgplayer/css/player.css');
	
	wp_localize_script('sgplayer_script', 'sgplayer_data', array(
        'call_letters_lower' => esc_html($call_letters_lower),
		'frequency_lower' => esc_html($frequency_lower),
    ));
	
	sgplayer_html();
}
add_action('wp_enqueue_scripts', 'sgplayer_enqueue_livestream');


function enqueue_load_fa() {
    wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.5.0/css/all.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_load_fa');
