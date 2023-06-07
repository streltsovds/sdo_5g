//@ts-ignore
import colorConverter from 'css-color-converter';

function colorToHex(anyColor: any) {
  return colorConverter.fromString(anyColor).toHexString();
}

export default colorToHex;
