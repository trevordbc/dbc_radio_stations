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

function get_elementor_podcast_widget_settings() {
    $elementor = \Elementor\Plugin::$instance;
    $active_widgets = $elementor->elements_manager->get_element_types();

    $settings = array();

    if (isset($active_widgets['podcast-player-widget-advanced'])) {
        $page_widgets = $elementor->frontend->get_builder_content_for_display(get_the_ID());

        foreach ($page_widgets as $widget) {
            if ('podcast-player-widget-advanced' === $widget->get_name()) {
                $settings = $widget->get_settings_for_display();
                break;
            }
        }
    }

    return $settings;
}

function podcast_enqueue_mediaelement() {
    wp_enqueue_style('wp-mediaelement');
    wp_enqueue_script('wp-mediaelement');
    
    wp_enqueue_style('audio_player_style', plugin_dir_url(__FILE__) . 'css/audio_player.css');
	
	wp_enqueue_script('audio_player_script', plugin_dir_url(__FILE__) . 'javascript/audio_player.js', array('jquery'), '', true);
    
    // Check if the podcast widget is present on the page
    $settings = get_elementor_podcast_widget_settings();
    if (!empty($settings)) {
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $rss_feeds = $settings['rss_feeds'];
        $podcastLimit = $settings['podcastamount'];

        $data = array();
        foreach ( $rss_feeds as $index => $feed ) {
            $podcastURL = $feed['url'];
            $rss = fetch_feed( $podcastURL );

            if ( ! is_wp_error( $rss ) ) {
                $maxitems = $rss->get_item_quantity( $podcastLimit );
                $rss_items = $rss->get_items( 0, $maxitems );

                $items = array();
                foreach ( $rss_items as $item ) {
                    $items[] = array(
                        'title' => $item->get_title(),
                        'enclosure' => $item->get_enclosure()->get_link(),
                        'duration' => $item->get_enclosure()->get_duration(),
                    );
                }
                $data[] = array(
                    'rssfeedtitle' => $feed['rssfeedtitle'],
                    'url' => $feed['url'],
                    'protectedcontent' => $feed['protectedcontent'],
                    'allowed_roles' => $feed['allowed_roles'],
                    'items' => $items,
                );
            }
        }

        wp_localize_script( 'audio_player_script', 'elementorPodcastData', array(
            'podcasts' => $data,
            'userRoles' => $user_roles,
        ) );
    }
}
add_action('wp_enqueue_scripts', 'podcast_enqueue_mediaelement');


function enqueue_load_fa() {
    wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.5.0/css/all.css' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_load_fa');
