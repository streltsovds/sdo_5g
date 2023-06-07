<template>
  <v-card class="hm-user-events"
          :height="'100%'"
          flat
          tile
  > 
    <div class="hm-user-events__wrapper">
      <p class="hm-user-events__readed-handler" @click="setAsReadedAll">
        Пометить как прочитанные
      </p>
      <p class="hm-user-events__read-all" @click="readAll">
        Все сообщения
      </p>
      <hm-user-event :event="item" v-for="(item,index) in events" :key="index" />
    </div>
  </v-card>
</template>

<script>
import HmUserEvent from "./partials/event.vue";

export default {
  components: {
    HmUserEvent
  },
  props: {
    url: {
      type: String,
      default: ''
    },
    debug: Boolean
  },
  data() {
    return {
      events: [],
    };
  },
  mounted(){
    if(this.url){
      this.getData();
    }
  },
  methods: {
    getData() {
      this.$axios.get(this.url).then(res => {
        if(res.data){
          this.events = res.data.events;
        }
      });
    },
    setAsReadedAll(){
      let events = [...this.events];
      let messageIds = events/*.filter(item => !item.readed)*/.map(item => item.message_id);
      if(messageIds.length > 0){
        this.$axios.post('/message/ajax/messages-readed', {
          messages: messageIds,
        })

        events.map(item => {
          item.readed = 1;
          return item;
        });

        this.events = events;
      }

      this.$root.view.numberOfNotifications = 0;
    },
    readAll(){
      window.location = '/message/view/system';
    }
  }
};
</script>
<style lang="scss">
  .hm-user-events {
    &__wrapper{
      padding: 0 16px 50px 16px;
      height: 95%;
      overflow-y: scroll;
    }
    &__read-all{
      background-color: #1F8EFA;
      border-radius: 4px;
      text-align: center;
      font-weight: normal;
      padding: 8.5px 0;
      font-size: 14px;
      line-height: 18px;
      letter-spacing: .15px;
      margin-bottom: 0!important;
      margin-top: 10px!important;
      color: #fff;
      cursor: pointer;
      &:hover, &:active{
        background-color: #1e86ee;
      }
    }
    &__readed-handler{
      border: 1px solid #1F8EFA;
      border-radius: 4px;
      text-align: center;
      font-weight: normal;
      padding: 8.5px 0;
      font-size: 14px;
      line-height: 18px;
      letter-spacing: .15px;
      margin-bottom: 0!important;
      color: #2574cf;
      cursor: pointer;
      &:hover{
        color: #2462aa;
      }
      &:active{
        color: #2462aa;
      }
    }
    .v-window.v-item-group .v-window__container{
      height: 100%; /* override vuetify2 inherit */
    }
  }
</style>
