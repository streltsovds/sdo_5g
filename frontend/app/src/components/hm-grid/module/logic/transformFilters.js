import { normalizeParamValue } from "./normalizeParamValue";

/**
 * Превращает переданный объект с фильтрами в объект параметров
 *
 * @param {{}} filters объект с переданными фильтрами
 * @param {String} gridId идентификатор грида
 * @returns {{}} объект для параметров
 */
export const transformFilters = (filters, gridId) => {
  const temp = {};
  for (const columnName in filters) {
    if (filters.hasOwnProperty(columnName)) {
      temp[columnName] = normalizeParamValue(filters[columnName]);
      temp["grid"] = gridId;
    }
  }
  return temp;
};
