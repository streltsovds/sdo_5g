<template>
  <v-flex class="subsection-info">
    <div class="subsection-info__main">

      <div class="subsection-info__main-header">
        <div class="subsection-info__main-header-wrapper">
          <div class="subsection-info__main-header-user-wrapper">
            <hm-card-link
              :url="`/user/list/view/user_id/${data.user_id}`"
              title="карточка пользователя"
              rel="pcard"
              float=" "
            >
              <img
                class="subsection-info__user-avatar"
                v-if="data.user_photo && data.user_photo !== '/'"
                :src="data.user_photo"
                alt="фото"
                width="34"
                height="34"
                style="border-radius: 50%; position:relative; "
              >
              <svg-icon v-else name="userCourse" class="subsection-info__user-avatar" width="34" height="34"/>
              <span class="subsection-info__user-name">{{ data.user_name }}</span>
            </hm-card-link>
            <span class="subsection-info__user-name">,&nbsp;</span>
            <span class="subsection-info__subsection-time">{{ data.created }}</span>
          </div>

          <div class="subsection-info__subsection-time-last-msg-wrapper">
            <span>Последнее сообщение:</span>
            <span class="subsection-info__subsection-time-last-msg">{{ data.last_msg }}</span>
          </div>
        </div>
        <div class="subsection-info__actions">
            <!-- <a>
                <svg-icon name="pin" class="subsection__actions-icon icon-pin" color="#485B70" height="16" width="16"/>
            </a> -->
            <a v-if="showLinks()" class="subsection-info__actions-link" :href="getEditLink()">
                <svg-icon name="edit" class="subsection-info__actions-icon icon-edit" color="#3E4E6C" height="16" width="16"/>
            </a>
            <a v-if="showLinks()" class="subsection-info__actions-link" :href="getDeleteLink()">
                <svg-icon name="delete" class="subsection-info__actions-icon icon-delete" color="#3E4E6C" height="16" width="16"/>
            </a>
            <v-tooltip top>
              <template v-slot:activator="{ on }">
                <div v-on="on" class="subsection-info__comments-wrapper">
                  <div>
                    <span class="subsection-info__comments-title">Ответы</span>
                  </div>
                  <div class="subsection-info__comments">
                    <svg-icon name="sayBubbleEmpty" class="subsection-info__comments-icon" color="#DADADA" height="42" width="42"/>
                    <div class="subsection-info__comments-counter">{{ data.count_msg }}</div>
                  </div>
                </div>
              </template>
              <span>Ответы</span>
            </v-tooltip>
        </div>
      </div>

      <div class="subsection-info__content">
        <a class="subsection-info__content-link" :href="getSectionUrl(data)">
          <strong>{{data.title}}</strong>
        </a>
        <div class="subsection-info__content-text" v-html="data.text"></div>
      </div>

    </div>

    <!-- <div class="subsection-info__comments-wrapper">
      <div>
        <span class="subsection-info__comments-title">Ответы</span>
      </div>
      <div class="subsection-info__comments">
        <svg-icon name="sayBubbleEmpty" class="subsection-info__comments-icon" color="#DADADA" height="42" width="42"/>
        <div class="subsection-info__comments-counter">{{ data.count_msg }}</div>
      </div>
    </div> -->

  </v-flex>
</template>
<script>
import HmCardLink from "@/components/els/hm-card-link"
import SvgIcon from "@/components/icons/svgIcon"
export default {
  props: ['data', 'subjectId', 'forum'],
  components: {HmCardLink, SvgIcon},
  computed: {
    userId() {
      return this.$store.getters['user/GET_DATA_USER'];
    }
  },
  methods: {
    getSectionUrl(section) {
      if(this.subjectId) return ['/forum/messages/index/forum_id/', section.forum_id, '/section_id/', section.section_id, '/subject_id/', this.subjectId].join('')
      else return ['/forum/messages/index/forum_id/', section.forum_id, '/section_id/', section.section_id].join('');
    },
    showLinks() {
      if(this.forum.moderator || this.forum.current_user_id === this.data.user_id) return true
      else return false
    },
    getDeleteLink() {
      if(this.forum.subject_id) return `/forum/themes/delete/forum_id/${this.data.forum_id}/section_id/${this.data.section_id}/subject_id/${this.forum.subject_id}`
      else return `/forum/themes/delete/forum_id/${this.data.forum_id}/section_id/${this.data.section_id}`
    },
    getEditLink() {
      return `/forum/themes/edit/forum_id/${this.data.forum_id}/section_id/${this.data.section_id}`;
    }
  }
}
</script>
<style lang="scss">
.subsection-info {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  border: 1px solid #CDDBF2;
  border-radius: 4px 0px 4px 4px;
  padding: 25px;
  margin-bottom: 12px;
  &__main-header-wrapper {
    display: flex;
    align-items: center;
    width: calc(100% - 100px);
  }
  &__main-header-user-wrapper {
    display: flex;
    align-items: center;
  }
  &__subsection-time-last-msg-wrapper {
    display: flex;
    align-items: center;
    margin-left: auto;
    font-weight: 400;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #1E1E1E;
  }
  &__subsection-time-last-msg {
    font-weight: 300;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #666666;
    margin-left: 8px;
  }
  &:last-child {
    margin-bottom: 0;
  }

  & .hm-card-link__link {
    display: flex;
    align-items: center;
  }

  &__main {
    width: calc(100% - 150px);
    position: relative;
    display: flex;
    flex-direction: column;
    &-header {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
    }
  }

  &__content {
    display: flex;
    flex-direction: column;
    margin-top: 16px;
    margin-left: 44px;
    &-link {
      font-weight: 500;
      font-size: 18px;
      line-height: 24px;
      letter-spacing: 0.02em;
      color: #131313 !important;
      text-decoration: none;
      margin-bottom: 8px;
      &:hover {
        color: #2574CF !important;
      }
    }
    &-text {
      font-weight: 300;
      font-size: 14px;
      line-height: 21px;
      letter-spacing: 0.02em;
      color: #000000;
    }
    & img {
      max-width: 100%;
      height: auto;
    }
  }

  &__actions {
    display: flex;
    align-items: center;
    margin-left: 36px;
    &-link {
      margin-left: 16px;
      & svg * {
          fill: #485B70;
        }
      &:hover {
        & svg * {
          fill: #2574CF;
        }
      }
    }
  }

  &__comments {

    margin-top: 12px;

    &-wrapper {
      width: 100px;
      position: absolute;
      display: flex;
      flex-direction: column;
      align-items: center;
      margin: 0;
      top: 0;
      right: -150px;
    }

    &-title {
      color: #979797;
      font-size: 12px;
      line-height: 18px;
    }

    &-counter {
      position: absolute;
      top: 42px;
      width: 42px;
      margin: auto;
      text-align: center;

      color: #2960A0;
      font-weight: 500;
    }
  }

  &__user-avatar {
    width: 34px !important;
    height: 34px !important;
    margin: 0;
    margin-right: 10px;
  }

  &__user-name {
    font-weight: 500;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #083A81;
  }

  &__subsection-time {
    font-weight: 300;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.02em;
    color: #666666;
  }
}
@media(max-width: 1279px) {
  .subsection-info {
    &__main-header-wrapper {
      flex-direction: column;
      align-items: flex-start;
    }
    &__subsection-time-last-msg-wrapper {
      margin-left: 45px;
    }
  }
}
@media(max-width: 959px) {
  .subsection-info {
    padding: 16px;
    &__main-header {
      flex-direction: column;
      align-items: flex-start;
    }
    &__main-header-wrapper {
      width: 100%;
    }
    &__main-header-user-wrapper {
      width: 100%;
    }
    &__subsection-time {
      margin-left: auto;
      font-size: 12px;
      white-space: nowrap;
    }
    &__subsection-time-last-msg-wrapper {
      width: calc(100% - 45px);
    }
    &__subsection-time-last-msg {
      margin-left: auto;
      font-size: 12px;
      white-space: nowrap;
    }
    &__actions {
      margin-top: 14px;
      margin-left: 28px;
      width: calc(100% - 28px);
    }
    &__actions-icon {
      width: 20px;
      height: 20px;
    }
    &__main {
      width: 100%;
    }
    &__comments-wrapper {
      position: relative;
      top: 0;
      right: 0;
      width: 42px;
      margin-left: auto;
    }
    &__comments-title {
      display: none;
    }
    &__comments {
      margin-top: 0;
    }
    &__comments-counter {
      top: 7px;
    }
  }
}
</style>
