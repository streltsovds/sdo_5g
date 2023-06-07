const iconType = el => {
  let type;

  if (el.type === 'external') {
    if (el.filetype === 'unknown') {
      type = el.type;
    } else {
      type = el.filetype;
    }
  } else {
    if (el.type) {
      if (el.type === 'unknown' || el.type === '0') {
        type = el.kbase_type;
      } else {
        type = el.type;
      }
    } else {
      type = el.kbase_type;
    }
  }

  return type;
};

export default iconType;
