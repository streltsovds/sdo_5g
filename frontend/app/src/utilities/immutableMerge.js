import merge from "lodash/merge";

/** https://gist.github.com/paduc/a3e95630ce8cfde35316#file-immutablemerge-js-L6 */
export default function imerge(...args){
  return merge({}, ...args);
}
