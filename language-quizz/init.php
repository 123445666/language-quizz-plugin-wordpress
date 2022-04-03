<?php

// function to create the DB / Options / Defaults					
function ss_options_install()
{

  global $wpdb;
  $table_name = $wpdb->prefix . "lang_quizz";
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE $table_name (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) CHARACTER SET utf8 NOT NULL,
            `image` varchar(500) CHARACTER SET utf8,
            `notes` varchar(1000) CHARACTER SET utf8 NOT NULL,
            PRIMARY KEY (`id`)
          ) $charset_collate; ";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);
}

// run the install scripts upon plugin activation
register_activation_hook(MY_PLUGIN_FILE_PATH, 'ss_options_install');

//menu items
add_action('admin_menu', 'lang_quizz_modifymenu');
function lang_quizz_modifymenu()
{

  //this is the main item for the menu
  add_menu_page(
    'Testimonials', //page title
    'Cute Testimonials', //menu title
    'manage_options', //capabilities
    'lang_quizz_list', //menu slug
    'lang_quizz_list' //function
  );

  //this is a submenu
  add_submenu_page(
    'lang_quizz_list', //parent slug
    'Add New Testimonial', //page title
    'Add New', //menu title
    'manage_options', //capability
    'lang_quizz_create', //menu slug
    'lang_quizz_create'
  ); //function

  //this submenu is HIDDEN, however, we need to add it anyways
  add_submenu_page(
    null, //parent slug
    'Update Testimonial', //page title
    'Update', //menu title
    'manage_options', //capability
    'lang_quizz_update', //menu slug
    'lang_quizz_update'
  ); //function
}
define('ROOTDIR', plugin_dir_path(__FILE__));
require_once(ROOTDIR . 'testimonials-list.php');
require_once(ROOTDIR . 'testimonials-create.php');
require_once(ROOTDIR . 'testimonials-update.php');
