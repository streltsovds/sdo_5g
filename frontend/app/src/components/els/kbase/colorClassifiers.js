const colorClassifiers = {
  'color-1': '#DAD3FD',
  'color-2': '#05C985',
  'color-3': '#D4E3FB',
  'color-4': '#FAF3D8',
  'color-5': '#FAF3D8',
  'color-6': '#CC83E9',
  'color-7': '#D4E3FB',
  'color-8': '#FDE1D9',
  'color-9': '#EDF4FC',
  'color-10': '#FFE9B9',
  'color-11': '#FDE1D9',
  'color-12': '#DAC5E2',
};

function returnColorClassifier(classColor) {
  return colorClassifiers[classColor];
}

export { returnColorClassifier as color };
