<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Drop all custom tables
$tables = array(
    $wpdb->prefix . 'pro_quiz_attempts',
    $wpdb->prefix . 'pro_quiz_answers',
    $wpdb->prefix . 'pro_quiz_questions',
    $wpdb->prefix . 'pro_quiz_subjects',
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Delete options
delete_option('pro_quiz_db_version');
