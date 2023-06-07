import { AtTypes } from '../../components/Main/MessageForm/AtEditor/types';
import HmRecruitersChatUrls from './urls';


class HmRecruitersChatApi {
  static async getMessageItems(
    at,
    query,
    lesson_id,
    subject_id
  ) {
    const reqAt = at === '#' ? 'hash' : at;
    const searchParams = `?query=${reqAt}${query}&lesson_id=${lesson_id}&subject_id=${subject_id}`;

    const response = await fetch(
      HmRecruitersChatUrls.GET_MESSAGE_ITEMS + searchParams,
      {
        method: 'GET',
      }
    );

    return await response.json();
  }

  static async getFilteredMessages(
    props
  ) {
    let searchParams = `?`;

    Object.keys(props).forEach((key) => {
      if (searchParams[searchParams.length - 1] !== '?') {
        searchParams += '&';
      }
      switch (key) {
        case 'query':
        case 'page':
          searchParams += `${key}=${props[key]}`;
          break;
        case 'items':
          {
            let items = props.items;
            Object.keys(items).forEach(k => {
              if (!items[k].length) return;
              if (searchParams[searchParams.length - 1] !== '?') {
                searchParams += '&';
              }
              searchParams = searchParams + `items[${k}]=${items[k].join(',')}`;
            });
          }
          break;
        default:
          break;
      }
    });

    const response = await fetch(
      HmRecruitersChatUrls.GET_FILTERED_MESSAGEs + searchParams,
      {
        method: 'GET',
      }
    );

    return await response.json();
  }
}

export default HmRecruitersChatApi;
