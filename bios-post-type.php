<?php
//Bio Custom Post Type
function register_bios() {
    $labels = array(
        'name'               => 'Bios',
        'singular_name'      => 'Personality Bio',
        'menu_name'          => 'Personality Bios',
        'name_admin_bar'     => 'Personality Bios',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Bio',
        'new_item'           => 'New Personality',
        'edit_item'          => 'Edit Bio',
        'view_item'          => 'View Personality Bio',
        'all_items'          => 'All Bios',
        'search_items'       => 'Search Bios',
        'parent_item_colon'  => 'Parent Bios:',
        'not_found'          => 'No Bios found.',
        'not_found_in_trash' => 'No Bios found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'description'        => 'Custom post type for On-Air Personality Bios',
        'public'             => true,
        'menu_position'      => 4,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'bios_order' ),
        'menu_icon'          => 'dashicons-id-alt',
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'on-air-personality-bios' ),
    );

    register_post_type( 'bios', $args );
}
add_action( 'init', 'register_bios' );
