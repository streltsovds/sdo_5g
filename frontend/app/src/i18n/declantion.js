/**
 * Склонение существительных
 * Правильная форма cуществительного рядом с числом (счетная форма).
 *
 * @example declension("файл", "файлов", "файла", 0); //returns "файлов"
 * @example declension("файл", "файлов", "файла", 1); //returns "файл"
 * @example declension("файл", "файлов", "файла", 2); //returns "файла"
 *
 * @param {string} oneNominative единственное число (именительный падеж)
 * @param {string} severalGenitive множественное число (родительный падеж)
 * @param {string} severalNominative множественное число (именительный падеж)
 * @param {(string|number)} number количество
 * @returns {string}
 */
export default (oneNominative, severalGenitive, severalNominative, number) => {
  number = number % 100;

  return number <= 14 && number >= 11
    ? severalGenitive
    : (number %= 10) < 5
    ? number > 2
      ? severalNominative
      : number === 1
      ? oneNominative
      : number === 0
      ? severalGenitive
      : severalNominative //number === 2
    : severalGenitive;
};
