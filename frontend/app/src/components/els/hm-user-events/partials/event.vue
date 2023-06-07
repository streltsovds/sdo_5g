<template>
  <div class="mt-3 item-events" :style="eventItemStyle">
    <div class="item-events__title" @click="openMessageHandler" >
      <div class="item-events__title-img">
        <img :src="titleImgSrc" :title="event.from" srcset="">
      </div>
      <div class="item-events__title-subject">
        <span>{{ _(event.subject || "Без темы") }}</span>
      </div>
    </div>
    <v-expand-transition>
      <div class="item-events__description" v-show="isOpened" v-html="_(event.description)" />
    </v-expand-transition>
  </div>
</template>

<script>

export default {
  components: {},
  props: {
    event: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      isOpened: false,
    };
  },
  computed: {
    isReaded(){
      return !!(+this.event.readed)
    },
    isSystemMessage(){
      return(this.event.from === "Системное сообщение");
    },
    titleImgSrc(){
      return '/' + !isSystemMessage ? this.event.senderPhotoUrl : 'android-icon-36x36.png';
    },
    eventItemStyle(){
      let style = {};
      if(this.isReaded){
        style.opacity = '0.55';
      }
      return style;
    }
  },
  methods: {
    openMessageHandler(){
      this.setReaded(this.event.message_id);
      this.isOpened = !this.isOpened;
      this.event.readed = 1;

      this.$root.view.numberOfNotifications--;
    },
    setReaded(messageId){
      this.$axios.post('/message/ajax/messages-readed', {
        messages: [messageId],
      })
    }
  }
};
</script>

<style lang="scss">
.item-events {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  background: #F5F5F5;
  border-radius: 6px;
  padding: 10px;
  box-sizing: border-box;
  height: auto!important;
  min-height: auto !important;
  cursor: pointer;
  > div {
    display: flex;
    width: 100%;
    flex-direction: column;
  }
  &__title{
    flex-direction: row !important;
    &-img {
      width: 28px;
      min-width: 28px;
      height: 28px;
      img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
      }
    }
    &-subject {
      margin-left: 12px;
      font-weight: 500;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #1E1E1E;
    }
  }
  &__description{
    margin-top: 3px;
    font-weight: normal;
    font-size: 12px;
    line-height: 18px;
    letter-spacing: 0.15px;
    color: #3E4E6C;
    width: 100%;
    cursor: default;
    p {
      margin: 0;
    }
  }
}

$animationDuration: 0.3s; // specify animation duration. Default value: 1s
@import "vue2-animate/src/sass/vue2-animate.scss";
.hiddenmessage {
  display: block;
}
</style>
