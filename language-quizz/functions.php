<?php

/**
 * Plugin Name:       Language Quizz
 * Plugin URI:        https://tiengphapvui.com
 * Description:       Image Language Quizz Plugin.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Funaway89
 * Author URI:        https://tiengphapvui.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       language-quizz
 */

define('MY_PLUGIN_FILE_PATH', __FILE__);

if (!defined('QUIZZ_PLUGIN_DIR'))
  define('QUIZZ_PLUGIN_DIR', plugin_dir_path(__FILE__));


require_once QUIZZ_PLUGIN_DIR . 'init.php';

require_once QUIZZ_PLUGIN_DIR . 'constants.php';

require_once QUIZZ_PLUGIN_DIR . 'utils.php';


add_action('admin_enqueue_scripts', 'lang_quizz_include_js');

function lang_quizz_include_js()
{

  // I recommend to add additional conditions just to not to load the scipts on each page

  if (!did_action('wp_enqueue_media')) {
    wp_enqueue_media();
  }

  wp_enqueue_script('myuploadscript', QUIZZ_PLUGIN_URL . 'assets/js/lang-quizz.js', array('jquery'));
}

add_action('wp_enqueue_scripts', "add_lang_files");

function add_lang_files()
{
  wp_enqueue_script('lang-quizz-script', QUIZZ_PLUGIN_ASSET . 'js/lang-quizz-front.js', false, '1.0.0', true);
  wp_enqueue_style('lang-quizz-stylesheet', QUIZZ_PLUGIN_ASSET . '/css/style.css', false, '1.0.0', 'all');
}

// function that runs when shortcode is called
function lang_quizz_shortcode()
{
  global $wpdb;

  $table_name = $wpdb->prefix . "lang_quizz";
  $rows = $wpdb->get_results("SELECT name,image,notes from $table_name LIMIT 4");

  $html = '
  <div class="grid">
<div id="quiz">
<hr style="margin-bottom: 20px">
<h2 id="question"></h2>
<h2 id="question-desc"></h2>
<p id="progress-next"></p>
<div class="buttons">
<button id="btn0"><span id="choice0"></span></button>
<button id="btn1"><span id="choice1"></span></button>
<button id="btn2"><span id="choice2"></span></button>
<button id="btn3"><span id="choice3"></span></button>
</div>
<hr style="margin-top: 50px">
<footer>
<p id="progress">Question x of y</p>
</footer>
</div>
</div>';

  // $html = '';
  // $html .= '<div class="container">';
  // $html .= '<div class="row">';
  // $html .= '<div class="col-md-12">';
  // $html .= '<div id="testimonial-slider" class="owl-carousel">';

  // foreach ($rows as $row) {
  //   $html .= "<div class='testimonial'>";
  //   if (!empty($row->image)) {
  //     $html .= "<div class='pic'>";
  //     $html .= "<img src='" . wp_get_attachment_url($row->image) . "' />";
  //     $html .= "</div>";
  //   }
  //   $html .= "<div class='description'>" . stripslashes($row->notes) . "</div>";
  //   $html .= "<h3 class='testimonial-title'>" . stripslashes($row->name) . "</h3>";
  //   $html .= "</div>";
  // }
  // $html .= "</div></div></div></div>";
  // Things that you want to do. 

  // Output needs to be return
  return $html;
}
// register shortcode
add_shortcode('quizz', 'lang_quizz_shortcode');
