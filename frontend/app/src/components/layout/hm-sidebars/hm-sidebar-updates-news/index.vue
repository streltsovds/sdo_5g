<template>
  <div id="sidebarupdates">
    <div class="sidebar-updates">
      <div class="sidebar-updates__header">
        <div class="sidebar-updates__header__img"
          v-if="!dataSidebar.subject.image">
          <img :src="dataSidebar.subject.icon">
          <div class="sidebar-updates__header__img-info">
            <span>{{ dataSidebar.subject.name | truncate(100)}}</span>
          </div>
        </div>
        <div class="sidebar-updates__header__image"
          v-else
          :style="{ backgroundImage: `url(${dataSidebar.subject.image})` }"
        >
          <div class="sidebar-updates__header__image-info">
            <span>{{ dataSidebar.subject.name | truncate(100)}}</span>
          </div>
        </div>
        <div class="sidebar-updates__header__image-edit" v-if="dataSidebar.showEdit">
          <hm-sidebar-button-edit :link="dataSidebar.editUrl"/>
        </div>
      </div>
      <div class="sidebar-updates__body">
        <div class="sidebar-updates__body-updates">
          <div class="sidebar-updates__body-updates-title">
            <svg
              width="16"
              height="20"
              viewBox="0 0 16 20"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M2.49756 20H13.2153C14.5924 20 15.7127 18.8796 15.7127 17.5027V11.0718V5.60578C15.7127 5.27778 15.538 4.85578 15.306 4.62378L11.0893 0.406889C10.8576 0.174889 10.4351 0 10.1073 0H4.64089H2.49733C1.12022 0 0 1.12044 0 2.49733V17.5024C0 18.8796 1.12044 20 2.49756 20ZM10.8897 1.20768L10.89 1.2079V4.10524C10.89 4.50102 11.2117 4.82279 11.6077 4.82279H14.5053L10.89 1.2079V1.20768H10.8897ZM2.4976 0.70638C1.51049 0.70638 0.707601 1.50949 0.707601 2.49638H0.707823V17.5015C0.707823 18.4886 1.51093 19.2915 2.49782 19.2915H13.2156C14.2029 19.2915 15.0056 18.4884 15.0056 17.5015V11.0708V5.6046C15.0056 5.58171 15.0023 5.55638 14.9976 5.52971H11.608C10.8218 5.52971 10.1825 4.89016 10.1825 4.10416V0.71438C10.1558 0.709936 10.1303 0.70638 10.1076 0.70638H4.64116H2.4976Z"
                fill="#1F8EFA"
              />
              <path
                d="M3.92309 14.6394H11.7898C11.9853 14.6394 12.1438 14.4812 12.1438 14.2856C12.1438 14.0899 11.9853 13.9316 11.7898 13.9316H3.92309C3.72754 13.9316 3.56909 14.0899 3.56909 14.2856C3.56909 14.4812 3.72754 14.6394 3.92309 14.6394Z"
                fill="#1F8EFA"
              />
              <path
                d="M3.92309 10.8894H11.7898C11.9853 10.8894 12.1438 10.731 12.1438 10.5354C12.1438 10.3403 11.9853 10.1816 11.7898 10.1816H3.92309C3.72754 10.1816 3.56909 10.3403 3.56909 10.5354C3.56909 10.731 3.72754 10.8894 3.92309 10.8894Z"
                fill="#1F8EFA"
              />
              <path
                d="M3.92309 7.13724H8.57465C8.7702 7.13724 8.92843 6.97902 8.92843 6.78347C8.92843 6.58835 8.7702 6.42969 8.57465 6.42969H3.92309C3.72754 6.42969 3.56909 6.58835 3.56909 6.78347C3.56909 6.97902 3.72754 7.13724 3.92309 7.13724Z"
                fill="#1F8EFA"
              />
            </svg>
            <span>Обновления</span>
          </div>

          <div  v-if="dataSidebar.recentActions.news && Object.keys(dataSidebar.recentActions.news).length > 0 ||
                 dataSidebar.resources && Object.keys(dataSidebar.resources).length > 0 ||
                 dataSidebar.recentActions.assignedUsers && Object.keys(dataSidebar.recentActions.assignedUsers).length > 0">

            <div class="sidebar-updates__body-updates-news" v-if="dataSidebar.recentActions.news && Object.keys(dataSidebar.recentActions.news).length > 0">
              <div v-for="(item, i) in dataSidebar.recentActions.news" :key="i">
                <div class="news-icon">
                  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.1629 3.11531C12.0042 1.58365 13.9082 0 16.2702 0C18.3616 0 20 3.98456 20 9.07151C20 14.1582 18.3616 18.1428 16.2698 18.143C13.9089 18.143 12.0056 16.5605 10.1649 15.03L10.1622 15.0277C9.41133 14.403 8.67911 13.795 7.942 13.3147V16.8191C7.942 18.083 6.918 19.1111 5.65911 19.1111H4.69444C3.43556 19.1111 2.41133 18.083 2.41133 16.8191V12.3316H2.28289C1.024 12.3316 0 11.3038 0 10.0398V8.10276C0 6.83861 1.024 5.81073 2.28289 5.81073H5.17711C6.922 5.81073 8.49622 4.50151 10.1629 3.11531ZM5.65954 18.4C6.52821 18.4 7.2351 17.6906 7.2351 16.8184V12.8979C6.56554 12.5492 5.88554 12.3307 5.17732 12.3307H3.1191V16.8184C3.1191 17.6906 3.82599 18.4 4.69488 18.4H5.65954ZM10.6139 14.4799C8.92413 13.0741 7.1768 11.6207 5.17702 11.6207H2.2828C1.41413 11.6207 0.707245 10.9112 0.707245 10.0391V8.10204C0.707245 7.22989 1.41413 6.52017 2.2828 6.52017H5.1768C7.17546 6.52017 8.92174 5.06822 10.6109 3.66379L10.6137 3.66144C11.8488 2.63423 13.1139 1.58493 14.495 1.05794C13.3219 2.56975 12.5399 5.55678 12.5399 9.07079C12.5399 12.5841 13.3217 15.5714 14.4946 17.0827C13.1139 16.5562 11.8488 15.5069 10.6139 14.4799ZM13.2474 9.07121C13.2474 14.0727 14.8103 17.4323 16.27 17.4323C17.7296 17.4323 19.2923 14.0727 19.2925 9.07121C19.2925 4.06948 17.7296 0.709635 16.27 0.709635C14.81 0.709635 13.2474 4.06948 13.2474 9.07121Z" fill="#1F8EFA"/>
                  </svg>
                </div>
                <div class="news-info">
                    <span>Внимание всем слушателям курса</span>
                    <a :href="item.viewUrl ">{{ item.announce  }}</a>
                </div>
              </div>
            </div>
            <div class="sidebar-updates__body-updates-files" v-if="dataSidebar.resources && Object.keys(dataSidebar.resources).length > 0">
              <div
                v-for="(item, i) in dataSidebar.resources"
                :key="i"
                class="sidebar-updates__body-updates-files-file"
              >
                <a class="files-file__icon" :href="item.quickViewUrl" download="">
                  <file-icon small :type="getFileIconType(item)"/>
                </a>
                <a class="files-file__info"
                    v-if="item.viewUrl" :href="item.viewUrl">
                  <span>{{ item.title }}</span>
                  <span>{{ item.updated }}</span>
                </a>
                <div class="files-file__info" v-else>
                  <span >{{ item.title }}</span>
                  <span>{{ item.updated }}</span>
                </div>
              </div>
            </div>
            <div class="sidebar-updates__body-updates-users" v-if="dataSidebar.recentActions.assignedUsers && Object.keys(dataSidebar.recentActions.assignedUsers).length > 0">
              <hm-contacts-card-mini
                v-for="(item, i) in dataCard"
                :key="i"
                :data-card="item"
                :icon-remove="false"
              />
            </div>
          </div>

          <div v-else class="sidebar-updates__info-empty">
            <hm-empty empty-type="short-slider"></hm-empty>
          </div>


        </div>
      </div>
    </div>
  </div>
</template>

<script>
import HmContactsCardMini from "@/components/els/hm-contacts/hm-contacts-card/hmContactsCardMini";
import FileIcon from "@/components/icons/file-icon/index";
import SvgIcon from "@/components/icons/svgIcon";
import EditActionButton from "@/components/els/subject/lessons/editActionButton";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
import HmEmpty from "@/components/helpers/hm-empty/index";
import getFileIconType from "@/utilities/getFileIconType";
export default {
  components: { HmSidebarButtonEdit, EditActionButton, SvgIcon, FileIcon, HmContactsCardMini, HmEmpty },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  computed: {
    dataCard() {
      let dataUsers = [];
      for (let item of this.dataSidebar.recentActions.assignedUsers ) {
        dataUsers.push({
          name: `${item.lastName} ${item.firstName}`,
          photo: item.image,
          description: "Назначен на курс",
        });
      }
      return dataUsers;
    },
  },
  methods: {
    getFileIconType,
    fileType(test) {
      switch (test) {
        case 0:
          return "default";
        case 1:
          return "text";
        case 2:
          return "web";
        case 3:
          return "image";
        case 4:
          return "audio";
        case 5:
          return "video";
        case 6:
          return "flash";
        case 7:
          return "document";
        case 8:
          return "table";
        case 9:
          return "table";
        case 10:
          return "presentation";
        case 81:
          return "pdf";
        case 99:
          return "archive";
      }
    },
  },
};
</script>

<style lang="scss">
#sidebarupdates {
  width: 100%;
  height: 100%;
  .sidebar-updates {
    width: 100%;
    height: 100%;
    &__header {
      width: 100%;
      height: 164px;
      position: relative;
      &__img {
        width: 100%;
        height: 164px;
        background: #a8c7ec;
        position: relative;
        display: flex;
        justify-content: center;
        > img {
          margin-top: 20px;
          width: 100px;
        }

        &-edit {
          position: absolute;
          bottom: -24px;
          right: 52px;
          z-index: 100;
        }

        &-info {
          width: 100%;
          height: 70px;
          position: absolute;
          bottom: 0;
          left: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(30, 30, 30, 0.5);
          > span {
            margin-top: 4px;
            font-size: 16px;
            line-height: 20px;
            letter-spacing: 0.02em;
            color: #ffffff;
            text-align: center;
          }
        }
      }
      &__image {
        width: 100%;
        height: 164px;
        background-position: center;
        background-size: cover;
        background-repeat: no-repeat;
        position: relative;
        display: flex;
        justify-content: center;

        &-edit {
          position: absolute;
          bottom: -24px;
          right: 52px;
          z-index: 100;
        }

        &-info {
          width: 100%;
          height: 76px;
          position: absolute;
          bottom: 0;
          left: 0;
          display: flex;
          align-items: center;
          justify-content: center;
          background: rgba(30, 30, 30, 0.5);
          > span {
            margin-top: 4px;
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #ffffff;
            text-align: center;
          }
        }
      }
    }

    &-empty {
      margin: 20px 0;
    }
    &__body {
      width: 100%;
      padding: 16px 12px 16px 16px;
      overflow: scroll;
      max-height: 70%;
      &-updates {
        width: 100%;
        display: flex;
        flex-direction: column;
        &-title {
          width: 100%;
          height: 24px;
          display: flex;
          align-items: center;
          justify-content: flex-start;
          margin-bottom: 16px;
          > svg {
            margin-right: 13px;
          }
          > span {
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1e1e1e;
          }
        }

        &-users {
          margin-top: 16px;
        }

        &-files {
          width: 100%;
          display: flex;
          flex-direction: column;
          > div {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 16px;
            .files-file__icon {
              margin-right: 12px;
              text-decoration: none;
              display: flex;
              align-items: center;
              justify-content: flex-start;
            }
            .files-file__info {
              text-decoration: none;
              display: flex;
              flex-direction: column;
              max-width: 250px;
              > span:nth-child(1) {
                font-weight: normal;
                font-size: 14px;
                line-height: 21px;
                letter-spacing: 0.02em;
                color: #1E1E1E;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
              }
              > span:nth-child(2) {
                font-weight: normal;
                font-size: 12px;
                line-height: 18px;
                letter-spacing: 0.15px;
                color: #70889E;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
              }
            }
          }
        }

        &-news {
          margin-top: 26px;
          width: 100%;
          display: flex;
          flex-direction: column;
          > div {
            width: 100%;
            margin-bottom: 12px;
            background: rgba(255, 233, 157, 0.5);
            border-radius: 6px;
            display: flex;
            .news-icon {
              padding: 0 12px;
              display: flex;
              justify-content: center;
              align-items: center;
            }
            .news-info {
              display: flex;
              flex-direction: column;
              padding: 10px 8px 5px 0;
              > span {
                font-weight: 500;
                font-size: 14px;
                line-height: 21px;
                letter-spacing: 0.02em;
                color: #1E1E1E;
              }
              > a {
                text-decoration: none;
                font-style: normal;
                font-weight: normal;
                font-size: 12px;
                line-height: 18px;
                letter-spacing: 0.15px;
                color: #70889E;
              }
            }
          }
        }
      }
    }
  }
}
</style>
