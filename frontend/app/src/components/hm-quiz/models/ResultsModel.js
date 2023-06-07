import AnswerModel from "./AnswerModel";

export default class ResultsModel {
  constructor() {
    this._answers = {};
    this._comments = {};
  }

  addAnswer(answer) {
    const { question_id } = answer;
    this._answers[question_id] = new AnswerModel(answer);
  }

  addComment(comment) {
    const { question_id, text } = comment;
    this._comments[question_id] = text;
  }

  removeAnswer(question_id) {
    if (this._answers[question_id]) delete this._answers[question_id];
  }

  collectAnswers(params, qIDs) {
    const saveParams = new FormData();
    for (const key in params) {
      if (params.hasOwnProperty(key)) {
        saveParams.append(key, params[key]);
      }
    }
    for (const question_id in this._answers) {
      if (this._answers.hasOwnProperty(question_id) &&
        qIDs.includes(question_id)) {
        const { answersForSave } = this._answers[question_id];
        for (const answer of answersForSave) {
          saveParams.append(...answer);
        }
      }

    }
    for (const question_id in this._comments) {
      if (!this._comments.hasOwnProperty(question_id)) continue;
      saveParams.append(`comment[${question_id}]`, this._comments[question_id]);
    }
    return saveParams;
  }

  toString() {
    return JSON.stringify(this._answers);
  }
}
