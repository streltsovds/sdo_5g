// export const shuffleArray = arr =>
//   arr
//     .map(a => [Math.random(), a])
//     .sort((a, b) => a[0] - b[0])
//     .map(a => a[1]);

/**
 * Shuffle an array
 * @param {any[]} arr array to shuffle
 * @return {any[]} shuffled copy of an array
 */
export const shuffleArray = arr => {
  const array = arr.map(x => x);
  for (
    var j, x, i = array.length;
    i;
    j = parseInt(Math.random() * i),
      x = array[--i],
      array[i] = array[j],
      array[j] = x
  );
  return array;
};

/**
 * Capitalize a string
 * @param {string} str string to capitalize
 * @return {string} capitalized string
 */
export const capitalize = str => str.charAt(0).toUpperCase() + str.slice(1);

/**
 * https://gist.github.com/ahtcx/0cd94e62691f539160b32ecda18af3d6
 * @param {Object} target
 * @param {Object} source
 */
export const merge = (target, source) => {
  // Iterate through `source` properties and if an `Object` set property to merge of `target` and `source` properties
  for (let key of Object.keys(source)) {
    if (source[key] instanceof Object && key in target)
      Object.assign(source[key], merge(target[key], source[key]));
  }

  // Join `target` and modified `source`
  Object.assign(target || {}, source);
  return target;
};

/**
 * Reassign propeties of one object to other
 */
export const rewriteProps = (to, from) => {
  for (const key in from) {
    if (from.hasOwnProperty(key)) {
      const element = from[key];
      to[key] = element;
    }
  }
};
