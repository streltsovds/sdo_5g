/**
 * Рандомное целое число
 * @param  {number?} min — от
 * @param  {number?} max — до
 * @return {number}      — случайное целоые число
 */
export function getRandomInt(min, max) {
  if (max == null) {
    if (min == null) {
      min = 0;
      max = 1;
    } else {
      max = min;
      min = 0;
    }
  }

  return (Math.random() * (max - min + 1) + min) | 0;
}
