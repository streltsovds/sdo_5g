const fonts = [
  {
    name: "Arial",
    font: "arial"
  },
  {
    name: "Helvetica",
    font: "helvetica"
  },
  {
    name: "Sans Serif",
    font: "sans-serif"
  },
  {
    name: "Courier New",
    font: "courier new"
  },
  {
    name: "Roboto",
    font: "Roboto"
  }
];

const formattingFontsArray = [];

fonts.forEach(fontItem => {
  formattingFontsArray.push(`${fontItem.name}=${fontItem.font}`);
});

const formattingFontsString = formattingFontsArray.join(";");

export default formattingFontsString;
