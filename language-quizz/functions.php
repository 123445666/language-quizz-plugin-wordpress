<?php
/*
Plugin Name: Language Quizz
Description:
Version: 1
Author: funaway89
*/
define('MY_PLUGIN_FILE_PATH', __FILE__);
define('MY_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));

include plugin_dir_path(__FILE__) . 'init.php';

add_action('admin_enqueue_scripts', 'lang_quizz_include_js');

function lang_quizz_include_js()
{

  // I recommend to add additional conditions just to not to load the scipts on each page

  if (!did_action('wp_enqueue_media')) {
    wp_enqueue_media();
  }

  wp_enqueue_script('myuploadscript', plugin_dir_url(__FILE__) . 'assets/js/lang-quizz.js', array('jquery'));
}

add_action('wp_enqueue_scripts', "add_lang_files");

function add_lang_files(){
  wp_enqueue_script('lang-quizz-script', plugins_url('assets/js/lang-quizz-front.js', __FILE__), false, '1.0.0', true);
  wp_enqueue_style( 'lang-quizz-stylesheet', plugins_url('assets/css/style.css', __FILE__), false, '1.0.0', 'all');
}

// function that runs when shortcode is called
function lang_quizz_shortcode()
{
  global $wpdb;

  $table_name = $wpdb->prefix . "lang_quizz";
  $rows = $wpdb->get_results("SELECT name,image,notes from $table_name");

  $html = `
  <div class="grid">
  <div id="quiz">
  <h1>Picture Quiz</h1>
  <hr style="margin-bottom: 20px">
  <p id="question"></p>
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
  </div>`;

  $html = '';
  $html .= '<link rel="stylesheet" href="' . plugins_url('assets/css/style.css', __FILE__) . '">';

  $html .= '<div class="container">';
  $html .= '<div class="row">';
  $html .= '<div class="col-md-12">';
  $html .= '<div id="testimonial-slider" class="owl-carousel">';

  foreach ($rows as $row) {
    $html .= "<div class='testimonial'>";
    if (!empty($row->image)) {
      $html .= "<div class='pic'>";
      $html .= "<img src='" . wp_get_attachment_url($row->image) . "' />";
      $html .= "</div>";
    }
    $html .= "<div class='description'>" . stripslashes($row->notes) . "</div>";
    $html .= "<h3 class='testimonial-title'>" . stripslashes($row->name) . "</h3>";
    $html .= "</div>";
  }
  $html .= "</div></div></div></div>";

  $html .= '<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.0.min.js"></script>';
  $html .= '<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js"></script>';
  $html .= '<script type="text/javascript" src="' . plugins_url('assets/js/lang-quizz-front.js', __FILE__) . '"></script>';

  // Things that you want to do. 



  $template_tarot = '<div class="tarot-post-deck text-center row margin-bottom-10 justify-content-center align-items-center">';

  // Output needs to be return
  return $html;
}
// register shortcode
add_shortcode('cute-testimonials', 'lang_quizz_shortcode');
