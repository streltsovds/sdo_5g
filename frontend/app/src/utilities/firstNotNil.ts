import isNil from "lodash/isNil";
import filter from "lodash/filter";

export default function firstNotNil(...args: any[]) {
  return filter([...args], (v) => !isNil(v))[0];
}
