<?php

class Pro_Quiz_Admin
{

    public function init()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Pro Quiz',
            'Pro Quiz',
            'manage_options',
            'pro-quiz',
            array($this, 'render_dashboard'),
            'dashicons-welcome-learn-more',
            30
        );

        add_submenu_page(
            'pro-quiz',
            'Subjects',
            'Subjects',
            'manage_options',
            'pro-quiz-subjects',
            array($this, 'render_subjects_page')
        );

        add_submenu_page(
            'pro-quiz',
            'Questions',
            'Questions',
            'manage_options',
            'pro-quiz-questions',
            array($this, 'render_questions_page')
        );

        add_submenu_page(
            'pro-quiz',
            'Add Question',
            'Add Question',
            'manage_options',
            'pro-quiz-add-question',
            array($this, 'render_add_question_page')
        );

        add_submenu_page(
            'pro-quiz',
            'Quiz Attempts',
            'Quiz Attempts',
            'manage_options',
            'pro-quiz-attempts',
            array($this, 'render_attempts_page')
        );

        add_submenu_page(
            'pro-quiz',
            'Instructions',
            'Instructions',
            'manage_options',
            'pro-quiz-instructions',
            array($this, 'render_instructions_page')
        );
    }

    public function enqueue_styles($hook)
    {
        if (strpos($hook, 'pro-quiz') !== false) {
            wp_enqueue_style('pro-quiz-admin-css', plugin_dir_url(__FILE__) . 'css/pro-quiz-admin.css', array(), '1.0');
        }
    }

    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'pro-quiz') !== false) {
            wp_enqueue_script('pro-quiz-admin-js', plugin_dir_url(__FILE__) . 'js/pro-quiz-admin.js', array('jquery'), '1.0', true);
        }
    }

    public function handle_form_submissions()
    {
        // Handle subject form submissions
        if (isset($_POST['pro_quiz_subject_action']) && wp_verify_nonce($_POST['pro_quiz_subject_nonce'], 'pro_quiz_subject')) {
            $this->handle_subject_save();
        }

        // Handle question form submissions
        if (isset($_POST['pro_quiz_question_action']) && wp_verify_nonce($_POST['pro_quiz_question_nonce'], 'pro_quiz_question')) {
            $this->handle_question_save();
        }

        // Handle delete actions
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['_wpnonce'])) {
            if (wp_verify_nonce($_GET['_wpnonce'], 'delete_item')) {
                if (isset($_GET['subject_id'])) {
                    $this->handle_subject_delete(intval($_GET['subject_id']));
                } elseif (isset($_GET['question_id'])) {
                    $this->handle_question_delete(intval($_GET['question_id']));
                }
            }
        }
    }

    // Dashboard
    public function render_dashboard()
    {
        global $wpdb;
        $subjects_table = $wpdb->prefix . 'pro_quiz_subjects';
        $questions_table = $wpdb->prefix . 'pro_quiz_questions';
        $attempts_table = $wpdb->prefix . 'pro_quiz_attempts';

        $total_subjects = $wpdb->get_var("SELECT COUNT(*) FROM $subjects_table");
        $total_questions = $wpdb->get_var("SELECT COUNT(*) FROM $questions_table");
        $total_attempts = $wpdb->get_var("SELECT COUNT(*) FROM $attempts_table");
        ?>
        <div class="wrap">
            <h1>Pro Quiz Dashboard</h1>
            <div class="pro-quiz-dashboard">
                <div class="dashboard-stats">
                    <div class="stat-box">
                        <h3><?php echo $total_subjects; ?></h3>
                        <p>Subjects</p>
                        <a href="<?php echo admin_url('admin.php?page=pro-quiz-subjects'); ?>" class="button">Manage Subjects</a>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $total_questions; ?></h3>
                        <p>Questions</p>
                        <a href="<?php echo admin_url('admin.php?page=pro-quiz-questions'); ?>" class="button">Manage Questions</a>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $total_attempts; ?></h3>
                        <p>Quiz Attempts</p>
                        <a href="<?php echo admin_url('admin.php?page=pro-quiz-attempts'); ?>" class="button">View Attempts</a>
                    </div>
                </div>
                
                <div class="dashboard-help-section">
                    <div class="help-box">
                        <h2><span class="dashicons dashicons-info"></span> Quick Start Guide</h2>
                        <p>New to Pro Quiz? Follow these simple steps to get started:</p>
                        <ol>
                            <li><strong>Create a Subject:</strong> Go to <a href="<?php echo admin_url('admin.php?page=pro-quiz-subjects'); ?>">Subjects</a> and add your first subject (e.g., "Mathematics", "History")</li>
                            <li><strong>Add Questions:</strong> Go to <a href="<?php echo admin_url('admin.php?page=pro-quiz-add-question'); ?>">Add Question</a> and create questions with multiple answers</li>
                            <li><strong>Mark Correct Answer:</strong> For each question, mark one answer as correct</li>
                            <li><strong>Display Quiz:</strong> Use the shortcode <code>[pro_quiz subject="subject-slug"]</code> on any page or post</li>
                        </ol>
                        <p><a href="<?php echo admin_url('admin.php?page=pro-quiz-instructions'); ?>" class="button button-primary">View Full Instructions</a></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // Subjects Page
    public function render_subjects_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_quiz_subjects';
        $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $subject = null;

        if ($edit_id > 0) {
            $subject = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_id), ARRAY_A);
        }

        // Get all subjects
        $subjects = $wpdb->get_results("SELECT s.*, COUNT(q.id) as question_count FROM $table_name s LEFT JOIN {$wpdb->prefix}pro_quiz_questions q ON s.id = q.subject_id GROUP BY s.id ORDER BY s.name ASC", ARRAY_A);
        ?>
        <div class="wrap">
            <h1>Subjects</h1>
            
            <div class="pro-quiz-admin-container">
                <div class="pro-quiz-form-section">
                    <h2><?php echo $edit_id > 0 ? 'Edit Subject' : 'Add New Subject'; ?></h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('pro_quiz_subject', 'pro_quiz_subject_nonce'); ?>
                        <input type="hidden" name="pro_quiz_subject_action" value="<?php echo $edit_id > 0 ? 'edit' : 'add'; ?>">
                        <input type="hidden" name="subject_id" value="<?php echo $edit_id; ?>">
                        
                        <table class="form-table">
                            <tr>
                                <th><label for="subject_name">Subject Name</label></th>
                                <td><input type="text" id="subject_name" name="subject_name" value="<?php echo $subject ? esc_attr($subject['name']) : ''; ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><label for="subject_slug">Slug</label></th>
                                <td><input type="text" id="subject_slug" name="subject_slug" value="<?php echo $subject ? esc_attr($subject['slug']) : ''; ?>" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><label for="subject_description">Description</label></th>
                                <td><textarea id="subject_description" name="subject_description" rows="4" class="large-text"><?php echo $subject ? esc_textarea($subject['description']) : ''; ?></textarea></td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" class="button button-primary" value="<?php echo $edit_id > 0 ? 'Update Subject' : 'Add Subject'; ?>">
                            <?php if ($edit_id > 0): ?>
                                <a href="<?php echo admin_url('admin.php?page=pro-quiz-subjects'); ?>" class="button">Cancel</a>
                            <?php endif; ?>
                        </p>
                    </form>
                </div>

                <div class="pro-quiz-list-section">
                    <h2>All Subjects</h2>
                    <?php if (empty($subjects)): ?>
                        <p>No subjects found. Add your first subject above.</p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Slug</th>
                                    <th>Questions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjects as $sub): ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($sub['name']); ?></strong></td>
                                        <td><?php echo esc_html($sub['slug']); ?></td>
                                        <td><?php echo $sub['question_count']; ?></td>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=pro-quiz-subjects&edit=' . $sub['id']); ?>" class="button button-small">Edit</a>
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pro-quiz-subjects&action=delete&subject_id=' . $sub['id']), 'delete_item'); ?>" 
                                               class="button button-small button-link-delete" 
                                               onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    // Questions Page
    public function render_questions_page()
    {
        global $wpdb;
        $questions_table = $wpdb->prefix . 'pro_quiz_questions';
        $subjects_table = $wpdb->prefix . 'pro_quiz_subjects';
        
        $subject_filter = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
        
        $where = '';
        if ($subject_filter > 0) {
            $where = $wpdb->prepare("WHERE q.subject_id = %d", $subject_filter);
        }
        
        $questions = $wpdb->get_results("SELECT q.*, s.name as subject_name FROM $questions_table q LEFT JOIN $subjects_table s ON q.subject_id = s.id $where ORDER BY q.question_order ASC, q.id DESC", ARRAY_A);
        $subjects = $wpdb->get_results("SELECT * FROM $subjects_table ORDER BY name ASC", ARRAY_A);
        ?>
        <div class="wrap">
            <h1>Questions</h1>
            
            <?php
            // Show admin notices
            if (isset($_GET['updated']) && $_GET['updated'] == '1') {
                $answers_count = isset($_GET['answers']) ? intval($_GET['answers']) : 0;
                $message = 'Question updated successfully.';
                if ($answers_count > 0) {
                    $message .= ' ' . $answers_count . ' answer(s) saved.';
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
            if (isset($_GET['added']) && $_GET['added'] == '1') {
                $answers_count = isset($_GET['answers']) ? intval($_GET['answers']) : 0;
                $message = 'Question added successfully.';
                if ($answers_count > 0) {
                    $message .= ' ' . $answers_count . ' answer(s) saved.';
                }
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
            }
            if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
                echo '<div class="notice notice-success is-dismissible"><p>Question deleted successfully.</p></div>';
            }
            ?>
            
            <div class="pro-quiz-admin-container">
                <div class="pro-quiz-filters">
                    <form method="get" action="">
                        <input type="hidden" name="page" value="pro-quiz-questions">
                        <select name="subject_id">
                            <option value="0">All Subjects</option>
                            <?php foreach ($subjects as $sub): ?>
                                <option value="<?php echo $sub['id']; ?>" <?php selected($subject_filter, $sub['id']); ?>>
                                    <?php echo esc_html($sub['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" class="button" value="Filter">
                        <a href="<?php echo admin_url('admin.php?page=pro-quiz-add-question'); ?>" class="button button-primary">Add New Question</a>
                    </form>
                </div>

                <?php if (empty($questions)): ?>
                    <p>No questions found. <a href="<?php echo admin_url('admin.php?page=pro-quiz-add-question'); ?>">Add your first question</a>.</p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Question</th>
                                <th>Subject</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($questions as $q): ?>
                                <tr>
                                    <td><?php echo $q['id']; ?></td>
                                    <td><strong><?php echo esc_html($q['question_title']); ?></strong></td>
                                    <td><?php echo esc_html($q['subject_name']); ?></td>
                                    <td><?php echo $q['question_order']; ?></td>
                                    <td><?php echo esc_html($q['status']); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=pro-quiz-add-question&edit=' . $q['id']); ?>" class="button button-small">Edit</a>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pro-quiz-questions&action=delete&question_id=' . $q['id']), 'delete_item'); ?>" 
                                           class="button button-small button-link-delete" 
                                           onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    // Add/Edit Question Page
    public function render_add_question_page()
    {
        global $wpdb;
        $questions_table = $wpdb->prefix . 'pro_quiz_questions';
        $answers_table = $wpdb->prefix . 'pro_quiz_answers';
        $subjects_table = $wpdb->prefix . 'pro_quiz_subjects';
        
        $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        $question = null;
        $answers = array();

        if ($edit_id > 0) {
            $question = $wpdb->get_row($wpdb->prepare("SELECT * FROM $questions_table WHERE id = %d", $edit_id), ARRAY_A);
            if ($question) {
                $answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $answers_table WHERE question_id = %d ORDER BY id ASC", $edit_id), ARRAY_A);
            }
        }

        $subjects = $wpdb->get_results("SELECT * FROM $subjects_table ORDER BY name ASC", ARRAY_A);
        ?>
        <div class="wrap">
            <h1><?php echo $edit_id > 0 ? 'Edit Question' : 'Add New Question'; ?></h1>
            
            <form method="post" action="" id="pro-quiz-question-form">
                <?php wp_nonce_field('pro_quiz_question', 'pro_quiz_question_nonce'); ?>
                <input type="hidden" name="pro_quiz_question_action" value="<?php echo $edit_id > 0 ? 'edit' : 'add'; ?>">
                <input type="hidden" name="question_id" value="<?php echo $edit_id; ?>">
                
                <table class="form-table">
                    <tr>
                        <th><label for="question_subject">Subject *</label></th>
                        <td>
                            <select id="question_subject" name="question_subject" class="regular-text" required>
                                <option value="">Select a Subject</option>
                                <?php foreach ($subjects as $sub): ?>
                                    <option value="<?php echo $sub['id']; ?>" <?php selected($question ? $question['subject_id'] : 0, $sub['id']); ?>>
                                        <?php echo esc_html($sub['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="question_title">Question Title *</label></th>
                        <td><input type="text" id="question_title" name="question_title" value="<?php echo $question ? esc_attr($question['question_title']) : ''; ?>" class="large-text" required></td>
                    </tr>
                    <tr>
                        <th><label for="question_content">Question Content</label></th>
                        <td>
                            <?php
                            $content = $question ? $question['question_content'] : '';
                            wp_editor($content, 'question_content', array(
                                'textarea_name' => 'question_content',
                                'textarea_rows' => 10,
                                'media_buttons' => false,
                            ));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="question_order">Order</label></th>
                        <td><input type="number" id="question_order" name="question_order" value="<?php echo $question ? esc_attr($question['question_order']) : '0'; ?>" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="question_status">Status</label></th>
                        <td>
                            <select id="question_status" name="question_status">
                                <option value="publish" <?php selected($question ? $question['status'] : 'publish', 'publish'); ?>>Publish</option>
                                <option value="draft" <?php selected($question ? $question['status'] : 'publish', 'draft'); ?>>Draft</option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2>Answers</h2>
                <div class="quiz-answers-meta-box">
                    <p class="description">Add multiple answers for this question. Select one answer as the correct answer.</p>
                    <div id="quiz-answers-wrapper">
                        <?php if (!empty($answers)): ?>
                            <?php foreach ($answers as $index => $answer):
                                $ui_key = !empty($answer['id']) ? 'existing_' . $answer['id'] : 'new_' . time() . '_' . $index;
                                ?>
                                <div class="quiz-answer-row" data-answer-id="<?php echo !empty($answer['id']) ? $answer['id'] : ''; ?>">
                                    <div class="answer-input-wrapper">
                                        <input type="text" name="quiz_answers[<?php echo esc_attr($ui_key); ?>][text]"
                                            value="<?php echo esc_attr($answer['answer_text']); ?>" 
                                            placeholder="Enter answer text" 
                                            class="regular-text">
                                        <label class="correct-answer-label">
                                            <input type="radio" name="quiz_correct_answer" value="<?php echo esc_attr($ui_key); ?>" <?php checked($answer['is_correct'], 1); ?>>
                                            <span>Mark as Correct</span>
                                        </label>
                                    </div>
                                    <button type="button" class="button remove-answer" title="Remove this answer">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <p>
                        <button type="button" class="button button-primary" id="add-answer">
                            <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> Add Answer
                        </button>
                    </p>
                    <p class="description" style="margin-top: 10px;">
                        <strong>Note:</strong> At least one answer must be marked as correct. Users will select one answer per question.
                    </p>
                </div>

                <p class="submit">
                    <input type="submit" class="button button-primary" value="<?php echo $edit_id > 0 ? 'Update Question' : 'Add Question'; ?>">
                    <a href="<?php echo admin_url('admin.php?page=pro-quiz-questions'); ?>" class="button">Cancel</a>
                </p>
            </form>
        </div>
        <?php
    }

    // Quiz Attempts Page
    public function render_attempts_page()
    {
        global $wpdb;
        $attempts_table = $wpdb->prefix . 'pro_quiz_attempts';
        $subjects_table = $wpdb->prefix . 'pro_quiz_subjects';
        
        $attempts = $wpdb->get_results("SELECT a.*, s.name as subject_name FROM $attempts_table a LEFT JOIN $subjects_table s ON a.subject_id = s.id ORDER BY a.created_at DESC LIMIT 100", ARRAY_A);
        ?>
        <div class="wrap">
            <h1>Quiz Attempts</h1>
            
            <?php if (empty($attempts)): ?>
                <p>No quiz attempts found yet.</p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Subject</th>
                            <th>Score</th>
                            <th>Correct/Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $attempt): ?>
                            <tr>
                                <td><?php echo $attempt['id']; ?></td>
                                <td><?php echo $attempt['user_id'] ? get_userdata($attempt['user_id'])->display_name : 'Guest'; ?></td>
                                <td><?php echo esc_html($attempt['subject_name']); ?></td>
                                <td><strong><?php echo number_format($attempt['score_percentage'], 1); ?>%</strong></td>
                                <td><?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?></td>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($attempt['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    // Instructions Page
    public function render_instructions_page()
    {
        ?>
        <div class="wrap pro-quiz-instructions">
            <h1>Pro Quiz Plugin - Instructions</h1>
            
            <div class="instructions-container">
                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-admin-settings"></span> Getting Started</h2>
                    <p>Pro Quiz is a powerful WordPress plugin that allows you to create interactive quizzes with subjects, questions, and multiple choice answers. All data is stored in custom database tables for optimal performance.</p>
                    
                    <h3>Step-by-Step Guide:</h3>
                    <div class="steps-list">
                        <div class="step-item">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h4>Create a Subject</h4>
                                <p>Subjects help you organize your questions into categories (e.g., "Mathematics", "History", "Science").</p>
                                <ul>
                                    <li>Go to <strong>Pro Quiz → Subjects</strong></li>
                                    <li>Enter a subject name (e.g., "French Language")</li>
                                    <li>Enter a slug (URL-friendly version, e.g., "french-language")</li>
                                    <li>Optionally add a description</li>
                                    <li>Click <strong>"Add Subject"</strong></li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h4>Add Questions</h4>
                                <p>Create questions with multiple choice answers. Each question must have at least one correct answer.</p>
                                <ul>
                                    <li>Go to <strong>Pro Quiz → Add Question</strong></li>
                                    <li>Select a subject from the dropdown</li>
                                    <li>Enter the question title (e.g., "What is the capital of France?")</li>
                                    <li>Optionally add question content/description</li>
                                    <li>Set the question order (for sorting)</li>
                                    <li>Choose status: <strong>Publish</strong> (visible) or <strong>Draft</strong> (hidden)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h4>Add Answers</h4>
                                <p>For each question, add multiple answers and mark one as correct.</p>
                                <ul>
                                    <li>In the <strong>"Answers"</strong> section, click <strong>"Add Answer"</strong></li>
                                    <li>Enter the answer text</li>
                                    <li>Click <strong>"Mark as Correct"</strong> for the correct answer</li>
                                    <li>You can add as many answers as you want</li>
                                    <li>Remove answers by clicking the <strong>"Remove"</strong> button</li>
                                    <li><strong>Important:</strong> At least one answer must be marked as correct</li>
                                </ul>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="step-number">4</div>
                            <div class="step-content">
                                <h4>Save the Question</h4>
                                <p>Click <strong>"Add Question"</strong> or <strong>"Update Question"</strong> to save.</p>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="step-number">5</div>
                            <div class="step-content">
                                <h4>Display the Quiz</h4>
                                <p>Use the shortcode to display your quiz on any page or post.</p>
                                <div class="code-examples">
                                    <h5>Display quiz by subject slug:</h5>
                                    <code>[pro_quiz subject="french-language"]</code>
                                    
                                    <h5>Display quiz by subject ID:</h5>
                                    <code>[pro_quiz subject_id="1"]</code>
                                    
                                    <h5>Display all questions (no filter):</h5>
                                    <code>[pro_quiz]</code>
                                </div>
                                <p><strong>How to use:</strong></p>
                                <ol>
                                    <li>Edit any page or post in WordPress</li>
                                    <li>Add the shortcode where you want the quiz to appear</li>
                                    <li>Publish or update the page</li>
                                    <li>The quiz will display with all questions from the selected subject</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-admin-tools"></span> Managing Your Quiz</h2>
                    
                    <h3>View All Questions</h3>
                    <p>Go to <strong>Pro Quiz → Questions</strong> to see all your questions. You can:</p>
                    <ul>
                        <li>Filter questions by subject</li>
                        <li>Edit existing questions</li>
                        <li>Delete questions</li>
                        <li>See question status (Published/Draft)</li>
                    </ul>

                    <h3>Edit Questions</h3>
                    <p>To edit a question:</p>
                    <ol>
                        <li>Go to <strong>Pro Quiz → Questions</strong></li>
                        <li>Click <strong>"Edit"</strong> next to the question you want to modify</li>
                        <li>Make your changes</li>
                        <li>Click <strong>"Update Question"</strong></li>
                    </ol>

                    <h3>Manage Subjects</h3>
                    <p>Go to <strong>Pro Quiz → Subjects</strong> to:</p>
                    <ul>
                        <li>View all subjects</li>
                        <li>See how many questions are in each subject</li>
                        <li>Edit or delete subjects</li>
                    </ul>
                </div>

                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-chart-bar"></span> Quiz Attempts</h2>
                    <p>Go to <strong>Pro Quiz → Quiz Attempts</strong> to view all quiz attempts by users. You can see:</p>
                    <ul>
                        <li>User who took the quiz (or "Guest" for non-logged-in users)</li>
                        <li>Subject of the quiz</li>
                        <li>Score percentage</li>
                        <li>Number of correct answers vs total questions</li>
                        <li>Date and time of the attempt</li>
                    </ul>
                </div>

                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-info"></span> Features</h2>
                    <div class="features-grid">
                        <div class="feature-box">
                            <h4><span class="dashicons dashicons-database"></span> Custom Tables</h4>
                            <p>All data is stored in custom database tables for better performance and organization.</p>
                        </div>
                        <div class="feature-box">
                            <h4><span class="dashicons dashicons-admin-multisite"></span> Multiple Subjects</h4>
                            <p>Organize questions into different subjects/categories.</p>
                        </div>
                        <div class="feature-box">
                            <h4><span class="dashicons dashicons-format-chat"></span> Multiple Answers</h4>
                            <p>Add as many answer options as you need for each question.</p>
                        </div>
                        <div class="feature-box">
                            <h4><span class="dashicons dashicons-yes-alt"></span> Instant Results</h4>
                            <p>Users see immediate feedback with correct/incorrect answers highlighted.</p>
                        </div>
                        <div class="feature-box">
                            <h4><span class="dashicons dashicons-chart-line"></span> Score Tracking</h4>
                            <p>Quiz attempts are automatically saved with scores and statistics.</p>
                        </div>
                        <div class="feature-box">
                            <h4><span class="dashicons dashicons-smartphone"></span> Responsive Design</h4>
                            <p>Quizzes look great on all devices - desktop, tablet, and mobile.</p>
                        </div>
                    </div>
                </div>

                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-editor-help"></span> Tips & Best Practices</h2>
                    <ul>
                        <li><strong>Question Order:</strong> Use the order field to control the sequence of questions in your quiz</li>
                        <li><strong>Question Status:</strong> Use "Draft" status to work on questions before making them public</li>
                        <li><strong>Subject Slugs:</strong> Use lowercase letters, numbers, and hyphens only (e.g., "french-language", "math-101")</li>
                        <li><strong>Answer Shuffling:</strong> Answers are automatically shuffled for each user to prevent cheating</li>
                        <li><strong>Required Fields:</strong> Subject, Question Title, and at least one answer are required</li>
                        <li><strong>Correct Answer:</strong> Always mark exactly one answer as correct per question</li>
                    </ul>
                </div>

                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-sos"></span> Troubleshooting</h2>
                    
                    <h3>Quiz not displaying?</h3>
                    <ul>
                        <li>Make sure questions are set to <strong>"Publish"</strong> status, not "Draft"</li>
                        <li>Verify the subject slug in the shortcode matches the subject slug in your database</li>
                        <li>Check that questions have at least one answer</li>
                        <li>Ensure the shortcode is placed correctly in your page/post content</li>
                    </ul>

                    <h3>No questions showing?</h3>
                    <ul>
                        <li>Check that you've created questions for the selected subject</li>
                        <li>Verify questions are published (not in draft status)</li>
                        <li>Make sure the subject ID or slug in the shortcode is correct</li>
                    </ul>

                    <h3>Answers not saving?</h3>
                    <ul>
                        <li>Make sure at least one answer is marked as correct</li>
                        <li>Verify all answer text fields are filled in</li>
                        <li>Check that you clicked "Add Question" or "Update Question" button</li>
                    </ul>
                </div>

                <div class="instruction-section">
                    <h2><span class="dashicons dashicons-email-alt"></span> Need Help?</h2>
                    <p>If you encounter any issues or have questions about using Pro Quiz, please:</p>
                    <ul>
                        <li>Check this instructions page for common solutions</li>
                        <li>Review the dashboard for quick statistics</li>
                        <li>Ensure all required fields are filled when creating subjects and questions</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    // Handle Subject Save
    private function handle_subject_save()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_quiz_subjects';
        
        $action = sanitize_text_field($_POST['pro_quiz_subject_action']);
        $name = sanitize_text_field($_POST['subject_name']);
        $slug = sanitize_text_field($_POST['subject_slug']);
        $description = isset($_POST['subject_description']) ? sanitize_textarea_field($_POST['subject_description']) : '';

        if ($action === 'edit') {
            $id = intval($_POST['subject_id']);
            $wpdb->update(
                $table_name,
                array(
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description
                ),
                array('id' => $id),
                array('%s', '%s', '%s'),
                array('%d')
            );
            wp_redirect(admin_url('admin.php?page=pro-quiz-subjects&updated=1'));
        } else {
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description
                ),
                array('%s', '%s', '%s')
            );
            wp_redirect(admin_url('admin.php?page=pro-quiz-subjects&added=1'));
        }
        exit;
    }

    // Handle Question Save
    private function handle_question_save()
    {
        global $wpdb;
        $questions_table = $wpdb->prefix . 'pro_quiz_questions';
        $answers_table = $wpdb->prefix . 'pro_quiz_answers';
        
        $action = sanitize_text_field($_POST['pro_quiz_question_action']);
        $subject_id = intval($_POST['question_subject']);
        $title = sanitize_text_field($_POST['question_title']);
        $content = isset($_POST['question_content']) ? wp_kses_post($_POST['question_content']) : '';
        $order = isset($_POST['question_order']) ? intval($_POST['question_order']) : 0;
        $status = isset($_POST['question_status']) ? sanitize_text_field($_POST['question_status']) : 'publish';

        if ($action === 'edit') {
            $id = intval($_POST['question_id']);
            $wpdb->update(
                $questions_table,
                array(
                    'subject_id' => $subject_id,
                    'question_title' => $title,
                    'question_content' => $content,
                    'question_order' => $order,
                    'status' => $status
                ),
                array('id' => $id),
                array('%d', '%s', '%s', '%d', '%s'),
                array('%d')
            );
            $question_id = $id;
        } else {
            $wpdb->insert(
                $questions_table,
                array(
                    'subject_id' => $subject_id,
                    'question_title' => $title,
                    'question_content' => $content,
                    'question_order' => $order,
                    'status' => $status
                ),
                array('%d', '%s', '%s', '%d', '%s')
            );
            $question_id = $wpdb->insert_id;
        }

        // Save answers
        if (isset($_POST['quiz_answers']) && is_array($_POST['quiz_answers']) && !empty($_POST['quiz_answers'])) {
            $answers = $_POST['quiz_answers'];
            $correct_index = isset($_POST['quiz_correct_answer']) ? sanitize_text_field($_POST['quiz_correct_answer']) : null;
            $existing_ids = array();
            $has_existing_answers = false;
            $answers_saved = 0;

            foreach ($answers as $index => $answer) {
                // Check if answer text exists and is not empty
                if (isset($answer['text']) && !empty(trim($answer['text']))) {
                    $is_correct = ($index == $correct_index) ? 1 : 0;
                    
                    if (strpos($index, 'existing_') === 0) {
                        $has_existing_answers = true;
                        $existing_id = intval(str_replace('existing_', '', $index));
                        if ($existing_id > 0) {
                            $existing_ids[] = $existing_id;
                            
                            $result = $wpdb->update(
                                $answers_table,
                                array(
                                    'answer_text' => sanitize_text_field($answer['text']),
                                    'is_correct' => $is_correct
                                ),
                                array('id' => $existing_id, 'question_id' => $question_id),
                                array('%s', '%d'),
                                array('%d', '%d')
                            );
                            if ($result !== false) {
                                $answers_saved++;
                            }
                        }
                    } else {
                        // New answer
                        $result = $wpdb->insert(
                            $answers_table,
                            array(
                                'question_id' => $question_id,
                                'answer_text' => sanitize_text_field($answer['text']),
                                'is_correct' => $is_correct
                            ),
                            array('%d', '%s', '%d')
                        );
                        if ($result !== false) {
                            $answers_saved++;
                        }
                    }
                }
            }

            // Delete removed answers - only if we're editing and had existing answers
            if ($has_existing_answers && !empty($existing_ids)) {
                $placeholders = implode(',', array_fill(0, count($existing_ids), '%d'));
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $answers_table WHERE question_id = %d AND id NOT IN ($placeholders)",
                    array_merge(array($question_id), $existing_ids)
                ));
            } elseif ($action === 'edit' && $has_existing_answers && empty($existing_ids)) {
                // If editing and all existing answers were removed, delete them
                $wpdb->delete($answers_table, array('question_id' => $question_id), array('%d'));
            }
        } elseif ($action === 'edit') {
            // If editing and no answers submitted, don't delete existing ones (user might have just updated question fields)
            // Only delete if explicitly needed - we'll leave existing answers intact
        }

        // Set success message
        $message = 'updated';
        if ($action === 'add') {
            $message = 'added';
        }
        
        // Add answer count to message if answers were saved
        if (isset($answers_saved) && $answers_saved > 0) {
            $message .= '&answers=' . $answers_saved;
        }
        
        wp_redirect(admin_url('admin.php?page=pro-quiz-questions&' . $message . '=1'));
        exit;
    }

    // Handle Subject Delete
    private function handle_subject_delete($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pro_quiz_subjects';
        $wpdb->delete($table_name, array('id' => $id), array('%d'));
        wp_redirect(admin_url('admin.php?page=pro-quiz-subjects&deleted=1'));
        exit;
    }

    // Handle Question Delete
    private function handle_question_delete($id)
    {
        global $wpdb;
        $questions_table = $wpdb->prefix . 'pro_quiz_questions';
        $answers_table = $wpdb->prefix . 'pro_quiz_answers';
        
        // Delete answers first
        $wpdb->delete($answers_table, array('question_id' => $id), array('%d'));
        // Delete question
        $wpdb->delete($questions_table, array('id' => $id), array('%d'));
        
        wp_redirect(admin_url('admin.php?page=pro-quiz-questions&deleted=1'));
        exit;
    }
}
