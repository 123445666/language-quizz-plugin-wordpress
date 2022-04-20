function populate() {
  getDataJson().then(function (data) {
    // create quiz
    var quiz = new Quiz(data);

    if (quiz.isEnded()) {
      showScores();
    } else {
      // show question
      var element = document.getElementById("question");
      element.innerHTML = quiz.getQuestionIndex().text;

      // show options
      var choices = quiz.getQuestionIndex().choices;
      for (var i = 0; i < choices.length; i++) {
        var element = document.getElementById("choice" + i);
        element.innerHTML = choices[i].image ? '<img src="' + choices[i].image + '"/>' : choices[i].name;
        guess(quiz, "btn" + i, choices[i].id);
      }

      showProgress();
    }

  })

};

function guess(quiz, id, guess) {
  var button = document.getElementById(id);
  button.onclick = function () {
    quiz.guess(guess);
    populate();
  }
};

function showProgress() {
  var currentQuestionNumber = localStorage.getItem("currentQuestionNumber");
  var element = document.getElementById("progress");
  element.innerHTML = "Question " + currentQuestionNumber + " of " + totalQuestions;
};

function showScores() {
  var gameOverHTML = "<h1>Result</h1>";
  gameOverHTML += "<h2 id='score'> Your scores: " + quiz.score + "</h2>";
  var element = document.getElementById("quiz");
  element.innerHTML = gameOverHTML;
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
  result = arr.slice(0, num).map(function () {
    return this.splice(Math.floor(Math.random() * this.length), 1)[0];
  }, arr.slice());

  return result;
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
      localStorage.setItem("currentQuestionNumber", 0);
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
      new Question(firstItem.name, choiceList, firstItem.id)
    ]
    return questions;
  });
}

// create questions
var questions = [
];
var totalQuestions = 0;

function Question(text, choices, answer) {
  this.text = text;
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

Quiz.prototype.guess = function (answer) {
  console.log(answer);
  console.log(this.getQuestionIndex().isCorrectAnswer(answer));
  if (this.getQuestionIndex().isCorrectAnswer(answer)) {
    this.score++;
    var indexItem = 0;
    if (localStorage.getItem("currentQuestionNumber") !== null) {
      indexItem = parseInt(localStorage.getItem("currentQuestionNumber"));
      localStorage.setItem("currentQuestionNumber", ++indexItem);
    } else {
      firstItemIndex = localStorage.getItem("currentQuestionNumber");
    }
  }

  //this.questionIndex++;
}

Quiz.prototype.isEnded = function () {
  return false;
  //return this.questionIndex === this.questions.length;
}

// display quiz
populate();


