import * as type from "./types";
import Vue from "vue";

import { adaptBackendData } from "../logic";
import uniqWith from 'lodash/uniqWith';
import isEqual from 'lodash/isEqual';

export default {
  [type.INIT_GRID]: state => {
    state.isGridInited = true;
  },
  [type.INIT_LOADING_START]: state => {
    state.isInitLoading = true;
  },
  [type.SET_API_URL]: (state, url) => {
    state.apiUrl = url;
  },
  // [type.SET_GRID_LOADING_STATE]: (state, isLoading) => {
  //   state.isLoading = isLoading;
  // },
  [type.OPEN_CLOSE_NAV_MENU]: (state, isOpenClose) => {
    state.IS_NAV_MENU_OPEN = isOpenClose
  },
  [type.INIT_LOADING_SUCCESS]: (state, payload) => {
    const adaptedData = adaptBackendData(payload);
    for (const key in adaptedData) {
      if (adaptedData.hasOwnProperty(key)) {
        const value = adaptedData[key];
        Vue.set(state, key, value);
      }
    }
    Vue.set(state, "isInitLoading", false);
  },
  [type.UPDATE_GRID]: (state, payload) => {
    const adaptedData = adaptBackendData(payload);
    for (const key in adaptedData) {
      if (adaptedData.hasOwnProperty(key)) {
        const value = adaptedData[key];
        Vue.set(state, key, value);
      }
    }
    Vue.set(state, "isLoading", false);
  },
  [type.UPDATE_SELECTED_ROWS]: (state, selectedRowsId) => {
    state.selectedRows = selectedRowsId;
  },
  [type.TOGGLE_ROW]: (state, { column, value }) => {
    const { defaultMassActionsColumn } = state;
    if (value) {
      state.selectedRows.push({ [defaultMassActionsColumn]: column });

      Vue.set(state, 'selectedRows', uniqWith(state.selectedRows, isEqual));
    } else {
      state.selectedRows = state.selectedRows.filter(
        row => row[defaultMassActionsColumn] !== column
      );
    }
  },
  [type.SELECT_ALL_ROWS]: state => {
    const allItems = state.massActionsAll
      .split(",")
      .map(item => ({ [state.defaultMassActionsColumn]: item }));

    state.selectedRows = allItems;
  },
  [type.DESELECT_ALL_ROWS]: state => {
    state.selectedRows = [];
  },
  [type.SELECT_CURRENT_ROWS]: state => {
    const { defaultMassActionsColumn, selectedRows, items } = state;
    const currentRows = items
      .map(item => ({
        [defaultMassActionsColumn]: item[defaultMassActionsColumn]
      }))
      .filter(
        item =>
          selectedRows.findIndex(
            value =>
              value[defaultMassActionsColumn] === item[defaultMassActionsColumn]
          ) === -1
      );
    state.selectedRows = [...selectedRows, ...currentRows];
  },
  [type.DESELECT_CURRENT_ROWS]: state => {
    const { defaultMassActionsColumn, selectedRows, items } = state;
    const newSelectedRows = selectedRows.filter(
      selectedRow =>
        items.findIndex(
          value =>
            value[defaultMassActionsColumn] ===
            selectedRow[defaultMassActionsColumn]
        ) === -1
    );
    state.selectedRows = newSelectedRows;
  },
  [type.SET_GRID_ID]: (state, gridId) => {
    state.gridId = gridId;
  },
  [type.PAGINATION_TO_FIRST_PAGE]: (state) => {
    Vue.set(state.pagination, "page", 1);
  },
  // filters
  [type.APPLY_FILTER]: (state, filter) => {
    // state.filters[filter.column] = filter.value;
    let header = state.headers[filter.column];

    header.filters.value = filter.value;
  },
  [type.REMOVE_FILTER]: (state, filter) => {
    let header = state.headers[filter.column];
    Vue.delete(header.filters, "value");
  },
  [type.SET_PAGINATION]: (state, pagination) => {
    Vue.set(state, "pagination", pagination);
  },
  // состояние между обновлениями с сервера
  [type.SET_PAGINATION_DIRTY]: (state, paginationDirty) => {
    Vue.set(state, "paginationDirty", paginationDirty);
  },
  // [type.SET_PAGE_ACTIONS]: (state, pageActions) => {
  //   Vue.set(state, "pageActions", pageActions);
  // },
  [type.SET_ERROR]: (state, errorObject) => {
    Object.entries(errorObject).forEach(([key, value]) => {
      Vue.set(state.error, key, value);
    });
    Vue.nextTick(() => {
      Vue.set(state, "isLoading", false);
    });
  },
  // vuex не позволяет модулю получить его namespace
  [type.SET_NAMESPACE]: (state, _namespace) => {
    Vue.set(state, "_namespace", _namespace);
  },
  [type.SET_FILTERS_VISIBILITY]: (state, filtersVisible) => {
    Vue.set(state, "filtersVisible", filtersVisible);
  },
  [type.TABLE_SWITCHER_MODE_SET]: (state, mode) => {
    Vue.set(state.tableSwitcher, "currentMode", mode);
  },
};
