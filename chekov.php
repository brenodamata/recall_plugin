<?php
/**
Plugin Name: Chekov
Plugin URI: http://github.com/terakeet/recall_plugin
Description: Retrieves recall data information
Version: 1.01
Author: Breno da Mata
Author URI: http://github.com/brenodamata
License: MIT
*/

//Exit if accessed directly
if ( ! defined( 'ABSPATH') ) {
  exit;
}

function trk_register_post_type() {

  $singular = 'Recall'
  $plural = 'Recalls'

  $labels = array(
    'name'               => $plural,
    'singular_name'      => $singular,
    'add_name'           => 'Add New',
    'add_new_item'       => 'Add New' . $singular,
    'edit'               => 'Edit',
    'edit_item'          => 'Edit ' . $singular,
    'new_item'           => 'New ' . $singular,
    'view'               => 'View ' . $singular,
    'view_item'          => 'View ' . $singular,
    'search_term'        => 'Search ' . plural,
    'parent'             => 'Parent ' . $singular,
    'not_found'          => 'No ' . $plural . ' found',
    'not_found_in_trash' => 'No ' . $plural . ' in Trash'
  );

  $args = array(
    'labels'              => $labels,
    'plubic'              => true,
    'plubic_queryable'    => true,
    'exclude_from_search' => false,
    'show_in_nav_menus'   => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 10,
    // 'menu_icon'        => 'dashicons',
    'can_export'          => true,
    'delete_with_user'    => false,
    'hierarchical'        => false,
    'has_archive'         => true,
    'query_var'           => 'post',
    'map_meta_cap'        => true,
    'reqrite' => array(
      'slug'        => 'recalls',
      'with_front'  => true,
      'pages'       => true,
      'feeds'       => true
    ),
    'supports' => array(
      'title',
      'editor',
      'author',
      'custom_fields'
    )

  );

  register_post_type( 'recall', $args);
}
add_action( 'init', 'trk_register_post_type');
