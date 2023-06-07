<template>
  <div class="hm-sidebar-user">
    <div class="hm-sidebar-user__header">
      <div class="hm-sidebar-user__header-img" :style="{backgroundImage: dataSidebar.userImage && dataSidebar.userImage !== '' ? `url(${dataSidebar.userImage})` : ''}" style="background-size: cover;">
        <div class="hm-sidebar-user__header-img__noPhoto-my" v-if="dataSidebar.myRecord && (!dataSidebar.userImage || dataSidebar.userImage === '')">
          <input @change="previewFiles" type="file">
          <svg width="64"
               height="56"
               viewBox="0 0 64 56"
               fill="none"
               xmlns="http://www.w3.org/2000/svg"
          >
            <path fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M16.4629 29.9433C16.4629 21.3759 23.4332 14.4062 32 14.4062C40.5667 14.4062 47.537 21.3759 47.537 29.9433C47.537 38.5101 40.5667 45.4811 32 45.4811C23.4332 45.4811 16.4629 38.5101 16.4629 29.9433ZM18.7272 29.9419C18.7272 37.2613 24.6813 43.2148 32.0001 43.2148C39.3188 43.2148 45.2729 37.2613 45.2729 29.9419C45.2729 22.6224 39.3188 16.6683 32.0001 16.6683C24.6813 16.6683 18.7272 22.6224 18.7272 29.9419Z"
                  fill="white"
            />
            <path fill-rule="evenodd"
                  clip-rule="evenodd"
                  d="M51.0734 9.60355L54.6368 9.60284C59.7995 9.60284 64 13.8034 64 18.966V45.7195C64 50.8814 59.7995 55.0827 54.6368 55.0834H9.3632C4.19982 55.0834 0 50.8821 0 45.7195V18.966C0 13.8034 4.19982 9.60284 9.3632 9.60284L12.9273 9.60355C14.2564 9.60355 15.8172 8.5056 16.2681 7.25404L17.4926 3.84782C18.2677 1.6896 20.6706 0 22.9646 0H41.0354C43.328 0 45.7301 1.6896 46.5074 3.84711L47.7319 7.25404C48.1828 8.5056 49.7436 9.60355 51.0734 9.60355ZM54.637 52.817C58.551 52.817 61.7361 49.632 61.7361 45.7173V18.9646C61.7361 15.0506 58.551 11.8656 54.637 11.8656L51.0744 11.8663C48.7818 11.8663 46.3782 10.176 45.6024 8.01917L44.3778 4.61224C43.927 3.35997 42.3654 2.26202 41.0356 2.26202H22.9649C21.6344 2.26202 20.0728 3.35997 19.6226 4.61224L18.3981 8.01846C17.6223 10.176 15.2194 11.8656 12.9268 11.8656L9.36344 11.8649C5.44949 11.8649 2.26442 15.0492 2.26442 18.9639V45.7173C2.26442 49.632 5.44949 52.817 9.36344 52.817H54.637Z"
                  fill="white"
            />
          </svg>
        </div>
        <svg v-else-if="!dataSidebar.myRecord && !dataSidebar.userImage || dataSidebar.userImage === ''"
             width="160"
             height="160"
             viewBox="0 0 160 160"
             fill="none"
             xmlns="http://www.w3.org/2000/svg"
        >
          <circle cx="80"
                  cy="80"
                  r="80"
                  fill="#4A90E2"
          />
          <path fill-rule="evenodd"
                clip-rule="evenodd"
                d="M79.9981 5C38.6009 5 5 38.6009 5 79.9981C5 121.399 38.6009 155 79.9981 155C121.399 155 155 121.399 155 79.9981C155 38.6009 121.399 5 79.9981 5ZM80.0023 27.498C92.4538 27.498 102.504 37.5486 102.504 50C102.504 62.4478 92.4538 72.4984 80.0023 72.4984C67.5546 72.4984 57.504 62.4478 57.504 50C57.504 37.5486 67.5546 27.498 80.0023 27.498ZM79.9984 134.001C61.2498 134.001 44.6729 124.398 34.998 109.85C35.2234 94.9224 64.9995 86.7504 79.9984 86.7504C94.9258 86.7504 124.773 94.9224 124.999 109.85C115.324 124.398 98.7507 134.001 79.9984 134.001Z"
                fill="white"
          />
        </svg>
      </div>
      <div class="hm-sidebar-user__header-edit" v-if="dataSidebar.showEditUrl && dataSidebar.editCardUrl && dataSidebar.editCardUrl !== ''">
        <hm-sidebar-button-edit :link="dataSidebar.editCardUrl" />
      </div>
    </div>
    <div class="hm-sidebar-user__body">
      <div class="hm-sidebar-user__body-fio">
        <span>{{ dataSidebar.userName }}</span>
      </div>

      <div class="hm-sidebar-user__body-loginas"
           v-if="dataSidebar.showLoginAs"
      >
        <a :href="dataSidebar.loginAsUrl">
          <svg-icon name="enter" style="width: 16px; height: 16px" color="#FFFFFF" />
          <span>Войти от имени</span>
        </a>
      </div>
      <div class="hm-sidebar-user__body-resp"
           v-if="dataSidebar.showEditResponsibility"
      >
        <a :href="dataSidebar.editResponsibilityUrl">
          <span>Настроить области ответственности</span>
        </a>
      </div>
      <div class="subject-sidebar__contacts-body__message-title">
        <svg-icon stroke-width="2"  width="20" height="20" color="#1f8efa" name="say-bubble"/>
        <span>Отправить сообщение</span>
      </div>
      <div class="subject-sidebar__contacts-body__message">
        <div class="subject-sidebar__contacts-body__message-form">
          <input type="text" v-model="textMessage" placeholder="Текст сообщения" @keydown.enter="sendingMessage">
          <div class="search-icon" @click="sendingMessage">
            <svg-icon width="24" height="24" color="#555" stroke="#555" strokeWidth="0.1" name="telegram" title="Отправить"/>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
import axios from 'axios'
import { mapActions } from "vuex";

export default {
  components: {HmSidebarButtonEdit, SvgIcon},
  props: {
    dataSidebar: {
      type: Object,
      default: () => {}
    },
  },
  data() {
    return {
      files: [],
      textMessage:'' //текст сообщения
    }
  },
  methods: {
    ...mapActions("notifications", [
      "addSuccessNotification",
      "addErrorNotification"
    ]),
    previewFiles(event) {
      if(event.target.files.length) {
        let formData = new FormData();
        formData.append('photo', event.target.files[0]);

        this.$axios
          .post(this.dataSidebar.uploadPhotoUrl, formData)
          .then(r => {
            if (r.status === 200 && r.uploaded) {
              console.log('ok');
            }
          })
          .catch(e => {
            console.error(e);
          });
      }
    },
    sendingMessage() {
      const userId = this.dataSidebar.userId;
      if(this.textMessage === '') this.addErrorNotification('Нельзя отправить пустое сообщение');
      else {
        const res = axios.post(` /message/send/simple/user_ids/${userId}`,{
          message: this.textMessage
        });
        if(res) {
          this.textMessage = '';
          this.addSuccessNotification("Сообщение отправлено");
        }
      }
    }
  },
}
</script>

<style lang="scss">
.hm-sidebar-user {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  overflow: auto;
  background: inherit;
    .subject-sidebar__contacts {
    width: 100%;
    height: 100%;
    &-image {
      width: 100%;
      display: flex;
      justify-content: center;
      position: relative;
      &__icon {
        margin-top: 16px;
      }
      &__title {
        width: 100%;
        height: 63px;
        display: flex;
        justify-content: center;
        align-items: center;
        background:  rgba(30,30,30,0.6);
        position: absolute;
        bottom: 0;
        > span {
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          padding: 0 42px;
        }
      }
    }
    &-body {
      padding: 26px 16px;
      overflow: auto;
      &__message-title {
        margin-top: 40px;
        margin-bottom: 0px;
        width: 100%;
        display: flex;
        align-items: center;

        > svg {
          margin-right: 10.5px;
        }
        > span {
          font-style: normal;
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
      }
      &__message {
        width: 100%;
        margin-top: 20px;
        &-form {
          width: 100%;
          height: 43px;
          background: #FFFFFF;
          box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
          border-radius: 4px;
          position: relative;
          > input {
            width: 100%;
            height: 100%;
            padding: 12px 50px 12px 16px;
            background: #FFFFFF;
            font-size: 14px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
            border-radius: 4px;
            color: rgba(0,0,0,.87);
            outline: none;
            &::placeholder {
              color: #70889E;
              font-size: 14px;
            }
            &:focus {
              outline: 1px solid #2c98f0;
            }
          }
          .search-icon {
            position: absolute;
            top: 9.5px;
            right: 17px;
            cursor: pointer;
          }
        }
      }
    }
  }
  &__header {
    width: 100%;
    height: 210px;
    display: flex;
    justify-content: center;
    position: relative;
    background: inherit;
    &:before {
      content: "";
      width: 100%;
      height: 130px;
      position: absolute;
      top: 0;
      left: 0;
      background: #D4E3FB;
      z-index: 100;
    }
    &:after {
      content: '';
      width: 100%;
      height: 80px;
      position: absolute;
      bottom: 0;
      left: 0;
      background: inherit;
      z-index: 100;
    }
    &-img {
      width: 160px;
      height: 160px;
      border-radius: 50%;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      margin-top: 50px;
      &__noPhoto-my {
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #4A90E2;
        position: relative;
        > input {
          cursor: pointer;
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          border-radius: 50%;
          opacity: 0;
          z-index: 100;
        }
      }
      > img {
        width: 100%;
        height: 100%;
      }
    }
    &-edit {
      position: absolute;
      right: 16px;
      bottom: 0;
      z-index: 5000;
    }
  }
  &__body {
    width: 100%;
    display: flex;
    flex-direction: column;
    padding: 26px 16px;
    box-sizing: border-box;
    &-fio {
      width: 100%;
      > span {
        font-weight: 500;
        font-size: 18px;
        line-height: 24px;
        letter-spacing: 0.02em;
        color: #1E1E1E;
      }
    }
    &-resp{
      border: 1px solid #4A90E2;
      border-radius: 4px;

      text-align: center;
      margin-top: 25px;
      &:hover{
          background-color: #4A90E2;
          span{
            color: #fff;
          }
        }
      > a {
        text-decoration: none;
        padding: 5px;
        display: block;
        span{
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #1E1E1E;
        }
      }
    }
    &-loginas {
      width: 100%;
      height: 36px;
      margin-top: 36px;
      > a {
        width: 100%;
        height: 100%;
        text-decoration: none;
        background: #FF9800;
        border-radius: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        > svg {
          margin-right: 8px;
          margin-top: 4px;
        }
        > span {
          font-weight: 300;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color: #FFFFFF;
        }
      }
    }
  }
}
</style>
