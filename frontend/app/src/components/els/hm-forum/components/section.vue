<template>
  <v-card class="hm-forum-theme">
    <div>
      <hm-forum-partial-message :section="section" :message="firstMessage" :is-first="true" />
    </div>

    <div>
      <v-flex class="pa-0"
              xs12
              sm12
              md12
      >
        <div v-for="(message, index) in forumMessages" :key="index">
          <hm-forum-partial-message :section="section" :message="message" @editMessage="editMessage" @deleteMessage="deleteMessage" @addMessage="addMessage" />
        </div>
      </v-flex>
    </div>

    <div class="forum-theme__reply" id="forum-theme-reply">
      <v-alert v-if="error" :value="true" type="warning">
        {{ error }}
      </v-alert>
      <span class="forum-theme__reply-title">Ответ на сообщение</span>
      <hm-forum-new-message :url="getNewMessageUrl()" :disabled="disabledInput" @sendMessage="sendMessage" textButton="Ответить" :target-hash="tinyMceTargetHash" />
    </div>
  </v-card>

</template>
<script>
import SvgIcon from "@/components/icons/svgIcon";
import hmCardLink from "@/components/els/hm-card-link";
import HmForumPartialMessage from "@/components/els/hm-forum/partials/message";
import HmForumNewMessage from './newMessage';
import globalActions from "@/store/modules/global/const/actions";

export default {
  name: "HmForumSection",
  components: {HmForumPartialMessage, SvgIcon, hmCardLink, HmForumNewMessage},
  props: {
    section: {
      type: Object,
      required: true
    },
    form: {
      type: String,
      required: true
    },
    tinyMceTargetHash: {
      type: String,
      default: ""
    }
  },
  data() {
    return {
      forumMessages: this.section.messages,
      disabledInput: false,
      error: ''
    }
  },
  computed: {
    firstMessage(){
      return {
        user_name: this.section.user_name,
        created: this.section.created,
        title: this.section.title,
        text: this.section.text,
        user_id: this.section.user_id,
        user_photo: this.section.user_photo,
      }
    },
  },
  mounted() {
  },
  methods: {
    getForumUrl() {
      return '/forum/sections/index/forum_id/' + this.section.forum_id;
    },
    getNewMessageUrl() {
      return `/forum/messages/create/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}`
    },
    deleteMessage(message) {
      const newMessages = JSON.parse(JSON.stringify(this.forumMessages));
      if(message.answer_to === 0) {
        delete newMessages[message.message_id];
      } else {
        const answers = this.findMessage(newMessages, message.answer_to).answers;
        delete answers[message.message_id];
      }
      this.setForumMessages(newMessages);
    },
    addMessage(message) {
      const newMessages = JSON.parse(JSON.stringify(this.forumMessages));
      if(message.answer_to === 0) {
        newMessages[message.message_id] = message;
      } else {
        const answers = this.findMessage(newMessages, message.answer_to).answers;
        answers[message.message_id] = message;
      }
      this.setForumMessages(newMessages);
    },
    editMessage(message) {
      const newMessages = JSON.parse(JSON.stringify(this.forumMessages));
      if(message.answer_to === 0) {
        newMessages[message.message_id] = {
          ...message,
          answers: newMessages[message.message_id].answers
        }
      } else {
        const answers = this.findMessage(newMessages, message.answer_to).answers;
        answers[message.message_id] = {
          ...message,
          answers: answers[message.message_id].answers
        };
      }
      this.setForumMessages(newMessages);
    },
    setForumMessages(messages) {
      this.forumMessages = messages;
    },
    findMessage(obj, id) {
      let object = {};
      Object.values(obj).some(message => {
        if(message.message_id === id) object = message
        else if(!object.message_id) object =  this.findMessage(message.answers, id)
      });
      return object;
    },
    sendMessage(value) {
      this.disabledInput = true;
      this.$store.dispatch(globalActions.setLoadingOn);
      const formData = new FormData();
      formData.append('text', value);
      formData.append('is_hidden', 0);
      fetch(this.getNewMessageUrl(), {
        method: 'POST',
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
        body: formData
      }).then(res => res.json())
        .then(data => {
          if(data.error_message) this.error = data.error_message
          if(!data.error_message) this.error = ''
          if(data.success === 1) this.addMessage(data.message)
          this.disabledInput = false;
          this.$store.dispatch(globalActions.setLoadingOff);
        })
        .catch(err => {
          this.error = 'Произошла ошибка при отправке сообщения';
          this.disabledInput = false;
          this.$store.dispatch(globalActions.setLoadingOff);
          console.log(err)
        })
    }
  }
};
</script>
<style lang="scss">
    .hm-forum-theme {
        padding: 22px 26px 40px 26px;
    }


    .forum-theme__forum-link {
        text-decoration: none;
    }

    .forum-theme__title {
        font-weight: 500;
        margin-bottom: 21px;
        font-size: 20px;
        line-height: 24px;
    }

    .forum-theme__parent-section {
        color: #1E1E1E;
    }

    .forum-theme__current-section {
        color: #666;
    }

    .forum-theme__arrow-back {
        padding: 0 18px 0 8px;
        line-height: 24px;
    }

    .forum-theme__reply {
        margin-top: 30px;

        &-title {
            font-size: 16px;
            font-weight: 400;
        }

        &-form {
        }

        .hm-tiny-mce {
            margin-top: 5px !important;
        }
    }


</style>
