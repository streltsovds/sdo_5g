import HmRecruitersChatState from '../../vuexStoreModule/state';
import { MutationPayload } from 'vuex';
import MutationTypesList from '../mutationTypesList';
import * as socket from '../../lib/socket/index';
import { clientActionNames } from '../../types/serverActions';
import Message from '../../types/message';
import { getItemsFromMessage } from '../../vuexStoreModule/helpers';
/**  ---------------------------------------------------------------------------
 * @desc Объект с текущим состоянием для сравнения c предыдущим
 */
let curState = {};
/**
 * @desc Подписка на обновления хранилища
 */
export default async function(mutation, state) {
  // Обновление текущего состояния
  const prevState = { ...curState };
  curState = state.HmRecruitersChat;

  const { payload } = mutation;

  switch (mutation.type) {
    case MutationTypesList.PUSH_MESSAGE:
      {
        let message = payload.message;
        // Возвращаем новое сообщение
        const isMyMessage = message.user_id === curState.user.id;
        if (isMyMessage) {
          const items = getItemsFromMessage(message.message);
          socket.action(clientActionNames.SEND, {
            message: message.message,
            items,
          });
        }
      }

      break;
    case MutationTypesList.REMOVE_MESSAGE:
      {
        let messageId = payload.messageId;
        socket.action(clientActionNames.DELETE, { messageId });
      }

      break;
    case MutationTypesList.PUSH_EDITED_MESSAGE:
      {
        let { message_id, message, user } = payload;
        const items = getItemsFromMessage(message);
        const isMyMessage = user.id === curState.user.id;
        if (isMyMessage) {
          socket.action(clientActionNames.EDIT, {
            messageId: message_id,
            message,
            items,
          });
        }
      }

      break;

    default:
      break;
  }
}
