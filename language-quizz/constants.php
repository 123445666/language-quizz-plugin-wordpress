<?php

define('QUIZZ_PLUGIN_NAME', 'Quizz');
define('QUIZZ_PLUGIN_CREATE', 'Create ' . QUIZZ_PLUGIN_NAME);
define('QUIZZ_PLUGIN_EDIT', 'Edit ' . QUIZZ_PLUGIN_NAME);
define('QUIZZ_PLUGIN_DELETE', 'Delete ' . QUIZZ_PLUGIN_NAME);
define('QUIZZ_PLUGIN_EXPORT_JSON', 'Export Json File ' . QUIZZ_PLUGIN_NAME);
define('QUIZZ_PLUGIN_MESSAGE_CREATE', QUIZZ_PLUGIN_NAME . ' Created !');
define('QUIZZ_PLUGIN_MESSAGE_EDIT', QUIZZ_PLUGIN_NAME . ' Edited !');
define('QUIZZ_PLUGIN_MESSAGE_DELETE', QUIZZ_PLUGIN_NAME . ' Deleted !');

if (!defined('QUIZZ_PLUGIN_DIR'))
    define('QUIZZ_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!defined('QUIZZ_NAME'))
    define('QUIZZ_NAME', 'QUIZZ');

if (!defined('QUIZZ_PLUGIN_URL'))
    define('QUIZZ_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!defined('QUIZZ_PLUGIN_DATA_UPLOADED_URL'))
    define('QUIZZ_PLUGIN_DATA_UPLOADED_URL', WP_CONTENT_URL . '/uploads/data/');

if (!defined('QUIZZ_PLUGIN_DATA_UPLOADED_DIR'))
    define('QUIZZ_PLUGIN_DATA_UPLOADED_DIR', WP_CONTENT_DIR . '/uploads/data/');

if (!defined('QUIZZ_PLUGIN_IMAGES_UPLOADED_URL'))
    define('QUIZZ_PLUGIN_IMAGES_UPLOADED_URL', WP_CONTENT_URL . '/uploads/quizz/');

if (!defined('QUIZZ_PLUGIN_IMAGES_UPLOADED_DIR'))
    define('QUIZZ_PLUGIN_IMAGES_UPLOADED_DIR', WP_CONTENT_DIR . '/uploads/quizz/');

if (!defined('QUIZZ_PLUGIN_BASENAME'))
    define('QUIZZ_PLUGIN_BASENAME', plugin_basename(__FILE__));

if (!defined('QUIZZ_PLUGIN_DIRNAME'))
    define('QUIZZ_PLUGIN_DIRNAME', dirname(QUIZZ_PLUGIN_BASENAME));

if (!defined('QUIZZ_PLUGIN_ASSET'))
    define('QUIZZ_PLUGIN_ASSET', QUIZZ_PLUGIN_URL . 'assets/');
