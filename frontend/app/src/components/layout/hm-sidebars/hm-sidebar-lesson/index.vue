<template>
  <div id="sidebarlesson">
    <div class="sidebar-lesson">
      <div
        v-if="!dataSidebar.subject.image"
        class="sidebar-lesson__img">
        <img :src="dataSidebar.subject.icon">
        <div class="sidebar-lesson__img-info">
          <span>{{ dataSidebar.subject.name | truncate(100)}}</span>
        </div>
      </div>
      <div
        v-else
        class="sidebar-lesson__image"
        :style="{ backgroundImage: `url(${dataSidebar.subject.image})` }"
      >
        <div class="sidebar-lesson__image-info">
          <span>{{ dataSidebar.subject.name | truncate(100)}}</span>
        </div>
      </div>
      <div  class="sidebar-lesson-info"
        v-if="dataSidebar.nextLesson">
        <div class="sidebar-lesson-info__next-occupation">
          <div class="sidebar-lesson-info__next-occupation-title">
            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M2.49756 20H13.2153C14.5924 20 15.7127 18.8796 15.7127 17.5027V11.0718V5.60578C15.7127 5.27778 15.538 4.85578 15.306 4.62378L11.0893 0.406889C10.8576 0.174889 10.4351 0 10.1073 0H4.64089H2.49733C1.12022 0 0 1.12044 0 2.49733V17.5024C0 18.8796 1.12044 20 2.49756 20ZM10.8899 1.20607L10.8901 1.20629V4.10363C10.8901 4.4994 11.2119 4.82118 11.6079 4.82118H14.5055L10.8901 1.20629V1.20607H10.8899ZM2.49703 0.707031C1.50992 0.707031 0.707031 1.51014 0.707031 2.49703H0.707253V17.5021C0.707253 18.4893 1.51036 19.2921 2.49725 19.2921H13.215C14.2024 19.2921 15.005 18.489 15.005 17.5021V11.0715V5.60525C15.005 5.58236 15.0017 5.55703 14.997 5.53036H11.6075C10.8213 5.53036 10.1819 4.89081 10.1819 4.10481V0.715031C10.1553 0.710587 10.1297 0.707031 10.107 0.707031H4.64059H2.49703Z" fill="#4A90E2"/>
              <path d="M3.92309 14.6414H11.7898C11.9853 14.6414 12.1438 14.4831 12.1438 14.2876C12.1438 14.0918 11.9853 13.9336 11.7898 13.9336H3.92309C3.72754 13.9336 3.56909 14.0918 3.56909 14.2876C3.56909 14.4831 3.72754 14.6414 3.92309 14.6414Z" fill="#4A90E2"/>
              <path d="M3.92334 10.8914H11.79C11.9856 10.8914 12.144 10.7329 12.144 10.5374C12.144 10.3423 11.9856 10.1836 11.79 10.1836H3.92334C3.72778 10.1836 3.56934 10.3423 3.56934 10.5374C3.56934 10.7329 3.72778 10.8914 3.92334 10.8914Z" fill="#4A90E2"/>
              <path d="M3.92334 7.14115H8.57489C8.77045 7.14115 8.92867 6.98293 8.92867 6.78737C8.92867 6.59226 8.77045 6.43359 8.57489 6.43359H3.92334C3.72778 6.43359 3.56934 6.59226 3.56934 6.78737C3.56934 6.98293 3.72778 7.14115 3.92334 7.14115Z" fill="#4A90E2"/>
            </svg>
            <span>{{ _('Следующее занятие') }}</span>
          </div>
          <div class="sidebar-lesson-info__next-occupation-info">
            <a :href="dataSidebar.nextLesson.executeUrl ">
              <div class="sidebar-lesson-info__next-occupation-info__title">
                <div class="sidebar-lesson-info__next-occupation-info__title-icon" :style="{backgroundImage: dataSidebar.nextLesson.icon ?  `url(${dataSidebar.nextLesson.icon})` : ''}">
                </div>
                <div class="sidebar-lesson-info__next-occupation-info__title-info">
                  <a :href="dataSidebar.nextLesson.executeUrl ">{{ dataSidebar.nextLesson.title }}</a>
                </div>
              </div>
              <div class="sidebar-lesson-info__next-occupation-info__description"
                   v-if="dataSidebar.nextLesson.descript">
                <span>{{ dataSidebar.nextLesson.descript }}</span>
              </div>
            </a>
            <div class="sidebar-lesson-info__next-occupation-info__date">
              <div class="sidebar-lesson-info__next-occupation-info__date-description">
                <div class="sidebar-lesson-info__next-occupation-info__date-descriptio-begin" v-if="dataSidebar.nextLesson.lessonDate.begin && dataSidebar.nextLesson.lessonDate.begin !== ''">
                  <span class="date__title">Начало занятия</span>
                  <div class="date__data" ><svg-icon name="timeStart" /><span>{{ dataSidebar.nextLesson.lessonDate.begin }}</span></div>
                </div>
                <div class="sidebar-lesson-info__next-occupation-info__date-descriptio-begin" >
                  <span  class="date__title">Окончание занятия</span>
                  <div class="date__data" v-if="dataSidebar.nextLesson.lessonDate.end && dataSidebar.nextLesson.lessonDate.end !== '' "><svg-icon name="timeEnd" /><span>{{ dataSidebar.nextLesson.lessonDate.end }}</span></div>
                  <div class="date__data" v-else><svg-icon name="timeEnd" /><span>не ограничено</span></div>
                </div>
              </div>

            </div>
            <!--div class="sidebar-lesson-info__next-occupation-info__state">
              <span>Занятие будет доступно при условии:</span>
            </div-->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SvgIcon from "@/components/icons/svgIcon";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
export default {
  components: {HmSidebarButtonEdit, SvgIcon},
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  mounted() {
  },
};
</script>

<style lang="scss">
#sidebarlesson {
  width: 100%;
  height: 100%;
  .sidebar-lesson {
    width: 100%;
    height: 100%;
    overflow: auto;
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
      &-info {
        width: 100%;
        height: 70px;
        position: absolute;
        bottom: 0;
        left: 0;
        display: flex;
        justify-content: center;
        align-items: center;
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

    &__info {
      width: 100%;
      padding: 16px;
      &-title {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        > svg {
          margin-right: 12px;
        }
        > span {
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          letter-spacing: 0.02em;
          color: #1e1e1e;
        }
      }
      &-body {
        width: 100%;
        height: auto;
        padding: 0 8px 25px 8px;
        margin-top: 17px;
        &-file {
          height: 22px;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          > a {
            cursor: default;
            text-decoration: none;
            > span {
              font-weight: normal;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #1e1e1e;
              text-transform: capitalize;
              cursor: default;
            }
          }
          > span {
            font-weight: normal;
            font-size: 14px;
            line-height: 21px;
            letter-spacing: 0.02em;
            color: #1e1e1e;
            text-transform: capitalize;
            cursor: default;
          }
          > a {
            text-decoration: none;
            cursor: default;
            > div {
              margin-right: 12px;
            }
          }
          &:hover {
            span {
              color: #2960a0;
            }
            > a > span {
              color: #2960a0;
            }
          }
        }
        &-file:not(:first-child) {
          margin-top: 17px;
        }
      }
    }

    &-info {
      width: 100%;
      height: auto;
      padding: 16px;
      &__next-occupation {
        margin-top: 26px;
        width: 100%;
        &-title {
          width: 100%;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          margin-bottom: 11px;
          > svg {
            margin-right: 13px;
          }
          > span {
            font-weight: 500;
            font-size: 16px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #1E1E1E;
          }
        }
        &-info {
          width: 100%;
          display: flex;
          flex-direction: column;
          justify-content: flex-start;
          align-items: center;
          margin-top: 16px;
          padding-left: 8px;
          > a {
            text-decoration: none;
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
          }
          &__title {
            overflow: hidden;
            width: 100%;
            height: 80px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            &-icon {
              width: 60px;
              height: 56px;
              display: flex;
              justify-content: center;
              align-items: center;
              background-size: contain;
              background-repeat: no-repeat;
            }
            &-info {
              width: calc(100% - 40px);
              height: 100%;
              display: flex;
              justify-content: flex-start;
              align-items: flex-start;
              padding-left: 12.5px;
              padding-top: 8px;
              > a {
                text-decoration: none;
                font-weight: 500;
                font-size: 14px;
                line-height: 21px;
                letter-spacing: 0.02em;
                color: #1E1E1E;
              }
            }

          }

          &__date {
            width: 100%;
            display: flex;
            flex-direction: column;
            margin-top: 16px;
            &-description {
              width: 100%;
              display: flex;
              > div {
                width: calc(50% - 8px);
                margin-right: 16px;
                display: flex;
                flex-direction: column;
                &:last-child {
                  margin-right: 0;
                }
                .date__title {
                  font-weight: normal;
                  font-size: 12px;
                  line-height: 18px;
                  letter-spacing: 0.15px;
                  color: #696969;
                  margin-bottom: 5px;
                }
                .date__data {
                  display: flex;
                  align-items: center;
                  justify-content: flex-start;
                  > svg {
                    margin-right: 6px;
                  }
                  > span {
                    font-weight: 500;
                    font-size: 13px;
                    line-height: 21px;
                    letter-spacing: 0.02em;
                    color: #696969;
                  }
                }
              }
            }

            &-title {
              width: 100%;
              height: 24px;
              margin-bottom: 16px;
              display: flex;
              justify-content: flex-start;
              align-items: center;
              > svg {
                margin-right: 12px;
              }
              > span {
                font-weight: 500;
                font-size: 16px;
                line-height: 24px;
                letter-spacing: 0.02em;
                color: #1E1E1E;
              }
            }

          }

          &__description {
            width: 100%;
            display: flex;
            margin-top: 16px;
            overflow: hidden;
            max-height: 110px;
            > span {
              font-weight: normal;
              font-size: 12px;
              line-height: 18px;
              letter-spacing: 0.15px;
              color: #3E4E6C;
            }
          }

          &__state {
            width: 100%;
            max-height: 46px;
            overflow: hidden;
            margin-top: 8px;
            > span {
              font-weight: 500;
              font-size: 12px;
              letter-spacing: 0.15px;
              color: #70889E;
            }
          }
        }
      }
    }
  }
}
</style>
