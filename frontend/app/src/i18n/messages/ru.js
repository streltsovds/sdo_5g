// см. https://v15.vuetifyjs.com/en/framework/internationalization#vue-i18n

import ruVuetifyBase from "vuetify/lib/locale/ru";
import lodash from "lodash";
let merge = lodash.merge;

export default {
  $vuetify: merge(ruVuetifyBase, {
    // здесь можно перезаписывать константы перевода базовых компонентов фреймворка vuetify
  }),
  // "Показать панель профиля": "Показать панель профиля (тест перевода)",
  "HyperMethod translation test": "Тест перевода от ГиперМетод",
  "роль plural": "нет ролей | 1 роль | {n} роли | {n} ролей",
  "метка plural": "нет меток | 1 метка | {n} метки | {n} меток",
  "Выполнить для n строк":
    "Строки не выделены | Выполнить для 1 строки | Выполнить для {n} строк | Выполнить для {n} строк",
};
