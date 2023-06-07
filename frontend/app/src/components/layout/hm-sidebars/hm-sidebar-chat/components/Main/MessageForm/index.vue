<template>
  <div class="hm-recruiters-chat__message-form">
    <at :api="api">
      <div class="hm-recruiters-chat__message-form-input"
           ref="messageInput"
           @keypress.enter="sendMessage"
           contenteditable
      />
    </at>
    <button class="hm-recruiters-chat__message-form-btn-send" :style="btnSendStyle" @click="sendMessage" />
  </div>
</template>

<script>
import Vue, { PropType as VuePropType } from 'vue';
import { storeModuleMapperDefault } from '../../../vuexStoreModule';
import Recruiter from '../../../types/recruiter'
import Vacancy from '../../../types/vacancy'
import At from './AtEditor/index.vue';

export default Vue.extend({
  name: 'HmRecruitersChatMainMessageForm',
  components:{
    At
  },
  computed:{
    ...storeModuleMapperDefault.mapState([
      'editingMessageId',
      'messages',
      'api'
    ]),
    newMessage(){
      return (this.$refs.messageInput).innerHTML;
    },
    btnSendStyle() {
      const top = (document.body.clientWidth > 1386 ? 230 : 290) + 'px';
      return { top };
    }
  },
  watch:{
    editingMessageId(value, oldValue){
      if(value !== null){
        let messageInput = (this.$refs.messageInput);
        const editingMessage = (this.messages || []).find(m => m.message_id === value);
        if(editingMessage){
          messageInput.innerHTML = editingMessage.message;
        }
      }
    }
  },
  methods:{
    ...storeModuleMapperDefault.mapActions([
      'pushMessage',
      'pushEditedMessage'
    ]),
    sendMessage(event){
      if (!(event.shiftKey === true && event.key === "Enter")){
        event.preventDefault();
        const messageInput = this.$refs.messageInput;
        const innerHTML = messageInput.innerHTML;
        if(!innerHTML) return;

        if(this.editingMessageId !== null){
          this.pushEditedMessage(innerHTML);
        }else{
          this.pushMessage({ message: innerHTML });
        }
        messageInput.innerHTML = '';
      }
    },
  },
});
</script>

<style lang="sass" src="./index.sass"/>
