import ContextModel from "./ContextModel";
import QuestionsListModel from "./QuestionsListModel";

import adaptContext from "./adapters/adaptContext";
import adaptQuestionList from "./adapters/adaptQuestionList";

class TestModel {
  constructor(model, withContext = false) {
    this._model = model;
    this.hasContext = withContext;
    if (withContext) {
      this._context = new ContextModel();
    }
    const progress = model.progress;
    const modelObj = model.model;
    this._type = model.model.attempt.type;
    this._comments =
      model.settings && model.settings.comments
        ? model.settings.comments
        : null;
    this.mode_self_test =
      (model.settings && parseInt(model.settings.mode_self_test, 10)) || null;
    this._limit_time =
      (model.settings && parseInt(model.settings.limit_time, 10)) || null;
    this._questions = new QuestionsListModel(
      adaptQuestionList(progress, modelObj)
    );
  }

  get commentInProcessOfFilling() {
    return this._comments;
  }

  get modeSelfTest() {
    return this.mode_self_test;
  }

  get limitTime() {
    return this._limit_time;
  }

  get showCommentForQuestion() {
    return (
      this._model.model.quest.displaycomment === 1
    );
  }

  get context() {
    return this._context ? this._context.get() : null;
  }
  set context(value) {
    this._context.set(adaptContext(value));
  }
  get questions() {
    return this._questions.get();
  }
  set questions(value) {
    this._questions.set(value);
  }
  get time() {
    return this._model.time_left;
  }
  get saveUrl() {
    return this._model.saveUrl;
  }
  get finalizeUrl() {
    return this._model.finalizeUrl;
  }
  get resultsUrl() {
    return this._model.resultsUrl;
  }
  get _modeTestPage() {
    let se = this._model.settings;
    if (!se || !se.hasOwnProperty('mode_test_page')) {
      return 1;
    }
    return parseInt(se.mode_test_page, 10);
  }
  get isMovementFree() {
    return this._modeTestPage === 1;
  }
  get isMovementRestricted() {
    return this._modeTestPage === 0;
  }
  get results() {
    return this._model.results;
  }
  get comments() {
    return this._model.comments;
  }
  get currentItem() {
    return this.questions.findIndex(
      questionItem => +questionItem.id === parseInt(this._model.itemId, 10)
    );
  }
  get description() {
    return this._model.model.quest.description;
  }
  get title() {
    return this._model.model.quest.name;
  }
  get progress() {
    return this._model.progress;
  }
  get type() {
    return this._type;
  }
}

export default TestModel;
