/**
 * Обработка входящих сообщений с сокет-сервера yii и вызов соответствующих экшенов для
 * внесения данных в хранилище
 */
import * as socket from './index';
import { serverActionNames as serverActions } from '../../types/serverActions';
import MutationTypesList from '../mutationTypesList';
import { Store } from 'vuex';
import HmRecruitersChatState from '../../vuexStoreModule/state';
import Message from '../../types/message';
import ActionTypesList from '../actionTypesList';

/**
 * Обработчика входящих сообщений
 * @exports
 */
export default function messageListener(
  event,
  store
) {
  const message = JSON.parse(event.data);
  const { action, data } = message;
  const state = store.state.HmRecruitersChat;

  switch (action) {
    // Подключение/отключение пользователя
    case serverActions.ONLINE:
    case serverActions.CONNECTED:
    case serverActions.DISCONNECTED: {
      return '';
    }
    case serverActions.EDIT:
      {
        const msg = message.data;
        const msgUserId = msg.user.id;
        if (state.user.id !== msgUserId) {
          store.commit(MutationTypesList.PUSH_EDITED_MESSAGE, msg);
        }
      }
      break;
    case serverActions.SENT:
      {
        const msg = message.data;
        const text = msg.message;
        const msgUserId = msg.user_id;
        if (state.user.id === msgUserId) {
          store.commit(MutationTypesList.SET_MESSAGE_ID, {
            text,
            msgId: msg.message_id,
          });
        }
      }
      break;
    case serverActions.DELETE:
      {
        const msg = message.data;
        const msgIsExits = !!(state.messages || []).find(
          m => m.message_id === msg.message_id
        );
        if (msgIsExits) {
          store.commit(MutationTypesList.REMOVE_MESSAGE, {
            messageId: msg.message_id,
          });
        }
      }
      break;
    //Новое сообщение
    case serverActions.SEND: {
      const msg = message.data;

      if (msg.user_id !== state.user.id) {
        store.dispatch(ActionTypesList.PUSH_MESSAGE, {
          message: data.message || '',
          msgFields: data,
        });
      }
    }
  }
}
