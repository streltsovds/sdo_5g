import { Actions } from 'vuex-smart-module';
import HmRecruitersChatState from '../vuexStoreModule/state';
import HmRecruitersChatGetters from '../vuexStoreModule/getters';
import HmRecruitersChatMutations from '../vuexStoreModule/mutations';
import { newEmptyMessage } from './helpers';
import Message from '../types/message';

class HmRecruitersChatActions extends Actions{
  // Actions instance has 'state', 'getters', 'commit' and 'dispatch' properties
  pushMessage(payload) {
    let { message, msgFields } = payload;

    let msgObj = {
      ...msgFields,
      message,
    };

    const newMsg = {
      ...newEmptyMessage(this.state.user),
      ...msgObj,
    };
    this.mutations.pushMessage({ message: newMsg });
  }
  pushEditedMessage(messageText) {
    let editingMessageId = this.state.editingMessageId;

    if (editingMessageId === null) return;

    let edMsg = (this.state.messages || []).find(
      m => m.message_id === editingMessageId
    );
    if (!edMsg) return;

    edMsg.message = messageText;

    this.mutations.pushEditedMessage(edMsg);
    this.mutations.setEditingMessageId(null);
  }

  removeMessage(payload) {
    let { messageId } = payload;

    this.mutations.removeMessage({ messageId: messageId });
  }

  toggleWatching(payload) {
    let { itemId, watchType } = payload;
    let newWatching = { ...this.state.watching[watchType] };
    newWatching[itemId] = !newWatching[itemId];

    let watching = {
      ...this.state.watching,
      [watchType]: newWatching,
    };

    this.mutations.setWatching({ watching });
  }

  throwFatalError(payload) {
    let { errorCode } = payload;

    this.mutations.throwFatalError({ errorCode });
  }
}

export default HmRecruitersChatActions;
