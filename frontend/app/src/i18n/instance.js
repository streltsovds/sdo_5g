import Vue from "vue";
import VueI18n from "vue-i18n";

Vue.use(VueI18n);

import ru from "./messages/ru";

/**
 * Плюрализация
 * @see https://kazupon.github.io/vue-i18n/guide/pluralization.html#custom-pluralization
 *
 * @param choice {number} a choice index given by the input to $tc: `$tc('path.to.rule', choiceIndex)`
 * @param choicesLength {number} an overall amount of available choices
 * @returns a final choice index to select plural word by
 **/
VueI18n.prototype.getChoiceIndex = function (choice, choicesLength) {
  // this === VueI18n instance, so the locale property also exists here
  if (this.locale !== "ru") {
    // proceed to the default implementation
  }

  if (choice === 0) {
    return 0;
  }

  const teen = choice > 10 && choice < 20;
  const endsWithOne = choice % 10 === 1;

  if (!teen && endsWithOne) {
    return 1;
  }

  if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
    return 2;
  }

  return (choicesLength < 4) ? 2 : 3;
}

export default (locale) => {
  return new VueI18n({
    locale: locale,
    messages: {
      ru: ru,
    },
    // закомментировать при отладке перевода на другой язык - в консоли js будут выдаваться строки с ненайденным переводом
    silentTranslationWarn: true,
  });
};
