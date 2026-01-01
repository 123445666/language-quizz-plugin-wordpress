<?php

class Pro_Quiz_Activator
{

    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Table: Subjects
        $table_subjects = $wpdb->prefix . 'pro_quiz_subjects';
        $sql_subjects = "CREATE TABLE $table_subjects (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY slug (slug),
            KEY name (name)
        ) $charset_collate;";
        dbDelta($sql_subjects);

        // Table: Questions
        $table_questions = $wpdb->prefix . 'pro_quiz_questions';
        $sql_questions = "CREATE TABLE $table_questions (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            subject_id mediumint(9) NOT NULL,
            question_title varchar(500) NOT NULL,
            question_content text,
            question_order int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'publish',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY subject_id (subject_id),
            KEY status (status),
            KEY question_order (question_order)
        ) $charset_collate;";
        dbDelta($sql_questions);

        // Table: Answers
        $table_answers = $wpdb->prefix . 'pro_quiz_answers';
        $sql_answers = "CREATE TABLE $table_answers (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            question_id mediumint(9) NOT NULL,
            answer_text text NOT NULL,
            is_correct tinyint(1) DEFAULT 0 NOT NULL,
            answer_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY question_id (question_id),
            KEY is_correct (is_correct)
        ) $charset_collate;";
        dbDelta($sql_answers);

        // Table: Quiz Attempts (optional - for tracking user attempts)
        $table_attempts = $wpdb->prefix . 'pro_quiz_attempts';
        $sql_attempts = "CREATE TABLE $table_attempts (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20),
            subject_id mediumint(9),
            total_questions int(11) NOT NULL,
            correct_answers int(11) NOT NULL,
            score_percentage decimal(5,2) NOT NULL,
            user_answers longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY subject_id (subject_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        dbDelta($sql_attempts);

        // Set database version for future migrations
        add_option('pro_quiz_db_version', '2.0');
    }

}
