import { generateRandomString } from "@/utilities";

import state from "./state";
import mutations from "./mutations";
import actions from "./actions";
import getters from "./getters";
import { SET_NAMESPACE } from "./actions/actions"

const module = {
  namespaced: true,
  state,
  getters,
  actions,
  mutations
};

export const HM_GRID_MODULE_NAME_PREFIX = "HmGrid";

/**
 * Генерирует рандомное имя используя дефолтный префикс
 * и добавля к нему случайную строку из 4х символов
 * @returns {string} сгененрированное случайное имя
 */
const generateModuleName = (gridId) => {
  gridId = gridId || generateRandomString(4);
  return `${HM_GRID_MODULE_NAME_PREFIX}-${gridId}`;
};

/**
 * Регистрирует модуль HmGrid в хранилище vuex
 *
 * Если дефолтное имя или сгенерированный префикс уже есть
 * в списке модулей, то генерирует новое название модуля,
 * состоящие из префикса и случайной строки из 4х символов.
 *
 * @param {{}} $store инстанс vuex-хранилища
 * @param {string|null} gridId
 * @returns {string} имя зарегистрированного модуля
 */
const registerModule = ($store, gridId) => {
  let moduleName = generateModuleName(gridId);
  if (!$store.state[moduleName]) {
    $store.registerModule(moduleName, module);
  } else {
    // обеспечение уникальности, если имя уже есть
    return registerModule($store);
  }
  $store.dispatch(moduleName + "/" + SET_NAMESPACE, moduleName);
  return moduleName;
};

export const registerGridVuexModule = registerModule;
