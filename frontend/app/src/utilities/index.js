export const generateRandomString = charactersNumber =>
  Math.random()
    .toString(36)
    .substring(2, 2 + charactersNumber);

export const delay = ms => {
  if (process.env.NODE_ENV === "production") {
    const warning = `Delay function won't do its thing while in production!
        For real delaying in async functions use "dangerouslySetDelayInProduction"`;
    console.warn(warning);
  }
  if (process.env.NODE_ENV === "development") {
    return new Promise(resolve =>
      setTimeout(() => {
        resolve();
      }, ms)
    );
  } else {
    return Promise.resolve();
  }
};

export const dangerouslySetDelayInProduction = ms => {
  return new Promise(resolve =>
    setTimeout(() => {
      resolve();
    }, ms)
  );
};

export const composeComputed = (originalcomputed = {}, objects) =>
  Object.assign({}, ...objects, originalcomputed);

export const decline = (number, titles) =>
  titles[
    number % 100 > 4 && number % 100 < 20
      ? 2
      : [2, 0, 1, 1, 1, 2][Math.min(number % 10, 5)]
  ];
