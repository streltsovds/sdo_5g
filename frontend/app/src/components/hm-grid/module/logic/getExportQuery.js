const EXPORT_QUERY_KEY = "_exportTo";

export const getExportQuery = (gridId, queryType) => ({
  [`${EXPORT_QUERY_KEY}${gridId}`]: queryType
});
