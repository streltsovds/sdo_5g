import qs from "qs";

/**
 * Build query url
 * @param {{}} query object to be converted to query string
 * @param {String|undefined} url url to send your query
 * @param {String|indefined} origin origin for url
 * @returns {String}
 */
export const buildQueryUrl = (
  query,
  url = window.location.pathname,
  origin = window.location.origin
) => `${origin}${url}?${qs.stringify(query)}`;
