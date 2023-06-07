import mergeCssClassesPrefix from "classnames-prefix";

export default function addCssClassesPrefixed(baseName, ...suffixes) {
  return mergeCssClassesPrefix(baseName)(baseName, ...suffixes);
}
