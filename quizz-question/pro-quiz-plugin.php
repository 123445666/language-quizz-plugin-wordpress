<?php
/**
 * Plugin Name: Pro Quiz Plugin
 * Plugin URI: 
 * Description: A professional quiz plugin for WordPress. Create quizzes with subjects, questions, and multiple choice answers. Users can take quizzes and see detailed results for each question.
 * Version: 2.0
 * Author: Antigravity
 * Text Domain: pro-quiz
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include classes
require_once plugin_dir_path(__FILE__) . 'includes/class-pro-quiz-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-pro-quiz-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'admin/class-pro-quiz-admin.php';
require_once plugin_dir_path(__FILE__) . 'public/class-pro-quiz-public.php';

// Activation Hooks
register_activation_hook(__FILE__, array('Pro_Quiz_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Pro_Quiz_Deactivator', 'deactivate'));

// Run Plugin
function run_pro_quiz_plugin()
{
    $plugin_admin = new Pro_Quiz_Admin();
    $plugin_admin->init();

    $plugin_public = new Pro_Quiz_Public();
    $plugin_public->init();
}

run_pro_quiz_plugin();
