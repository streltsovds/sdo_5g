<template>
  <!-- <div :class="wrapperClass">
    <div class="hm-recruiters-chat__message-header">
      <div class="hm-recruiters-chat__message-user">
        <div class="hm-recruiters-chat__message-user-avatar">
          <img :src="message.user.avatarUrl">
        </div>
        <p class="hm-recruiters-chat__message-user-name">
          {{ message.user.name }}
        </p>
      </div>
      <p class="hm-recruiters-chat__message-date">
        {{ messageDate }}
      </p>
    </div>

    <div class="hm-recruiters-chat__message-text">
      <div class="hm-recruiters-chat__message-controls" v-if="isMyMessage">
        <div class="hm-recruiters-chat__message-icon--edit hm-recruiters-chat__message-icon" @click="onEditMessage"/>
        <div class="hm-recruiters-chat__message-icon--remove hm-recruiters-chat__message-icon" @click="onRemoveMessage" />
      </div>
      <p v-html="message.message" />
    </div>
  </div> -->
  <div :class="wrapperClass">
    <div class="hm-recruiters-chat__message-header">
      <div class="hm-recruiters-chat__message-user">
        <p class="hm-recruiters-chat__message-user-name">
          {{ message.user.name }}
        </p>
      </div>
      <p class="hm-recruiters-chat__message-date">
        {{ messageDate }}
      </p>
    </div>

    <div class="hm-recruiters-chat__message-text">
      <img class="hm-recruiters-chat__message-photo" :src="message.user.avatarUrl">
      <!-- <div class="hm-recruiters-chat__message-controls" v-if="isMyMessage">
        <div class="hm-recruiters-chat__message-icon--edit hm-recruiters-chat__message-icon" @click="onEditMessage"/>
        <div class="hm-recruiters-chat__message-icon--remove hm-recruiters-chat__message-icon" @click="onRemoveMessage" />
      </div> -->
      <p v-if="!isMyMessage" class="hm-recruiters-chat__message-value" v-html="message.message" />
      <v-menu v-else offset-y>
        <template v-slot:activator="{ on, attrs }">
          <p v-bind="attrs" v-on="on" class="hm-recruiters-chat__message-value" v-html="message.message" />
        </template>
        <v-list>
          <v-list-item>
            <div class="hm-recruiters-chat__message-icon--edit hm-recruiters-chat__message-icon" @click="onEditMessage">Редактировать</div>
          </v-list-item>
          <v-list-item>
            <div class="hm-recruiters-chat__message-icon--remove hm-recruiters-chat__message-icon" @click="onRemoveMessage">Удалить</div>
          </v-list-item>
        </v-list>
      </v-menu>
    </div>
  </div>
</template>

<script>
import Vue, { PropType as VuePropType } from 'vue';

import Message from '../../../types/message';
import { storeModuleMapperDefault } from '../../../vuexStoreModule';
import Recruiter from '../../../types/recruiter';
import mergeCssClassesPrefix from 'classnames-prefix';
import moment, { Moment } from 'moment';

const CLS = 'hm-recruiters-chat__message';

export default Vue.extend({
  name: 'HmRecruitersChatMainMessage',
  props: {
    message: {
      type: Object,
      required: true
    }
  },
  computed:{
    ...storeModuleMapperDefault.mapState(['user']),
    isMyMessage(){
      return this.user.id === this.message.user.id
    },
    messageDate(){
      moment.locale('ru')
      const createdAtMoment = moment(this.message.created_at);
      const now = moment();
      const isY = createdAtMoment.format('D') === now.format('D');
      const format = isY ? 'сегодня в LT' : 'D MMMM LT';

      return createdAtMoment.format(format);
    },
    wrapperClass(){
      return mergeCssClassesPrefix(CLS)(
        CLS,
        {
          '--my': this.isMyMessage,
          '--sending': !this.message.message_id
        }
      );
    },
  },
  methods:{
    ...storeModuleMapperDefault.mapActions(['removeMessage']),
    ...storeModuleMapperDefault.mapMutations(['setEditingMessageId']),
    onRemoveMessage(){
      this.$root.$confirm('Вы действительно хотите удалить сообщение?').then((res) => {
        if(res){
          this.removeMessage({messageId: this.message.message_id});
        }
      })
    },
    onEditMessage(){
      this.setEditingMessageId(this.message.message_id);
    }
  }
});
</script>

<style lang="sass" src="./index.sass"/>
