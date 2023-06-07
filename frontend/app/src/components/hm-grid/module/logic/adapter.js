import isNil from "lodash/isNil";
import isEmpty from "lodash/isEmpty";

/**
 * Превращает данные приходящие с бэка по масс-экшенам в подходящие нам
 * @param {Array<{}>} massActions массив масс-экшенов с бэкенда
 * @returns {Array<{}>} массив с правильной и подходящей структурой
 */
const adaptMassActions = massActions => {
  /**
   * Возвращает первый ключ из объекта масс-экшена.
   *
   * Который фактически является лейблом масс экшена
   * @param {{}} massAction объект с описание масс-экшена
   * @returns {String} лейбл масс-экшена
   */
  const getMassActionLabel = massAction => Object.keys(massAction)[0];
  /**
   * Возвращает все свойства масс-эшена
   * @param {{}} massAction обьект масс-экшена
   * @returns {{}} объект со свойствами масс-экшена
   */
  const getMassActionProperties = massAction => Object.values(massAction)[0];

  return massActions.map(massAction => {
    return {
      label: getMassActionLabel(massAction),
      ...getMassActionProperties(massAction)
    };
  });
};

/**
 * Превращет данные с бэка о колонках в нужный нам массив
 * @param {{}} headers Объект с колокнками
 * @param {Boolean} isSortingEnabled включена или отключена сортировка
 * @param {{}} filters объект с фильтрами соответствующими колонке
 * @returns {Array<{}>} массив отсортированных по позиции колонок с правильной структурой
 */
const adaptHeaders = (headers, isSortingEnabled, filters) => {
  // if table column has position than we push it to ordered array
  const orderedItems = [];
  // if not than to this one
  const unOrderedItems = [];
  if (!headers) return [];
  Object.entries(headers).forEach(([value, options]) => {
    /**
     * @var {Number|null|undefined} position Порядковый номер колонки
     * @var {String|undefined} title Отображаемое название колонки
     * @var {Boolean|null} hidden указывает на то что колонка скрыта
     */
    const { position, title, hidden, color } = options;
    // свойство `position` может присутствовать и быть пустым, а может и вообще не быть
    const hasNoPosition = position === null || position === undefined;
    // тоже самое и тут
    const isHidden =
      (hidden !== undefined || hidden !== null) && hidden === true;

    const headerObj = {
      value,
      // fallback to value property
      text: title || value,
      sortable: isSortingEnabled,
      // TODO: get this from backend
      align: "left",
      isHidden,
      filters: filters[value],
      color: color
    };

    if (hasNoPosition) {
      unOrderedItems.push(headerObj);
    } else {
      if (position !== 1) {
        // change it to whatever you like
        headerObj.align = "left";
      }
      orderedItems[position - 1] = headerObj;
    }
  });
  return [...orderedItems.filter(Boolean), ...unOrderedItems];
};

/**
 * Превращает строки таблицы с бэкенда в нужную структуру
 * @param {Array<{}>} items массив строк таблицы с бэкенда
 * @returns {Array<{}>} массив строк таблицы
 */
const adaptItems = items => items;

const adaptExports = exportsList => exportsList;

/**
 * Превращает данные о пагинации с бэкенда в подходящие нам
 *
 * @param {SortObject} param0 объект с данными о сортировке таблицы
 * @param {Number} rowsPerPage число строк на одной странице
 * @param {Number} totalItems число всех строк
 * @param {String} page текущая страница
 *
 * @returns
 */
const adaptPagination = (
  { column, direction },
  rowsPerPage,
  totalItems,
  page
) => {
  return {
    sortBy: column,
    // descending должен быть `true` если направление `DESC`
    descending: direction !== "ASC",
    rowsPerPage,
    totalItems,
    page: Number(page)
  };
};

/**
 * Адаптирует данные с бэкенда в нужную структуру
 * @param {{}} data данные с бэкенда
 * @returns {{}} структура данных для инициализации
 */
const adaptData = data => {
  console.log("hm-grid adaptData(): data: ", data);

  const {
    currentPage,
    defaultMassActionsColumn,
    filters,
    headerActions,
    gridId,
    isSortingEnabled,
    massActions,
    rowsPerPageItems,
    tableSettings,
    MassActionsAll,
  } = data;

  const orderedHeaders = adaptHeaders(
    tableSettings.tableColumns,
    isSortingEnabled,
    filters
  );

  let headers = {};
  let headersOrder = [];

  for (var index = 0; index < orderedHeaders.length; index++) {
    let header = orderedHeaders[index];
    let name = header.value;
    headers[name] = header;
    headersOrder[index] = name;
  }

  const items = adaptItems(data.data);

  const pagination = adaptPagination(
    tableSettings.order,
    tableSettings.pagination,
    tableSettings.totalRecords,
    currentPage
  );
  const exports = adaptExports(tableSettings.links);
  return {
    defaultMassActionsColumn,
    exports,
    gridId,
    headerActions,
    headers,
    headersOrder,
    items,
    massActions: adaptMassActions(massActions),
    massActionsAll: MassActionsAll,
    pagination,
    rowsPerPageItems,
    tableSwitcher: tableSettings.tableSwitcher,
    // tableFilters: tableSettings.tableFilters,
  };
};

export const adaptBackendData = adaptData;

/**
 * Объект с данными о сортировке
 * @typedef {Object} SortObject
 * @property {String} column имя колонки
 * @property {'ASC'|'DESC'} direction направление сортировки
 */
