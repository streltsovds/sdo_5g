import { Mutations } from 'vuex-smart-module';
import { filterInitialData } from '..//vuexStoreModule/helpers';
import Vue from 'vue';
import { forOwn } from 'lodash';
import HmRecruitersChatState from './state';
import Message from '..//types/message';

class HmRecruitersChatMutations extends Mutations {
  stateInit(payload) {
    let initialData = filterInitialData(payload);
    let state = this.state;
    forOwn(initialData, function(value, fieldName) {
      if (!value) return;
      /** Vue.set обязателен, чтобы появилась реактивность у полей, которые были undefined */
      Vue.set(state, fieldName, value);
    });
  }

  componentEmitFunctionSet(payload) {
    Vue.set(this.state, 'componentEmitFn', payload.emitFn);
  }

  pushMessage(payload) {
    const { message } = payload;
    let msgs = [...this.state.messages] || [];

    msgs.push(message);
    Vue.set(this.state, 'messages', msgs);
  }

  pushEditedMessage(message) {
    let msgs = [...(this.state.messages || [])];
    const editedMsgsIndex = (msgs || []).findIndex(
      m => m.message_id === message.message_id
    );

    msgs[editedMsgsIndex] = message;
    Vue.set(this.state, 'messages', msgs);
  }

  setMessageId(payload) {
    const { text, msgId } = payload;
    let msgs = this.state.messages || [];
    let msg = msgs.find(
      m =>
        m.message === text && m.user_id === this.state.user.id && !m.message_id
    );
    if (msg) {
      msg.message_id = msgId;
      Vue.set(this.state, 'messages', msgs);
    }
  }

  removeMessage(payload) {
    const { messageId } = payload;
    let msgs = this.state.messages || [];

    let msg = msgs.find(m => m.message_id === messageId);

    if (msg) {
      msgs.splice(msgs.indexOf(msg), 1);

      Vue.set(this.state, 'messages', msgs);
    }
  }

  setEditingMessageId(id) {
    Vue.set(this.state, 'editingMessageId', id);
  }

  setSearchString(searchString) {
    Vue.set(this.state, 'searchString', searchString);
  }

  setWatching(payload) {
    const { watching } = payload;
    Vue.set(this.state, 'watching', watching);
  }

  pushMessages(payload) {
    const { messages } = payload;
    let newMsgs = this.state.messages || [];
    newMsgs.push(...messages);

    Vue.set(this.state, 'messages', newMsgs);
  }

  setMessages(messages) {
    Vue.set(this.state, 'messages', messages);
  }

  throwFatalError(payload) {
    const { errorCode } = payload;
    Vue.set(this.state.errors, 'fatal', errorCode);
  }
}

export default HmRecruitersChatMutations;
