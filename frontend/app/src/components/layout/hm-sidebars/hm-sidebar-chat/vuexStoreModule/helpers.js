import { isEmpty, isObjectLike, pickBy } from 'lodash';
import moment from 'moment';
import { ItemTypes } from '../types/itemTypes';
import Message from '../types/message';
import User from '../types/recruiter';

/**
 * Отсеивать случаи, когда приходит пустой массив вместо объекта
 * @param data
 */
export function filterInitialData(data) {
  return pickBy(data, function(value, _key) {
    return !isObjectLike(value) || !isEmpty(value);
  });
}

export function newEmptyMessage(user) {
  return {
    created_at: moment().format(),
    message: '',
    user_id: user.id,
    message_id: null,
    user: user,
  };
}

export function getItemsFromMessage(message) {
  const items = [];
  const elems = document.createElement('div');
  elems.innerHTML = message;
  const spans = elems.querySelectorAll('span[data-type]');
  spans.forEach(n => {
    const dateType = n.getAttribute('data-type');
    const { TYPE_CANDIDATE, TYPE_RECRUITER, TYPE_VACANCY } = ItemTypes;
    const isNeedSubstring = [
      TYPE_CANDIDATE,
      TYPE_RECRUITER,
      TYPE_VACANCY,
    ].includes(dateType);

    let text = isNeedSubstring ? n.innerHTML.substring(1) : n.innerHTML;

    items.push({
      text,
      type: dateType,
      itemId: n.getAttribute('data-item-id') || null,
    });
  });

  elems.remove();
  return items;
}
