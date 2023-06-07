<template>
  <div id="sidebarExtrasUser">
    <div class="sidebar-extras-user">
      <div
        class="sidebar-extras-user__img"
        v-if="!dataSidebar.subject.image"
      >
        <img :src="dataSidebar.subject.icon">
        <div class="sidebar-extras-user__img-info">
          <span>{{ dataSidebar.subject.name | truncate(100) }}</span>
        </div>
      </div>
      <div
        class="sidebar-extras-user__image"
        v-else
        :style="{ backgroundImage: `url(${dataSidebar.subject.image})` }"
      >
        <div class="sidebar-extras-user__image-info">
          <span>{{ dataSidebar.subject.name | truncate(100) }}</span>
        </div>
      </div>
      <div class="sidebar-extras-user__info">
        <div class="sidebar-extras-user__info-title">
          <svg
            width="17"
            height="20"
            viewBox="0 0 17 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M13.2702 17.8513C13.2702 19.0362 12.3064 20 11.1218 20H2.14867C0.963778 20 0 19.0362 0 17.8513V5.28911C0 4.10422 0.963778 3.14067 2.14867 3.14067H3.14089V2.49733C3.14089 1.12044 4.10467 0 5.28911 0H7.08378H11.5904C11.9182 0 12.3407 0.174889 12.5722 0.406667L16.0042 3.83844C16.2362 4.07022 16.4111 4.49244 16.4111 4.82044V9.32689V14.3622C16.4111 15.7391 15.2909 16.8593 13.9138 16.8593H13.2702V17.8513ZM15.2033 4.03813L12.3731 1.20768V3.3199C12.3731 3.71568 12.6949 4.03813 13.0909 4.03813H15.2033ZM3.84829 2.4966C3.84829 1.50949 4.49451 0.70638 5.28917 0.70638H7.08384H11.5905C11.6132 0.70638 11.6387 0.709714 11.6654 0.714158V3.31905C11.6654 4.10505 12.3047 4.74438 13.091 4.74438H15.6956C15.7003 4.77105 15.7036 4.7966 15.7036 4.81971V9.32593V14.3615C15.7036 15.3482 14.9007 16.1513 13.9136 16.1513H5.63829C4.65117 16.1513 3.84806 15.3484 3.84806 14.3615V2.4966H3.84829ZM0.707601 5.28877V17.851C0.707601 18.6457 1.35405 19.2921 2.14871 19.2921H11.1218C11.9163 19.2921 12.5625 18.6457 12.5625 17.851V16.859H5.63827C4.26138 16.859 3.14093 15.7388 3.14093 14.3619V3.84766H2.14871C1.35405 3.84766 0.707601 4.49388 0.707601 5.28877Z"
              fill="#1F8EFA"
            />
            <path
              d="M6.54 12.3716H13.0116C13.2069 12.3716 13.3653 12.2134 13.3653 12.0178C13.3653 11.8223 13.2069 11.6641 13.0116 11.6641H6.54C6.34488 11.6641 6.18622 11.8223 6.18622 12.0178C6.18622 12.2134 6.34488 12.3716 6.54 12.3716Z"
              fill="#1F8EFA"
            />
            <path
              d="M6.54 9.22926H13.0116C13.2069 9.22926 13.3653 9.07082 13.3653 8.87526C13.3653 8.67971 13.2069 8.52148 13.0116 8.52148H6.54C6.34488 8.52148 6.18622 8.67971 6.18622 8.87526C6.18622 9.07082 6.34488 9.22926 6.54 9.22926Z"
              fill="#1F8EFA"
            />
            <path
              d="M6.54 6.0921H10.3196C10.5151 6.0921 10.6733 5.93365 10.6733 5.73832C10.6733 5.54321 10.5151 5.38477 10.3196 5.38477H6.54C6.34489 5.38477 6.18622 5.54321 6.18622 5.73832C6.18622 5.93365 6.34489 6.0921 6.54 6.0921Z"
              fill="#1F8EFA"
            />
          </svg>
          <span>Дополнительные материалы</span>
        </div>
        <div
          class="sidebar-extras-user__info-body"
          v-if="dataSidebar.resources && dataSidebar.resources.length !== 0"
        >
          <div
            class="sidebar-extras-user__info-body-file"
            v-for="(item, i) in dataSidebar.resources"
            :key="i"
          >
            <a :href="item.quickViewUrl"
               download=""
            ><file-icon :type="getFileIconType(item)" :small="true" /></a>
            <a v-if="item.viewUrl"
               :href="item.viewUrl"
            ><span>{{ item.title | truncate(100) }}</span></a>
            <span v-else>{{ item.title | truncate(100) }}</span>
          </div>
        </div>

        <hm-empty v-else empty-type="full" sub-label="Дополнительные материалы в курсе не созданы" />

      </div>
    </div>
  </div>
</template>

<script>
import FileIcon from "@/components/icons/file-icon/index";
import HmEmpty from "@/components/helpers/hm-empty/index";
import getFileIconType from "@/utilities/getFileIconType";
export default {
  components: { FileIcon, HmEmpty },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  mounted() {
    console.log(this.dataSidebar);
  },
  /**
   * TODO отдавать с backend строку (`FilesModel::toHumanReadable()`)
   */
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
  $image-height: 164px;

  #sidebarExtrasUser {
    width: 100%;
    height: 100%;
    .sidebar-extras-user {
      width: 100%;
      height: 100%;
      overflow: auto;
      &__img {
        width: 100%;
        height: $image-height;
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
          overflow: hidden;
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
        max-height: calc(100% - #{$image-height});

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
        &-empty {
          margin: 20px 0;
        }
        &-body {
          width: 100%;
          height: auto;
          padding: 0 8px 25px 8px;
          margin-top: 17px;
          &-file {
            height: 28px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            line-height: 18px;
            > a {
              cursor: default;
              text-decoration: none;
              > span {
                font-weight: normal;
                font-size: 14px;
                line-height: 16px !important;
                letter-spacing: 0.02em;
                color: #1e1e1e;
                cursor: default;
              }
            }
            > span {
              font-weight: normal;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #1e1e1e;
              cursor: default;
            }
            > a {
              text-decoration: none;
              cursor: pointer;
              > div {
                margin-right: 12px;
              }
              > span {
                cursor: pointer;
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

      &__base {
        width: 100%;
        height: 36px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 34px 0 15px 0;
        &-title {
          width: 294px;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          text-decoration: none;
          background: #FF9800;
          border-radius: 4px;
          .settings {
            margin-right: 10px;
          }
          > span {
            font-weight: 500;
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
