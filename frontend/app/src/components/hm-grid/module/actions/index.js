import * as mutation from "../mutations/types";
import * as getterTypes from "../getters/names";
import axios from "axios";
import { DEBOUNCE_DESKTOP_TIMEOUT, DEBOUNCE_PAGINATION_TIMEOUT } from "../../constants";

import lodash from "lodash";
// const debounce = require("lodash.debounce");
// const isEqual = require("lodash.isequal");
const debounce = lodash.debounce;
const isEqual = lodash.isEqual;

import {
  INIT_GRID,
  INIT_LOAD_REQUEST,
  INIT_LOADING_SUCCESS,
  REQUEST_RELOAD,
  REQUEST_PAGINATION_UPDATE,
  UPDATE_SELECTED_ROWS,
  EXEC_MASS_ACTION,
  REQUEST_FILTER,
  APPLY_FILTER,
  APPLY_SWITCHER_MODE,
  REMOVE_FILTER,
  TOGGLE_ALL_CURRENT_ROWS,
  TOGGLE_ALL_ROWS,
  SET_PAGINATION,
  TOGGLE_ROW,
  EXPORT_GRID,
  SET_GRID_LOADING_ON,
  SET_GRID_LOADING_OFF,
  SET_NAMESPACE,
  FILTERS_VISIBILITY_TOGGLE,
} from "./actions";

import * as G from '../getters/names'

import {
  transformPagination,
  transformFilters,
  createRequestParamsObject,
  getExportQuery,
  buildQueryUrl,
  buildForm
} from "../logic";

import globalActions from "../../../../store/modules/global/const/actions"
import * as mutations from "../mutations/types";
import * as actions from "./actions";

const method = {
  POST: "POST",
  GET: "GET"
};

const CancelToken = axios.CancelToken;
let source;

export default {
  [INIT_GRID]: ({ commit }) => {
    commit(mutation.INIT_GRID);
  },
  [SET_NAMESPACE]: ({ commit }, _namespace) => {
    commit(mutation.SET_NAMESPACE, _namespace);
  },
  [INIT_LOAD_REQUEST]: ({ commit, state, dispatch }) => {
    commit(REQUEST_RELOAD);
  },
  [REQUEST_RELOAD]: async ({ commit, dispatch, getters, state }) => {
    const { gridId, apiUrl } = state;

    const filters = getters[G.FILTERS_VALUES];
    const pagination = getters[G.PAGINATION_CURRENT];

    const transformedPagination = transformPagination(pagination, gridId);
    const transformedFilters = transformFilters(filters, gridId);

    console.log("hm-grid action REQUEST_RELOAD: filters:", transformedFilters);

    const tableSwitcherParams = getters[G.TABLE_SWITCHER_URL_PARAMS];

    const params = createRequestParamsObject(
      transformedFilters,
      transformedPagination,
      tableSwitcherParams
    );

    source = CancelToken.source();

    dispatch(SET_GRID_LOADING_ON);

    try {
      let response = await axios({
        method: method.POST,
        url: apiUrl,
        cancelToken: source.token,
        params,
      });

      let data = response.data;

      commit(mutation.UPDATE_GRID, data);
    } catch (error) {
      if (axios.isCancel(error)) {
        console.debug("Grid update request canceled", error.message);
      } else {
        if (!error.response) return console.error(error);
        const { errorType, message, exception } = error.response.data;
        const errorObject = {
          code: errorType || null,
          message: message || null,
          details: exception || error,
          isShown: true,
        };
        console.error(exception || error);
        commit(mutation.SET_ERROR, errorObject);
      }
    }

    dispatch(SET_GRID_LOADING_OFF);
  },
  [INIT_LOADING_SUCCESS]: ({ commit }, newBackendData) => {
    commit(mutation.INIT_LOADING_SUCCESS, newBackendData);
  },
  [REQUEST_PAGINATION_UPDATE]: debounce(async function(
    { commit, dispatch, state, getters },
    pagination
  ) {
    // Если новая пагинация и старая одинаковые то не шлем запрос и отменяем существующие
    if (isEqual(state.pagination, pagination)) {
      if (source) {
        source.cancel("Request canceled by user pagination request.");
      }
      return;
    }

    commit(mutation.SET_PAGINATION_DIRTY, pagination);

    try {
      await dispatch(REQUEST_RELOAD);
    } catch (error) {
      // pass
    }

    commit(mutation.SET_PAGINATION_DIRTY, null);
  },
  DEBOUNCE_PAGINATION_TIMEOUT),
  [REQUEST_FILTER]: debounce(async function({ commit, dispatch, getters, state }) {
      console.log('REQUEST_FILTER');
      await dispatch(REQUEST_RELOAD);
    },
    DEBOUNCE_DESKTOP_TIMEOUT,
    { trailing: true }
  ),
  [APPLY_SWITCHER_MODE]: debounce(
    async ({ commit, dispatch }, switcherMode) => {
      commit(mutations.TABLE_SWITCHER_MODE_SET, switcherMode);
      commit(mutations.PAGINATION_TO_FIRST_PAGE);
      // commit(mutations.SET_PAGINATION_DIRTY, null);
      dispatch(actions.REQUEST_RELOAD);
    },
    DEBOUNCE_PAGINATION_TIMEOUT
  ),
  [APPLY_FILTER]: ({ commit, dispatch }, filter) => {
    console.log("hm-grid action APPLY_FILTER", filter);

    commit(mutation.APPLY_FILTER, filter);
    commit(mutation.PAGINATION_TO_FIRST_PAGE);
    dispatch(REQUEST_FILTER);
  },
  [REMOVE_FILTER]: ({ commit, dispatch }, filter) => {
    commit(mutation.REMOVE_FILTER, filter);
    dispatch(REQUEST_FILTER);
  },
  [UPDATE_SELECTED_ROWS]: ({ commit }, selectedRowsIds) => {
    commit(mutation.UPDATE_SELECTED_ROWS, selectedRowsIds);
  },
  [TOGGLE_ALL_CURRENT_ROWS]: ({ commit }, doSelect) => {
    if (doSelect) {
      commit(mutation.SELECT_CURRENT_ROWS);
    } else {
      commit(mutation.DESELECT_CURRENT_ROWS);
    }
  },
  [TOGGLE_ALL_ROWS]: ({ commit }, doSelect) => {
    if (doSelect) {
      commit(mutation.SELECT_ALL_ROWS);
    } else {
      commit(mutation.DESELECT_ALL_ROWS);
    }
  },
  [TOGGLE_ROW]: ({ commit }, payload) => {
    commit(mutation.TOGGLE_ROW, payload);
  },
  [EXEC_MASS_ACTION]: ({ state, getters }, { url, field, submassAction }) => {
    const {
      [getterTypes.POST_MASS_IDS_PARAM]: postMassParam,
      [getterTypes.MASS_ACTIONS_ALL_PARAM]: MassActionsAllParam,
      [getterTypes.MASS_ACTIONS_ALL]: MassActionsAll
    } = getters;

    const postMassAll = state.selectedRows.map(item => item[field]);
    const query = {
      [postMassParam]: postMassAll.join(),
      [MassActionsAllParam]: MassActionsAll
    };
    if (submassAction) {
      const { label, value } = submassAction;
      query[label] = value;
    }
    // Костыль ибо в query строку не запихиешь много параметров
    const form = buildForm(url, query);
    console.dir(query);
    form.submit();
  },
  [SET_PAGINATION]: ({ dispatch }, pagination) => {
    dispatch(REQUEST_PAGINATION_UPDATE, pagination);
  },
  [EXPORT_GRID]: ({ state, getters }, exportType) => {
    const { gridId } = state;
    const transformedPagination = transformPagination(
      state.pagination,
      state.gridId
    );
    const transformedFilters = transformFilters(
      getters[G.FILTERS_VALUES],
      state.gridId
    );
    const exportQuery = getExportQuery(gridId, exportType);
    const query = buildQueryUrl({
      ...transformedPagination,
      ...transformedFilters,
      ...exportQuery
    });
    window.open(query);
  },
  [SET_GRID_LOADING_ON]: ({ dispatch,  state }) => {
    // TODO get namespace name
    dispatch(globalActions.setLoadingOn, state._namespace, { root: true });
  },
  [SET_GRID_LOADING_OFF]: ({ dispatch, state }) => {
    dispatch(globalActions.setLoadingOff, state._namespace, { root: true });
  },
  [FILTERS_VISIBILITY_TOGGLE]: ({ commit, state }) => {
    commit(mutation.SET_FILTERS_VISIBILITY, !state.filtersVisible);
  },
};
