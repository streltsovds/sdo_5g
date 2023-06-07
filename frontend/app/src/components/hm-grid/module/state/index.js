export default {
  _namespace: null,
  apiUrl: null,
  config: {
    massActionField: null,
    hasHeader: true,
    hasFooter: true,
    title: "",
    itemKey: null,
    actionsField: null
  },
  defaultMassActionsColumn: "id",
  error: {
    message: null,
    code: null,
    details: null,
    isShown: false
  },
  exports: [],

  /**
   * NOTE: filters переходят в headers при инициализации:
   * @see ../logic/adapter.js
   * @see ../mutations/index.js
   *   : INIT_LOADING_SUCCESS
   **/
  // filters: {},
  // tableFilters: [],
  filtersVisible: false,
  gridId: "gridApi",
  headers: {},
  headersOrder: [],
  isGridInited: false,
  isInitLoading: false,
  // isLoading: false,
  isNavMenuOpen: false,
  items: [],
  massActions: [],
  massActionsAll: "",
  pagination: {},
  paginationDirty: null,
  rowsPerPageItems: [50],
  selectedRows: [],
  tableSwitcher: [],
};
