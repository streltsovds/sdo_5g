<template>
  <div id="sidebarExtrasManager">
    <div class="sidebar-extras-manager">
      <div class="sidebar-extras-manager__img"
           v-if="!dataSidebar.subject.image"
      >
        <img :src="dataSidebar.subject.icon">
        <div class="sidebar-extras-manager__img-info">
          <span>{{ dataSidebar.subject.name | truncate(100) }}</span>
        </div>
        <div class="sidebar-extras-manager__img-edit">
          <hm-sidebar-button-edit v-if="showEditButton" :link="dataSidebar.editUrl" />
        </div>
      </div>
      <div class="sidebar-extras-manager__image"
           v-else
           :style="{ backgroundImage: `url(${dataSidebar.subject.image})` }"
      >
        <div class="sidebar-extras-manager__image-info">
          <span>{{ dataSidebar.subject.name | truncate(100) }}</span>
        </div>
        <div class="sidebar-extras-manager__image-edit">
          <hm-sidebar-button-edit v-if="showEditButton" :link="dataSidebar.editUrl" title="Редактировать учебный курс" />
        </div>
      </div>
      <div class="sidebar-extras-manager__info">
        <div class="sidebar-extras-manager__info-title">
          <icon-sheets color="#1F8EFA" />
          <span>Дополнительные материалы</span>
        </div>

        <div v-if="listFile && listFile.length">
          <div class="sidebar-extras-manager__info-add">
            <a :href="dataSidebar.createUrl">
              <svg-icon
                name="add"
                style="width: 18px; height: 18px"
                color="#1F8EFA"
                stroke-width="0.3"
              />
              <span title="Создать дополнительный материал">Создать</span>
            </a>
          </div>
          <div class="sidebar-extras-manager__info-body">
            <div
              class="sidebar-extras-manager__info-body-file"
              v-for="(item, i) in listFile"
              :key="i"
            >
              <a class="sidebar-extras-manager__info-body-file__icon" :href="item.quickViewUrl" download="">
                <file-icon :type="getFileIconType(item)" :small="true" />
              </a>
              <a class="sidebar-extras-manager__info-body-file__title" v-if="item.viewUrl" :href="item.viewUrl">
                <span>{{ item.title | truncate(20) }}</span>
              </a>
              <span class="sidebar-extras-manager__info-body-file__title" v-else>{{ item.title | truncate(20) }}</span>
              <div class="sidebar-extras-manager__info-body-file__actions">
                <v-menu offset-y offset-x left>
                  <template v-slot:activator="{ on }">
                    <div class="button-shape" v-on="on">
                      <icon-dots-vertical width="4" />
                    </div>
                  </template>
                  <div class="sidebar-extras-manager__info-body-file__actions-menu">
                    <edit-action-button
                      v-for="action in [{name:'редактировать',iconName:'Edit', url: item.editUrl},{name:'удалить',iconName:'Delete', url: item.deleteUrl, confirm: true}]"
                      :title="_(action.name)"
                      :icon-name="action.iconName"
                      :action-url="action.url"
                      :confirm-action="item.confirm"
                    />
                  </div>
                </v-menu>
              </div>
            </div>
          </div>
        </div>

        <hm-empty v-else :sub-label="generateURLCreate" empty-type="full" />

      </div>
      <div class="sidebar-extras-manager__form">
        <hm-file-downloader
          :url="fileDownloaderUrl"
          :hash="dataSidebar.elFinder.attribs.folderHash"
          @dataRes="dataResParent"
          text="Или перетащите файлы сюда"
          placeholder="Будут автоматически созданы дополнительные материалы на основе каждого загруженного файла."
        />
      </div>
    </div>
  </div>
</template>

<script>
import FileIcon from "@/components/icons/file-icon/index";
import SvgIcon from "@/components/icons/svgIcon";
import EditActionButton from "@/components/els/subject/lessons/editActionButton";
import HmFileDownloader from "@/components/media/hm-file-downloader/index";
import HmSidebarButtonEdit from "@/components/els/hm-actions/buttons/sidebar-edit/index";
import HmEmpty from "@/components/helpers/hm-empty";
import iconSheets from "@/components/icons/items/iconSheets";
import iconDotsVertical from "@/components/icons/items/iconDotsVertical";
import getFileIconType from "@/utilities/getFileIconType";

export default {
  components: {
    EditActionButton,
    FileIcon,
    HmEmpty,
    HmFileDownloader,
    HmSidebarButtonEdit,
    iconSheets,
    iconDotsVertical,
    SvgIcon,
  },
  props: {
    dataSidebar: {
      type: [Object, Array],
      default: () => {},
    },
  },
  data() {
    return {
      //:TODO написать хранилище для всех файлов, что бы потом при загрузке ловить и сохранять
      listFile: []
    }
  },
  computed: {
    fileDownloaderUrl() {
      return `/storage/index/elfinder/subject_id/${this.dataSidebar.subject.subid}/subject/subject-extra-materials`
    },
    generateURLCreate() {
      return 'Дополнительные материалы в курсе не созданы.' +
        ` <a href='/subject/extra/create/subject_id/${this.dataSidebar.subject.subid}'>` +
        'Создать' +
        '</a>';
    },
    showEditButton() {
      // Список ролей, которым доступно редактирование курса
      return ['dean'].includes(this.dataSidebar.currentUser.role);
    }
  },
  mounted() {
    this.listFile = this.dataSidebar.resources || [];
  },
  methods: {
    dataResParent(data) {
      this.listFile = this.listFile.concat(data)
    },
    getFileIconType
  },
};
</script>

<style lang="scss">
#sidebarExtrasManager {
  width: 100%;
  height: 100%;
  .sidebar-extras-manager {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    overflow: auto;
    &__img {
      width: 100%;
      height: 164px;
      min-height: 164px;
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

    &__info {
      width: 100%;
      padding: 16px;
      margin-top: 16px;
      max-height: 60% !important;
      overflow: auto;
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
        padding: 0 0 25px 12px;
        margin-top: 17px;
        &-file {
          min-height: 22px;
          display: flex;
          justify-content: flex-start;
          align-items: center;
          &__icon {
            width: 12%;
            cursor: default;
            text-decoration: none;
            > span {
              font-weight: normal;
              font-size: 14px;
              line-height: 21px;
              letter-spacing: 0.02em;
              color: #1e1e1e;
              cursor: default;
            }
          }
          &__title {
            width: 80%;
            cursor: default;
            text-decoration: none;
            > span {
              font-weight: normal;
              font-size: 14px;
              line-height: 21px;
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
            text-transform: capitalize;
            cursor: default;
          }
          > a {
            text-decoration: none;
            cursor: pointer;
            > span {
              cursor: pointer;
            }
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
      &-add {
        width: 100%;
        height: 21px;
        display: flex;
        justify-content: center;
        margin: 24px 0 0 12px;
        > a {
          text-decoration: none;
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          > svg {
            margin-right: 12px;
          }
          > span {
            font-weight: 400;
            font-size: 14px;
            line-height: 24px;
            letter-spacing: 0.02em;
            color: #70889e;
          }
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
        border: 1px solid #4A90E2;
        border-radius: 4px;
        .settings {
          margin-right: 10px;
        }
        > span {
          font-weight: 500;
          font-size: 14px;
          line-height: 21px;
          letter-spacing: 0.02em;
          color:  #000000;
        }
      }
    }

    &__form {
      width: 100%;
      max-height: 140px;
      height: auto;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 0 16px;
      margin-top: 15px;
      margin-bottom: 15px;
    }
  }

  .sidebar-extras-manager__info-body-file__actions {
      display: flex;
      .button-shape {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        cursor: pointer;
        > svg {
          fill: #212121;

          path {
            /**
             * TODO inherit может не работатб в некоторых браузерах
             * https://caniuse.com/?search=inherit
             */
            fill: inherit !important;
          }
        }
        &:hover {
          background: rgba(102, 102, 102, 0.14);
          > svg {
            fill: #3796eb;
          }
        }
      }
  }
  .hm-file-downloader__placeholder {
    span {
      font-size: 13px;
    }
  }
}
.sidebar-extras-manager__info-body-file__actions-menu {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  box-shadow: 0 8px 10px rgba(0, 0, 0, 0.2),
  0 6px 30px rgba(0, 0, 0, 0.12);
  border-radius: 4px;
  > a:hover {
    > svg {
    }
  }
}
</style>
