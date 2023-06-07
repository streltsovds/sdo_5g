const FORM_ELEMENT_NAME = "form";
const POST = "POST";
const POSITION = "beforeend";

/**
 * TODO взять стороннюю библиотеку. Вложенные массивы и объекты сейчас не поддерживаются
 *
 * Создать разметку инпута
 * @param {String} name имя инпута
 * @param {Array|String} value массив значений инпута
 * @returns {String} разметка инпута
 */
const createInputHTML = (name, value) => {
  if (Array.isArray(value)) {
    let nameOfArray = `${name}[]`;
    let result = [];
    for (let item of value) {
      result.push(createInputHTML(nameOfArray, item));
    }
    return result.join("\n");
  }

  return `<input name="${name}" value="${value}" />`;
};

/**
 * Создать форму для дальнейшег о сабмита
 * @param {String} url
 * @param {{}} queryObj Объект с полями формы
 * @returns {HTMLFormElement} форма для дальнейшего сабмита
 */
export const buildForm = (action, queryObj) => {
  const form = document.createElement(FORM_ELEMENT_NAME);
  form.method = POST;
  form.action = action;
  Object.entries(queryObj).forEach(([name, value]) => {
    form.insertAdjacentHTML(POSITION, createInputHTML(name, value));
  });
  document.body.appendChild(form);
  return form;
};
