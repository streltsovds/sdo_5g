<template>
  <div
    class="hm-forum-message"
    :is-first="isFirst"
    :class="{
      'hm-forum-message_border': !isFirst && (level !== 0 || Object.keys(message.answers).length !== 0),
      'hm-forum-message_first': isFirst
    }"
  >
    <div class="hm-forum-message__head">
      <div class="hm-forum-message__user-info">
        <hm-card-link
          :url="`/user/list/view/user_id/${message.user_id}`"
          title="карточка пользователя"
          rel="pcard"
          float=" "
        >
          <img
            class="hm-forum-message__user-avatar"
            v-if="message.user_photo && message.user_photo !== '/'"
            :src="message.user_photo"
            alt="фото"
            width="34"
            height="34"
            style="border-radius: 50%; position:relative; "
          >
          <svg-icon v-else name="userCourse" class="hm-forum-message__user-avatar" width="34" height="34"/>
          <span class="hm-forum-message__user-name">{{ message.user_name }}, </span>
        </hm-card-link>

        <div class="hm-forum-message__message-created">
          {{ message.created }}
        </div>
      </div>
      <div class="hm-forum-message__actions">
        <v-tooltip v-if="!isFirst" bottom>
          <template v-slot:activator="{ on, attrs }">
            <button v-bind="attrs" v-on="on" @click="showTiny(message.message_id, 'new')" class="hm-forum-message__action">
              <svg-icon name="messages" color="black" height="20" width="20"/>
            </button>
          </template>
          <span>Ответить</span>
        </v-tooltip>
        <v-tooltip v-if="!isFirst && showLinks()" bottom>
          <template v-slot:activator="{ on, attrs }">
            <button v-bind="attrs" v-on="on" @click="showTiny(message.message_id, 'edit')" class="hm-forum-message__action">
              <svg-icon name="edit" color="black" height="16" width="16"/>
            </button>
          </template>
          <span>Изменить сообщение</span>
        </v-tooltip>
        <v-tooltip v-if="isFirst && showLinks()" bottom>
          <template v-slot:activator="{ on, attrs }">
            <a v-bind="attrs" v-on="on" :href="getEditLink()" class="hm-forum-message__action">
              <svg-icon name="edit" color="black" height="16" width="16"/>
            </a>
          </template>
          <span>Изменить сообщение</span>
        </v-tooltip>
        <v-tooltip v-if="!isFirst && showLinks()" bottom>
          <template v-slot:activator="{ on, attrs }">
            <button v-bind="attrs" v-on="on" @click="deleteMessage" class="hm-forum-message__action">
              <svg-icon name="delete" color="black"  height="16"  width="16" />
            </button>
          </template>
          <span>Удалить сообщение</span>
        </v-tooltip>
        <v-tooltip v-if="isFirst && showLinks() && !section.forum.subject_id" bottom>
          <template v-slot:activator="{ on, attrs }">
            <a v-bind="attrs" v-on="on" :href="getDeleteLink()" class="hm-forum-message__action">
              <svg-icon name="delete" color="black" height="16" width="16"/>
            </a>
          </template>
          <span>Удалить сообщение</span>
        </v-tooltip>
        <!-- <div class="hm-forum-message__action">
          <svg-icon name="lock"
                    color="black"
                    height="20"
                    width="20"
          />
        </div> -->
      </div>
    </div>
    <div class="hm-forum-message__body">

      <div v-if="message.title" class="hm-forum-message__title">
        {{ message.title }}
      </div>
      <div class="hm-forum-message__text" v-html="message.text" />
      <v-alert v-if="error" :value="true" type="warning">
        {{ error }}
      </v-alert>
      <hm-forum-new-message
        v-if="tinyType && tinyType === 'new' && !isFirst"
        :key="tinyType"
        @sendMessage="sendMessage"
        :disabled="disabledInput"
        textButton="Ответить"
      />
      <hm-forum-new-message
        v-if="tinyType && tinyType === 'edit' && !isFirst"
        :key="tinyType"
        :text="message.text"
        @sendMessage="editMessage"
        :disabled="disabledInput"
        textButton="Сохранить"
      />

    </div>
    <div class="hm-forum-message__footer">
      <a class="hm-forum-message__reply-btn" v-if="isFirst" href="#forum-theme-reply">
        <svg-icon class="hm-forum-message__reply-btn-icon" name="messages" color="#ffffff" height="20" width="20"/>
        Ответить
      </a>
    </div>
    <div v-for="(answer, i) in message.answers" :key="i">
      <hm-forum-partial-message :section="section" :message="answer" :level="currentLevel+1" @addMessage="addMessageSection" @deleteMessage="deleteMessageSection" @editMessage="editMessageSection" />
    </div>
  </div>

</template>


<script>
import SvgIcon from "@/components/icons/svgIcon";
import hmCardLink from "@/components/els/hm-card-link";
import HmForumPartialMessage from "@/components/els/hm-forum/partials/message";
import HmForumNewMessage from '../components/newMessage';
import globalActions from "@/store/modules/global/const/actions";

export default {
  name: "HmForumPartialMessage",
  components: {SvgIcon, hmCardLink, HmForumPartialMessage, HmForumNewMessage},
  props: {
    message: {
      type: Object,
      required: true
    },
    level: {
      type: Number,
      default: 0
    },
    isFirst: {
      type: Boolean,
      default: false
    },
    section: {
      type: Object,
      required: true
    },
  },
  data() {
    return {
      currentLevel: this.level,
      tinyType: false,
      disabledInput: false,
      error: ''
    }
  },
  computed: {
    userId() {
      return this.$store.getters['user/GET_DATA_USER'];
    }
  },
  mounted() {
  },
  methods: {
    getClassByLevel(level) {
      return 'hm-forum-message__margin message-level-' + level;
    },
    showLinks() {

      if(this.section.forum.moderator || this.section.current_user_id === this.message.user_id) return true
      else return false
    },
    getDeleteLink() {
      if(this.section.forum.subject_id) return `/forum/themes/delete/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}/subject_id/${this.section.forum.subject_id}`
      else return `/forum/themes/delete/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}`
    },
    getEditLink() {
      return `/forum/themes/edit/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}`;
    },
    showTiny(id, type) {
      if(this.tinyType === type) this.tinyType = null
      else this.tinyType = type
    },
    deleteMessageSection(data) {
      this.$emit('deleteMessage', data)
    },
    editMessageSection(data) {
      this.$emit('editMessage', data)
    },
    addMessageSection(data) {
      this.$emit('addMessage', data)
    },
    deleteMessage() {
      this.$store.dispatch(globalActions.setLoadingOn);
      fetch(`/forum/messages/delete/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}/message_id/${this.message.message_id}`, {
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
      })
      .then(res => res.json())
      .then(data => {
        if(data.error_message) this.error = data.error_message
        if(data.success === 1) this.deleteMessageSection(this.message);
        this.$store.dispatch(globalActions.setLoadingOff);
      })
      .catch(err => {
        console.error(err);
        this.error = 'Произошла ошибка при удалении сообщения';
        this.$store.dispatch(globalActions.setLoadingOff);
      })
    },
    editMessage(value) {
      this.disabledInput = true;
      this.$store.dispatch(globalActions.setLoadingOn);
      const formData = new FormData();
      formData.append('text', value);
      formData.append('is_hidden', 0);
      fetch(`/forum/messages/edit/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}/message_id/${this.message.message_id}`, {
        method: 'POST',
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.error_message) this.error = data.error_message
        if(!data.error_message) {
          this.tinyType = false;
          this.error = '';
        }
        if(data.success === 1) this.editMessageSection(data.message);
        this.$store.dispatch(globalActions.setLoadingOff);
        this.disabledInput = false;
      })
      .catch(err => {
        this.error = 'Произошла ошибка при сохранении изменений';
        console.error(err);
        this.$store.dispatch(globalActions.setLoadingOff);
        this.disabledInput = false;
      })
    },
    sendMessage(value) {
      this.disabledInput = true;
      this.$store.dispatch(globalActions.setLoadingOn);
      const formData = new FormData();
      formData.append('text', value);
      formData.append('is_hidden', 0);
      fetch(`/forum/messages/create/forum_id/${this.section.forum_id}/section_id/${this.section.section_id}/message_id/${this.message.message_id}/answer_to/${this.message.message_id}`, {
        method: 'POST',
        headers: {
          'X_REQUESTED_WITH': 'XMLHttpRequest',
        },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.error_message) this.error = data.error_message;
        if(!data.error_message) {
          this.tinyType = false;
          this.error = '';
        }
        if(data.success === 1) this.addMessageSection(data.message);
        this.$store.dispatch(globalActions.setLoadingOff);
        this.disabledInput = false;
      })
      .catch(err => {
        this.error = 'Произошла ошибка при отправке сообщения';
        console.error(err);
        this.$store.dispatch(globalActions.setLoadingOff);
        this.disabledInput = false;
      })
    }
  }
};
</script>
<style lang="scss">
    .hm-forum-message {
        // display: flex;
        // flex-direction: row;

        padding: 20px;
        padding-right: 0;
        background: #fff;
        border-radius: 4px;
        margin-bottom: 5px;

        &__user-name {
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
        }

        & .hm-card-link__link {
          display: flex;
          align-items: center;
        }

        &_border {
          border-radius: 0;
          border-left: 1px solid rgba(130, 147, 163, 0.4);
        }

        &[is-first] { /* todo: разные цвета, как и зачем? */
            background-color: #FFFAE5 !important;
            margin-bottom: 56px;
        }
        &__body{
          display: flex;
          flex-direction: column;
          margin-top: 16px;
          margin-left: 44px;
          margin-right: 20px;

          & img {
            max-width: 100%;
            height: auto !important;
          }
        }
        &__head{
            display: flex;
            justify-content: space-between;
            margin-right: 20px;
        }
        &__user-info{
            display: flex;
            height: 34px;
            align-items: center
        }
        &__user-avatar {
          width: 34px !important;
          height: 34px !important;
          margin: 0;
          margin-right: 10px;
        }
        &__user-name{
            font-weight: 500;
            font-size: 14px;
            color: #083A81;
            margin-right: 4px;
        }
        &__user-role{
            font-weight: 300;
            font-size: 14px;
            color: #666666;
            margin-right: 8px;
        }
        &__message-created{
            font-weight: 300;
            font-size: 14px;
            color: #666666;
        }
        &__actions{
            display: flex;
        }
        &__title{
          font-weight: 500;
          font-size: 18px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #131313 !important;
          text-decoration: none;
          margin-bottom: 8px;
        }
        &__text{
          font-weight: 300;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #000000;
        }
        &__reply-btn{
            display: inline-flex;
            padding: 6px 26px;

            font-weight: 400;
            font-size: 14px;
            color: #FFFFFF!important;
            background: #FF9800;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.05);
            border-radius: 4px;
            cursor: pointer;
            transition: opacity 0.2s;
            text-decoration: none;
            &:visited{
                color: #fff;
            }
            &:hover{
                transition: opacity 0.2s;
                opacity: 0.8;
            }
        }
        &__reply-btn-icon{
            margin-right: 8px;
        }
        &__footer{
            margin-top: 26px;
            margin-left: 44px;
        }
        &__action{
            margin-left: 16px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            align-content: center;
            justify-content: center;
            svg * {
                fill: #485B70;
            }
            svg:hover *{
                fill: #2574CF;
            }
        }
    }

    // .hm-forum-message__margin {
    //     width: 0;

    //     &.message-level-1,
    //     &.message-level-2,
    //     &.message-level-3,
    //     &.message-level-4,
    //     &.message-level-5 {
    //         border-left: 1px solid #8293A3;
    //     }
    // }

    // .message-info__user-avatar {
    //     width: 34px !important;
    //     height: 34px !important;
    //     margin: 0 10px 0 10px;
    // }

    // .message-info__user-name {
    //     color: #083A81;
    //     font-weight: 500;
    // }

    // .message-info {
    // }

    // .message-info__message-time {
    //     color: #666;
    // }

    // .message__message-content {
    //     margin-left: 54px; // На ширину message-info__user-avatar + padding + margin слева-спаава
    // }

    // .message__message-text {
    //     font-size: 14px;
    // }

    // .subsection__create-message {
    //     width: 153px;
    //     display: flex;
    //     justify-content: center;
    //     align-items: center;
    //     margin-right: 28px;
    //     font-size: 14px;
    //     line-height: 24px;
    //     margin: 20px 0;
    // }

    // .subsection__create-message-icon {
    //     margin-right: 5px;
    // }
    @media(max-width: 959px) {
      .hm-forum-message {
        &__head {
          align-items: flex-start;
        }
        &__actions {
          margin-top: 6px;
        }
        &__user-info {
          flex-direction: column;
          align-items: flex-start;
          height: min-content;
        }
        &__message-created {
          margin-left: 44px;
          margin-top: 10px;
        }
      }

      .hm-forum-message_first {
        & .hm-forum-message__user-info {
          flex-direction: row;
          align-items: center;
          width: 100%;
        }
        & .hm-forum-message__head {
          flex-direction: column;
        }
        & .hm-forum-message__message-created {
          margin: 0;
          margin-left: auto;
          white-space: nowrap;
        }
        & .hm-forum-message__actions {
          margin-left: 25px;
          margin-top: 20px;
        }
        & .hm-forum-message__action {
          width: 20px;
          height: 20px;
        }
      }
    }
    @media(max-width: 599px) {
      .hm-forum-theme {
        margin: -16px;
        width: 100vw;
        max-width: 100vw;
        padding: 16px !important;
        padding-top: 22px !important;
      }
    }
</style>
