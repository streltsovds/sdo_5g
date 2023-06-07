const order = {
  DESCENDING: "DESC",
  ASCENDING: "ASC"
};
const ORDER_KEY_PREFIX = "order";
const ORDER_VALUE_SEPARATOR = "_";

/**
 * Получить значение направления сортировки
 * @param {Boolean} isDescending сортировка сверху вниз
 * @returns {"DESC"|"ASC"} направление сортировки
 */
const getOrderDirection = isDescending =>
  isDescending ? order.DESCENDING : order.ASCENDING;

/**
 * Получить значение сортировки
 * @param {String} columnToOrder имя сортируемой колонки
 * @param {Boolean} isDescending направление сортировки
 * @returns {String} значение сортировки
 */
const composeOrderValue = (columnToOrder, isDescending) =>
  `${columnToOrder}${ORDER_VALUE_SEPARATOR}${getOrderDirection(isDescending)}`;

/**
 * Получить значение ключа сортировки
 * @param {String} gridId идентификатор грида
 * @returns {String} ключ сортировки
 */
const composeOrderKey = gridId => `${ORDER_KEY_PREFIX}${gridId}`;

/**
 * Превращает объект пагинации в пригодный для отправки
 * на сортировку
 *
 * @param {IPagination} param0 объект пагинации
 * @param {String} gridId идентификатор грида
 * @returns {{}} объект параметров запроса
 */
export const transformPagination = (
  { descending, sortBy, rowsPerPage, page },
  gridId
) => ({
  perPage: rowsPerPage,
  page,
  [composeOrderKey(gridId)]: composeOrderValue(sortBy, descending)
});

/**
 * @typedef {Object} IPagination
 * @property {Boolean} descending направление сортировки
 * @property {String} sortBy имя колонки для сортировки
 * @property {Number} rowsPerPage число строк на странице
 * @property {Number} page номер текущей страницы
 */
