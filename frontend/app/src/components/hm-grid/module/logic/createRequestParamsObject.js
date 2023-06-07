// дефолтная строчка для параметра
// нужна для работы фильтров и сортировки
const DEFAULT_PARAMETER_OBJ = {
  gridmod: "ajax"
};

/**
 * Превратить объекты в query-пригодные параметры
 * @param  {...{}} paramObjects объекты для передачи в параметры
 * @returns {{}} объект пригодный для трансформации в query параметры
 */
export const createRequestParamsObject = (...paramObjects) =>
  Object.assign({}, ...[DEFAULT_PARAMETER_OBJ, ...paramObjects]);
