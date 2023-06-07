<template>
  <div class="hm-recruiters-chat__message-logs">
    <ul class="hm-recruiters-chat__message-logs-messages" @scroll="onScroll">
      <li class="msg-not-found" v-if="!messages.length">
        Сообщений не найдено
      </li>
      <li v-for="msg in (isFiltering ? [] : messages)" :key="msg.id">
        <hm-recruiters-chat-main-message :message="msg" />
      </li>
      <div class="lds-ring" v-if="loadingNewMessages || isFiltering">
        <div /><div /><div /><div />
      </div>
    </ul>
  </div>
</template>

<script>
import Vue, { PropType as VuePropType } from 'vue';

import { storeModuleMapperDefault } from '../../../vuexStoreModule';
import HmRecruitersChatMainMessage from '../../../components/Main/Message/index.vue'
import Message from '../../../types/message';
import HmRecruitersChatApi from '../../../lib/api';
import { isEqual } from 'lodash';
import MutationTypesList from '../../../lib/mutationTypesList';
import { Store } from 'vuex';
import HmRecruitersChatState from '../../../vuexStoreModule/state';


export default Vue.extend({
  name: 'HmRecruitersChatMainMessageLogs',
  components:{
    HmRecruitersChatMainMessage
  },
  data() {
    return {
      loadingNewMessages: false,
      currentPage: 1,
      allMsgsIsLoaded: false,
      isTyping: false,
      typingTimeoutId: null,
      isFiltering: false
    }
  },
  computed: {
    ...storeModuleMapperDefault.mapState([
      'messages',
    ]),
  },
  watch:{
    searchString(){
      this.typingTimeout();
    },
    isTyping(value, oldValue){
      if(!value && oldValue){
        this.isFiltering = true;
        this.allMsgsIsLoaded = false;
        this.currentPage = 0;
        this.getFilteredMessages().then(res => {
          this.setMessages(res);
          this.isFiltering = false;
          this.currentPage++;
        }).catch(error => {
          this.isFiltering = false;
        })
      }
    },
    watching(value, oldValue){
      if(!isEqual(value, oldValue)){
        this.typingTimeout();
      }
    }
  },
  mounted(){
    // window.onscroll = this.onScroll;
  },
  methods:{
    ...storeModuleMapperDefault.mapMutations([
      'pushMessages',
      'setMessages'
    ]),
    getFilteredMessages(){
      const vacs = Object.keys(this.watching.vacancies).filter(v => this.watching.vacancies[v]).map(Number);
      const recs = Object.keys(this.watching.recruiters).filter(r => this.watching.recruiters[r]).map(Number);
      const profs = Object.keys(this.watching.profiles).filter(r => this.watching.profiles[r]).map(Number);

      return HmRecruitersChatApi.getFilteredMessages({
        page: this.currentPage + 1,
        query: this.searchString,
        items: {
          vacancy: [...vacs],
          recruiter: [...recs],
          profile: [...profs],
        }
      })
    },
    onScroll(e){
      const scrollBottom = document.body.scrollHeight - window.innerHeight - window.scrollY;

      if(scrollBottom === 0 && !this.loadingNewMessages && !this.allMsgsIsLoaded){
        this.loadingNewMessages = true;

        this.getFilteredMessages().then(res => {
          if(res.length) this.currentPage++
          if(!res.length) this.allMsgsIsLoaded = true;
          this.pushMessages({messages: res});
          this.loadingNewMessages = false;

        }).catch(error => {
          this.loadingNewMessages = false;
        })
      }

    },
    async typingTimeout(timer = 1000){
      this.isTyping = true;
      if(this.typingTimeoutId){
        clearTimeout(this.typingTimeoutId);
      }
      this.typingTimeoutId = setTimeout(()=>{
        this.isTyping = false;
      },timer)
    },
  },
});
</script>

<style lang="sass" src="./index.sass"/>
