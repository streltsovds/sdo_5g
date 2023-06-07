export const DEFAULT_GRID_MODULE_NAME = "gridApi";

export const NO_ELEVATION = "elevation-0";
export const LOW_ELEVATION = "elevation-2";
export const MID_ELEVATION = "elevation-4";

// export const TABLE_WRAPPER_CLS = "v-table__overflow";
// vuetify 2 change:
export const TABLE_WRAPPER_CLS = "v-data-table__wrapper";

export const TABLE_BODY_SELECTOR = ".v-data-table tbody";
export const TABLE_BODY_LOADING_CLS = "isLoading";

export const MIN_TABLE_WIDTH = 311;

export const MIN_PAGINATION_WIDTH = MIN_TABLE_WIDTH;
export const MAX_PAGINATION_WIDTH = 430;

export const MIN_MENU_WIDTH = 300;
export const MAX_MENU_WIDTH = 500;

export const RESIZE_DOM_EVENT = "resize";

export const FILTER_EVENT = "filter";
export const SELECT_EVENT = "select";
export const SELECTED_EVENT = "selected";
export const INVALID_EVENT = "invalid";
export const TOGGLE_ALL_EVENT = "toggleAll";
export const TOGGLE_ALL_CURRENT_EVENT = "toggleAllCurrent";

export const SLIDEX_TRANSITION = "v-slide-x-transition";
export const SLIDEY_TRANSITION = "v-slide-y-transition";
export const SLIDEY_REVERSE_TRANSITION = "v-slide-y-reverse-transition";

export const SUB_MASS_ACTIONS_PROPERTY = "sub_mass_actions";
export const COLUMN_ACTIONS_NAME = "actions";

export const DEBOUNCE_DESKTOP_TIMEOUT = 1000;
export const DEBOUNCE_PAGINATION_TIMEOUT = 500

export const TEXT_FILTER_TYPE = "text";
export const SELECT_FILTER_TYPE = "select";
export const DATESMART_FILTER_TYPE = "DateSmart";
export const DATE_FILTER_TYPE = "Date";

export const TEXT_COMPONENT_NAME = "hm-grid-filter-text";
export const SELECT_COMPONENT_NAME = "hm-grid-filter-select";
export const DATESMART_COMPONENT_NAME = "hm-grid-filter-date-range";
export const DATE_COMPONENT_NAME = "hm-grid-filter-date-range";

export const READY_FILTERS = [
  TEXT_FILTER_TYPE,
  SELECT_FILTER_TYPE,
  DATESMART_FILTER_TYPE,
  DATE_FILTER_TYPE,
];

export const SUB_MASS_ACTION_SELECT_TYPE = "select";
export const SUB_MASS_ACTION_AUTOCOMPLETE_TYPE = "fcbk";
export const SUB_MASS_ACTION_INPUT_TYPE = "input";

export const hmGridComponentName = {
  SELECT: "hm-grid-sub-mass-action-select",
  AUTOCOMPLETE: "hm-grid-sub-mass-action-autocomplete",
  INPUT: "hm-grid-sub-mass-action-input",
  DEFAULT: "select"
};

export const AUTOCOMPLETE_PROP = "fcbk";
export const DATA_URL_PROP = "DataUrl";
export const MAX_ITEMS_PROP = "MaxItems";
export const ALLOWED_NEW_PROP = "AllowNewItems";
export const INPUT_PROP = "input";
export const MULTIPLE_PROP = "multiple";
export const SELECT_PROP = "select";

export const VUETIFY_AUTOCOMPLETE_TYPE = "v-autocomplete";
export const VUETIFY_SELECT_TYPE = "v-select";
export const AUTOCOMPLETE_TRESHOLD = 20;
export const COMPONENT_CLS_AUTOCOMPLETE = "hm-sub-mass-action__autocomplete";
export const COMPONENT_CLS_SELECT = "hm-sub-mass-action__select";
