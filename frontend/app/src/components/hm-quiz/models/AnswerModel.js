const md5 = require('md5');

const QuestionType = {
  SINGLE: 0,
  MULTIPLE: 1,
  TEXT: 2,
  MAPPING: 3,
  SORTING: 4,
  CLASSIFICATION: 5,
  IMAGEMAP: 6,
  PLACEHOLDER: 7,
  FREE: 8,
};

const getAnswerForSingle = ({ question_id, answers }) => {
  const result = [];
  result.push([`results[${question_id}]`, answers]);
  return result;
};

const getAnswerForMultiple = ({ question_id, answers }) => {
  const result = [];
  for (const variant_id of answers) {
    result.push([`results[${question_id}][]`, variant_id]);
  }
  return result;
};

const getAnswerForText = ({ question_id, answers }) => {
  const result = [];
  result.push([`results[${question_id}]`, -1]);
  result.push([`results_${question_id}`, answers]);
  return result;
};

const getAnswerForMapping = ({ question_id, answers }) => {
  const result = [];
  for (const answer in answers) {
    if (answers.hasOwnProperty(answer)) {
      const answerValue = answers[answer];
      result.push([`results[${question_id}][${answer}]`, answerValue]);
    }
  }
  return result;
};

const getAnswerForSorting = ({ question_id, answers }) => {
  const result = [];
  for (const answer in answers) {
    if (answers.hasOwnProperty(answer)) {
      const answerValue = answers[answer];
      result.push([`results[${question_id}][${answer}]`, answerValue]);
    }
  }
  return result;
};

const getAnswerForClassification = ({ question_id, answers }) => {
  const result = [];
  for (const answer in answers) {
    if (answers.hasOwnProperty(answer)) {
      const answerValue = answers[answer];
      result.push([`results[${question_id}][${answer}]`, answerValue]);
    }
  }
  return result;
};

const getAnswerForImageMap = ({ question_id, answers }) => {
  const result = [];
  for (const variant_id of answers) {
    result.push([`results[${question_id}][]`, variant_id]);
  }
  return result;
};

const getAnswerForPlaceholder = ({ question_id, answers }) => {
  const result = [];
  for (const answer in answers) {
    if (answers.hasOwnProperty(answer)) {
      const answerId = answer;
      const answerValue = answers[answerId];
      if (Array.isArray(answerValue)) {
        for (const answer of answerValue) {
          result.push([`results[${question_id}][${answerId}][]`, answer]);
        }
      } else {
        result.push([`results[${question_id}][${answerId}]`, answerValue]);
      }
    }
  }
  return result;
};

const appendAnswerToFormData = {
  [QuestionType.SINGLE]: getAnswerForSingle,
  [QuestionType.MULTIPLE]: getAnswerForMultiple,
  [QuestionType.TEXT]: getAnswerForText,
  [QuestionType.MAPPING]: getAnswerForMapping,
  [QuestionType.SORTING]: getAnswerForSorting,
  [QuestionType.CLASSIFICATION]: getAnswerForClassification,
  [QuestionType.IMAGEMAP]: getAnswerForImageMap,
  [QuestionType.PLACEHOLDER]: getAnswerForPlaceholder,
  [QuestionType.FREE]: getAnswerForText,
};

export default class AnswerModel {
  constructor(data) {
    this.question_id = data.question_id;
    this.type = data.type;
    this.answers = data.answers;
  }
  get answersForSave() {
    const answer = {
      question_id: this.question_id,
      answers: this.answers,
    };
    return this._getAnswersForSave(answer);
  }

  get _typeAsNumber() {
    return QuestionType[this.type.toUpperCase()];
  }

  get _getAnswersForSave() {
    return appendAnswerToFormData[this._typeAsNumber];
  }
}
