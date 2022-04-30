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
  wp_enqueue_script('lang-quizz-script', QUIZZ_PLUGIN_ASSET . 'js/index.js', array('jquery'), '2.0.5', true);
  wp_enqueue_style('lang-quizz-stylesheet', QUIZZ_PLUGIN_ASSET . 'styles/index.css', false, '2.0.5', 'all');
}

// function that runs when shortcode is called
function lang_quizz_shortcode()
{
  global $wpdb;

  $table_name = $wpdb->prefix . "lang_quizz";
  $rows = $wpdb->get_results("SELECT name,image,notes from $table_name LIMIT 4");

  $html = '
  <div id="quiz" class="w-full flex flex-wrap items-center justify-center bg-gray-100 rounded-lg shadow-lg p-5 mb-6 ">
  <div class="header w-full p-6">
    <div class="w-full flex-none mb-3 text-2xl leading-none text-slate-900 text-center">
      <h3 class="mb-3 text-yellow-900">Quizz Từ vựng Tiếng Pháp</h3>
      <h2 id="question" class="flex-auto text-4xl text-red-500 text-yellow-900"></h2>
    </div>
  </div>
  <section class="buttons grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 m-4">
    <div>
      <div id="btn0" class="bg-white relative hover:shadow-2xl"><span id="choice0"></div>
      <div class="quiz-icon text-red-700"></div>
    </div>
    <div>
      <div id="btn1" class="bg-white relative hover:shadow-2xl"><span id="choice1"></div>
      <div class="quiz-icon text-green-400"></div>
    </div>
    <div>
      <div id="btn2" class="bg-white relative hover:shadow-2xl"><span id="choice2"></span></div>
      <div class="quiz-icon"></div>
    </div>
    <div>
      <div id="btn3" class="bg-white relative hover:shadow-2xl"><span id="choice3"></span></div>
      <div class="quiz-icon"></div>
    </div>
  </section>
</div>';

  // Things that you want to do. 

  // Output needs to be return
  return $html;
}
// register shortcode
add_shortcode('quizz', 'lang_quizz_shortcode');
