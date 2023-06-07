// import {getModuleNamespace} from "@/store/utilities"
import isEmpty from "lodash/isEmpty";
import isNil from "lodash/isNil";
import forOwn from "lodash/forOwn";

import * as N from "./names";

const getDefaultItemKey = state => {
  if (state.headers[0]) {
    return state.headers[0].value;
  }
  return "id";
};
const getDefaultActionField = state =>
  state.headers.filter(header => header.isHidden)[0]
    ? state.headers.filter(header => header.isHidden)[0].value
    : "id";

const POST_MASS_IDS_PREFIX = "postMassIds_";
const MASS_ACTIONS_ALL_PREFIX = "massActionsAll_";
/**
 * gets
 * (state, getters, rootState, rootGetters)
 */
export default {
  [N.ACTION_FIELD]: state =>
    state.config.actionsField !== undefined && state.config.actionsField
      ? state.config.actionsField
      : getDefaultActionField(state),
  [N.ALL_CURRENT_ROWS_SELECTED]: (state, getters) =>
    getters[N.NUMBER_OF_CURRENT_ROWS_SELECTED] === state.items.length,
  [N.ALL_ROWS_SELECTED]: (state, getters) =>
    getters[N.MASS_ACTIONS_ALL_ARRAY].length === state.selectedRows.length,

  [N.FILTERS_VALUES]: (state, getters) => {
    let result = {};

    for (const [, header] of Object.entries(state.headers)) {
      let filter = header.filters;

      if (!filter || isNil(filter.value)) {
        continue;
      }

      result[header.value] = filter.value;
    }

    return result;
  },

  [N.FILTERS_APPLIED]: (state, getters) => {
    for (const [, header] of Object.entries(state.headers)) {
      let filter = header.filters;

      if (!filter || isNil(filter.value)) {
        continue;
      }

      let stringValue = filter.value + '';

      if (!isEmpty(stringValue)) {
        return true;
      }
    }
    return false;
  },

  [N.HEADERS_ORDERED]: state => {
    let result = [];

    for (let headerName of state.headersOrder) {
      result.push(state.headers[headerName]);
    }

    return result;
  },

  [N.HEADERS_SHOWN]: (state, getters) =>
    getters[N.HEADERS_ORDERED].filter(header => !header.isHidden),

  [N.HIDE_ACTIONS]: state => !state.items.length || !state.config.hasFooter,
  [N.HEADERS_HIDE]: state => !state.config.hasHeader || !state.headers.length,
  [N.IS_ALL_SELECTABLE]: state =>
    Boolean(state.items.length) && Boolean(state.massActions.length),
  [N.IS_NAV_MENU_OPEN]: state => state.isNavMenuOpen,
  [N.ITEM_KEY]: state => state.defaultMassActionsColumn,
  [N.LOADING]: (state, getters, rootState, rootGetters) => {
    // получаем из глобального хранилища
    // state.isLoading
    if (!rootGetters.componentIsLoading) {
      console.error("vuex: rootGetters.componentIsLoading undefined");
      return false;
    }

    // TODO get namespace
    // doesn't work
    // console.log(getModuleNamespace(this, state));

    return rootGetters.componentIsLoading(state._namespace);
  },
  [N.MASS_ACTIONS_ALL_PARAM]: state =>
    `${MASS_ACTIONS_ALL_PREFIX}${state.gridId}`,
  [N.MASS_ACTIONS_ALL]: state => state.massActionsAll,
  [N.MASS_ACTIONS_ALL_ARRAY]: state =>
    state.massActionsAll.split(",").map(id => ({
      [state.defaultMassActionsColumn]: id
    })),
  [N.NO_CURRENT_ROWS_SELECTED]: (state, getters) =>
    getters[N.NUMBER_OF_CURRENT_ROWS_SELECTED] === 0,
  [N.NOT_ALL_CURRENT_ROWS_SELECTED]: (state, getters) =>
    getters[N.NUMBER_OF_CURRENT_ROWS_SELECTED] === 0 ||
    getters[N.NUMBER_OF_CURRENT_ROWS_SELECTED] < state.items.length
      ? true
      : false,
  [N.NUMBER_OF_CURRENT_ROWS_SELECTED]: state => {
    const { selectedRows, items, defaultMassActionsColumn } = state;
    return items.filter(
      item =>
        selectedRows.findIndex(selecterRow => {
          return (
            item[defaultMassActionsColumn] ===
            selecterRow[defaultMassActionsColumn]
          );
        }) !== -1
    ).length;
  },

  [N.PAGINATION_CURRENT]: state =>
    state.paginationDirty || state.pagination || 1,

  [N.POST_MASS_IDS_PARAM]: state => `${POST_MASS_IDS_PREFIX}${state.gridId}`,
  [N.TABLE_SWITCHER]: state => isEmpty(state.tableSwitcher) ? null : state.tableSwitcher,

  [N.TABLE_SWITCHER_URL_PARAMS]: state => {
    if (isEmpty(state.tableSwitcher)) {
      return {};
    }

    let { param, currentMode } = state.tableSwitcher;

    // console.log('param:', param);
    // console.log('currentMode:', currentMode);

    return {
      [param]: currentMode,
    };
  },
  // [TABLE_FILTERS]: state => state.tableFilters,
};
