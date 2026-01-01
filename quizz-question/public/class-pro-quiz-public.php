<?php

class Pro_Quiz_Public
{

    public function init()
    {
        add_shortcode('pro_quiz', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_pro_quiz_save_attempt', array($this, 'save_quiz_attempt'));
        add_action('wp_ajax_nopriv_pro_quiz_save_attempt', array($this, 'save_quiz_attempt'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('pro-quiz-public-css', plugin_dir_url(__FILE__) . 'css/pro-quiz-public.css');
        wp_enqueue_script('pro-quiz-public-js', plugin_dir_url(__FILE__) . 'js/pro-quiz-public.js', array('jquery'), '1.0', true);
        wp_localize_script('pro-quiz-public-js', 'proQuiz', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pro_quiz_ajax')
        ));
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'subject' => '',
            'subject_id' => '',
        ), $atts);

        global $wpdb;
        $questions_table = $wpdb->prefix . 'pro_quiz_questions';
        $subjects_table = $wpdb->prefix . 'pro_quiz_subjects';
        $answers_table = $wpdb->prefix . 'pro_quiz_answers';

        // Get subject ID
        $subject_id = 0;
        $subject_name = '';
        
        if (!empty($atts['subject_id'])) {
            $subject_id = intval($atts['subject_id']);
            $subject = $wpdb->get_row($wpdb->prepare("SELECT * FROM $subjects_table WHERE id = %d", $subject_id), ARRAY_A);
            if ($subject) {
                $subject_name = $subject['name'];
            }
        } elseif (!empty($atts['subject'])) {
            $subject = $wpdb->get_row($wpdb->prepare("SELECT * FROM $subjects_table WHERE slug = %s", $atts['subject']), ARRAY_A);
            if ($subject) {
                $subject_id = $subject['id'];
                $subject_name = $subject['name'];
            }
        }

        // Get questions
        $where = "WHERE status = 'publish'";
        if ($subject_id > 0) {
            $where .= $wpdb->prepare(" AND subject_id = %d", $subject_id);
        }
        
        $questions = $wpdb->get_results("SELECT * FROM $questions_table $where ORDER BY question_order ASC, id ASC", ARRAY_A);

        if (empty($questions)) {
            return '<div class="pro-quiz-container"><p class="quiz-no-questions">No questions found for this quiz. Please add questions in the admin area.</p></div>';
        }

        ob_start();
        ?>
        <div class="pro-quiz-container" data-subject-id="<?php echo esc_attr($subject_id); ?>">
            <div class="quiz-header">
                <h2 class="quiz-title">Quiz</h2>
                <?php if (!empty($subject_name)): ?>
                    <p class="quiz-subject">Subject: <strong><?php echo esc_html($subject_name); ?></strong></p>
                <?php endif; ?>
                <p class="quiz-instructions">Please answer all questions and click "Submit Quiz" when finished.</p>
            </div>
            
            <form class="pro-quiz-form" id="pro-quiz-form">
                <?php $q_index = 0; ?>
                <?php foreach ($questions as $question): 
                    $q_index++;
                    $question_id = $question['id'];

                    // Fetch answers from Custom Table
                    $answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $answers_table WHERE question_id = %d ORDER BY id ASC", $question_id), ARRAY_A);

                    if (empty($answers))
                        continue;
                    
                    // Find correct answer ID before shuffling
                    $correct_answer_id = null;
                    foreach ($answers as $ans) {
                        if ($ans['is_correct']) {
                            $correct_answer_id = $ans['id'];
                            break;
                        }
                    }
                    
                    // Shuffle answers for display (optional - you can remove this if you want fixed order)
                    shuffle($answers);
                    ?>
                    <div class="quiz-question" data-question-id="<?php echo esc_attr($question_id); ?>" data-correct-answer-id="<?php echo esc_attr($correct_answer_id); ?>">
                        <div class="question-header">
                            <span class="question-number"><?php echo $q_index; ?></span>
                            <h3 class="question-title"><?php echo esc_html($question['question_title']); ?></h3>
                        </div>
                        <?php if (!empty($question['question_content'])): ?>
                            <div class="quiz-content">
                                <?php echo wp_kses_post($question['question_content']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="quiz-answers">
                            <?php foreach ($answers as $a_index => $answer): 
                                $is_correct = $answer['is_correct'] ? 1 : 0;
                                ?>
                                <label class="quiz-answer-label" data-answer-id="<?php echo esc_attr($answer['id']); ?>" data-is-correct="<?php echo $is_correct; ?>">
                                    <input type="radio" name="question_<?php echo esc_attr($question_id); ?>" value="<?php echo esc_attr($answer['id']); ?>" required>
                                    <span class="radio-custom"></span>
                                    <span class="answer-text"><?php echo esc_html($answer['answer_text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <div class="question-feedback" style="display:none;">
                            <span class="feedback-icon"></span>
                            <span class="feedback-text"></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="quiz-actions">
                    <button type="submit" class="quiz-submit-btn">Submit Quiz</button>
                </div>
            </form>
            
            <div class="quiz-results" id="quiz-results" style="display:none;">
                <div class="results-header">
                    <h3>Quiz Results</h3>
                </div>
                <div class="results-summary" id="results-summary"></div>
                <div class="results-details" id="results-details"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Save quiz attempt to database
    public function save_quiz_attempt()
    {
        check_ajax_referer('pro_quiz_ajax', 'nonce');

        if (!isset($_POST['subject_id']) || !isset($_POST['total_questions']) || !isset($_POST['correct_answers'])) {
            wp_send_json_error('Missing required data');
        }

        global $wpdb;
        $attempts_table = $wpdb->prefix . 'pro_quiz_attempts';

        $user_id = get_current_user_id();
        $subject_id = intval($_POST['subject_id']);
        $total_questions = intval($_POST['total_questions']);
        $correct_answers = intval($_POST['correct_answers']);
        $score_percentage = $total_questions > 0 ? round(($correct_answers / $total_questions) * 100, 2) : 0;
        $user_answers = isset($_POST['user_answers']) ? json_encode($_POST['user_answers']) : '';

        $result = $wpdb->insert(
            $attempts_table,
            array(
                'user_id' => $user_id ? $user_id : null,
                'subject_id' => $subject_id,
                'total_questions' => $total_questions,
                'correct_answers' => $correct_answers,
                'score_percentage' => $score_percentage,
                'user_answers' => $user_answers
            ),
            array('%d', '%d', '%d', '%d', '%f', '%s')
        );

        if ($result) {
            wp_send_json_success(array('attempt_id' => $wpdb->insert_id));
        } else {
            wp_send_json_error('Failed to save attempt');
        }
    }
}
