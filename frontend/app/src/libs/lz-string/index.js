/**
 * Импортированные алгоритмы комрессии из lz-string
 * https://github.com/pieroxy/lz-string
 *
 * Это нужно для передачи их внутрь web worker'а
 * Так как склонировать функцию нельзя туда,
 * передается строка, которая потом там превращается в фцнкцию
 * через eval()
 */
import compress from "./compress";
import decompress from "./decompress";
export const Compress = compress;
export const Decompress = decompress;
