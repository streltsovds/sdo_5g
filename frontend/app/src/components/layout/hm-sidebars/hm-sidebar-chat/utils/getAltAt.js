import {
  altAts,
  altAtsType,
  AtTypes,
} from '../components/Main/MessageForm/AtEditor/types';

const getAltAt = (at) => {
  if (at === '"' || at === '№' || at === ';') {
    return altAts[at];
  }
  return at;
};

export default getAltAt;
