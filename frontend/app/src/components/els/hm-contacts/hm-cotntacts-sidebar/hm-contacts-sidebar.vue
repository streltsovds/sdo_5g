<template>
  <div id="hmContactsSidebar">
    <div class="subject-sidebar__contacts">
      <div class="subject-sidebar__contacts-image"
           :style="{
             background: backgroundImage ? '' : 'rgba(31,142,250,0.50)',
             backgroundImage: backgroundImage ? `url(${urlIconCourse})` : ''
             }">
        <div
          v-if="!backgroundImage"
          class="subject-sidebar__contacts-image__icon">
          <svg width="76" height="71" viewBox="0 0 76 71" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M57.3224 28.8191V48.3988C61.0581 45.3073 62.4749 41.9581 62.4749 41.9581V26.8869L62.3461 26.7581L75.3563 21.4767V16.3242C75.3563 16.3242 64.1496 20.9615 57.3224 23.7953V28.8191ZM38.0004 54.8393C18.6784 54.8393 13.5259 41.9581 13.5259 41.9581V27.0156L13.6546 26.758L38.0004 36.8054L57.3224 28.8189V48.3986C54.4886 50.7173 52.1699 51.8766 52.1699 51.8766C48.692 53.5513 44.0547 54.8393 38.0004 54.8393Z" fill="#AAB1BA"/>
            <path d="M0.644043 16.5943L38 1.13672L75.356 16.5943C75.356 16.5943 64.1492 21.2316 57.322 24.0655V29.0892L38 37.0757L13.6543 27.0282L0.644043 21.747V16.5943ZM41.0914 18.1402C41.6066 17.7538 41.8643 17.2386 41.8643 16.5945C41.8643 15.1775 40.1897 14.0182 37.9999 14.0182C35.81 14.0182 34.1354 15.1775 34.1354 16.5945C34.1354 18.0115 35.81 19.1708 37.9999 19.1708C39.2881 19.1707 40.4474 18.7842 41.0914 18.1402Z" fill="#AAB1BA"/>
            <path d="M41.865 16.1935C41.865 16.7087 41.6074 17.2241 41.0922 17.7393C40.448 18.3834 39.2887 18.7698 38.0006 18.7698C35.8108 18.7698 34.1362 17.6105 34.1362 16.1935C34.1362 14.7765 35.8108 13.6172 38.0006 13.6172C40.1905 13.6173 41.865 14.7767 41.865 16.1935Z" fill="#F2EDDA"/>
            <path d="M57.3229 57.4141C58.7399 57.4141 59.8992 58.5734 59.8992 59.9904C59.8992 61.4074 58.7399 62.5667 57.3229 62.5667C55.9059 62.5667 54.7466 61.4074 54.7466 59.9904C54.7466 58.5734 55.9061 57.4141 57.3229 57.4141Z" fill="#FF7474"/>
            <path d="M62.4746 70.9441C62.2169 70.9441 61.9594 70.6864 61.8305 70.4289L60.5424 65.2764V65.1476C60.5424 64.89 60.8001 64.6324 61.0576 64.5035C61.444 64.3747 61.7017 64.6323 61.8305 65.0187L63.1186 70.1712V70.3C63.1186 70.5576 62.8609 70.8152 62.6034 70.9441H62.4746ZM52.1695 70.9441H52.0407C51.783 70.8153 51.5255 70.5576 51.5255 70.3V70.1712L52.8136 65.0187C52.9423 64.6323 53.2 64.5035 53.5865 64.5035C53.8441 64.6323 54.1017 64.89 54.1017 65.1476V65.2764L52.8136 70.4289C52.6847 70.6864 52.4271 70.9441 52.1695 70.9441ZM57.3219 70.9441C56.9355 70.9441 56.6778 70.6864 56.6778 70.3V66.4356C56.6778 66.0491 56.9355 65.7914 57.3219 65.7914C57.7084 65.7914 57.9661 66.0491 57.9661 66.4356V70.3C57.9661 70.6864 57.7084 70.9441 57.3219 70.9441ZM57.3219 63.2153C55.5185 63.2153 54.1017 61.7983 54.1017 59.995C54.1017 58.4492 55.261 57.1611 56.678 56.7747V48.4018V28.8221V24.7L51.783 26.761L38.2576 32.2999C38.1288 32.4287 37.8712 32.4287 37.7424 32.2999L0.38644 16.8424C0.128763 16.7135 0 16.456 0 16.1983C0 15.9406 0.128763 15.6831 0.38644 15.5542L37.7424 0.0965724C37.8712 -0.0321908 38.1288 -0.0321908 38.2576 0.0965724L75.6136 15.5542C75.8712 15.683 76 15.9406 76 16.1983C76 16.456 75.8712 16.7135 75.6136 16.8424L57.9661 24.1848V27.9204L74.7118 20.9645V20.0629C74.7118 19.6764 74.9694 19.4187 75.3559 19.4187C75.7423 19.4187 76 19.6764 76 20.0629V21.3509C76 21.6086 75.8712 21.8661 75.6136 21.9951L57.9661 29.2085V47.1135C60.5424 44.9237 61.5728 42.3475 61.8305 41.8321V32.944C61.8305 32.5576 62.0881 32.2999 62.4746 32.2999C62.861 32.2999 63.1187 32.5576 63.1187 32.944V41.9609C63.1187 42.0897 63.1187 42.0897 63.1187 42.2186C63.1187 42.3473 61.7017 46.083 57.9662 48.7881V56.9033C59.3832 57.161 60.5425 58.4491 60.5425 60.1236C60.5424 61.7983 59.1254 63.2153 57.3219 63.2153ZM57.3219 58.0628C56.2914 58.0628 55.3897 58.9644 55.3897 59.995C55.3897 61.0256 56.2914 61.9272 57.3219 61.9272C58.3525 61.9272 59.2541 61.0256 59.2541 59.995C59.2541 58.9644 58.3525 58.0628 57.3219 58.0628ZM2.31864 16.1983L37.9999 31.0118L55.5187 23.7984L41.3491 18.5169C40.5762 19.1611 39.4169 19.5475 38.1288 19.5475C35.5525 19.5475 33.6203 18.1305 33.6203 16.3272C33.6203 14.5239 35.5525 13.1069 38.1288 13.1069C40.7052 13.1069 42.6374 14.5239 42.6374 16.3272C42.6374 16.7137 42.5086 17.1001 42.3797 17.4865L57.4509 23.1544L73.939 16.3272L38.2576 1.51357L2.31864 16.1983ZM37.9999 14.2661C36.1965 14.2661 34.7796 15.1677 34.7796 16.1983C34.7796 17.2289 36.1966 18.1305 37.9999 18.1305C39.0305 18.1305 40.0609 17.7441 40.5762 17.2289L40.705 17.1001C40.9627 16.8424 41.0914 16.456 41.0914 16.0695C41.2204 15.1679 39.8034 14.2661 37.9999 14.2661ZM37.9999 55.4865C18.4202 55.4865 13.0101 42.3475 12.8813 42.2187C12.8813 42.09 12.8813 42.09 12.8813 41.9611V32.9442C12.8813 32.5577 13.139 32.3 13.5254 32.3C13.9119 32.3 14.1695 32.5577 14.1695 32.9442V41.8323C14.6847 42.9916 20.2238 53.9407 37.356 54.1984V41.9611C37.356 41.5746 37.6136 41.3169 38.0001 41.3169C38.3865 41.3169 38.6442 41.5746 38.6442 41.9611V54.1984C44.956 54.0696 49.3358 52.5239 51.9119 51.2356C52.1696 51.1069 52.556 51.2356 52.8136 51.4933C53.0713 51.751 52.8136 52.1374 52.5559 52.3949C49.5931 53.8119 44.8271 55.4865 37.9999 55.4865ZM37.9999 37.4525C37.8712 37.4525 37.8712 37.4525 37.7422 37.4525L0.38644 21.9949C0.128763 21.8662 0 21.6086 0 21.3509V20.0629C0 19.6764 0.257677 19.4187 0.644118 19.4187C1.03056 19.4187 1.28824 19.6764 1.28824 20.0629V20.9645L38.0001 36.1644L51.9119 30.3678C52.2984 30.2391 52.556 30.3678 52.8136 30.7543C52.9423 31.1407 52.8136 31.3984 52.4271 31.6559L38.2576 37.5813C38.1288 37.4525 38.1288 37.4525 37.9999 37.4525Z" fill="#51565F"/>
          </svg>
        </div>
        <div class="subject-sidebar__contacts-image__title">
          <span style="color: #FFFFFF; letter-spacing: 0.02em; font-size: 20px">{{ subjectTitle | truncate(100)}} </span>
        </div>
      </div>
      <div class="subject-sidebar__contacts-body">
        <div class="subject-sidebar__contacts-body__search-title">
          <svg-icon  width="20" height="20" color="#1f8efa" name="search" />
          <span>Поиск</span>
        </div>
        <hm-search
          placeholder="ФИО или должность"
          :subject-id="subjectId"
        />
        <div class="subject-sidebar__contacts-body__message-title">
          <svg-icon stroke-width="2"  width="20" height="20" color="#1f8efa" name="say-bubble"/>
          <span>Отправить сообщение</span>
        </div>
        <div class="subject-sidebar__contacts-body__users">
          <hm-contacts-card-mini
            v-for="(item, i) in arrUsers"
            :key="i"
            :data-card="item"
          />
        </div>
        <div
          v-if="arrUsers.length > 0"
          class="subject-sidebar__contacts-body__clear">
          <span  @click="clearStorage">Очистить список </span>
        </div>
        <div class="subject-sidebar__contacts-body__message" :class="{'disabled-message' : arrUsers.length > 0 ? false : true}">
          <div class="subject-sidebar__contacts-body__message-form">
            <input type="text" v-model="textMessage" placeholder="Текст сообщения" @keydown.enter="sendingMessage">
            <div class="search-icon" @click="sendingMessage">
              <svg-icon width="24" height="24" color="#555" stroke="#555" strokeWidth="0.1" name="telegram" title="Отправить"/>
            </div>
          </div>
        </div>
<!--        <hm-contacts-new-canal />-->
      </div>
    </div>
  </div>
</template>

<script>
  import Axios from 'axios'
  import { mapActions } from "vuex";
  import HmSearch from "@/components/els/hm-search/hm-search";
  import HmContactsCardMini from "@/components/els/hm-contacts/hm-contacts-card/hmContactsCardMini";
  import SvgIcon from "@/components/icons/svgIcon";
  import HmContactsNewCanal from "@/components/els/hm-contacts/hm-contacts-new-canal/hmContactsNewCanal";
export default {
  name: "hm-contacts-sidebar",
    components: {HmContactsNewCanal, SvgIcon, HmContactsCardMini, HmSearch},
    props: {
      urlIconCourse: {type: String, default: ''},
      subjectId: {type: [Number, String], default: ''},
      subjectTitle: {type:String, default: ''}

  },
  data() {
    return {
      numberPage: 40,
      page: 1,
      textMessage:'' //текст сообщения
    }
  },
  computed: {
      /*
      * метод возврата добавленных пользователей
      * */
    arrUsers() {
        return this.$store.getters['dataContacts/selectedUser']
      },
    activeSearch() {
      return this.$store.getters['dataContacts/searchString']
    },
    // свойство определяющее, пришла ли фотка
    backgroundImage() {
      return this.urlIconCourse !== null && this.urlIconCourse !== undefined && this.urlIconCourse !== '' ? true : false
    }
  },
  methods: {
    ...mapActions("notifications", [
      "addSuccessNotification",
      "addErrorNotification"
    ]),
    //загрузка данных по странице и обновление всех полей
    async initComp() {
      const res = await  Axios.get(`/subject/contacts/search/?subject_id=${this.subjectId}`);
      if(res) {
        //записывание данных в хранилище
        this.$store.commit('dataContacts/SET__AllData',res.data )
        // обновляю активную страницу ( ставлю на 1 )
        this.$store.commit('dataContacts/SET__activePageNullified')
         // обновляю строку поиска
        this.$store.commit('dataContacts/SET__searchNullified')
        // обновляю массив выбранных пользователей
        this.$store.commit('dataContacts/SET__ClearSelectedUser')
      }
    },
    //Метод очистки хрнилища по добавленным пользователям
    clearStorage() {
        this.$store.commit('dataContacts/SET__ClearSelectedUser' )
    },
    /**
    * метод отправки сообщения выбранным пользователям
    */
    sendingMessage() {
      const arrIdUsers = this.$store.getters['dataContacts/selectedUser'].map(el => el.id).join(',')
      if(this.textMessage === '') this.addErrorNotification('Нельзя отправить пустое сообщение');
      else {
          if(arrIdUsers.length > 0) {
          const res = Axios.post(` /message/send/simple/subject_id/${this.subjectId}/user_ids/${arrIdUsers}`, {
            message: this.textMessage
          });
          if(res) {
              this.textMessage = '';
              this.addSuccessNotification("Сообщение отправлено");
          }
        } else {
            this.addErrorNotification('Не отмечены пользователи');
        }
      }
    }
  },
  mounted() {
    this.initComp()

  }
}
</script>

<style lang="scss">
$image-height: 164px;

#hmContactsSidebar {
  width: 100%;
  height: 100%;
  .subject-sidebar__contacts {
    width: 100%;
    height: 100%;
    &-image {
      width: 100%;
      height: 164px;
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      position: relative;
      display: flex;
      justify-content: center;
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
      max-height: calc(100% - #{$image-height});
      &__clear {
        width: 100%;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        margin-top: 10px;
        &-button {
          width: 26px;
          height: 26px;
          display: flex;
          justify-content: center;
          align-items: center;
          border-radius: 50%;
          transition: .1s linear;
          &:hover {
            background:  #E6E6E6;
            > svg {
              fill :  #666666;
            }
          }
          > svg {
            fill: #C4C4C4;
          }
          &:active {
            > svg {
              fill : #1E1E1E;
            }
            cursor: pointer;
          }
        }
        > span {
          font-weight: normal;
          font-size: 12px;
          line-height: 18px;
          letter-spacing: 0.15px;
          color: #2574CF;
          cursor: pointer;
        }
      }
      &__search-title {
        width: 100%;
        height: 24px;
        margin-bottom: 16px;
        display: flex;
        align-content: center;
        > svg {
          margin-right: 12px;
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
      &__users {
        width: 100%;
        margin-top: 2px;
        margin-bottom: 16px;
        display: flex;
        flex-direction: column;
      }
      &__message {
        width: 100%;
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

      .disabled-message {
        position: relative;
        &:after {
          content: '';
          position: absolute;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          z-index: 10;
          background: rgb(255, 255, 255,0.4);
        }
      }

    }
  }
}
</style>
