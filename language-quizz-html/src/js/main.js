import "../styles/main.css";

jQuery(function () {
  function populate() {
    if (document.getElementById("question") === null) return;

    if (performance.navigation.type == performance.navigation.TYPE_RELOAD) {
      document.getElementById("question").focus();
    }

    getDataJson().then(function (data) {
      // create quiz
      var quiz = new Quiz(data);
      if (quiz.isEnded()) {
        showScores();
      } else {
        // show notes
        var eNote = document.getElementById("question-desc");
        // eNote.innerHTML = decodeURIComponent(quiz.getQuestionIndex().firstItem.notes);
        // show question
        var element = document.getElementById("question");
        element.innerHTML = quiz.getQuestionIndex().firstItem.name;
        // show options
        var choices = quiz.getQuestionIndex().choices;
        for (var i = 0; i < choices.length; i++) {
          var element = document.getElementById("choice" + i);
          element.innerHTML = choices[i].image ? '<img alt="' + choices[i].name + '" class="object-cover rounded-lg shadow border-2 border-solid border-white transition-all ease-in-out duration-300 cursor-pointer" src="' + choices[i].image + '"/>' : choices[i].name;
          guess(quiz, "btn" + i, choices[i].id);
        }
      }
    });
  };

  function guess(quiz, id, guess) {
    var button = document.getElementById(id);
    button.onclick = function () {
      quiz.guess(guess, this);
      //populate();
    }
  };

  function showScores() {
    var gameOverHTML = "<div class='p-5'>";
    gameOverHTML += "<h3 id='score' class='flex-auto text-4xl mb-5 text-yellow-900'> Your scores: <br/><div class='text-red-300 text-center'>" + localStorage.getItem("currentQuestionNumber") + "</div></h3>";
    gameOverHTML += "<div id='quizz-reset' class='mt-3 text-center sm:mt-0 py-2 px-8 bg-orange-200 hover:bg-red-200 hover:shadow-2xl font-bold text-yellow-900 rounded-lg shadow-md cursor-pointer'> Bắt đầu lại Quiz </div>";
    gameOverHTML += "</div>";
    var element = document.getElementById("quiz");
    element.innerHTML = gameOverHTML;
    var resetElement = document.getElementById("quizz-reset");
    resetElement.onclick = (function () {
      localStorage.setItem("currentQuestionNumber", 0);
      localStorage.setItem("QuizIsEnd", 0);
      location.reload();
    });
  };

  async function fetchDataAsync(url) {
    const response = await fetch(url);
    const jsonData = await response.json();
    return jsonData.result.data;
  }

  function shuffle(array) {
    let currentIndex = array.length, randomIndex;

    // While there remain elements to shuffle.
    while (currentIndex != 0) {

      // Pick a remaining element.
      randomIndex = Math.floor(Math.random() * currentIndex);
      currentIndex--;

      // And swap it with the current element.
      [array[currentIndex], array[randomIndex]] = [
        array[randomIndex], array[currentIndex]];
    }

    return array;
  }

  function getMultipleRandom(arr, num) {
    var result = arr.slice(0, num).map(function () {
      return this.splice(Math.floor(Math.random() * this.length), 1)[0];
    }, arr.slice());

    return result;
  }

  function insertAfter(referenceNode, newNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
  }

  function getDataJson() {
    return fetchDataAsync('/wp-content/uploads/data/quizz-data.json').then(function (dataQuestionsJson) {
      var dataQuestions = [];
      var choiceList = [];
      var firstItemIndex = 0;

      totalQuestions = Object.keys(dataQuestionsJson).length;
      if (localStorage.getItem("currentQuestionNumber") === null) {
        localStorage.setItem("currentQuestionNumber", 0);
      } else if (localStorage.getItem("currentQuestionNumber") >= totalQuestions) {
        localStorage.setItem("QuizIsEnd", 1);
      }
      else {
        firstItemIndex = localStorage.getItem("currentQuestionNumber");
      }

      var firstItem = dataQuestionsJson[firstItemIndex];

      if (dataQuestions != null)
        choiceList.push(firstItem);

      delete dataQuestionsJson[firstItemIndex];

      for (var i in dataQuestionsJson) {
        if (dataQuestionsJson[i] !== "undefined")
          dataQuestions.push(dataQuestionsJson[i]);
      }

      const arrayQuestions = getMultipleRandom(dataQuestions, 3);
      choiceList.push(...arrayQuestions);

      choiceList = shuffle(choiceList);

      questions = [
        new Question(firstItem, choiceList, firstItem.id)
      ]
      return questions;
    });
  }

  // create questions
  var questions = [
  ];
  var totalQuestions = 0;

  function Question(firstItem, choices, answer) {
    this.firstItem = firstItem;
    this.choices = choices;
    this.answer = answer;
  }

  Question.prototype.isCorrectAnswer = function (choice) {
    return this.answer === choice;
  }


  function Quiz(data) {
    this.score = 0;
    this.questions = data;
    this.questionIndex = 0;
  }

  Quiz.prototype.getQuestionIndex = function () {
    return this.questions[this.questionIndex];
  }

  Quiz.prototype.guess = function (answer, buttonElement) {

    if (this.getQuestionIndex().isCorrectAnswer(answer)) {
      this.score++;
      var indexItem = 0;
      buttonElement.innerHTML += '<div class="quiz-icon text-green-400"><i class="fa fa-check"></i></div>';

      buttonElement.classList.add("right-choice");
      if (localStorage.getItem("currentQuestionNumber") !== null) {
        indexItem = parseInt(localStorage.getItem("currentQuestionNumber"));
        localStorage.setItem("currentQuestionNumber", ++indexItem);
      } else {
        firstItemIndex = localStorage.getItem("currentQuestionNumber");
      }

      this.CountdownTimer(buttonElement);
      return;
    }

    buttonElement.innerHTML += '<div class="quiz-icon text-red-700"><i class="fa fa-times"></i></div>';
    buttonElement.classList.add("wrong-choice");
    localStorage.setItem("QuizIsEnd", 1);
    this.RestartQuiz(buttonElement);

    //this.questionIndex++;
  }

  Quiz.prototype.isEnded = function () {
    if (localStorage.getItem("QuizIsEnd") === null) {
      return false;
    } else {
      var isEnd = localStorage.getItem("QuizIsEnd");
      if (isEnd == 1)
        return true;
    }
    return false;
  }

  Quiz.prototype.CountdownTimer = function (element) {
    var timeleft = 4;

    var el = document.createElement("div");
    element.insertBefore(el, element.firstChild);

    var downloadTimer = setInterval(function () {
      timeleft--;
      el.innerHTML = "<p class='text-center p-5 text-yellow-900'>" + "Câu hỏi tiếp theo sẽ xuất hiện trong " + timeleft + "s" + "</p>";
      if (timeleft == 1) {
        location.reload();
        return;
      }
    }, 1000);
  }

  Quiz.prototype.RestartQuiz = function (element) {
    var timeleft = 4;

    var el = document.createElement("div");
    element.insertBefore(el, element.firstChild);

    var downloadTimer = setInterval(function () {
      timeleft--;
      el.innerHTML = "<p class='text-center p-5 text-yellow-900'>" + "Bạn đã trả lời sai !!! " + timeleft + "s" + "</p>";
      if (timeleft == 1) {
        location.reload();
        return;
      }
    }, 1000);
  }
  // display quiz
  populate();
})
