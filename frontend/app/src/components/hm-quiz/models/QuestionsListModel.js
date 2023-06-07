const ModelParagon = [];

export default class QuestionListModel {
  constructor(model = ModelParagon) {
    this._model = model;
  }
  set(value) {
    this._model = value;
  }
  get() {
    return this._model;
  }
}
