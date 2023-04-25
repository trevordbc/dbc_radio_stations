<?php
function register_syndicated_stations() {
    $labels = array(
        'name'               => 'Syndicated Stations',
        'singular_name'      => 'Syndicated Station',
        'menu_name'          => 'Syndicated Stations',
        'name_admin_bar'     => 'Syndicated Station',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Syndicated Station',
        'new_item'           => 'New Syndicated Station',
        'edit_item'          => 'Edit Syndicated Station',
        'view_item'          => 'View Syndicated Station',
        'all_items'          => 'All Syndicated Stations',
        'search_items'       => 'Search Syndicated Stations',
        'parent_item_colon'  => 'Parent Syndicated Stations:',
        'not_found'          => 'No Syndicated Stations found.',
        'not_found_in_trash' => 'No Syndicated Stations found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'description'        => 'Custom post type for syndicated radio stations',
        'public'             => true,
        'menu_position'      => 5,
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'menu_icon'          => 'dashicons-megaphone',
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'syndicated-stations' ),
    );

    register_post_type( 'syndicated_stations', $args );
}
add_action( 'init', 'register_syndicated_stations' );

function add_syndicated_station_url_meta() {
    register_meta( 'post', 'syndicated_station_url', array(
        'type' => 'string',
        'description' => 'URL for syndicated radio stations',
        'single' => true,
		'meta_key' => 'syndicated_url',
        'show_in_rest' => true,
    ) );
}
add_action( 'init', 'add_syndicated_station_url_meta' );

function add_syndicated_station_meta_fields() {
    add_meta_box( 'syndicated_station_url_meta', 'Station URL', 'syndicated_station_url_meta_callback', 'syndicated_stations', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'add_syndicated_station_meta_fields' );

function syndicated_station_url_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'syndicated_station_url_nonce' );
    $url_value = get_post_meta( $post->ID, '_syndicated_station_url', true );
    echo '<label for="syndicated_station_url_field">Enter the URL of the station:</label>';
    echo '<input type="text" id="syndicated_station_url_field" name="syndicated_station_url_field" value="' . esc_attr( $url_value ) . '">';
}

function save_syndicated_station_url_meta( $post_id ) {
    if ( ! isset( $_POST['syndicated_station_url_nonce'] ) || ! wp_verify_nonce( $_POST['syndicated_station_url_nonce'], basename( __FILE__ ) ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( isset( $_POST['post_type'] ) && 'syndicated_stations' === $_POST['post_type'] ) {
        if ( current_user_can( 'edit_post', $post_id ) ) {
            if ( isset( $_POST['syndicated_station_url_field'] ) ) {
                update_post_meta( $post_id, '_syndicated_station_url', sanitize_text_field( $_POST['syndicated_station_url_field'] ) );
            } else {
                delete_post_meta( $post_id, '_syndicated_station_url' );
            }
        }
    }
}
add_action( 'save_post', 'save_syndicated_station_url_meta' );
