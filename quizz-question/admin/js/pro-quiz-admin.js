jQuery(document).ready(function ($) {
    var wrapper = $('#quiz-answers-wrapper');
    var add_btn = $('#add-answer');

    // Add new answer row
    add_btn.on('click', function (e) {
        e.preventDefault();
        var newIndex = 'new_' + new Date().getTime() + '_' + Math.random().toString(36).substr(2, 9);
        var html = `
            <div class="quiz-answer-row" data-answer-id="">
                <div class="answer-input-wrapper">
                    <input type="text" name="quiz_answers[${newIndex}][text]" placeholder="Enter answer text" class="regular-text">
                    <label class="correct-answer-label">
                        <input type="radio" name="quiz_correct_answer" value="${newIndex}">
                        <span>Mark as Correct</span>
                    </label>
                </div>
                <button type="button" class="button remove-answer" title="Remove this answer">Remove</button>
            </div>
        `;
        wrapper.append(html);
    });

    // Remove answer row
    wrapper.on('click', '.remove-answer', function (e) {
        e.preventDefault();
        var row = $(this).closest('.quiz-answer-row');
        var radio = row.find('input[type="radio"]');
        
        // If removing the correct answer, uncheck it
        if (radio.is(':checked')) {
            radio.prop('checked', false);
        }
        
        row.fadeOut(200, function() {
            $(this).remove();
            // Ensure at least one answer remains marked as correct if any answers exist
            checkCorrectAnswer();
        });
    });

    // Ensure at least one correct answer is selected
    function checkCorrectAnswer() {
        var hasChecked = wrapper.find('input[name="quiz_correct_answer"]:checked').length > 0;
        if (!hasChecked && wrapper.find('.quiz-answer-row').length > 0) {
            // Auto-select first answer if none is selected
            wrapper.find('.quiz-answer-row:first input[name="quiz_correct_answer"]').prop('checked', true);
        }
    }

    // Validate before form submission
    $('#pro-quiz-question-form').on('submit', function(e) {
        var answerCount = wrapper.find('.quiz-answer-row').length;
        var correctAnswer = wrapper.find('input[name="quiz_correct_answer"]:checked').length;
        var hasAnswers = false;
        
        // Check if any answer has text
        wrapper.find('input[name^="quiz_answers"]').each(function() {
            if ($(this).val().trim() !== '') {
                hasAnswers = true;
                return false; // break
            }
        });
        
        if (answerCount === 0 || !hasAnswers) {
            alert('Please add at least one answer for this question.');
            e.preventDefault();
            return false;
        }
        
        if (correctAnswer === 0) {
            alert('Please mark at least one answer as correct.');
            e.preventDefault();
            return false;
        }
    });

    // Check on page load
    checkCorrectAnswer();
    
    // Check when radio buttons change
    wrapper.on('change', 'input[name="quiz_correct_answer"]', function() {
        checkCorrectAnswer();
    });
});
