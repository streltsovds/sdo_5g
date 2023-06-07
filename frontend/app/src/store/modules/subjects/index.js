const subjects = {
  name: null,
  shortname: null,
  code: null,
  short_description: null,
  description: null,
  icon_banner: null,
  reg_type: null,
  claimant_process_id: null,
  period: null,
  begin: null,
  end: null,
  longtime: null,
  period_restriction_type: null,
  price: null,
  price_currency: null,
  plan_users: null,
  icon: null,
  rooms: null,
  direction_id: null,
  scale_id: null,
  auto_mark: null,
  formula_id: null,
  threshold: null,
  auto_graduate: null,
  classifier_10: null
};

const getters = {
  GET_SCALE_ID(state) {
    return state.scale_id
  },
  GET_AUTO_MARK(state) {
    return state.auto_mark
  }
};
const mutations = {
  RESET(state) {
    state.name = null;
    state.shortname = null;
    state.code = null;
    state.short_description = null;
    state.description = null;
    state.icon_banner = null;
    state.reg_type = null;
    state.claimant_process_id = null;
    state.period = null;
    state.begin = null;
    state.end = null;
    state.longtime = null;
    state.period_restriction_type = null;
    state.price = null;
    state.price_currency = null;
    state.plan_users = null;
    state.icon = null;
    state.rooms = null;
    state.direction_id = null;
    state.scale_id = null;
    state.auto_mark = null;
    state.formula_id = null;
    state.threshold = null;
    state.auto_graduate = null;
    state.classifier_10 = null;
  },
  SET_SCALE_ID(state, data) {
    state.scale_id = data.scale_id;
  },
  SET_AUTO_MARK(state, data) {
    state.auto_mark = data.auto_mark;
  },
  SET_THRESHOLD(state, data) {
    state.threshold = data.threshold;
  },
  SET_FORMULA_ID(state, data) {
    state.formula_id = data.formula_id;
  },
  SET_BEGIN(state, data) {
    state.begin = data.begin;
  },
  SET_END(state, data) {
    state.end = data.end;
  }
};

const actions = {
  reset({ commit }) {
    commit("RESET");
  },
  set_scale_id({ commit }, data) {
    commit("SET_SCALE_ID", data);
  },
  set_auto_mark({ commit }, data) {
    commit("SET_AUTO_MARK", data);
  },
  set_threshold({ commit }, data) {
    commit("SET_THRESHOLD", data);
  },
  set_formula_id({ commit }, data) {
    commit("SET_FORMULA_ID", data);
  },
  set_begin({ commit }, data) {
    commit("SET_BEGIN", data);
  },
  set_end({ commit }, data) {
    commit("SET_END", data);
  }
};

export default {
  namespaced: true,
  state: subjects,
  getters,
  mutations,
  actions
};
