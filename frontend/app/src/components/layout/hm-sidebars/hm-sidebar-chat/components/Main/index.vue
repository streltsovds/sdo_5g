<template>
  <div class="hm-recruiters-chat__main">
    <hm-recruiters-chat-main-message-logs />
    <hm-recruiters-chat-main-message-form />
  </div>
</template>

<script>
import Vue, { PropType as VuePropType } from 'vue';
import HmRecruitersChatMainMessageForm from './MessageForm/index.vue'
import HmRecruitersChatMainMessageLogs from './MessageLogs/index.vue'

export default Vue.extend({
  name: 'HmRecruitersChatMain',
  components:{
    HmRecruitersChatMainMessageForm,
    HmRecruitersChatMainMessageLogs
  }
});
</script>

<style lang="sass" src="./index.sass"/>
