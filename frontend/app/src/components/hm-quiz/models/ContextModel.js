import { merge } from "../utils";

const ModelParagon = {
  exerciseName: {
    key: `Название занятия`,
    value: `Нe указано`
  },
  courseName: {
    key: `Название курса`,
    value: `Нe указано`
  },
  questionsCount: {
    key: `Всего вопросов`,
    value: `Нe указано`
  },
  attemptsCount: {
    key: `попыток`,
    value: {
      left: {
        key: `Осталось`,
        value: `не указано`
      },
      total: {
        key: `Всего`,
        value: `не указано`
      },
      delimiter: {
        value: `/`
      }
    }
  }
};

export default class ContextModel {
  constructor(model = ModelParagon) {
    this._model = model;
  }
  set(value) {
    this._model = merge(this._model, value);
  }
  get() {
    return this._model;
  }
}
