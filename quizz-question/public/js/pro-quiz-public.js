jQuery(document).ready(function ($) {
    var form = $('#pro-quiz-form');
    var resultsContainer = $('#quiz-results');
    var resultsSummary = $('#results-summary');
    var resultsDetails = $('#results-details');

    form.on('submit', function (e) {
        e.preventDefault();

        var totalQuestions = form.find('.quiz-question').length;
        var correctCount = 0;
        var answeredCount = 0;
        var results = [];

        // Reset previous results
        form.find('.quiz-answer-label').removeClass('correct incorrect selected');
        form.find('.question-feedback').hide().removeClass('success error');
        form.find('.feedback-icon, .feedback-text').empty();

        form.find('.quiz-question').each(function () {
            var question = $(this);
            var questionId = question.data('question-id');
            var questionTitle = question.find('.question-title').text();
            var selectedInput = question.find('input[type="radio"]:checked');
            var feedback = question.find('.question-feedback');
            var feedbackIcon = feedback.find('.feedback-icon');
            var feedbackText = feedback.find('.feedback-text');

            // Find the correct answer element
            var correctLabel = question.find('.quiz-answer-label[data-is-correct="1"]');
            var correctAnswerText = correctLabel.find('.answer-text').text();

            var result = {
                questionId: questionId,
                questionTitle: questionTitle,
                correctAnswer: correctAnswerText,
                userAnswer: '',
                isCorrect: false,
                answered: false
            };

            if (selectedInput.length > 0) {
                answeredCount++;
                result.answered = true;
                
                var selectedLabel = selectedInput.closest('label');
                var selectedAnswerText = selectedLabel.find('.answer-text').text();
                result.userAnswer = selectedAnswerText;
                
                var isCorrect = selectedLabel.attr('data-is-correct') === '1';

                if (isCorrect) {
                    correctCount++;
                    result.isCorrect = true;
                    selectedLabel.addClass('correct selected');
                    feedback.addClass('success').show();
                    feedbackIcon.html('✓');
                    feedbackText.text('Correct!');
                } else {
                    selectedLabel.addClass('incorrect selected');
                    correctLabel.addClass('correct'); // Highlight the correct answer
                    feedback.addClass('error').show();
                    feedbackIcon.html('✗');
                    feedbackText.text('Incorrect. The correct answer is: ' + correctAnswerText);
                }
            } else {
                // Not answered
                correctLabel.addClass('correct'); // Highlight correct even if skipped
                feedback.addClass('error').show();
                feedbackIcon.html('⚠');
                feedbackText.text('Not answered. The correct answer is: ' + correctAnswerText);
            }

            results.push(result);
        });

        // Calculate percentage
        var percentage = totalQuestions > 0 ? Math.round((correctCount / totalQuestions) * 100) : 0;

        // Show Results Summary
        var summaryHtml = '<div class="score-display">';
        summaryHtml += '<div class="score-circle">';
        summaryHtml += '<span class="score-number">' + correctCount + '</span>';
        summaryHtml += '<span class="score-total">/ ' + totalQuestions + '</span>';
        summaryHtml += '</div>';
        summaryHtml += '<div class="score-percentage">' + percentage + '%</div>';
        summaryHtml += '<div class="score-message">';
        if (percentage >= 80) {
            summaryHtml += '<p class="message-excellent">Excellent work!</p>';
        } else if (percentage >= 60) {
            summaryHtml += '<p class="message-good">Good job!</p>';
        } else {
            summaryHtml += '<p class="message-needs-improvement">Keep practicing!</p>';
        }
        summaryHtml += '</div>';
        summaryHtml += '</div>';

        resultsSummary.html(summaryHtml);

        // Show Results Details
        var detailsHtml = '<div class="results-list">';
        results.forEach(function(result, index) {
            detailsHtml += '<div class="result-item ' + (result.isCorrect ? 'correct' : 'incorrect') + '">';
            detailsHtml += '<div class="result-question">';
            detailsHtml += '<span class="result-number">' + (index + 1) + '.</span>';
            detailsHtml += '<span class="result-title">' + result.questionTitle + '</span>';
            detailsHtml += '</div>';
            detailsHtml += '<div class="result-answer">';
            if (result.answered) {
                detailsHtml += '<div class="user-answer">';
                detailsHtml += '<strong>Your answer:</strong> ' + result.userAnswer;
                detailsHtml += '<span class="result-icon">' + (result.isCorrect ? '✓' : '✗') + '</span>';
                detailsHtml += '</div>';
            } else {
                detailsHtml += '<div class="user-answer skipped">';
                detailsHtml += '<strong>Not answered</strong>';
                detailsHtml += '</div>';
            }
            if (!result.isCorrect) {
                detailsHtml += '<div class="correct-answer">';
                detailsHtml += '<strong>Correct answer:</strong> ' + result.correctAnswer;
                detailsHtml += '</div>';
            }
            detailsHtml += '</div>';
            detailsHtml += '</div>';
        });
        detailsHtml += '</div>';

        resultsDetails.html(detailsHtml);

        // Show results container
        resultsContainer.slideDown(400);
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: resultsContainer.offset().top - 50
        }, 500);

        // Save quiz attempt to database
        var subjectId = $('.pro-quiz-container').data('subject-id') || 0;
        if (subjectId > 0 && typeof proQuiz !== 'undefined') {
            var userAnswers = {};
            results.forEach(function(result) {
                userAnswers[result.questionId] = {
                    userAnswer: result.userAnswer,
                    isCorrect: result.isCorrect,
                    answered: result.answered
                };
            });

            $.ajax({
                url: proQuiz.ajax_url,
                type: 'POST',
                data: {
                    action: 'pro_quiz_save_attempt',
                    nonce: proQuiz.nonce,
                    subject_id: subjectId,
                    total_questions: totalQuestions,
                    correct_answers: correctCount,
                    user_answers: userAnswers
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Quiz attempt saved:', response.data);
                    }
                }
            });
        }

        // Disable form inputs after submission
        form.find('input[type="radio"]').prop('disabled', true);
        form.find('.quiz-submit-btn').prop('disabled', true).text('Quiz Submitted');
    });
});
